<?php
class PHPurchaseUpdater {
  
  protected $_version;
  protected $_orderNumber;
  protected $_motherShipUrl = 'http://www.cart66.com/phpurchase-latest.php';
  
  public function __construct() {
    $setting = new PHPurchaseSetting();
    $this->_version = $setting->lookupValue('version');
    $this->_orderNumber = $setting->lookupValue('order_number');
  }
  
  /**
   * Check the currently running versoin against the version of the latest release.
   * 
   * @return mixed The new version number if there is a new version, otherwise false.
   */
  public function newVersion() {
    $setting = new PHPurchaseSetting();
    $orderNumber = $setting->lookupValue('orderNumber');

    $versionCheck = $this->_motherShipUrl . "?task=getLatestVersion&id=$this->_orderNumber";
    $newVersion = false;
    
    $latest = @file_get_contents($versionCheck);
    if(!empty($latest)) {
      if($latest != $this->_version) {
        $newVersion = $latest;
      }
    }
    
    return $newVersion;
  }
  
  public function getCallHomeUrl() {
    return $this->_motherShipUrl;
  }
  
  public function getOrderNumber() {
    return $this->_orderNumber;
  }

}