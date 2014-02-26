<?php
/*<noscript class="no-script">Please Enable Javascript. For information on how to enable javascript, please click <a href="http://help.yahoo.com/l/us/yahoo/help/faq/browsers/browsers-63474.html" target="_blank">here.</a></noscript>
<form class="phorm2" action='<?php echo $url ?>' method='post' id="phorm2">
  <input type='hidden' name='phpurchase-action' value='<?php echo $gateway ?>'>
  <table id="address-table">
    <tr>
      <td valign='top' style="">
        <ul id="billingAddress" class="shortLabels" style="width: 275px;">
          <?php if($gateway == 'freecheckout'): ?>
            <li><h2>Shipping Address</h2></li>
          <?php else: ?>
            <li><h2>Billing Address</h2></li>
          <?php endif; ?>

          <li>
            <label>First name:</label>
            <input type="text" id="billing-firstName" name="billing[firstName]" value="<?php PHPurchaseCommon::showValue($b['firstName']); ?>">
          </li>

          <li>
            <label>Last name:</label>
            <input type="text" id="billing-lastName" name="billing[lastName]" value="<?php PHPurchaseCommon::showValue($b['lastName']); ?>">
          </li>

          <li>
            <label>Address:</label>
            <input type="text" id="billing-address" name="billing[address]" value="<?php PHPurchaseCommon::showValue($b['address']); ?>">
          </li>

          <li>
            <label>&nbsp;</label>
            <input type="text" id="billing-address2" name="billing[address2]" value="<?php PHPurchaseCommon::showValue($b['address2']); ?>">
          </li>

          <li>
            <label>City:</label>
            <input type="text" id="billing-city" name="billing[city]" value="<?php PHPurchaseCommon::showValue($b['city']); ?>">
          </li>

          <li><label class="short">State:</label>
          <select style="min-width: 125px;" id="billing-state" class="required" title="State billing address" name="billing[state]"></select>
          </li>

          <li>
            <label>Zip code:</label>
            <input type="text" id="billing-zip" name="billing[zip]" value="<?php PHPurchaseCommon::showValue($b['zip']); ?>">
          </li>

          <li>
            <label class="short">Country:</label>
            <select title="country" id="billing-country" name="billing[country]">
              <?php foreach(PHPurchaseCommon::getCountries() as $code => $name): ?>
              <option value="<?php echo $code ?>"><?php echo $name ?></option>
              <?php endforeach; ?>
            </select>
          </li>
        </ul>
		<?php if($gateway == 'freecheckout'): ?>
			 <input type='hidden' id='sameAsBilling' name='sameAsBilling' value='1' />
		<?php endif; ?>
      </td>
	      <?php if($gateway != 'freecheckout'): ?>
	  <td valign='top' style="">
        <ul id="shipping-info">
          <li><h2>Shipping Address</h2></li>
    
          <li>
            <label style='width: auto;'>Same as billing address:</label>
            <input type='checkbox' id='sameAsBilling' name='sameAsBilling' value='1' style='width: auto;'>
          </li>
        </ul>

        <ul id="shippingAddress" class="shortLabels" style="width: 275px; display: none;">

          <li>
            <label>First name:</label>
            <input type="text" id="shipping-firstName" name="shipping[firstName]" value="<?php PHPurchaseCommon::showValue($s['firstName']); ?>">
          </li>

          <li>
            <label>Last name:</label>
            <input type="text" id="shipping-lastName" name="shipping[lastName]" value="<?php PHPurchaseCommon::showValue($s['lastName']); ?>">
          </li>

          <li>
            <label>Address:</label>
            <input type="text" id="shipping-address" name="shipping[address]" value="<?php PHPurchaseCommon::showValue($s['address']); ?>">
          </li>

          <li>
            <label>&nbsp;</label>
            <input type="text" id="shipping-address2" name="shipping[address2]" value="<?php PHPurchaseCommon::showValue($s['address2']); ?>">
          </li>

          <li>
            <label>City:</label>
            <input type="text" id="shipping-city" name="shipping[city]" value="<?php PHPurchaseCommon::showValue($s['city']); ?>">
          </li>

          <li>
            <label class="short">State:</label>
            <select style="min-width: 125px;" id="shipping-state" class="required" title="State shipping address" name="shipping[state]"></select>
          </li>

          <li>
            <label>Zip code:</label>
            <input type="text" id="shipping-zip" name="shipping[zip]" value="<?php PHPurchaseCommon::showValue($s['zip']); ?>">
          </li>

          <li>
            <label class="short">Country:</label>
            <select title="country" id="shipping-country" name="shipping[country]">
              <?php foreach(PHPurchaseCommon::getCountries() as $code => $name): ?>
              <option value="<?php echo $code ?>"><?php echo $name ?></option>
              <?php endforeach; ?>
            </select>
          </li>
        </ul>
      </td>
    <?php else: ?>
      <!--<input type='hidden' id='sameAsBilling' name='sameAsBilling' value='1' />-->
    <?php endif; ?>
      <td valign='top' id="ccInfo">
  
        <ul class="shortLabels">
          <?php if($gateway == 'freecheckout'): ?>
            <li><h2>Contact Information</h2></li>
          <?php else: ?>
            <li><h2>Payment Information</h2></li>
          <?php endif; ?>
        
          <?php if($gateway != 'freecheckout'): ?>
          <li>
            <label>Card Type:</label>
            <select id="payment-cardType" name="payment[cardType]">
              <?php foreach($cardTypes as $name => $value): ?>
                <option value="<?php echo $value ?>"><?php echo $name ?></option>
              <?php endforeach; ?>
            </select>
          </li>
        
          <li>
            <label>Card&nbsp;Number:</label>
            <input type="text" id="payment-cardNumber" name="payment[cardNumber]" value="<?php PHPurchaseCommon::showValue($p['cardNumber']); ?>">
          </li>
        
          <li>
            <label>Expiration:</label>
            <select id="payment-cardExpirationMonth" name="payment[cardExpirationMonth]">
              <option value=''></option>
              <?php 
                for($i=1; $i<=12; $i++){
                  $val = $i;
                  if(strlen($val) == 1) {
                    $val = '0' . $i;
                  }
                  echo "<option value='$val'>$val</option>\n";
                } 
              ?>
            </select>
          
          /
            <select id="payment-cardExpirationYear" name="payment[cardExpirationYear]" style="margin:0;">
              <option value=''></option>
              <?php
                $year = date('Y');
                for($i=$year; $i<=$year+12; $i++){
                  echo "<option value='$i'>$i</option>\n";
                } 
              ?>
            </select>
          
          </li>
          
          <li>
            <label>Security ID:</label>
            <input type="text" id="payment-securityId" name="payment[securityId]" style="width: 30px;" value="<?php PHPurchaseCommon::showValue($p['securityId']); ?>">
            <p class="description">Security code on back of card</p>
          </li>

          <?php endif; ?>
        
          <li>
            <label>Email:</label>
            <input type="text" id="payment-email" name="payment[email]" value="<?php PHPurchaseCommon::showValue($p['email']); ?>">
          </li>
        
          <li>
            <label>Phone:</label>
            <input type="text" id="payment-phone" name="payment[phone]" value="<?php PHPurchaseCommon::showValue($p['phone']); ?>">
          </li> 

          <?php if($_SESSION['PHPurchaseCart']->hasSubscriptionProducts()): ?>
            <li><label>Password:</label>
            <input type="password" id="payment-password" name="payment[password]" value="<?php PHPurchaseCommon::showValue($p['password']); ?>">
            </li>

            <li><label>Confirm Password:</label>
            <input type="password" id="payment-password2" name="payment[password2]" value="<?php PHPurchaseCommon::showValue($p['password2']); ?>">
            </li>
          <?php endif; ?>

          <li>&nbsp;</li>

          <li>
            <!--<label>&nbsp;</label>-->
            <?php
              $cartImgPath = $setting->lookupValue('cart_images_url');
              if($cartImgPath) {
                if(strpos(strrev($cartImgPath), '/') !== 0) {
                  $cartImgPath .= '/';
                }
                $completeImgPath = $cartImgPath . 'complete-order.png';
              }
            ?>
            <?php if($cartImgPath): ?>
              <input id="PHPurchaseCheckoutButton" type="image" src='<?php echo $completeImgPath ?>' value="Complete Order" />
            <?php else: ?>
              <input id="PHPurchaseCheckoutButton" class="PHPurchaseButtonPrimary" type="submit"  value="Complete Order" />
            <?php endif; ?>

            <p class="description" style="color: #757575;">Your receipt will be on the next page and also immediately emailed to you.
            <strong>We respect your&nbsp;privacy!</strong></p>
			<p style="float:right;margin-top:10px;">
			<img alt="ups charcoal shipping" src="../../../media/LOGO_L.gif">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<script type="text/javascript" language="javascript">var ANS_customer_id="bf9aa227-0e2a-47f5-9198-a661aea7c0a0";</script> <script type="text/javascript" language="javascript" src="//verify.authorize.net/anetseal/seal.js" ></script></p>
          </li>
        </ul>
              
      </td>
    </tr>
</table>

</form>
<script>document.getElementById('phorm2').style.display='block';</script>*/
?>

<noscript class="no-script">Please Enable Javascript. For information on how to enable javascript, please click <a href="http://help.yahoo.com/l/us/yahoo/help/faq/browsers/browsers-63474.html" target="_blank">here.</a></noscript>
<form class="phorm2" action='<?php echo $url ?>' method='post' id="phorm2">
  <input type='hidden' name='phpurchase-action' value='<?php echo $gateway ?>'>
  <table id="address-table">
    <tr>
      <td valign='top' style="">
        <ul id="billingAddress" class="shortLabels" style="width: 275px;">
          <?php if($gateway == 'freecheckout'): ?>
            <li><h2>Shipping Address</h2></li>
          <?php else: ?>
            <li><h2>Billing Address</h2></li>
          <?php endif; ?>

		  <li style="<?php if(!$_SESSION['PHPurchaseCart']->isPallet()){echo 'display: none';}?>">
            <label>Company name:</label>
            <input type="text" id="billing-companyName" name="companyName" <?php if($_SESSION['PHPurchaseCart']->isPallet()){echo 'required';}?>>
          </li>
		  
          <li>
            <label>First name:</label>
            <input type="text" id="billing-firstName" name="billing[firstName]" value="<?php PHPurchaseCommon::showValue($b['firstName']); ?>">
          </li>

          <li>
            <label>Last name:</label>
            <input type="text" id="billing-lastName" name="billing[lastName]" value="<?php PHPurchaseCommon::showValue($b['lastName']); ?>">
          </li>

          <li>
            <label>Address:</label>
            <input type="text" id="billing-address" name="billing[address]" value="<?php PHPurchaseCommon::showValue($b['address']); ?>">
          </li>

          <li>
            <label>&nbsp;</label>
            <input type="text" id="billing-address2" name="billing[address2]" value="<?php PHPurchaseCommon::showValue($b['address2']); ?>">
          </li>

          <li>
            <label>City:</label>
            <input type="text" id="billing-city" name="billing[city]" value="<?php PHPurchaseCommon::showValue($b['city']); ?>">
          </li>

          <li><label class="short">State:</label>
          <select style="min-width: 125px;" id="billing-state" class="required" title="State billing address" name="billing[state]"></select>
          </li>

          <li>
            <label>Zip code:</label>
            <input type="text" id="billing-zip" name="billing[zip]" value="<?php PHPurchaseCommon::showValue($b['zip']); ?>">
          </li>

          <li>
            <label class="short">Country:</label>
            <select title="country" id="billing-country" name="billing[country]">
              <?php foreach(PHPurchaseCommon::getCountries() as $code => $name): ?>
              <option value="<?php echo $code ?>"><?php echo $name ?></option>
              <?php endforeach; ?>
            </select>
          </li>
        </ul>
		<?php if($gateway == 'freecheckout'): ?>
			 <input type='hidden' id='sameAsBilling' name='sameAsBilling' value='1' />
		<?php endif; ?>
      </td>
	      <?php if($_SESSION['PHPurchaseCart']->requireShipping() && $gateway != 'freecheckout'): ?>
	  <td valign='top' style="">
        <ul id="shipping-info">
          <li><h2>Shipping Address</h2></li>
    
          <li>
            <label style='width: auto;'>Same as billing address:</label>
            <input type='checkbox' id='sameAsBilling' name='sameAsBilling' value='1' style='width: auto;'>
          </li>
        </ul>

        <ul id="shippingAddress" class="shortLabels" style="width: 275px; display: none;">

          <li>
            <label>First name:</label>
            <input type="text" id="shipping-firstName" name="shipping[firstName]" value="<?php PHPurchaseCommon::showValue($s['firstName']); ?>">
          </li>

          <li>
            <label>Last name:</label>
            <input type="text" id="shipping-lastName" name="shipping[lastName]" value="<?php PHPurchaseCommon::showValue($s['lastName']); ?>">
          </li>

          <li>
            <label>Address:</label>
            <input type="text" id="shipping-address" name="shipping[address]" value="<?php PHPurchaseCommon::showValue($s['address']); ?>">
          </li>

          <li>
            <label>&nbsp;</label>
            <input type="text" id="shipping-address2" name="shipping[address2]" value="<?php PHPurchaseCommon::showValue($s['address2']); ?>">
          </li>

          <li>
            <label>City:</label>
            <input type="text" id="shipping-city" name="shipping[city]" value="<?php PHPurchaseCommon::showValue($s['city']); ?>">
          </li>

          <li>
            <label class="short">State:</label>
            <select style="min-width: 125px;" id="shipping-state" class="required" title="State shipping address" name="shipping[state]"></select>
          </li>

          <li>
            <label>Zip code:</label>
            <input type="text" id="shipping-zip" name="shipping[zip]" value="<?php PHPurchaseCommon::showValue($s['zip']); ?>">
          </li>

          <li>
            <label class="short">Country:</label>
            <select title="country" id="shipping-country" name="shipping[country]">
              <?php foreach(PHPurchaseCommon::getCountries() as $code => $name): ?>
              <option value="<?php echo $code ?>"><?php echo $name ?></option>
              <?php endforeach; ?>
            </select>
          </li>
		</ul>
		<ul class="shortLabels" style="width: 275px; <?php if(!$_SESSION['PHPurchaseCart']->isPallet()){echo 'display: none';}?>">
		  <li>
		    <label class="short" style="font-size: 0.75em">Does your delivery location has a loading dock?</label>
			<input type="checkbox" name="loadingDock" value="exists">Yes
		  </li>
		</ul>
		<!--<ul class="shortLabels" style="width: 275px; <?php //if(!$_SESSION['PHPurchaseCart']->isPallet()){echo 'display: none';}?>">
		  <li>
            <label class="short">Location Type</label>
			<span style="margin-left: -4px; margin-right: -6px;">:</span>
			<div style="display: inline-table">
			<select title="type" id="shipping-location-type" name="locationType">
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
			<div style="font-size: .7em; font-style: italic;margin-left: 5px;color: #858585;">For freight/pallet shipping only</div>
			</div>
          </li>
        </ul>-->
      </td>
    <?php else: ?>
      <!--<input type='hidden' id='sameAsBilling' name='sameAsBilling' value='1' />-->
    <?php endif; ?>
      <td valign='top' id="ccInfo">
  
        <ul class="shortLabels">
          <?php if($gateway == 'freecheckout'): ?>
            <li><h2>Contact Information</h2></li>
          <?php else: ?>
            <li><h2>Payment Information</h2></li>
          <?php endif; ?>
        
          <?php if($gateway != 'freecheckout'): ?>
          <li>
            <label>Card Type:</label>
            <select id="payment-cardType" name="payment[cardType]">
              <?php foreach($cardTypes as $name => $value): ?>
                <option value="<?php echo $value ?>"><?php echo $name ?></option>
              <?php endforeach; ?>
            </select>
          </li>
        
          <li>
            <label>Card&nbsp;Number:</label>
            <input type="text" id="payment-cardNumber" name="payment[cardNumber]" value="<?php PHPurchaseCommon::showValue($p['cardNumber']); ?>">
          </li>
        
          <li>
            <label>Expiration:</label>
            <select id="payment-cardExpirationMonth" name="payment[cardExpirationMonth]">
              <option value=''></option>
              <?php 
                for($i=1; $i<=12; $i++){
                  $val = $i;
                  if(strlen($val) == 1) {
                    $val = '0' . $i;
                  }
                  echo "<option value='$val'>$val</option>\n";
                } 
              ?>
            </select>
          
          /
            <select id="payment-cardExpirationYear" name="payment[cardExpirationYear]" style="margin:0;">
              <option value=''></option>
              <?php
                $year = date('Y');
                for($i=$year; $i<=$year+12; $i++){
                  echo "<option value='$i'>$i</option>\n";
                } 
              ?>
            </select>
          
          </li>
          
          <li>
            <label>Security ID:</label>
            <input type="text" id="payment-securityId" name="payment[securityId]" style="width: 30px;" value="<?php PHPurchaseCommon::showValue($p['securityId']); ?>">
            <p class="description">Security code on back of card</p>
          </li>

          <?php endif; ?>
        
          <li>
            <label>Email:</label>
            <input type="text" id="payment-email" name="payment[email]" value="<?php PHPurchaseCommon::showValue($p['email']); ?>">
          </li>
        
          <li>
            <label>Phone:</label>
            <input type="text" id="payment-phone" name="payment[phone]" value="<?php PHPurchaseCommon::showValue($p['phone']); ?>">
          </li> 

          <?php if($_SESSION['PHPurchaseCart']->hasSubscriptionProducts()): ?>
            <li><label>Password:</label>
            <input type="password" id="payment-password" name="payment[password]" value="<?php PHPurchaseCommon::showValue($p['password']); ?>">
            </li>

            <li><label>Confirm Password:</label>
            <input type="password" id="payment-password2" name="payment[password2]" value="<?php PHPurchaseCommon::showValue($p['password2']); ?>">
            </li>
          <?php endif; ?>

          <li>&nbsp;</li>

          <li>
            <!--<label>&nbsp;</label>-->
            <?php
              $cartImgPath = $setting->lookupValue('cart_images_url');
              if($cartImgPath) {
                if(strpos(strrev($cartImgPath), '/') !== 0) {
                  $cartImgPath .= '/';
                }
                $completeImgPath = $cartImgPath . 'complete-order.png';
              }
            ?>
            <?php if($cartImgPath): ?>
              <input id="PHPurchaseCheckoutButton" type="image" src='<?php echo $completeImgPath ?>' value="Complete Order" />
            <?php else: ?>
              <input id="PHPurchaseCheckoutButton" class="PHPurchaseButtonPrimary" type="submit"  value="Complete Order" />
            <?php endif; ?>

            <p class="description" style="color: #757575;">Your receipt will be on the next page and also immediately emailed to you.
            <strong>We respect your&nbsp;privacy!</strong></p>
			<p style="float:right;margin-top:10px;">
			<img alt="ups charcoal shipping" src="../../../media/LOGO_L.gif">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<script type="text/javascript" language="javascript">var ANS_customer_id="bf9aa227-0e2a-47f5-9198-a661aea7c0a0";</script> <script type="text/javascript" language="javascript" src="//verify.authorize.net/anetseal/seal.js" ></script></p>
          </li>
        </ul>
              
      </td>
    </tr>
</table>

</form>
<script>document.getElementById('phorm2').style.display='block';</script>