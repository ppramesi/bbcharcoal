<?php
if(PHPURCHASEPRO) {
  require_once dirname(__FILE__) . '/recurring-item.php';
  require_once dirname(__FILE__) . '/recurring-transaction.php';
}


class PHPurchaseOrder extends PHPurchaseModelAbstract {
  
  protected $_orderInfo = array();
  protected $_items = array();
  
  public function __construct($id=null) {
    $this->_tableName = PHPurchaseCommon::getTableName('orders');
    parent::__construct($id);
  }
  
  public function loadByOuid($ouid) {
    $sql = $this->_db->prepare("SELECT id from $this->_tableName where ouid=%s", $ouid);
    $id = $this->_db->get_var($sql);
    $this->load($id);
  }
  
  public function setInfo(array $info) {
    $this->_orderInfo = $info;
  }
  
  public function setItems(array $items) {
    $this->_items = $items;
  }
  
  public function save() {
    $this->_orderInfo['ouid'] = md5($this->_orderInfo['trans_id'] . $this->_orderInfo['bill_address']);
    PHPurchaseCommon::log('order.php:' . __LINE__ . ' - Saving Order Information (Items: ' . count($this->_items). '): ' . print_r($this->_orderInfo, true));
    $this->_db->insert($this->_tableName, $this->_orderInfo);
    $this->id = $this->_db->insert_id;
    $key = $this->_orderInfo['trans_id'] . '-' . $this->id . '-';
    foreach($this->_items as $item) {
      
      // Deduct from inventory
      PHPurchaseProduct::decrementInventory($item->getProductId(), $item->getOptionInfo(), $item->getQuantity());
      
      $data = array(
        'order_id' => $this->id,
        'product_id' => $item->getProductId(),
        'product_price' => $item->getProductPrice(),
        'item_number' => $item->getItemNumber(),
        'description' => $item->getFullDisplayName(),
        'quantity' => $item->getQuantity(),
        'duid' => md5($key . $item->getProductId())
      );
      
      $formEntryIds = '';
      $fIds = $item->getFormEntryIds();
      if(is_array($fIds) && count($fIds)) {
        $formEntryIds = implode(',', $fIds);
      }
      $data['form_entry_ids'] = $formEntryIds;
      
      if($item->getCustomFieldInfo()) {
        $data['description'] .= "\n" . $item->getCustomFieldDesc() . ":\n" . $item->getCustomFieldInfo();
      }
      
      $orderItems = PHPurchaseCommon::getTableName('order_items');
      $this->_db->insert($orderItems, $data);
      $orderItemId = $this->_db->insert_id;
      PHPurchaseCommon::log("Saved order item ($orderItemId): " . $data['description'] . "\nSQL: " . $this->_db->last_query);
      
      if($item->isSubscription()) {
        $ri = new PHPurchaseRecurringItem();
        
        // Set payment type to one of the possible options - default is credit card (cc)
        $paymentType = 'cc';
        $paymentTypeOptions = array('cc', 'manual');
        if(isset($this->_orderInfo['payment_type']) && in_array($this->_orderInfo['payment_type'], $paymentTypeOptions)) {
          $paymentType = $this->_orderInfo['payment_type'];
        }
        elseif(substr($this->_orderInfo['trans_id'], 0, 3) == 'MT-') {
          $paymentType = 'manual';
        }
        
        $data = array(
          'account_id' => $this->_orderInfo['account_id'],
          'order_item_id' => $orderItemId,
          'amount' => $item->getRecurringPrice(),
          'recurring_interval' => $item->getRecurringInterval(),
          'recurring_unit' => $item->getRecurringUnit(),
          'recurring_occurrences' => $item->getRecurringOccurrences(),
          'status' => 'active',
          'payment_type' => $paymentType,
          'start_date' => $item->getStartDate()
        );
        
        $ri->setData($data);
        $ri->save();
        
        if($item->getProductPrice() > 0) {
          // The subscription was charged, no free trial
          $data = array(
            'account_id' => $this->_orderInfo['account_id'],
            'order_item_id' => $orderItemId,
            'recurring_item_id' => $ri->id,
            'amount' => $item->getProductPrice(),
            'payment_due_on' => date('Y-m-d'),
            'transaction_id' => $this->_orderInfo['trans_id']
          );
          $rt = new PHPurchaseRecurringTransaction();
          $rt->setData($data);
          $rt->save();
          PHPurchaseCommon::log("Saved recurring item: " . print_r($data, true));
        }
        else {
          PHPurchaseCommon::log("Not saving recurring item because the item price is not greater than zero: " . $item->getProductPrice());
        }
      }
      
    }
    return $this->id;
  }
  
  public function getOrderRows($where=null) {
    if(!empty($where)) {
      $sql = "SELECT * from $this->_tableName $where order by ordered_on desc";
    }
    else {
      $sql = "SELECT * from $this->_tableName order by ordered_on desc";
    }
    $orders = $this->_db->get_results($sql);
    return $orders;
  }
  
  public function getItems() {
    $orderItems = PHPurchaseCommon::getTableName('order_items');
    $sql = "SELECT * from $orderItems where order_id = $this->id order by product_price desc";
    $items = $this->_db->get_results($sql);
    return $items;
  }
  
  public function updateStatus($status) {
    if($this->id > 0) {
      $data['status'] = $status;
      $this->_db->update($this->_tableName, $data, array('id' => $this->id), array('%s'));
      return $status;
    }
    return false;
  }
  
  public function deleteMe() {
    if($this->id > 0) {
      
      // Delete attached Gravity Forms if they exist
      $items = $this->getItems();
      foreach($items as $item) {
        if(!empty($item->form_entry_ids)) {
          $entryIds = explode(',', $item->form_entry_ids);
          if(is_array($entryIds)) {
            foreach($entryIds as $entryId) {
              RGFormsModel::delete_lead($entryId);
            }
          } 
        }
        
        // Delete recurring items
        $recurringItems = PHPurchaseCommon::getTableName('recurring_items');
        $sql = "DELETE from $recurringItems where order_item_id = $item->id";
        $this->_db->query($sql);
        
        // Delete recurring transactions
        $recurringTransactions = PHPurchaseCommon::getTableName('recurring_transactions');
        $sql = "DELETE from $recurringTransactions where order_item_id = $item->id";
        $this->_db->query($sql);
      }
      
      // Delete order items
      $orderItems = PHPurchaseCommon::getTableName('order_items');
      $sql = "DELETE from $orderItems where order_id = $this->id";
      $this->_db->query($sql);
      
      // Delete the order
      $sql = "DELETE from $this->_tableName where id = $this->id";
      $this->_db->query($sql);
    }
  }
  
}
