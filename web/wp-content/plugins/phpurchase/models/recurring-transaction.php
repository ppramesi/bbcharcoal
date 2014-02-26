<?php
require_once dirname(__FILE__) . '/recurring-item.php';

class PHPurchaseRecurringTransaction extends PHPurchaseModelAbstract {
  
  public function __construct($id=null) {
    $this->_tableName = PHPurchaseCommon::getTableName('recurring_transactions');
    parent::__construct($id);
  }
  
  public function log($recurringItemId, $amount, $transactionId, $dueDate) {
    $ri = new PHPurchaseRecurringItem($recurringItemId);
    $dueDate = date('Y-m-d', strtotime($dueDate));
    $this->account_id = $ri->account_id;
    $this->order_item_id = $ri->order_item_id;
    $this->recurring_item_id = $ri->id;
    $this->amount = $ri->amount;
    $this->payment_due_on = $dueDate;
    $this->transaction_id = $transactionId;
    
    $this->save();
    
    $data = $this->getData();
    PHPurchaseCommon::log("Logged recurring transaction: " . $this->id . print_r($data, true));
    PHPurchaseCommon::log("Recurring transaction SQL: " . $this->getLastQuery());
    
    return $this->id;
  }
  
}