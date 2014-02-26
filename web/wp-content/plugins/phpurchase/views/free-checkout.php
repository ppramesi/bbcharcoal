<?php
$free = new FreeGateway();
$setting = new PHPurchaseSetting();

$errors = array();
$jqErrors = array();

if($_SERVER['REQUEST_METHOD'] == "POST" && $_POST['phpurchase-action'] == 'freecheckout') {
  $free->setBilling($_POST['billing']);
  $free->setPayment($_POST['payment']);
  $free->validateCartForCheckout();
  if(!isset($_POST['sameAsBilling'])) {
    $free->setShipping($_POST['shipping']);
  }
  
  $errors = $free->getErrors();
  $jqErrors = $free->getJqErrors();
  
  /** Allowing non-free transactions now for the sake of manually processing payments **/
  /*
  if(!$_SESSION['PHPurchaseCart']->getGrandTotal()==0) {
    $errors['Transaction Error'] = "Order total is not free (\${$_SESSION['PHPurchaseCart']->getGrandTotal()})";
  } 
   */
  
  if(count($errors) == 0) {
    // Save Free Order
    $statusOptions = PHPurchaseCommon::getOrderStatusOptions();
    $status = $statusOptions[0];
    $transId = 'MT-' . PHPurchaseCommon::getRandNum();

    $taxLocation = $free->getTaxLocation();
    $tax = $_SESSION['PHPurchaseCart']->getTax($taxLocation['state'], $taxLocation['zip']);
    $total = $_SESSION['PHPurchaseCart']->getGrandTotal() + $tax;

    $orderId = $free->saveOrder($total, $tax, $transId, $status);

  
    $_SESSION['order_id'] = $orderId;
    $receiptLink = PHPurchaseCommon::getPageLink('store/receipt');
    header("Location: " . $receiptLink);
  }
  
  $p = $free->getPayment();
  $b = $free->getBilling();
  $s = $free->getShipping();
}

// Show errors
if(count($errors)) {
  echo "<div id='phpurchaseErrors'>";
  echo "<p><b>We're sorry.<br/>Your order could not be completed for the following reasons:</b></p><ul>";
  foreach($errors as $key => $value) {
    echo "<li>$key: $value</li>";
  }
  echo "</ul></div>";
}



$gateway="freecheckout";

// Populate info for logged in users making free purchases
if(PHPURCHASEPRO) {
  $gw = PHPurchaseCommon::gatewayName();
  if($gw == 'quantum') {
    require_once(WP_PLUGIN_DIR . '/phpurchase/pro/Quantum/vault.php');
    $customer = new Quantum_VaultCustomer($_SESSION['PHPurchaseMember']);
    $b = array(
      'firstName' => $customer->FirstName,
      'lastName' => $customer->LastName,
      'address' => $customer->Address,
      'city' => $customer->City,
      'state' => $customer->State,
      'zip' => $customer->ZipCode
    );
    
    $p = array(
      'email' => $customer->EmailAddress,
      'phone' => $customer->PhoneNumber
    );
  }
  elseif($gw == 'authnet') {
    require_once(WP_PLUGIN_DIR . '/phpurchase/pro/Authnet/cim.php');
    $customer = new CIM($_SESSION['PHPurchaseMember']);
    $b = $customer->getShipping();
    $p = array('email' => $customer->getEmail(), 'phone' => $customer->getPhone());
  }
  
  
}

include(WP_PLUGIN_DIR . '/phpurchase/views/checkout-form.php');

// Include the client side javascript validation                 
include(WP_PLUGIN_DIR . '/phpurchase/views/client/checkout.php'); 
