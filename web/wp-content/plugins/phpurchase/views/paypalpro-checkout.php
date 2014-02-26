<?php
$setting = new PHPurchaseSetting();
$cardTypes = array('Visa' => 'Visa', 'Mastercard' => 'Mastercard', 'Discover' => 'Discover', 'American Express' => 'Amex');

// Set up the PayPal object
$settingsOk = true;
$mode = SANDBOX ? 'TEST' : 'LIVE';
try {
  $pp = new PHPayPal($mode);
}
catch(Exception $e) {
  $settingsOk = false;
}

if(!$settingsOk) {
  ?>
  <div id='phpurchaseErrors'>
    <p><strong>PayPal Pro Is Not Configured</strong></p>
    <p>In order to use PayPal Pro Checkout you must enter your PayPal API username, password and signature in the PHPurchase Settings Panel</p>
  </div>
  <?php
}


$errors = array();
$jqErrors = array();
$checkoutPage = get_page_by_path('store/checkout');

if($_SERVER['REQUEST_METHOD'] == "POST" && $_POST['phpurchase-action'] == 'paypalprocheckout') {
  
  // Billing Validation
  $pp->setBilling($_POST['billing']);
  
  // Payment Validation
  $pp->setPayment($_POST['payment']);
  
  // Shipping Validation
  if(!isset($_POST['sameAsBilling'])) {
    $pp->setShipping($_POST['shipping']);
  }
  
  $errors = $pp->getErrors();
  $jqErrors = $pp->getJqErrors();
  
  if(count($errors) == 0) {
      // Set credit card data
      $pp->setCreditCardData();
      $pp->setPayerInfo();
      $pp->setPayerName();
      $pp->setAddress();
      $pp->setShipToAddress();
      
      // Calculate taxes
      $tax = $pp->getTaxAmount();
      
      // Calculat total amount to charge customer
      $total = $_SESSION['PHPurchaseCart']->getGrandTotal() + $tax;
      $total = number_format($total, 2);
      
      // Calculate total cost of all items in cart, not including tax and shipping
      $itemTotal = $_SESSION['PHPurchaseCart']->getSubTotal() - $_SESSION['PHPurchaseCart']->getDiscountAmount();
      $itemTotal = number_format($itemTotal, 2);
      
      // Calculate shipping costs
      $shipping = $_SESSION['PHPurchaseCart']->getShippingCost();
      
      // Calculate IPN URL
      $ipnPage = get_page_by_path('store/ipn');
      $ipnUrl = get_permalink($ipnPage->ID);
      
      // Set payment information
      // 'CURRENCYCODE' => CURRENCY_CODE,
      // 'NOTIFYURL' => $ipnUrl
      $payment = array(
        'AMT' => $total,
        'ITEMAMT' => $itemTotal,
        'SHIPPINGAMT' => $shipping,
        'TAXAMT' => $tax
      );
      $pp->setPaymentDetails($payment);
      
      // Add cart items to PayPal
      $items = $_SESSION['PHPurchaseCart']->getItems(); // An array of PHPurchaseCartItem objects
      foreach($items as $i) {
        $itemData = array(
          'NAME' => $i->getFullDisplayName(),
          'AMT' => $i->getProductPrice(),
          'NUMBER' => $i->getItemNumber(),
          'QTY' => $i->getQuantity()
        );
        $pp->addItem($itemData);
      }
      
      // Add a coupon discount if needed
      $discount = $_SESSION['PHPurchaseCart']->getDiscountAmount();
      if($discount > 0) {
        $negDiscount = 0 - $discount;
        $itemData = array(
          'NAME' => 'Discount',
          'AMT' => $negDiscount,
          'NUMBER' => 'DSC',
          'QTY' => 1
        );
        $pp->addItem($itemData);
      }
      
      // Do direct payment
      $response = $pp->DoDirectPayment();
      $result = strtoupper($response['ACK']);
      
      if($result == 'SUCCESS' || $result == 'SUCCESSWITHERRORS' || isset($response['TRANSACTIONID'])) {
        // Approved
        $statusOptions = PHPurchaseCommon::getOrderStatusOptions();
        $status = $statusOptions[0];
        $transId = $response['TRANSACTIONID'];
        $orderId = $pp->saveOrder($total, $tax, $transId, $status);
      
        $_SESSION['order_id'] = $orderId;
        $receiptLink = PHPurchaseCommon::getPageLink('store/receipt');
        header("Location: " . $receiptLink);
      }
      else {
        // Not approved
        PHPurchaseCommon::log("Do Direct Payment Response: " . print_r($response, true));
        $errors['Card Transaction'] = $response['L_LONGMESSAGE0'];
      }
  }
}

// Show errors
if(count($errors)) {
  echo PHPurchaseCommon::showErrors($errors);
}

$p = $pp->getPayment();
$b = $pp->getBilling();
$s = $pp->getShipping();

if($settingsOk) {
  $gateway = 'paypalprocheckout';
  include(WP_PLUGIN_DIR . '/phpurchase/views/checkout-form.php');            
  include(WP_PLUGIN_DIR . '/phpurchase/views/client/checkout.php');
}
?>


