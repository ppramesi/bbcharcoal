<?php
//if(!$_SESSION['test']) {echo 'Sorry, our checkout is down temporarily for service. Please check back soon.';die;}
$_SESSION['PHPurchaseCart']->resetPromotionStatus();
$items = $_SESSION['PHPurchaseCart']->getItems();
$shippingMethods = $_SESSION['PHPurchaseCart']->getShippingMethods();
$shipping = $_SESSION['PHPurchaseCart']->getShippingCost();
$promotion = $_SESSION['PHPurchaseCart']->getPromotion();
$product = new PHPurchaseProduct();
$subtotal = $_SESSION['PHPurchaseCart']->getSubTotal();
$discountAmount = $_SESSION['PHPurchaseCart']->getDiscountAmount();
$cartPage = get_page_by_path('store/cart');
$checkoutPage = get_page_by_path('store/checkout');
$setting = new PHPurchaseSetting();

// Try to return buyers to the last page they were on when the click to continue shopping

if(empty($_SESSION['PHPurchaseLastPage'])) {
  // If the last page is not set, use the store url
  $_SESSION['PHPurchaseLastPage'] = $setting->lookupValue('store_url') ? $setting->lookupValue('store_url') : get_bloginfo('url');
}

$fullMode = true;
if(isset($data['mode']) && $data['mode'] == 'read') {
  $fullMode = false;
}

$tax = 0;
if(isset($data['tax']) && $data['tax'] > 0) {
  $tax = $data['tax'];
}
else {
  // Check to see if all sales are taxed
  $tax = $_SESSION['PHPurchaseCart']->getTax('All Sales');
}

$cartImgPath = $setting->lookupValue('cart_images_url');
if($cartImgPath && stripos(strrev($cartImgPath), '/') !== 0) {
  $cartImgPath .= '/';
}
if($cartImgPath) {
  $continueShoppingImg = $cartImgPath . 'continue-shopping.png';
}

if(count($items)): ?>
<?php
  $cartPage = get_page_by_path('store/cart');
  $cartPageLink = get_permalink($cartPage->ID);
  if($_SERVER['SERVER_PORT'] == '443') {
    $cartPageLink = str_replace('http://', 'https://', $cartPageLink);
  }
?>

<?php if(!empty($_SESSION['PHPurchaseInventoryWarning']) && $fullMode): ?>
  <div class="PHPurchaseUnavailable">
    <h1>Inventory Restriction</h1>
    <?php 
      echo $_SESSION['PHPurchaseInventoryWarning'];
      unset($_SESSION['PHPurchaseInventoryWarning']);
    ?>
    <input type="button" name="close" value="Ok" id="close" class="PHPurchaseButtonSecondary modalClose" />
  </div>
<?php endif; ?>


<?php if(isset($_SESSION['PHPurchaseZipWarning'])): ?>
  <div id="PHPurchaseZipWarning" class="PHPurchaseUnavailable">
    <h2>Please Provide Your Zip Code</h2>
    <p>Before you can checkout, please provide the zip code for where we will be shipping your order.</p>
    <?php 
      unset($_SESSION['PHPurchaseZipWarning']);
    ?>
    <input type="button" name="close" value="Ok" id="close" class="PHPurchaseButtonSecondary modalClose" />
  </div>
<?php elseif(isset($_SESSION['PHPurchaseShippingWarning'])): ?>
  <div id="PHPurchaseShippingWarning" class="PHPurchaseUnavailable">
    <h2>No Shipping Option Selected</h2>
    <p>We cannot process your order because you have not selected a shipping method.</p>
    <?php 
      unset($_SESSION['PHPurchaseShippingWarning']);
    ?>
    <input type="button" name="close" value="Ok" id="close" class="PHPurchaseButtonSecondary modalClose" />
  </div>
<?php endif; ?>

<form id='PHPurchaseCartForm' action='<?php echo $cartPageLink ?>' method='post'>
  <input type='hidden' name='task' value='updateCart'>
  <table id='viewCartTable' cellspacing="0" cellpadding="0" border="0" style="">
    <tr>
      <th style='text-align: left;'>Product</th>
      <th style='text-align: left;' colspan="1">Quantity</th>
      <th>&nbsp;</th>
      <th style='text-align: left;'>Item&nbsp;Price</th>
      <th style='text-align: left;'>Item&nbsp;Total</th>
    </tr>
  
    <?php foreach($items as $itemIndex => $item): ?>
      <?php 
        $product->load($item->getProductId());
        $price = $item->getProductPrice() * $item->getQuantity();
      ?>
      <tr>
        <td <?php if($item->hasAttachedForms()) { echo "style='border-bottom: none;'"; } ?> >
          <?php #echo $item->getItemNumber(); ?>
          <?php echo $item->getFullDisplayName(); ?>
          <?php echo $item->getCustomField($itemIndex, $fullMode); ?>
        </td>
        <?php if($fullMode): ?>
          <?php
            $removeItemImg = WPCURL . '/plugins/phpurchase/images/remove-item.png';
            if($cartImgPath) {
              $removeItemImg = $cartImgPath . 'remove-item.png';
            }
          ?>
        <td style='text-align: left; <?php if($item->hasAttachedForms()) { echo " border-bottom: none;"; } ?>' colspan="2">
          <input type='text' name='quantity[<?php echo $itemIndex ?>]' value='<?php echo $item->getQuantity() ?>' style='width: 35px; margin-left: 5px;'/>
          
          <?php $removeLink = get_permalink($cartPage->ID); ?>
          <?php $taskText = (strpos($removeLink, '?')) ? '&task=removeItem&' : '?task=removeItem&'; ?>
          <a href='<?php echo $removeLink . $taskText ?>itemIndex=<?php echo $itemIndex ?>' title='Remove item from cart'><img src='<?php echo $removeItemImg ?>' /></a>
            
        </td>
        <?php else: ?>
          <td style='text-align: left; <?php if($item->hasAttachedForms()) { echo " border-bottom: none;"; } ?>' colspan="2"><?php echo $item->getQuantity() ?></td>
        <?php endif; ?>
        <td <?php if($item->hasAttachedForms()) { echo "style='border-bottom: none;'"; } ?>><?php echo CURRENCY_SYMBOL ?><?php echo number_format($item->getProductPrice(), 2) ?></td>
        <td <?php if($item->hasAttachedForms()) { echo "style='border-bottom: none;'"; } ?>><?php echo CURRENCY_SYMBOL ?><?php echo number_format($price, 2) ?></td>
      </tr>
      <?php if($item->hasAttachedForms()): ?>
        <tr>
          <td colspan="5">
            <a href='#' class="showEntriesLink" rel="<?php echo 'entriesFor_' . $itemIndex ?>">Show Details <?php #echo count($item->getFormEntryIds()); ?></a>
            <div id="<?php echo 'entriesFor_' . $itemIndex ?>" class="showGfFormData" style="display: none;">
              <?php echo $item->showAttachedForms($fullMode); ?>
            </div>
          </td>
        </tr>
      <?php endif;?>
    <?php endforeach; ?>

    <tr>
      <?php if($fullMode): ?>
      <td class='noBorder'>&nbsp;</td>
      <td class='noBorder' colspan='2' style='text-align: left;'>
        <input type='submit' name='updateCart' value='Update Total' class="PHPurchaseButtonSecondary" />
      </td>
      <?php else: ?>
        <td class='noBorder' colspan='3'>&nbsp;</td>
      <?php endif; ?>
      <td class='noBorder' colspan="1" style='text-align: right; font-weight: bold;'>Subtotal:</td>
      <td class='noBorder' colspan="1" style="text-align: left; font-weight: bold;"><?php echo CURRENCY_SYMBOL ?><?php echo number_format($subtotal, 2); ?></td>
    </tr>
    
    <?php if($_SESSION['PHPurchaseCart']->requireShipping()): ?>



      <?php if($setting->lookupValue('use_live_rates')): ?>
        <?php $zipStyle = "style=''"; ?>
        
        <?php if($fullMode): ?>
          <?php if(!empty($_SESSION['phpurchase_shipping_zip'])): ?>
            <?php $zipStyle = "style='display: none;'"; ?>

            <tr id="set-shipping-zip-code">
              <th colspan="5" align="right">
                Shipping to <?php $frloctystr = $_SESSION['PHPurchaseCart']->isPallet() ? " (" . $_SESSION['freight_loc_type'] . ")" : "";echo $_SESSION['phpurchase_shipping_zip'] . $frloctystr; ?> 
                <?php
                  if($setting->lookupValue('international_sales')) {
                    echo $_SESSION['phpurchase_shipping_country_code'];
                  }
                ?>
                (<a href="#" id="change_shipping_zip_link">change</a>)
                &nbsp;
                <?php
                  $liveRates = $_SESSION['PHPurchaseCart']->getUpsRates();
                  $rates = $liveRates->getRates();
                  $selectedRate = $liveRates->getSelected();
                  $shipping = $_SESSION['PHPurchaseCart']->getShippingCost();
                ?>
                <select name="live_rates" id="live_rates">
                  <?php foreach($rates as $rate): 
					$servStrs = explode(' - ', $rate->service);
					if( !empty($servStrs[1]) ){
					  $servStr = $servStrs[0] . ' (' . $servStrs[1] . ')';
					  $servVal = $servStrs[2];
					}else{
					  $servStr = $servVal = $servStrs[0];
					}
				  ?>
                    <option value='<?php echo $rate->service ?>' <?php if($selectedRate->service == $rate->service) { echo 'selected="selected"'; } ?>>
                      <?php 
                        if($rate->rate !== false) {
                          echo "$servStr: \$$rate->rate";
                        }
                        else {
                          echo "$rate->service";
                        }
                      ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </th>
            </tr>
          <?php endif; ?>
        
          <tr id="set_shipping_zip_row" <?php echo $zipStyle; ?>>
            <th colspan="5" align="right">
            	<span style="position: relative;top: 1px;">Would You Need Lift Gate?<span style="color: #808080; font-size:.8em"> (For Pallet/Freight Shipping Only)</span>:</span><input id="liftgate-box" type="checkbox" name="liftGate" value="yes" style="margin-right: -4px;margin-left: 6px;margin-bottom: 8px;position: relative;top: 6px;height: 17px;width: 17px;" <?php if(strcasecmp($_SESSION['liftGate'], "yes") == 0){echo "checked";} ?>/>
              
			  <br>
              <span>Delivery Location Type<span style="color: #808080; font-size:.8em"> (For Pallet/Freight Shipping Only)</span>:</span>
              <select title="type" id="shipping-location-type" name="locationType" style="margin-left: 0;margin-right: -4px;width: 219px;height: 24px;">
				  <option value="Commercial">Commercial/Business</option>
				  <option value="Residential">Residential</option>
				  <option value="DistributionCenter">Distribution Center</option>
				  <option value="SelfStorage">Self Storage</option>
				  <option value="Hotel">Hotel</option>
				  <option value="Airport">Airport</option>
				  <option value="CampParkResort">Camp/Park/Resort</option>
				  <option value="Church">Church</option>
				  <option value="CorrectionalFacility">Correctional Facility</option>
				  <option value="Construction">Construction Site</option>
				  <option value="ContainerYard">Container Yard/CFS</option>
				  <option value="TradeShow">Trade Show/Convention</option>
				  <option value="Farm">Farm</option>
				  <option value="GolfCourse">Golf Course/Country Club</option>
				  <option value="IndianReservation">Indian Reservation</option>
				  <option value="MilitaryBase">Military Base</option>
				  <option value="Mine">Mine</option>
				  <option value="Museum">Museum</option>
				  <option value="PortPier">Port/Pier/Wharf</option>
				  <option value="PublicUtility">Public Utility</option>
				  <option value="School">School/University</option>
				  <option value="CarrierTerminal">Trucking Terminal</option>
				  <option value="OtherLimitedAccess">Other Limited Access</option>
              </select>
			  <br>
			  <span class='required-zip-code'>*Enter Your Zip Code</span>:<input type="text" name="shipping_zip" value="" id="shipping_zip" size="5" style="margin-right: -4px;margin-left: 5px;"/>
				<?php if($setting->lookupValue('international_sales')): ?>
                <select name="shipping_country_code">
                  <?php
                    $customCountries = PHPurchaseCommon::getCustomCountries();
                    foreach($customCountries as $code => $name) {
                      echo "<option value='$code'>$name</option>\n";
                    }
                  ?>
                </select>
              <?php else: ?>
                <?php
                  $homeCountry = $setting->lookupValue('home_country');
                  if($homeCountry) {
                    list($homeCountryCode, $homeCountryName) = explode('~', $homeCountry);
                  }
                  else {
                    $homeCountryCode = 'US'; // Default to US if the home country code cannot be determined
                  }
                ?>
                <input type="text" name="shipping_country_code" value="<?php echo $homeCountryCode ;?>" id="shipping_country_code" style="margin-right: -4px">
              <?php endif; ?>
			  <br>
              <input type="submit" name="updateCart" value="Calculate Shipping" id="shipping_submit" class="PHPurchaseButtonSecondary" style="width: 220px !important;"/>
            </th>
          </tr>
        <?php else:  // Cart in read mode ?>
          <tr>
            <th colspan="5" align='right'>
              <?php
                $liveRates = $_SESSION['PHPurchaseCart']->getUpsRates();
                if($liveRates && !empty($_SESSION['phpurchase_shipping_zip']) && !empty($_SESSION['phpurchase_shipping_country_code'])) {
                  $selectedRate = $liveRates->getSelected();
				  $kaboom = explode(" - ", $selectedRate->service);
				  $freightName = $kaboom[0];
				  $loctype = $_SESSION['PHPurchaseCart']->isPallet() ? " (" . $_SESSION['freight_loc_type'] . ")" : "";;
				  $freightDay = empty($kaboom[1]) ? "" : " (" . $kaboom[1] . ")";
                  echo "Shipping to " . $_SESSION['phpurchase_shipping_zip'] . $loctype . " via " . $freightName . $freightDay;
                }
                else {
                  $cartPage = get_page_by_path('store/cart');
                  $link = get_permalink($cartPage->ID);
                  
                  if(empty($_SESSION['phpurchase_shipping_zip'])) {
                    PHPurchaseCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Live rate warning: No shipping zip in session");
                    $_SESSION['PHPurchaseZipWarning'] = true;
                  }
                  elseif(empty($_SESSION['phpurchase_shipping_country_code'])) {
                    PHPurchaseCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Live rate warning: No shipping country code in session");
                    $_SESSION['PHPurchaseShippingWarning'] = true;
                  }
                  
                  wp_redirect($link);
                  exit();
                }
              ?>
            </th>
          </tr>
        <?php endif; // End cart in read mode ?>
        
      <?php  else: ?>
        <?php if(count($shippingMethods) > 1 && $fullMode): ?>
        <tr>
          <th colspan='5' align="right">Shipping Method: &nbsp;
            <select name='shipping_method_id' id='shipping_method_id'>
              <?php foreach($shippingMethods as $name => $id): ?>
              <option value='<?php echo $id ?>' 
               <?php echo ($id == $_SESSION['PHPurchaseCart']->getShippingMethodId())? 'selected' : ''; ?>><?php echo $name ?></option>
              <?php endforeach; ?>
            </select>
          </th>
        </tr>
        <?php elseif(!$fullMode): ?>
        <tr>
          <th colspan='2' align="right">Shipping Method:</th>
          <th colspan='3' align="left">
            <?php 
              $method = new PHPurchaseShippingMethod($_SESSION['PHPurchaseCart']->getShippingMethodId());
              echo $method->name;
            ?>
          </th>
        </tr>
        <?php endif; ?>
      <?php endif; ?>
    <?php if(!empty($_SESSION['phpurchase_shipping_zip'])): ?>
	    <tr>
	      <td class='noBorder' colspan='1'>&nbsp;</td>
	      <td class='noBorder' colspan="2" style='text-align: center;'>&nbsp;</td>
	      <td class='noBorder' colspan="1" style='text-align: right; font-weight: bold;'>Shipping:</td>
	      <td class='noBorder' colspan="1" style="text-align: left; font-weight: bold;"><?php echo CURRENCY_SYMBOL ?><?php echo $shipping ?></td>
	    </tr>













    <?php endif; ?>
    <?php else: ?>
	<tr>


















      <td class='noBorder' colspan='1'>&nbsp;</td>
      <td class='noBorder' colspan="2" style='text-align: center;'>&nbsp;</td>
      <td class='noBorder' colspan="1" style='text-align: right; font-weight: bold;'>Shipping:</td>
      <td class='noBorder' colspan="1" style="text-align: left; font-weight: bold;">FREE</td>
    </tr>
	<?php endif; ?>
    
    <?php if($promotion): ?>
      <tr>
        <td class='noBorder' colspan='2'>&nbsp;</td>
        <td class='noBorder' colspan="2" style='text-align: right; font-weight: bold;'>Coupon:</td>
        <td class='noBorder' colspan="1" style="text-align: left; font-weight: bold;"><?php echo $promotion->getAmountDescription(); ?></td>
      </tr>
    <?php endif; ?>
    
    
    <?php if($tax > 0): ?>
      <tr>
        <td class='noBorder' colspan='3'>&nbsp;</td>
        <td class='noBorder' colspan="1" style='text-align: right; font-weight: bold;'>Tax:</td>
        <td class='noBorder' colspan="1" style="text-align: left; font-weight: bold;"><?php echo CURRENCY_SYMBOL ?><?php echo number_format($tax, 2); ?></td>
      </tr>
    <?php endif; ?>
    
      <tr>
        <td class='noBorder' style="text-align: left;" colspan='1'>
          <?php if($fullMode && PHPurchaseCommon::activePromotions()): ?>
            Do you have a coupon? &nbsp;
            <input type='text' name='couponCode' value='' size="12" />
            <?php if($_SESSION['PHPurchaseCart']->getPromoStatus() < 0): ?>
              <div style='color: red;'><br/><?php echo $_SESSION['PHPurchaseCart']->getPromoMessage(); ?></div>
            <?php endif; ?>
          <?php endif; echo '<a href="'.URL_BASE.'refund-return-shipping-and-privacy-policies/">TERMS OF USE</a>'?>
        </td>
        <td class='noBorder' colspan="2" valign="top">
          <?php if($fullMode && PHPurchaseCommon::activePromotions()): ?>
            <input type='submit' name='updateCart' value='Apply Coupon' class="PHPurchaseButtonSecondary" />
          <?php endif; ?>&nbsp;
        </td>
        <?php if(!empty($_SESSION['phpurchase_shipping_zip']) || !$_SESSION['PHPurchaseCart']->requireShipping()): ?>

	        <td class='noBorder' colspan="1" style='text-align: right; font-weight: bold;' valign="top">Total:</td>
	        <td class='noBorder' colspan="1" style="text-align: left; font-weight: bold;" valign="top">
	          <?php echo CURRENCY_SYMBOL ?><?php echo number_format($subtotal + $tax + $shipping - $discountAmount, 2); ?>
	        </td>
        <?php endif; ?>

      </tr>
  
  </table>
</form>

  <?php if($fullMode): ?>
  <table id="viewCartTableNav">
    <tr>
      <td style='text-align: left; vertical-align: top;'>
        <?php if($cartImgPath): ?>
          <a href='<?php echo $_SESSION['PHPurchaseLastPage']; ?>'><img src='<?php echo $continueShoppingImg ?>' /></a>
        <?php else: ?>
          <a href='<?php echo $_SESSION['PHPurchaseLastPage']; ?>' class="PHPurchaseButtonSecondary">Continue Shopping</a>
        <?php endif; ?>
      </td>
      <td style='text-align: right; vertical-align: top;'>
        <?php
          $isFree = true;
          if($_SESSION['PHPurchaseCart']->getGrandTotal() > 0.00) {
            $isFree = false;
          }
          if($setting->lookupValue('auth_username') || $setting->lookupValue('paypalpro_api_username') || $isFree):
        ?>
          <?php
            $checkoutImg = false;
            if($cartImgPath) {
              $checkoutImg = $cartImgPath . 'checkout.png';
            }
          ?>
			<?php if(!empty($_SESSION['phpurchase_shipping_zip']) || !$_SESSION['PHPurchaseCart']->requireShipping()): ?>


	          <?php if($checkoutImg): ?>
	            <a href='<?php echo get_permalink($checkoutPage->ID) ?>'><img src='<?php echo $checkoutImg ?>' /></a>
	          <?php else: ?>
	            <a href='<?php echo get_permalink($checkoutPage->ID) ?>' class="PHPurchaseButtonPrimary">Checkout</a>
	          <?php endif; ?>

	        <?php endif; ?>
        <?php else: ?>
          <style type='text/css'>
            #paypalCheckout {
              float: right;
            }
          </style>
          <?php include(dirname(__FILE__) . '/paypal-checkout.php'); ?>
        <?php endif; ?>
      </td>
    </tr>
  </table>
  <?php endif; ?>
<?php else: header("Location: http://www.bbcharcoal.com/products");?>
  <center>
  <h3>Your Cart Is Empty</h3><br/>
  <?php if($cartImgPath): ?>
    <p><a href='<?php echo $_SESSION['PHPurchaseLastPage']; ?>'><img style="border: 0px;" src='<?php echo $continueShoppingImg ?>' /></a>
  <?php else: ?>
    <p><a href='<?php echo $_SESSION['PHPurchaseLastPage']; ?>' class="PHPurchaseButtonSecondary">Continue Shopping</a>
  <?php endif; ?>
  </center>
  <?php
    $_SESSION['PHPurchaseCart']->clearPromotion();
  ?>
<?php endif; ?>

<script type="text/javascript" charset="utf-8">
//<![CDATA[
  $jq = jQuery.noConflict();

  $jq('document').ready(function() {
    $jq('#shipping_method_id').change(function() {
      $jq('#PHPurchaseCartForm').submit();
    });
    
    $jq('#live_rates').change(function() {
      $jq('#PHPurchaseCartForm').submit();
    });
    
    $jq('.showEntriesLink').click(function() {
      var panel = $jq(this).attr('rel');
      $jq('#' + panel).toggle();
      return false;
    });
    
    $jq('#change_shipping_zip_link').click(function() {
      $jq('#set_shipping_zip_row').toggle();
      return false;
    });
  });
  
//]]>  
</script>