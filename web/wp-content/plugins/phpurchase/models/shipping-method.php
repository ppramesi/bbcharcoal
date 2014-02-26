<?php
class PHPurchaseShippingMethod extends PHPurchaseModelAbstract {
  
  public function __construct($id=null) {
    $this->_tableName = PHPurchaseCommon::getTableName('shipping_methods');
    parent::__construct($id);
  }

  public function deleteMe() {
    if($this->id > 0) {
      $ratesTable = PHPurchaseCommon::getTableName('shipping_rates');
      $sql = "DELETE from $ratesTable where shipping_method_id=" . $this->id;
      $this->_db->query($sql);
      parent::deleteMe();
    }
  }
  
  public function getMethodsForCarrier($carrier) {
    $methods = array();
    $shippingMethods = PHPurchaseCommon::getTableName('shipping_methods');
    $sql = "SELECT name, code from $shippingMethods where carrier='$carrier'";
    $results = $this->_db->get_results($sql);
    foreach($results as $m) {
      $methods[$m->name] = $m->code;
    }
    return $methods;
  }
  
  /**
   * Only save shipping methods if the carrier code combo does not exist.
   */
  public function save() {
    $save = true;
    
    $shippingMethods = PHPurchaseCommon::getTableName('shipping_methods');
    if(!empty($this->carrier) && !empty($this->code)) {
      $sql = "SELECT id from $shippingMethods where carrier=%s and code=%s";
      $sql = $this->_db->prepare($sql, $this->carrier, $this->code);
      $id = $this->_db->get_var($sql);
      $save = $id === NULL;
    }
    
    if($save) {
      parent::save();
    }
  }
  
  /**
   * Delete all methods for the given carrier if the carrier code is not in the given array
   */
  public function pruneCarrierMethods($carrier, array $codes) {
    $codes = implode(',', $codes);
    $shippingMethods = $this->_tableName;
    $sql = "DELETE from $shippingMethods where carrier='$carrier' and code NOT IN ($codes)";
    $this->_db->query($sql);
  }
  
  public function isLiveMethod($id=null) {
    $id = empty($id) ? $this->id : $id;
    $m = new PHPurchaseShippingMethod($id);
    return !empty($m->code);
  }
  
  public function clearAllLiveRates() {
    $shippingMethods = $this->_tableName;
    $sql = "DELETE from $shippingMethods where carrier != ''";
    $this->_db->query($sql);
  }
  
}
