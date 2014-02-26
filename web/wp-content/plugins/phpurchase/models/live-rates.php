<?php
class PHPurchaseLiveRates {
  public $toZip;
  public $toCountryCode;
  public $weight;
  public $locType;
  public $rates = array();
  
  public function __construct() {
    $this->rates = array();
  }
  
  public function addRate($service, $rate, $code) {
    $this->rates[] = new PHPurchaseLiveRate($service, $rate, $code);
  }
  
  /**
   * Return an array or PHPurchaseLiveRate objects sorted by rate
   */
  public function getRates() {
    usort($this->rates, array($this, 'sortRate'));
    return $this->rates;
  }
  
  public function clearRates() {
    $this->rates = array();
  }
  
  /**
   * Return the PHPurchaseLiverRate object of the  selected service.
   * If no service has been selected, return the service name of the least expensive service.
   */
  public function getSelected() {
    $rates = $this->getRates();
    $liveRate = $rates[0]; // The least expensive live rate
    foreach($rates as $r) {
      if($r->isSelected) {
        $liveRate = $r;
        break;
      }
    }
    return $liveRate;
  }
  
  /**
   * Set all rates to not selected except the rate with the given service name
   */
  public function setSelected($serviceName) {
    $rates = $this->getRates();
    foreach($rates as $r) {
      $r->isSelected = false;
      if($r->service == $serviceName) {
        $r->isSelected = true;
      }
    }
  }
  
  /**
   * Sort live rate objects based on rate
   */
  private function sortRate(PHPurchaseLiveRate $a, PHPurchaseLiveRate $b) {
    if($a->rate == $b->rate) {
      return 0;
    }
    return ($a->rate > $b->rate) ? 1 : -1;
  }
  
}



class PHPurchaseLiveRate {
  public $service;
  public $rate;
  public $isSelected;
  public $code;
  
  public function __construct($service='', $rate='', $code='', $isSelected=false) {
    $this->service = $service;
    $this->rate = $rate;
    $this->isSelected = $isSelected;
	$this->code = $code;
  }
  
}



/*
$liveRates = new PHPurchaseLiveRates();
$liveRates->addRate('B', 2.00);
$liveRates->addRate('A', 1.00);
$liveRates->addRate('C', 3.00);

$liveRates->setSelected('A');
$liveRates->setSelected('B');
$l = $liveRates->getSelected();
echo $l->service;
*/