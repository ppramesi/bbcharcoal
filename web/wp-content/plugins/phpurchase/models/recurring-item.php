<?php
require_once dirname(__FILE__) . '/recurring-transaction.php';

$gw = PHPurchaseCommon::gatewayName();
if($gw == 'quantum') {
  require_once dirname(__FILE__) . '/../pro/Quantum/vault-customer.php';
}
elseif($gw == 'authnet') {
  require_once dirname(__FILE__) . '/../pro/Authnet/cim.php';
}

class PHPurchaseRecurringItem extends PHPurchaseModelAbstract {
  
  public function __construct($id=null) {
    $this->_tableName = PHPurchaseCommon::getTableName('recurring_items');
    parent::__construct($id);
  }
  
  public static function getActiveSubscriptions() {
    global $wpdb;
    $recurringItems = PHPurchaseCommon::getTableName('recurring_items');
    $today = date('Y-m-d 00:00:00');
    $sql = "SELECT * from $recurringItems where status='active' and start_date <= '$today'";
    $items = $wpdb->get_results($sql);
    return $items;
  }
  
  /**
   * Return an array of recurring_item ids ready to be charged
   */
  public static function getSubscriptionsToCharge() {
    $ready = array();
    $subscriptions = self::getActiveSubscriptions();
    if(is_array($subscriptions)) {
      $today = strtotime(date('m/d/Y'));
      
      foreach($subscriptions as $s) {
        if($s->payment_type == 'cc') {
          $nextPaymentDate = self::getNextPaymentFor($s->id);
          if($nextPaymentDate) {
            $nextTs = strtotime($nextPaymentDate);
            if($nextTs <= $today) {
              $ready[] = $s->id;
            }
          }
        }
        
      }
      
    }
    return $ready;
  }
  
  public static function chargeSubscriptions($subscriptionsIds) {
    $gw = PHPurchaseCommon::gatewayName();
    if($gw == 'quantum') {
      self::chargeSubscriptionsQuantum($subscriptionsIds);
    }
    elseif($gw == 'authnet') {
      self::chargeSubscriptionsAuthnet($subscriptionsIds);
    }
  }
  
  /**
   * Attempt to charge all of the subscriptions referenced by their recurring item id
   * Return the number of subscriptions that were charged
   * 
   * @param array Recurring item ids
   * @return int
   */
  public static function chargeSubscriptionsAuthnet($subscriptionIds) {
    PHPurchaseCommon::log("CIM charging subscriptions: " . count($subscriptionIds));
    $numCharged = 0;
    if(is_array($subscriptionIds)) {
      $customer = new CIM();
      
      foreach($subscriptionIds as $id) {
        
        // Get payment due date for subscription - this could be today or some day in the past
        $dueDate = self::getNextPaymentFor($id);
        if($dueDate) {
          $item = new PHPurchaseRecurringItem($id);
          if($item->amount > 0) {
            
            // Charge the customer
            $customer->load($item->account_id, false);
            $result = $customer->charge($item->amount);
            if(is_scalar($result)) {
              PHPurchaseCommon::log("Charged customer $customer->id for $item->amount");
              $rt = new PHPurchaseRecurringTransaction();
              $rt->log($id, $item->amount, $result, $dueDate);
              
              // Check to see if this is the last transaction and update the subscription status to completed if it is
              if($item->recurring_occurrences > 0) {
                self::getNextPaymentFor($id); // This function will set the recurring item status to completed if all payments have been made
              }
                            
              // Send recurring transaction email receipt
              $customer->sendRecurringChargeSuccessEmail($id);
            }
            else {
              // Set status to overdue
              $item->status = "overdue";
              $item->save();
              $customer->sendRecurringChargeFailureEmail($id);
            }
            
          }
        }
      }
      
    }
    return $numCharged;
  }
  
  /**
   * Attempt to charge all of the subscriptions referenced by their recurring item id
   * Return the number of subscriptions that were charged
   * 
   * @param array Recurring item ids
   * @return int
   */
  public static function chargeSubscriptionsQuantum($subscriptionIds) {
    $numCharged = 0;
    if(is_array($subscriptionIds)) {
      $vault = new Quantum_Vault();
      
      foreach($subscriptionIds as $id) {
        
        // Get payment due date for subscription - this could be today or some day in the past
        $dueDate = self::getNextPaymentFor($id);
        if($dueDate) {
          $item = new PHPurchaseRecurringItem($id);
          if($item->amount > 0) {
            
            // Charge the customer
            $customer = self::getVaultCustomerForSubscription($id);
            $response = $vault->createTransaction($customer, $item->amount);
            $xml = new SimpleXMLElement($response);
            $result = $xml->Result[0];
            PHPurchaseCommon::log("Charged customer $customer->id for $item->amount");
            
            if(strtolower($result->Status) == "approved") {
              // Log the transaction
              PHPurchaseCommon::log("Charge to customer was a success: $result->TransactionID");
              $rt = new PHPurchaseRecurringTransaction();
              $rt->log($id, $result->Amount, $result->TransactionID, $dueDate);
              
              // Check to see if this is the last transaction and update the subscription status to completed if it is
              if($item->recurring_occurrences > 0) {
                self::getNextPaymentFor($id); // This function will set the recurring item status to completed if all payments have been made
              }
                            
              // Send recurring transaction email receipt
              $customer->sendRecurringChargeSuccessEmail($id);
            }
            else {
              // Set status to overdue
              $item->status = "overdue";
              $item->save();
              $customer->sendRecurringChargeFailureEmail($id);
            }
            
          }
        }
      }
      
    }
    return $numCharged;
  }
  
  public static function getVaultCustomerForSubscription($subscriptionId) {
    $customer = false;
    $recurringItem = new PHPurchaseRecurringItem($subscriptionId);
    if($recurringItem->account_id > 0) {
      $customer = new Quantum_VaultCustomer($recurringItem->account_id);
      if($customer->id < 1) {
        throw new Exception("Unalbe to get vault customer for the given subscription id: $subscriptionId");
      }
    }
    else {
      throw new Exception("Unalbe to get recurring item for the given subscription id: $subscriptionId");
    }
    return $customer;
  }
  
  public static function getLastPaymentFor($recurringItemId) {
    global $wpdb;
    $recurringItemId = $wpdb->escape($recurringItemId);
    $recurringTrans = PHPurchaseCommon::getTableName('recurring_transactions');
    $sql = "SELECT DATE_FORMAT(payment_due_on, '%m/%d/%Y') as payment_due_on 
            FROM $recurringTrans 
            WHERE recurring_item_id = $recurringItemId
            ORDER BY id desc
            LIMIT 1";
    $dueDate = $wpdb->get_var($sql);
    if($dueDate) {
      $dueDate = date('n/j/Y', strtotime($dueDate));
    }
    PHPurchaseCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Due date: $dueDate\nDerived from SQL: $sql");
    return $dueDate;
  }
  
  /**
   * Return the next payment date as a string or false if all payments are completed.
   */
  public static function getNextPaymentFor($recurringItemId) {
    global $wpdb;
    $nextPayment = '';
    $lastPayment = self::getLastPaymentFor($recurringItemId);
    $recurringItems = PHPurchaseCommon::getTableName('recurring_items');
    $recurringItemId = $wpdb->escape($recurringItemId);
    $sql = "SELECT id, DATE_FORMAT(start_date, '%m/%d/%Y') as start_date, recurring_interval, recurring_unit, recurring_occurrences 
            FROM $recurringItems 
            WHERE id = $recurringItemId";
    $item = $wpdb->get_row($sql);
    
    if($lastPayment) {
      // See if total number of required payments has been made
      if($item->recurring_occurrences > 0) {
        $recurringTrans = PHPurchaseCommon::getTableName('recurring_transactions');
        $sql = "SELECT count(*) as numTransactions from $recurringTrans where recurring_item_id = %d";
        $query = $wpdb->prepare($sql, $item->id);
        $numTransactions = $wpdb->get_var($query);
        if($numTransactions >= $item->recurring_occurrences) {
          // All payments are complete so set the status to complete
          $ri = new PHPurchaseRecurringItem($item->id);
          $ri->status = 'complete';
          $ri->save();
          $nextPayment = false;
        }
      }
      
      if($nextPayment !== false) {
        $dateCalc = $lastPayment . ' +' . $item->recurring_interval .  ' ' . $item->recurring_unit;
        $nextPayment = date('n/j/Y', strtotime($dateCalc));
      }
      
    }
    else {
      $nextPayment = $item->start_date;
    }
    
    PHPurchaseCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Next Calculated Payment: $nextPayment\nDerived from SQL: $sql");
    return $nextPayment;
  }
  
  public static function getRecurringItem($id) {
    global $wpdb;
    $recurringItems = PHPurchaseCommon::getTableName('recurring_items');
    $sql = "SELECT * from $recurringItems where id=%d";
    $query = $wpdb->prepare($sql, $id);
    $row = $wpdb->get_row($query);
    return $row;
  }
  
  public function getOrderItemInfo() {
    $orderItems = PHPurchaseCommon::getTableName('order_items');
    $sql = "SELECT * from $orderItems where id = %d";
    $query = $this->_db->prepare($sql, $this->order_item_id);
    $oi = $this->_db->get_row($query);
    return $oi;
  }
}