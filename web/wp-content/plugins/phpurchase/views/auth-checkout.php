<?php
// Initialize the Authorize.net Payment Gateway
$setting = new PHPurchaseSetting();
$authUrl = $setting->lookupValue('auth_url');
$authnet = new AuthorizeNet($authUrl);

if(PHPURCHASEPRO) {

  $gw = PHPurchaseCommon::gatewayName();
  if($gw == 'quantum') {
    require_once(WP_PLUGIN_DIR . '/phpurchase/pro/Quantum/vault.php');
    $customer = new Quantum_VaultCustomer($_SESSION['PHPurchaseMember']);
  }
  elseif($gw == 'authnet') {
    require_once(WP_PLUGIN_DIR . '/phpurchase/pro/Authnet/cim.php');
    $customer = new CIM($_SESSION['PHPurchaseMember']);
  }
}

$showMemberShipping = 'none';
$showUpdateForm = 'none';

$cardTypes = PHPurchaseCommon::getCardTypes();

$errors = array();
$jqErrors = array();
$checkoutPage = get_page_by_path('store/checkout');
if($_SERVER['REQUEST_METHOD'] == "POST" && $_POST['phpurchase-action'] == 'authcheckout') {

  PHPurchaseCommon::log("User Clicked the Complete Order Button", TRUE);
  
  $authnet->validateCartForCheckout();
  $authnet->setBilling($_POST['billing']);
  $authnet->setPayment($_POST['payment']);
  if(!isset($_POST['sameAsBilling'])) {
    $authnet->setShipping($_POST['shipping']);
  }

  // Validate Shipping Street Address
  require_once(LIB_BASE.'scheduleShipment.php');
  if(!isset($_SESSION['shippingerrors'])) {
	$_SESSION['shippingerrors'] = 0;
  }
  $isValidAddress = validateStreetAddress();

  $errors = $authnet->getErrors();
  $jqErrors = $authnet->getJqErrors();
	if(!$isValidAddress[0]) {
		PHPurchaseCommon::log("User Clicked the Complete Order Button with Invalid Shipping Address", TRUE);
		$errors['Invalid Address'] = 'Invalid Shipping Address';
	}
  
  // Password validation for subscription products
  $customerId = 0;
  $createVaultCustomer = false;
  if(isset($_POST['payment']['password'])) {
    if(empty($errors)) {
      $customer->populate($authnet);
      $customerErrors = $customer->validate();
      if(empty($customerErrors)) {
        $createVaultCustomer = true;
        $remoteErrors = $customer->create();
        if(count($remoteErrors)) {
          $errors = array_merge($errors, $remoteErrors);
        }
        else {
          $customerId = $customer->id;
          PHPurchaseCommon::log("Remote customer created with id: " . $customerId);
        }
      }
      else {
        $errors = array_merge($errors, $customerErrors);
      }
    }
  }

  // Charge credit card for one time transaction using Authorize.net API
  if(count($errors) == 0 && empty($_SESSION['PHPurchaseInventoryWarning'])) {
    $taxLocation = $authnet->getTaxLocation();
    $tax = $_SESSION['PHPurchaseCart']->getTax($taxLocation['state'], $taxLocation['zip']);
    $total = $_SESSION['PHPurchaseCart']->getGrandTotal() + $tax;

	PHPurchaseCommon::log("User Clicked the Complete Order Button with No Validation Errors", TRUE);

    $authnet->initCheckout($total);

    $response = $authnet->process();
	
	//echo $response;
	$weTesting = false;
    if($response == '1' || $weTesting) {
      // Approved
      $statusOptions = PHPurchaseCommon::getOrderStatusOptions();
      $status = $statusOptions[0];
      $transId = $authnet->getTransactionId();
  
	  PHPurchaseCommon::log("User Clicked the Complete Order Button and the CC Transaction Successful. Transaction ID: ".$transId, TRUE);
	  
	  // Schedule Shipment.
	  PHPurchaseCommon::log("Scheduling Shipment...");
	  $shippingResults = scheduleShipment($isValidAddress[1]);
	  
      //voidShipment($shippingResults[1]['ShipmentIdentificationNumber']['VALUE']);
	  //voidShipment('1Z12345E0390856432');
      $orderId = $authnet->saveOrder($total, $tax, $transId, $status, $customerId, $shippingResults);
	  
      $_SESSION['order_id'] = $orderId;
	  $_SESSION['order_successful'] = TRUE;

      $receiptLink = PHPurchaseCommon::getPageLink('store/receipt');
	  PHPurchaseCommon::log("User Clicked the Complete Order Button and Shipping scheduled, Purchase Completed. Redirecting...", TRUE);
      header("Location: " . $receiptLink);
    }
    else {

      // Not approved
      $errors['Card Transaction'] = $authnet->getResponseReasonText();
      if(empty($errors['Card Transaction'])) {
        $errors['Card Transaction'] = $response;
      }
	  PHPurchaseCommon::log("User Clicked the Complete Order Button and the Credit Card Response returned FALSE. Response value: ".$response, TRUE);
      $authnet->dumpFields();
      $authnet->dumpResponse();
    }
  }
} else {
	PHPurchaseCommon::log("User Clicked the Checkout Button.", TRUE);
}

// Show errors
if(count($errors)) {
  echo PHPurchaseCommon::showErrors($errors);
  PHPurchaseCommon::log("User Clicked the Complete Order Button and Errors Occured: ". print_r($errors, true), TRUE);
}

if(!empty($_SESSION['PHPurchaseInventoryWarning'])) {
  PHPurchaseCommon::log("Inventory message is not empty");
  echo $_SESSION['PHPurchaseInventoryWarning'];
  unset($_SESSION['PHPurchaseInventoryWarning']);
}

$p = $authnet->getPayment();
$b = $authnet->getBilling();
$s = $authnet->getShipping();

?>

<div id="phpurchase-form">
  <?php
    $completeImgPath = WPCURL . '/plugins/phpurchase/images/complete-order.png';
    $cartImgPath = $setting->lookupValue('cart_images_url');
    if($cartImgPath) {
      if(strpos(strrev($cartImgPath), '/') !== 0) {
        $cartImgPath .= '/';
      }
      $completeImgPath = $cartImgPath . 'complete-order.png';
    }
 
    $ssl = $setting->lookupValue('auth_force_ssl');
    $url = get_permalink($checkoutPage->ID);
    if($ssl == 'yes' || !empty($_SERVER['HTTPS'])) {
      $url = str_replace('http:', 'https:', $url);
    }
  ?>
  
<?php if($_SESSION['PHPurchaseCart']->hasSubscriptionProducts()): ?>
  <?php if(!isset($_SESSION['PHPurchaseMember'])): ?>
    <h2>Please Log In If You Already Have An Account</h2>
    <?php echo do_shortcode('[account-login redirect="stay"]'); ?>
  <?php endif; ?>
<?php endif; ?>

<?php if(PHPURCHASEPRO && isset($_SESSION['PHPurchaseMember'])): ?>
  <?php 
    $gw = PHPurchaseCommon::gatewayName();
    if($gw == 'quantum') {
      include(WP_PLUGIN_DIR . '/phpurchase/pro/QuantumViews/vault_form.php'); 
    }
    elseif($gw == 'authnet') {
      include(WP_PLUGIN_DIR . '/phpurchase/pro/Authnet/member-checkout.php'); 
    }
  ?>
<?php else: ?>
  <?php $gateway = 'authcheckout'; ?>
  <?php include(WP_PLUGIN_DIR . '/phpurchase/views/checkout-form.php'); ?>
<?php endif; ?>

</div>

<?php 
  // Include the client side javascript validation                 
  include(WP_PLUGIN_DIR . '/phpurchase/views/client/checkout.php'); 
?>
