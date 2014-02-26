<?php
abstract class PHPurchaseGatewayAbstract {

  protected $_errors;
  protected $_jqErrors;
  protected $_billing;
  protected $_shipping;
  protected $_payment;
  protected $_taxRate;

  public function getErrors() {
    if(!is_array($this->_errors)) {
      $this->_errors = array();
    }
    return $this->_errors;
  }

  public function getJqErrors() {
    if(!is_array($this->_jqErrors)) {
      $this->_jqErrors = array();
    }
    return $this->_jqErrors;
  }

  public function clearErrors() {
    $this->_errors = array();
    $this->_jqErrors = array();
  }

  public function setBilling($b) {
    $this->_billing = $b;
    $skip = array('address2');
    foreach($b as $key => $value) {
      if(!in_array($key, $skip)) {
        $value = trim($value);
        if($value == '') {
          $keyName = ucwords(preg_replace('/([A-Z])/', " $1", $key));
          $this->_errors['Billing ' . $keyName] = "Billing $keyName required";
          $this->_jqErrors[] = "billing-$key";
        }
      }
    }
  } 

  public function setPayment($p) {
    
    // Remove all non-numeric characters from card number
    if(isset($p['cardNumber'])) {
      $cardNumber = $p['cardNumber'];
      $p['cardNumber'] = preg_replace('/\D/', '', $cardNumber);
    }
    
    $this->_payment = $p;
    
    foreach($p as $key => $value) {
      $value = trim($value);
      if($value == '') {
        $keyName = preg_replace('/([A-Z])/', " $1", $key);
        $this->_errors['Payment ' . $keyName] = "Payment $keyName required";
        $this->_jqErrors[] = "payment-$key";
      }
    }
    if(strlen($p['cardNumber']) < 13) {
      $this->_errors['Payment Card Number'] = 'Invalid credit card number';
      $this->_jqErrors[] = "payment-cardNumber";
    } 

    // For customer information manager 
    if(isset($p['password'])) {
      if($p['password'] != $p['password2']) {
        $this->_errors['Password'] = "Passwords do not match";
        $this->_jqErrors[] = 'payment-password';
      }
    }
  }

  public function setShipping($s) {
    $this->_shipping = $s;
    $skip = array('address2');
    foreach($s as $key => $value) {
      if(!in_array($key, $skip)) {
        $value = trim($value);
        if($value == '') {
          $keyName = preg_replace('/([A-Z])/', " $1", $key);
          $this->_errors['Shipping ' . $keyName] = "Shipping $keyName Required";
          $this->_jqErrors[] = "shipping-$key";
        }
      }
    }
  }

  public function getShipping() {
    return count($this->_shipping) ? $this->_shipping : $this->_billing;
  }

  public function getBilling() {
    return $this->_billing;
  }

  public function getPayment() {
    return $this->_payment;
  }

  /**
   * Return and array with state and zip of shipping location
   * array('state' => 'XX', 'zip' => 'YYYYY');
   *
   * @return array
   */
  public function getTaxLocation() {
    $ship = $this->getShipping();
    $taxLocation = array (
      'state' => $ship['state'],
      'zip' => $ship['zip']
    );
    return $taxLocation;
  }

  /**
   * Return true if the order should be taxed
   *
   * @return boolean
   */
  public function isTaxed() {
    $s = $this->getShipping();
    if(count($s)) {
      $taxRate = new PHPurchaseTaxRate();
      $isTaxed = $taxRate->loadByZip($s['zip']);
      if($isTaxed == false) {
        $isTaxed = $taxRate->loadByState($s['state']);
      }
      $this->_taxRate = $taxRate;
      return $isTaxed;
    }
    else {
      throw new Exception('Unable to determine tax rate because shipping data is unavailable');
    }
  }

  public function taxShipping() {
    if(!isset($this->_taxRate)) {
      $this->isTaxed();
    }
    $taxShipping = ($this->tax_shipping == 1) ? true : false;
    return $taxShipping;
  }

  public function getTaxAmount() {
    $tax = 0;
    if($this->isTaxed()) {
      $taxable = $_SESSION['PHPurchaseCart']->getTaxableAmount();
      if($this->taxShipping()) {
        $taxable += $_SESSION['PHPurchaseCart']->getShippingCost();
      }
      $tax = number_format($taxable * ($this->_taxRate->rate/100), 2);
    }
    return $tax;
  }

  /**
   * Store order in database after successful transaction is processed
   */
  public function saveOrder($total, $tax, $transId, $status, $accountId=0, $shipmentResults) {
    $address = $this->getShipping();
    $b = $this->getBilling();
    $p = $this->getPayment();

    $orderInfo['ship_first_name'] = $address['firstName'];
    $orderInfo['ship_last_name'] = $address['lastName'];
    $orderInfo['ship_address'] = $address['address'];
    $orderInfo['ship_address2'] = $address['address2'];
    $orderInfo['ship_city'] = $address['city'];
    $orderInfo['ship_state'] = $address['state'];
    $orderInfo['ship_zip'] = $address['zip'];
    $orderInfo['ship_country'] = PHPurchaseCommon::getCountryName($address['country']);

    $orderInfo['bill_first_name'] = $b['firstName'];
    $orderInfo['bill_last_name'] = $b['lastName'];
    $orderInfo['bill_address'] = $b['address'];
    $orderInfo['bill_address2'] = $b['address2'];
    $orderInfo['bill_city'] = $b['city'];
    $orderInfo['bill_state'] = $b['state'];
    $orderInfo['bill_zip'] = $b['zip'];
    $orderInfo['bill_country'] = PHPurchaseCommon::getCountryName($b['country']);

    $orderInfo['phone'] = preg_replace("/[^0-9]/", "", $p['phone']);
    $orderInfo['email'] = $p['email'];
    $orderInfo['coupon'] = PHPurchaseCommon::getPromoMessage();
    $orderInfo['tax'] = $tax;
    $orderInfo['shipping'] = $_SESSION['PHPurchaseCart']->getShippingCost();
    $orderInfo['subtotal'] = $_SESSION['PHPurchaseCart']->getSubTotal();
    $orderInfo['total'] = preg_replace("/[^0-9\.]/", "", $total);
    $orderInfo['trans_id'] = $transId;
    $orderInfo['status'] = $status;
    $orderInfo['ordered_on'] = date('Y-m-d H:i:s');
    $orderInfo['shipping_method'] = $_SESSION['PHPurchaseCart']->getShippingMethodName();
    $orderInfo['account_id'] = $accountId;
	$boom = explode('-', $shipmentResults);
	
	$orderInfo['shipping_results'] = serialize($shipmentResults);
    $orderId = $_SESSION['PHPurchaseCart']->storeOrder($orderInfo);
	if(strcasecmp('pallet', $boom[0]) == 0){
		freightHelper::insertFrOrderToDB($orderId, $boom[1], $boom[2], $boom[3], $boom[4], $boom[5]);
	}
    PHPurchaseCommon::log("Order Saved\n\n".print_r($orderInfo, TRUE), TRUE);
    return $orderId;
  }
  
  /**
   * Make sure there is at least one product in the cart.
   * Return true if the cart is valid, otherwise false.
   * 
   * @return boolean
   */
  public function validateCartForCheckout() {
    $isValid = true;
    $itemCount = $_SESSION['PHPurchaseCart']->countItems();
    if($itemCount < 1) {
      $this->_errors['Invalid Cart'] = "There must be at least one item in the cart.";
      $isValid = false;
    }
    return $isValid;
  }
  
}
