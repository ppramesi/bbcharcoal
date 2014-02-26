<?php
$settingsOk = true;
$setting = new PHPurchaseSetting();
$username = $setting->lookupValue('paypalpro_api_username');
$password = $setting->lookupValue('paypalpro_api_password');
$signature = $setting->lookupValue('paypalpro_api_signature');
if(!($username && $password && $signature)) {
  $settingsOk = false;
  ?>
  <div id='phpurchaseErrors'>
    <p><strong>PayPal Express Checkout Is Not Configured</strong></p>
    <p>In order to use PayPal Express Checkout you must enter your PayPal API username, password and signature in the PHPurchase Settings Panel</p>
  </div>
  <?php
}


if($_SERVER['REQUEST_METHOD'] == "POST" && $_POST['phpurchase-action'] == 'paypalexpresscheckout') {
  // Set up the PayPal object
  $mode = SANDBOX ? 'TEST' : 'LIVE';
  $pp = new PHPayPal($mode);
  
  
  // Calculate total amount to charge customer
  $total = $_SESSION['PHPurchaseCart']->getGrandTotal();
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
  $payment = array(
    'AMT' => $total,
    'CURRENCYCODE' => CURRENCY_CODE,
    'ITEMAMT' => $itemTotal,
    'SHIPPINGAMT' => $shipping,
    'NOTIFYURL' => $ipnUrl
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
  $discount = number_format($_SESSION['PHPurchaseCart']->getDiscountAmount(), 2);
  
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
  
  // Set Express Checkout URLs
  $returnPage = get_page_by_path('store/express');
  $returnUrl = get_permalink($returnPage->ID);
  $cancelPage = get_page_by_path('store/checkout');
  $cancelUrl = get_permalink($cancelUrl->ID);
  $ecUrls = array(
    'RETURNURL' => $returnUrl,
    'CANCELURL' => $cancelUrl
  );
  $pp->setEcUrls($ecUrls);
  
  $response = $pp->SetExpressCheckout();
  $ack = strtoupper($response['ACK']);
  if('SUCCESS' == $ack || 'SUCCESSWITHWARNING' == $ack) {
    $_SESSION['PayPalProToken'] = $response['TOKEN'];
    $expressCheckoutUrl = $pp->getExpressCheckoutUrl($response['TOKEN']);
  	header("Location: $expressCheckoutUrl");
  	exit;
  }
  else {
    echo "<pre>";
    print_r($response);
    echo "</pre>";
  }
}
?>

<?php if($settingsOk): ?>
<?php  
if(!isset($data['style'])) {
  $data['style'] = "clear:both; float: right; margin: 10px 10px 0px 0px;";
}
?>
<form action='<?php echo $url ?>' method='post' style="<?php echo $data['style']; ?>">
  <input type='hidden' name='phpurchase-action' value='paypalexpresscheckout'>
  <input type="image" src="https://www.paypal.com/en_US/i/btn/btn_xpressCheckout.gif" align="left" style="margin-right:7px;">
</form>
<?php endif; ?>
