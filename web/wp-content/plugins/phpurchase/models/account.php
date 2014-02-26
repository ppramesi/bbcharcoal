<?php
class PHPurchaseAccount extends PHPurchaseModelAbstract {
  
  public function __construct($id=null) {
    $this->_tableName = PHPurchaseCommon::getTableName('accounts');
    parent::__construct($id);
  }
  
}