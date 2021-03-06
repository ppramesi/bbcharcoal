<!-- PayPal Checkout -->
<?php
  $items = $_SESSION['PHPurchaseCart']->getItems();
  $shipping = $_SESSION['PHPurchaseCart']->getShippingCost();
  $shippingMethod = $_SESSION['PHPurchaseCart']->getShippingMethodName();
  $setting = new PHPurchaseSetting();
  $paypalEmail = $setting->lookupValue('paypal_email');
  $returnUrl = $setting->lookupValue('paypal_return_url');
  
  $checkoutOk = true;
  if($_SESSION['PHPurchaseCart']->requireShipping()) {
    $liveRates = $setting->lookupValue('use_live_rates');
    if($liveRates) {
      if(!isset($_SESSION['PHPurchaseLiveRates'])) {
        $checkoutOk = false;
      }
      else {
        // Check to make sure a valid shipping method is selected
        $selectedRate = $_SESSION['PHPurchaseLiveRates']->getSelected();
        if($selectedRate->rate === false) {
          $checkoutOk = false;
        }
      }
    }
  }
  
  
  $ipnPage = get_page_by_path('store/ipn');
  $ipnUrl = get_permalink($ipnPage->ID);
  
  // Start affiliate program integration
  $aff = '';
  if (!empty($_SESSION['ap_id'])) {
    $aff = $_SESSION['ap_id'];
  }
  elseif(isset($_COOKIE['ap_id'])) {
    $aff = $_COOKIE['ap_id'];
  }
  if(!empty($aff)) {
    $aff = '~' . $aff;
  }
  // End affilitate program integration
  
  if(!empty($paypalEmail)):
?>

<?php if(!empty($data['style'])): ?>
<style type='text/css'>
  #paypalCheckout {
    <?php $styles = explode(';', $data['style']); ?>
    <?php foreach($styles as $style): ?>
      <?php if(!empty($style)) echo $style . ";\n"; ?>
    <?php endforeach; ?>
  }
</style>
<?php else: ?>
<style type='text/css'>
  #paypalCheckout {
    clear:both; 
    float: right; 
    margin: 10px 10px 0px 0px;";
  }
</style>
<?php endif; ?>


<?php if($_SESSION['PHPurchaseCart']->countItems() > 0): ?>
  <?php
    $paypalAction = 'https://www.paypal.com/cgi-bin/webscr';
    if(SANDBOX) {
      $paypalAction = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
    }
  ?>
  <?php if($checkoutOk): ?>
    <form id='paypalCheckout' action="<?php echo $paypalAction ?>" method="post">
      <?php 
        $i = 1;
        foreach($items as $item) {
          echo "\n<input type='hidden' name='item_name_$i' value=\"" . $item->getFullDisplayName() .  ' ' . $item->getCustomFieldInfo() . "\" />";
          echo "\n<input type='hidden' name='item_number_$i' value='" . $item->getItemNumber() . "' />";
          echo "\n<input type='hidden' name='amount_$i' value='" . $item->getProductPrice() . "' />";
          echo "\n<input type='hidden' name='quantity_$i' value='" . $item->getQuantity() . "' />";
          $i++;
        }
        echo "\n<input type='hidden' name='business' value='" . $setting->lookupValue('paypal_email'). "' />";
        echo "\n<input type='hidden' name='shopping_url' value='" . $setting->lookupValue('shopping_url') . "' />\n";
      ?>
    
      <input type="hidden" name="cmd" value="_cart" />
      <input type="hidden" name="no_shipping" value="2" />
      <input type="hidden" name="upload" value="1" />
      <input type="hidden" name="currency_code" value="<?php echo CURRENCY_CODE; ?>" id="currency_code" />
      <input type="hidden" name="custom" value="<?php echo $shippingMethod ?><?php echo $aff;  ?>" />

      <?php if($shipping > 0): ?>
        <input type='hidden' name='handling_cart' value='<?php echo $shipping ?>' />
      <?php endif;?>
    
      <?php if($_SESSION['PHPurchaseCart']->getDiscountAmount() > 0): ?>
        <input type="hidden" name="discount_amount_cart" value="<?php echo number_format($_SESSION['PHPurchaseCart']->getDiscountAmount(), 2); ?>"/>
      <?php endif; ?>
    
      <input type="hidden" name="notify_url" value="<?php echo $ipnUrl ?>">
      <?php if($returnUrl): ?>
        <input type="hidden" name="return" value="<?php echo $returnUrl ?>" />
      <?php endif; ?>
  
      <input id='PayPalCheckoutButton' type='image' src='https://www.paypal.com/en_US/i/btn/btn_xpressCheckout.gif' value='Checkout With PayPal' />
    </form>
  <?php endif; ?>
<?php endif; ?>

  <?php else: ?>
    <p>You must configure your payment settings</p>
  <?php endif; ?>
