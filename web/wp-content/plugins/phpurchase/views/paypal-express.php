<?php
$setting = new PHPurchaseSetting();
$username = $setting->lookupValue('paypalpro_api_username');
$password = $setting->lookupValue('paypalpro_api_password');
$signature = $setting->lookupValue('paypalpro_api_signature');

// Set up the PayPal object
$mode = SANDBOX ? 'TEST' : 'LIVE';
$pp = new PHPayPal($mode);


$delivery = $_SESSION['PHPurchaseCart']->getShippingMethodName();
$tax = 0;

if($_SERVER['REQUEST_METHOD'] == "POST") {
  if($_POST['task'] == 'doexpresscheckout') {
    $token = PHPurchaseCommon::postVal('token');
    $payerId = PHPurchaseCommon::postVal('PayerID');
    $itemAmount = $_SESSION['PHPurchaseCart']->getSubTotal() - $_SESSION['PHPurchaseCart']->getDiscountAmount();
    $shipping = $_SESSION['PHPurchaseCart']->getShippingCost();
    if(isset($_POST['tax']) && $_POST['tax'] > 0) {
      $tax = PHPurchaseCommon::postVal('tax');
    }
    
    PHPurchaseCommon::log("Preparing DoExpressCheckout:\nToken: $token\nPayerID: $payerId\nItem Amount: $itemAmount\nShipping: $shipping\nTax: $tax");
    $response = $pp->DoExpressCheckout($token, $payerId, $itemAmount, $shipping, $tax);
    
    $ack = strtoupper($response['ACK']);
    if('SUCCESS' == $ack) {
      $token = PHPurchaseCommon::postVal('token');
      $payerId = PHPurchaseCommon::postVal('PayerID');
      $details = $pp->GetExpressCheckoutDetails($token);
      
      $opts = $setting->lookupValue('status_options');
      $status = '';
      if(!empty($opts)) {
        $opts = explode(',', $opts);
        $status = trim($opts[0]);
      }
      $transId = $response['TRANSACTIONID'];
      $promo = $_SESSION['PHPurchaseCart']->getPromotion();
      $promoMsg = "none";
      if($promo) {
        $promoMsg = $promo->code . ' (-' . CURRENCY_SYMBOL . number_format($_SESSION['PHPurchaseCart']->getDiscountAmount(), 2) . ')';
      }
      
      list($shipFirstName, $shipLastName) = split(' ', $details['SHIPTONAME'], 2);
      $orderInfo['ship_first_name'] = $shipFirstName;
      $orderInfo['ship_last_name'] = $shipLastName;
      $orderInfo['ship_address'] = $details['SHIPTOSTREET'];
      $orderInfo['ship_address2'] = $details['SHIPTOSTREET2'];
      $orderInfo['ship_city'] = $details['SHIPTOCITY'];
      $orderInfo['ship_state'] = $details['SHIPTOSTATE'];
      $orderInfo['ship_zip'] = $details['SHIPTOZIP'];
    
      $orderInfo['bill_first_name'] = $details['FIRSTNAME'];
      $orderInfo['bill_last_name'] = $details['LASTNAME'];
      $orderInfo['bill_address'] = '';
      $orderInfo['bill_address2'] = '';
      $orderInfo['bill_city'] = '';
      $orderInfo['bill_state'] = '';
      $orderInfo['bill_zip'] = '';
    
      $orderInfo['phone'] = preg_replace("/[^0-9]/", "", $details['PHONENUM']);
      $orderInfo['email'] = $details['EMAIL'];
      $orderInfo['coupon'] = $promoMsg;
      $orderInfo['tax'] = $response['TAXAMT'];
      $orderInfo['shipping'] = $_SESSION['PHPurchaseCart']->getShippingCost();
      $orderInfo['subtotal'] = $_SESSION['PHPurchaseCart']->getSubTotal();
      $orderInfo['total'] = $response['AMT'];
      $orderInfo['trans_id'] = $response['TRANSACTIONID'];
      $orderInfo['status'] = $status;
      $orderInfo['ordered_on'] = date('Y-m-d H:i:s');
      $orderInfo['shipping_method'] = $_SESSION['PHPurchaseCart']->getShippingMethodName();
      $orderId = $_SESSION['PHPurchaseCart']->storeOrder($orderInfo);  
    
      $receiptPage = get_page_by_path('store/receipt');
      $_SESSION['order_id'] = $orderId;
      header("Location: " . get_permalink($receiptPage->ID));
    }
    else {
      echo "<pre>";
      echo "Amount: $amount --- Tax: $tax\n";
      print_r($response);
      echo "</pre>";
    }
  }
}
elseif(isset($_GET['token']) && isset($_GET['PayerID'])) {
  $token = PHPurchaseCommon::getVal('token');
  $payerId = PHPurchaseCommon::getVal('PayerID');
  $details = $pp->GetExpressCheckoutDetails($token);
  $state = $details['SHIPTOSTATE'];
  
  // Calculate tax
  $tax = 0;
  $taxRate = new PHPurchaseTaxRate();
  
  $isTaxed = $taxRate->loadByZip($details['SHIPTOZIP']);
  if($isTaxed == false) {
    $isTaxed = $taxRate->loadByState($state);
  }
  
  if($isTaxed) {
    $taxable = $_SESSION['PHPurchaseCart']->getTaxableAmount();
    if($taxRate->tax_shipping == 1) {
      $taxable += $_SESSION['PHPurchaseCart']->getShippingCost();
    }
    $tax = number_format($taxable * ($taxRate->rate/100), 2);
  }
}
?>

<?php echo do_shortcode('[cart mode="read" tax="'. $tax .'"]'); ?>

<div style="margin: 20px auto; display: block;">
  <table border="0" cellpadding="0" cellspacing="0">
    <tr>
      <td valign="top">
        <p>
          <strong>Billing Information</strong><br/>
          <?php echo $details['FIRSTNAME'] ?> <?php echo $details['LASTNAME'] ?><br/>
          <?php echo "PayPal Status: " . $details['PAYERSTATUS'] ?><br/>
          <?php if(isset($details['PHONENUM'])): ?>
            Phone: <?php echo $details['PHONENUM'] ?><br/>
          <?php endif; ?>
          Email: <?php echo $details['EMAIL'] ?>
        </p>
      </td>
      <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
      <td>
        <?php if($delivery != "Download"): ?>
          <p>
            <strong>Shipping Information</strong><br/>
          <?php echo $details['SHIPTONAME'] ?><br/>
          <?php echo $details['SHIPTOSTREET'] ?><br/>
      
          <?php if(!empty($details['SHIPTOSTREET2'])): ?>
            <?php echo $details['SHIPTOSTREET2'] ?><br/>
          <?php endif; ?>
      
          <?php echo $details['SHIPTOCITY'] ?> <?php echo $details['SHIPTOSTATE'] ?>, <?php echo $details['SHIPTOZIP'] ?>
      
          <?php if(!empty($details['SHIPTOCOUNTRYCODE'])): ?>
            <?php echo $details['SHIPTOCOUNTRYCODE'] ?>
          <?php endif; ?>
          </p>
        <?php else: ?>
          &nbsp;
        <?php endif; ?>
      </td>
    </tr>
  </table>
</div>

<form action='<?php echo $url ?>' method='post' style="<?php echo $data['completestyle']; ?>">
  <input type="hidden" name="task" value="doexpresscheckout">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <input type="hidden" name="PayerID" value="<?php echo $payerId; ?>">
  <input type="hidden" name="CURRENCYCODE" value="<?php echo CURRENCY_CODE ?>">
  
  <input type="hidden" name="tax" value="<?php echo $tax; ?>">
  
  <?php
    $cartImgPath = $setting->lookupValue('cart_images_url');
    if($cartImgPath) {
      if(strpos(strrev($cartImgPath), '/') !== 0) {
        $cartImgPath .= '/';
      }
      $completeImgPath = $cartImgPath . 'complete-order.png';
      echo "<input type='image' style='width:auto; height:auto; padding: 10px 0px 10px 0px;' src='$completeImgPath' value='Complete Order' />";
    }
    else {
      echo "<input type='submit' class='PHPurchaseButtonPrimary' style='' value='Complete Order' />";
    }
  ?>
  <p style="color: #757575;">Your receipt will be on the next page and also emailed to you.</p>
</form>
