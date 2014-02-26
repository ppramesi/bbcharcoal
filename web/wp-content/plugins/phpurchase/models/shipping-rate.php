<?php
class PHPurchaseShippingRate extends PHPurchaseModelAbstract {
  
  public function __construct($id=null) {
    $this->_tableName = PHPurchaseCommon::getTableName('shipping_rates');
    parent::__construct($id);
  }
  
}