<?php
class PHPurchaseShippingRule extends PHPurchaseModelAbstract {
  
  public function __construct($id=null) {
    $this->_tableName = PHPurchaseCommon::getTableName('shipping_rules');
    parent::__construct($id);
  }
  
}