<?php
require_once dirname(__FILE__) .  '/../models/tax-rate.php';
require_once dirname(__FILE__) .  '/../models/setting.php';
$rate = new PHPurchaseTaxRate();
$setting = new PHPurchaseSetting();
$successMessage = '';

if($_SERVER['REQUEST_METHOD'] == "POST") {
  if($_POST['phpurchase-action'] == 'save rate') {
    $data = $_POST['tax'];
    if(isset($data['zip']) && !empty($data['zip'])) {
      list($low, $high) = explode('-', $data['zip']);
      
      if(isset($low)) {
        $low = trim($low);
      }
      
      if(isset($high)) {
        $high = trim($high);
      }
      else { $high = $low; }
      
      if(is_numeric($low) && is_numeric($high)) {
        if($low > $high) {
          $x = $high;
          $high = $low;
          $low = $x;
        }
        $data['zip_low'] = $low;
        $data['zip_high'] = $high;
      }
      
    }
    $rate->setData($data);
    $rate->save();
    $rate->clear();
    $successMessage = "Tax rate saved";
  }
} 
elseif(isset($_GET['task']) && $_GET['task'] == 'deleteTax' && isset($_GET['id']) && $_GET['id'] > 0) {
  $id = PHPurchaseCommon::getVal('id');
  $rate->load($id);
  $rate->deleteMe();
  $rate->clear();
}

$cardTypes = $setting->lookupValue('auth_card_types');
if($cardTypes) {
  $cardTypes = explode('~', $cardTypes);
}
else {
  $cardTypes = array();
}

?>

<?php if(!empty($successMessage)): ?>
  
<script type='text/javascript'>
  var $j = jQuery.noConflict();

  $j(document).ready(function() {
    setTimeout("$j('#PHPurchaseSuccessBox').hide('slow')", 2000);
  });
</script>
  
<div class='PHPurchaseSuccessModal' id="PHPurchaseSuccessBox" style=''>
  <p><strong>Success</strong><br/>
  <?php echo $successMessage ?></p>
</div>


<?php endif; ?>

<!-- Example Code Block -->
<!--
<div id="widgets-left">
  <div id="available-widgets">
    
    <div class="widgets-holder-wrap">
      <div class="sidebar-name">
        <div class="sidebar-name-arrow"><br/></div>
        <h3>Example Setting <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
      </div>
      <div class="widget-holder">
        <p class="description">This is a test</p>
        <div>
          <p>This is the content area</p>
        </div>
      </div>
    </div>
    
  </div>
</div>
-->

<div id="saveResult"></div>

<div id="widgets-left" style="margin-right: 50px;">
  <div id="available-widgets">
    
    <!-- Order Number -->
    <div class="widgets-holder-wrap">
      <div class="sidebar-name">
        <div class="sidebar-name-arrow"><br/></div>
        <h3>Main Settings <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
      </div>
      <div class="widget-holder">
        <p class="description">In order to get updates as they are released you must enter in your order number.</p>
        <div>
          <form id="orderNumberForm" class="ajaxSettingForm" action="" method='post'>
            <input type='hidden' name='action' value='save_settings' />
            <input type='hidden' name='_success' value='Your main settings have been saved.'>
            <ul>
              <li>
                <label style="display: inline-block; width: 120px; text-align: right;"  for='order_number'>Order Number:</label>
                <input type='text' name='order_number' id='order_number' style='width: 375px;' value='<?php echo $setting->lookupValue('order_number'); ?>' />
              </li>
              <li>
                <label style="display: inline-block; width: 120px; text-align: right;" for='paypalpro_email'>Hide system pages:</label>
                <input type='radio' name='hide_system_pages' id='hide_system_pages' value='1' 
                  <?php echo $setting->lookupValue('hide_system_pages') == '1' ? 'checked="checked"' : '' ?>/> Yes
                <input type='radio' name='hide_system_pages' id='hide_system_pages' value='' 
                  <?php echo $setting->lookupValue('hide_system_pages') != '1'? 'checked="checked"' : '' ?>/> No
                <p class="label_desc" style="width: 450px;">Hiding system pages will hide all the pages that PHPurchase installs 
                  from your site's navigation. Express, IPN, and Receipt will always be hidden. Selecting 'Yes' will also hide
                  Store, Cart, and Checkout which you may want to have your customers access through the PHPurchase Shopping Cart widget rather than your
                  site's main navigation.</p>
              </li>
              <li>
                <label style="display: inline-block; width: 120px; text-align: right;"  for='order_number'>Home country:</label>
                <select title="country" id="home_country" name="home_country">
                  <?php 
                    $homeCountryCode = 'US';
                    $homeCountry = $setting->lookupValue('home_country');
                    if($homeCountry) {
                      list($homeCountryCode, $homeCountryName) = explode('~', $homeCountry);
                    }
                    
                    foreach(PHPurchaseCommon::getCountries(true) as $code => $name) {
                      $selected = ($code == $homeCountryCode) ? 'selected="selected"' : '';
                      echo "<option value='$code~$name' $selected>$name</option>";
                    }
                  ?>
                </select>
                <p class="label_desc">Your home country will be the default country on your checkout form</p>
              </li>
              <li>
                <label style="display: inline-block; width: 120px; text-align: right;">Currency symbol:</label>
                <input type="text" name="currency_symbol" value="<?php echo htmlentities($setting->lookupValue('currency_symbol'));  ?>" id="currency_symbol">
                <span class="description">Use the HTML entity such as &amp;pound; for &pound; British Pound Sterling or &amp;euro; for &euro; Euro</span>
              </li>
              <li>
                <label style="display: inline-block; width: 120px; text-align: right;">Currency character:</label>
                <input type="text" name="currency_symbol_text" value="<?php echo $setting->lookupValue('currency_symbol_text'); ?>" id="currency_symbol_text">
                <span class="description">Do NOT use the HTML entity. This is the currency character used for the email receipts.</span>
              </li>
              <li>
                <label style="display: inline-block; width: 120px; text-align: right;" for='international_sales'>International sales:</label>
                <input type='radio' name='international_sales' id='international_sales_yes' value='1' 
                  <?php echo $setting->lookupValue('international_sales') == '1' ? 'checked="checked"' : '' ?>/> Yes
                <input type='radio' name='international_sales' id='international_sales_no' value='' 
                  <?php echo $setting->lookupValue('international_sales') != '1'? 'checked="checked"' : '' ?>/> No
              </li>
              
              <li id="eligible_countries_block">
                <label style="display: inline-block; width: 120px; text-align: right;" for='countries[]'>Ship to countries:</label>
                <div style="float: none; margin: -10px 0px 20px 125px;">
                <select name="countries[]" class="multiselect" multiple="multiple">
                  <?php
                    $countryList = $setting->lookupValue('countries');
                    $countryList = $countryList ? explode(',', $countryList) : array();
                  ?>
                  <?php foreach(PHPurchaseCommon::getCountries(true) as $code => $country): ?>
                    <?php 
                      $selected = (in_array($code . '~' .$country, $countryList)) ? 'selected="selected"' : '';
                      if(!empty($code)):
                    ?>
                      <option value="<?php echo $code . '~' . $country; ?>" <?php echo $selected ?>><?php echo $country ?></option>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </select>
                </div>
              </li>
              <li><label style="display: inline-block; width: 120px; text-align: right;" for='auth_force_ssl]'>Use SSL:</label>
              <?php
                $force = $setting->lookupValue('auth_force_ssl');
              ?>
              <input type='radio' name='auth_force_ssl' value='yes' style='width: auto;' <?php if($force == 'yes') { echo "checked='checked'"; } ?>><label style='width: auto; padding-left: 5px;'>Yes</label>
              <input type='radio' name='auth_force_ssl' value='no' style='width: auto;' <?php if($force == 'no') { echo "checked='checked'"; } ?>><label style='width: auto; padding-left: 5px;'>No</label>
                <span style="margin-left: 10px;" class="description">Be sure to select yes for SSL when your site is live</span>
              </li>

              <li><label style="display: inline-block; width: 120px; text-align: right;" for='track_inventory'>Track inventory:</label>
              <?php
                $track = $setting->lookupValue('track_inventory');
              ?>
              <input type='radio' name='track_inventory' value='1' style='width: auto;' <?php if($track == '1') { echo "checked='checked'"; } ?>><label style='width: auto; padding-left: 5px;'>Yes</label>
              <input type='radio' name='track_inventory' value='0' style='width: auto;' <?php if($track == '0') { echo "checked='checked'"; } ?>><label style='width: auto; padding-left: 5px;'>No</label>
                <p style="width: 450px;" class="label_desc">This feature uses ajax. If you have javascript errors in your theme clicking Add To Cart buttons will not add products to the cart.</p>
              </li>

              <li>
                <label style="display: inline-block; width: 120px; text-align: right;" >&nbsp;</label>
                <input type='submit' name='submit' class="button-primary" style='width: 60px;' value='Save' />
              </li>

            </ul>
          </form>
        </div>
      </div>
    </div>
  
    <!-- Tax Rates -->
    <?php $rates = $rate->getModels(); ?>
    <div class="widgets-holder-wrap <?php echo count($rates) ? '' : 'closed'; ?>">
      <div class="sidebar-name">
        <div class="sidebar-name-arrow"><br/></div>
        <h3>Tax Rates <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
      </div>
      <div class="widget-holder">
        <p class="description">If you would like to collect sales tax please enter the tax rate information below. 
          You may enter tax rates for zip codes or states. If you are entering zip codes, you can enter individual 
          zip codes or zip code ranges. A zip code range is entered with the low value separated from the high value
          by a dash. For example, 23000-25000. Zip code tax rates take precedence over state tax rates.
          You may also choose whether or not you want to apply taxes to shipping charges.</p>
          
        <p class="description">NOTE: If you are using PayPal Website Payments Standard you must set up the tax rate 
          information <strong>in your paypal account</strong>.</p>
          
        <div>
          <form action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>" method='post'>
            <input type='hidden' name='phpurchase-action' value='save rate' />
            <input type='hidden' name='tax[id]' value='<?php echo $tax->id ?>' />
            <ul>
              <li><label for='tax[state]' style='width: auto;'>State:</label>
                <select name='tax[state]' id='tax_state'>
                  <option value="">&nbsp;</option>
                  <option value="All Sales">All Sales</option>
                  <optgroup label="United States">
                    <option value="AL">Alabama</option>
                    <option value="AK">Alaska</option>
                    <option value="AZ">Arizona</option>
                    <option value="AR">Arkansas</option>
                    <option value="CA">California</option>
                    <option value="CO">Colorado</option>
                    <option value="CT">Connecticut</option>
                    <option value="DE">Delaware</option>
                    <option value="FL">Florida</option>
                    <option value="GA">Georgia</option>
                    <option value="HI">Hawaii</option>
                    <option value="ID">Idaho</option>
                    <option value="IL">Illinois</option>
                    <option value="IN">Indiana</option>
                    <option value="IA">Iowa</option>
                    <option value="KS">Kansas</option>
                    <option value="KY">Kentucky</option>
                    <option value="LA">Louisiana</option>
                    <option value="ME">Maine</option>
                    <option value="MD">Maryland</option>
                    <option value="MA">Massachusetts</option>
                    <option value="MI">Michigan</option>
                    <option value="MN">Minnesota</option>
                    <option value="MS">Mississippi</option>
                    <option value="MO">Missouri</option>
                    <option value="MT">Montana</option>
                    <option value="NE">Nebraska</option>
                    <option value="NV">Nevada</option>
                    <option value="NH">New Hampshire</option>
                    <option value="NJ">New Jersey</option>
                    <option value="NM">New Mexico</option>
                    <option value="NY">New York</option>
                    <option value="NC">North Carolina</option>
                    <option value="ND">North Dakota</option>
                    <option value="OH">Ohio</option>
                    <option value="OK">Oklahoma</option>
                    <option value="OR">Oregon</option>
                    <option value="PA">Pennsylvania</option>
                    <option value="RI">Rhode Island</option>
                    <option value="SC">South Carolina</option>
                    <option value="SD">South Dakota</option>
                    <option value="TN">Tennessee</option>
                    <option value="TX">Texas</option>
                    <option value="UT">Utah</option>
                    <option value="VT">Vermont</option>
                    <option value="VA">Virginia</option>
                    <option value="WA">Washington</option>
                    <option value="WV">West Virginia</option>
                    <option value="WI">Wisconsin</option>
                    <option value="WY">Wyoming</option>
                  </optgroup>
                  <optgroup label="Canada">
                    <option value="AB">Alberta</option>
                    <option value="BC">British Columbia</option>
                    <option value="MB">Manitoba</option>
                    <option value="NB">New Brunswick</option>
                    <option value="NF">Newfoundland</option>
                    <option value="NT">Northwest Territories</option>
                    <option value="NS">Nova Scotia</option>
                    <option value="NU">Nunavut</option>
                    <option value="ON">Ontario</option>
                    <option value="PE">Prince Edward Island</option>
                    <option value="PQ">Quebec</option>
                    <option value="SK">Saskatchewan</option>
                    <option value="YT">Yukon Territory</option>
                  </optgroup>
                </select>
              
                <span style="width: auto; text-align: center; padding: 0px 10px;">or</span>
                <label for='tax[zip]' style='width:auto;'>Zip:</label>
                <input type='text' value='' name='tax[zip]' size="14" />
                <label for='tax[rate]' style='width:auto; padding-left: 5px;'>Rate:</label>
                <input type='text' value='' name='tax[rate]' style='width: 55px;' /> %
                <select name='tax[tax_shipping]'>
                  <option value="0">Don't tax shipping</option>
                  <option value="1">Tax shipping</option>
                </select>
                <input type='submit' name='submit' class="button-primary" style='width: 60px; margin: 10px; margin-right: 0px;' value='Save' />
              </li>
            </ul>
          </form>
          
          <?php if(count($rates)): ?>
          <table class="widefat" style='width: 350px; margin-bottom: 30px;'>
          <thead>
          	<tr>
          		<th>Location</th>
          		<th>Rate</th>
          		<th>Tax Shipping</th>
          		<th>Actions</th>
          	</tr>
          </thead>
          <tbody>
            <?php foreach($rates as $rate): ?>
             <tr>
               <td>
                 <?php 
                 if($rate->zip_low > 0) {
                   if($rate->zip_low > 0) { echo $rate->zip_low; }
                   if($rate->zip_high > $rate->zip_low) { echo '-' . $rate->zip_high; }
                 }
                 else {
                   echo $rate->getFullStateName();
                 }
                 ?>
               </td>
               <td><?php echo number_format($rate->rate,2) ?>%</td>
               <td>
                 <?php
                 echo $rate->tax_shipping > 0 ? 'yes' : 'no';
                 ?>
               </td>
               <td>
                 <a class='delete' href='?page=phpurchase-settings&task=deleteTax&id=<?php echo $rate->id ?>'>Delete</a>
               </td>
             </tr>
            <?php endforeach; ?>
          </tbody>
          </table>
          <?php endif; ?>
        </div>
      </div>
    </div>
    
    <!-- PayPal API Information -->
    <div class="widgets-holder-wrap <?php echo $setting->lookupValue('paypalpro_api_username') ? '' : 'closed'; ?>">
      <div class="sidebar-name">
        <div class="sidebar-name-arrow"><br/></div>
        <h3>PayPal Pro and Express Checkout Settings <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
      </div>
      <div class="widget-holder">
        <p class="description">If you have signed up for the PayPal Pro account or if you plan to use PayPal Express Checkout, 
          please configure you settings below.</p>
        <div>
          <form id="PayPalProApiForm" class="ajaxSettingForm" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>" method='post'>
            <input type='hidden' name='action' value='save_settings' />
            <input type='hidden' name='_success' value='Your PayPal Pro and Express Checkout settings have been saved.'>
            <ul>
              <li><label style="display: inline-block; width: 120px; text-align: right;" for='paypalpro_email'>PayPal Pro Email:</label>
              <input type='text' name='paypalpro_email' id='paypalpro_email' style='width: 375px;' 
              value='<?php echo $setting->lookupValue('paypalpro_email'); ?>' />
              </li>

              <li><label style="display: inline-block; width: 120px; text-align: right;" for='paypalpro_api_username'>API Username:</label>
              <input type='text' name='paypalpro_api_username' id='paypalpro_api_username' style='width: 375px;' 
              value='<?php echo $setting->lookupValue('paypalpro_api_username'); ?>' />
              </li>

              <li><label style="display: inline-block; width: 120px; text-align: right;" for='paypalpro_api_password'>API Password:</label>
              <input type='text' name='paypalpro_api_password' id='paypalpro_api_password' style='width: 375px;' 
              value='<?php echo $setting->lookupValue('paypalpro_api_password'); ?>' />
              </li>

              <li><label style="display: inline-block; width: 120px; text-align: right;" for='paypalpro_api_signature'>API Signature:</label>
              <input type='text' name='paypalpro_api_signature' id='paypalpro_api_signature' style='width: 375px;' 
              value='<?php echo $setting->lookupValue('paypalpro_api_signature'); ?>' />
              </li>

              <li><label style="display: inline-block; width: 120px; text-align: right;">&nbsp;</label>
                <input type='submit' name='submit' class="button-primary" style='width: 60px;' value='Save' /></li>
                
              <li><p class='label_desc' style='color: #999'>Note: The Website Payments Pro solution can only be implemented by UK, Canadian and US Merchants.
                  <a href="https://www.x.com/docs/DOC-1510">Learn more</a></p></li>
            </ul>
          </form>
        </div>
      </div>
    </div>
    
    <!-- PayPal Website Payments Standard -->
    <div class="widgets-holder-wrap <?php echo $setting->lookupValue('paypal_email') ? '' : 'closed'; ?>">
      <div class="sidebar-name">
        <div class="sidebar-name-arrow"><br/></div>
        <h3>PayPal Website Payments Standard <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
      </div>
      <div class="widget-holder">
        <div>
          <form id="PayPalStandardSettings" class="ajaxSettingForm" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>" method='post'>
            <input type='hidden' name='action' value='save_settings' />
            <input type='hidden' name='_success' value='Your PayPal Website Payments Standard settings have been saved.'>
            <ul>
              <li><label style="display: inline-block; width: 120px; text-align: right;" for='paypal_email'>PayPal Email:</label>
              <input type='text' name='paypal_email' id='paypal_email' style='width: 375px;' value='<?php echo $setting->lookupValue('paypal_email'); ?>' />
              </li>
              
              <label style="display: inline-block; width: 120px; text-align: right;" for="currency_code">Default Currency:</label>
              <select name="currency_code"  id="currency_code">
                <?php
                  $currencies = PHPurchaseCommon::getPayPalCurrencyCodes();
                  $current_lc = $setting->lookupValue('currency_code');
                  foreach($currencies as $name => $code) {
                    $selected = '';
                    if($code == $current_lc) {
                      $selected = 'selected="selected"';
                    }
                    echo "<option value='$code' $selected>$name</option>\n";
                  }
                ?>
              </select>

              <li><label style="display: inline-block; width: 120px; text-align: right;" for='shopping_url'>Shopping URL:</label>
              <input type='text' name='shopping_url' id='paypal_email' style='width: 375px;' value='<?php echo $setting->lookupValue('shopping_url'); ?>' />
              <p style="margin-left: 125px;" class="description">Used when buyers click 'Continue Shopping' in the PayPal Cart.</p>
              </li>

              <li><label style="display: inline-block; width: 120px; text-align: right;" for='paypal_return_url'>Return URL:</label>
              <input type='text' name='paypal_return_url' id='paypal_return_url' 
              style='width: 375px;' value='<?php echo $setting->lookupValue('paypal_return_url'); ?>' />
              <p style="margin-left: 125px;" class="description">Where buyers are sent after paying at PayPal.</p>
              </li>

              <li><label style="display: inline-block; width: 120px; text-align: right;" for='ipn_url'>Notification URL:</label>
              <span style="padding:0px; margin:0px;">
                <?php
                $ipnPage = get_page_by_path('store/ipn');
                $ipnUrl = get_permalink($ipnPage->ID);
                echo $ipnUrl;
                ?>
              </span>
              <p style="margin-left: 125px;" class="description">Instant Payment Notification (IPN)</p></li>

              <li><label style="display: inline-block; width: 120px; text-align: right;" for='submit'>&nbsp;</label>
              <input type='submit' name='submit' class="button-primary" style='width: 60px;' value='Save' /></li>
            </ul>
          </form>
        </div>
      </div>
    </div>
    
    <!-- Authorize.net Setings -->
    <a name="gateway"></a>
    <div class="widgets-holder-wrap <?php echo $setting->lookupValue('auth_url') ? '' : 'closed'; ?>">
      <div class="sidebar-name">
        <div class="sidebar-name-arrow"><br/></div>
        <h3>Authorize.net &amp; Quantum Gateway Settings <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
      </div>
      <div class="widget-holder">
        <p class="description">These settings configure your connection to your Authorize.net compatible payment gateway.</p>
        <p class="description"><b>Authorize.net URL:</b> <em>https://secure.authorize.net/gateway/transact.dll</em></p>
        <p class="description"><b>Quantum Gateway URL:</b> <em>https://secure.quantumgateway.com/cgi/authnet_aim.php</em></p>
        <div>
          <form id="AuthorizeFormSettings" class="ajaxSettingForm" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>" method='post'>
            <input type='hidden' name='action' value='save_settings' />
            <input type='hidden' name='_success' value='Your payment gateway settings have been saved.'>
            <ul>
              <li><label style="display: inline-block; width: 120px; text-align: right;" for='auth_url'>Gateway URL:</label>
              <input type='text' name='auth_url' id='auth_url' style='width: 375px;' value='<?php echo $setting->lookupValue('auth_url'); ?>' />
              </li>

              <li><label style="display: inline-block; width: 120px; text-align: right;" for='auth_username'>Username:</label>
              <input type='text' name='auth_username' id='auth_username' style='width: 375px;' value='<?php echo $setting->lookupValue('auth_username'); ?>' />
              </li>

              <li><label style="display: inline-block; width: 120px; text-align: right;" for='auth_trans_key'>Transaction key:</label>
              <input type='text' name='auth_trans_key' id='auth_trans_key' style='width: 375px;' 
                value='<?php echo $setting->lookupValue('auth_trans_key'); ?>' />
              </li>
              
              <li><label style="display: inline-block; width: 120px; text-align: right;" for='auth_vault_key'>Vault key:</label>
              <input type='text' name='auth_vault_key' id='auth_vault_key' style='width: 375px;' 
                value='<?php echo $setting->lookupValue('auth_vault_key'); ?>' /> 
              <p class="description" style='margin-left: 125px;'>The vault key is only used for Quantum Gateway subscriptions</p>
              </li>

              <li><label style="display: inline-block; width: 120px; text-align: right;" for="auth[auth_card_types]">Accept Cards:</label>
              <input type="checkbox" name="auth_card_types[]" value="mastercard" style='width: auto;' 
                <?php echo in_array('mastercard', $cardTypes) ? 'checked="checked"' : '' ?>><label style='width: auto; padding-left: 5px;'>Mastercard</label>
              <input type="checkbox" name="auth_card_types[]" value="visa" style='width: auto;'
                <?php echo in_array('visa', $cardTypes) ? 'checked="checked"' : '' ?>><label style='width: auto; padding-left: 5px;'>Visa</label>
              <input type="checkbox" name="auth_card_types[]" value="amex" style='width: auto;'
                <?php echo in_array('amex', $cardTypes) ? 'checked="checked"' : '' ?>><label style='width: auto; padding-left: 5px;'>American Express</label>
              <input type="checkbox" name="auth_card_types[]" value="discover" style='width: auto;'
                <?php echo in_array('discover', $cardTypes) ? 'checked="checked"' : '' ?>><label style='width: auto; padding-left: 5px;'>Discover</label>
              </li>

              <li><label style="display: inline-block; width: 120px; text-align: right;" for='submit'>&nbsp;</label>
              <input type='submit' name='submit' class="button-primary" style='width: 60px;' value='Save' />
              </li>
            </ul>
          </form>
        </div>
      </div>
    </div>
    
    <!-- Receipt Settings -->
    <div class="widgets-holder-wrap <?php echo $setting->lookupValue('receipt_from_name') ? '' : 'closed'; ?>">
      <div class="sidebar-name">
        <div class="sidebar-name-arrow"><br/></div>
        <h3>Email Receipt Settings <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
      </div>
      <div class="widget-holder">
        <div>
          <p class="description">These are the settings used for sending email receipts to your customers after they place an order.</p>
          <form id="emailReceiptForm" class="ajaxSettingForm" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>" method='post'>
            <input type='hidden' name='action' value='save_settings' />
            <input type='hidden' name='_success' value='The email receipt settings have been saved.'>
            <ul>
              
              <li><label style="display: inline-block; width: 120px; text-align: right;" for='receipt_from_name'>From Name:</label>
              <input type='text' name='receipt_from_name' id='receipt_from_name' style='width: 375px;' 
              value='<?php echo $setting->lookupValue('receipt_from_name'); ?>' />
              <p style="margin-left: 125px;" class="description">The name of the person from whom the email receipt will be sent. 
                You may want this to be your company name.</p></li>

              <li><label style="display: inline-block; width: 120px; text-align: right;" for='receipt_from_address'>From Address:</label>
              <input type='text' name='receipt_from_address' id='receipt_from_address' style='width: 375px;' 
              value='<?php echo $setting->lookupValue('receipt_from_address'); ?>' />
              <p  style="margin-left: 125px;" class="description">The email address the email receipt will be from.</p>
              </li>

              <li><label style="display: inline-block; width: 120px; text-align: right;" for='receipt_subject'>Receipt Subject:</label>
              <input type='text' name='receipt_subject' id='receipt_subject' style='width: 375px;' 
              value='<?php echo $setting->lookupValue('receipt_subject'); ?>' />
              <p style="margin-left: 125px;" class="description">The subject of the email receipt</p></li>
            
              <li><label style="display: inline-block; width: 120px; text-align: right; margin-top: 0px;" for='receipt_intro'>Receipt Intro:</label>
              <br/><textarea style="width: 375px; height: 140px; margin-left: 125px; margin-top: -20px;" 
              name='receipt_intro'><?php echo $setting->lookupValue('receipt_intro'); ?></textarea>
              <p style="margin-left: 125px;" class="description">This text will appear at the top of the receipt email message above the list of 
                items purchased.</p></li>

              <li><label style="display: inline-block; width: 120px; text-align: right;" for='receipt_copy'>Copy Receipt To:</label>
              <input type='text' name='receipt_copy' id='receipt_copy' style='width: 375px;' value='<?php echo $setting->lookupValue('receipt_copy'); ?>' />
              <p style="margin-left: 125px;" class="description">Use commas to separate addresses.</p>
              </li>
              
              <li><label style="display: inline-block; width: 120px; text-align: right;" for='submit'>&nbsp;</label>
              <input type='submit' name='submit' class="button-primary" style='width: 60px;' value='Save' /></li>
            </ul>
          </form>
        </div>
      </div>
    </div>
    
    <!-- Order Status Options -->
    <div class="widgets-holder-wrap <?php echo $setting->lookupValue('status_options') ? '' : 'closed'; ?>">
      <div class="sidebar-name">
        <div class="sidebar-name-arrow"><br/></div>
        <h3>Status Options<span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
      </div>
      <div class="widget-holder">
        <p class="description">Define the order status options to suite your business needs. For example, you may want to have new, complete, and canceled.</p>
        <div>
          <form id="statusOptionForm" class="ajaxSettingForm" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>" method='post'>
            <input type='hidden' name='action' value='save_settings' />
            <input type='hidden' name='_success' value='The order status option settings have been saved.'>
            <ul>
              
              <li><label style="display: inline-block; width: 120px; text-align: right;" for='status_options'>Order statuses:</label>
              <input type='text' name='status_options' id='status_options' style='width: 80%;' 
              value='<?php echo $setting->lookupValue('status_options'); ?>' />
              <p style="margin-left: 125px;" class="description">Separate values with commas. (ex. new,complete,cancelled)</p></li>

              <li><label style="display: inline-block; width: 120px; text-align: right;" for='submit'>&nbsp;</label>
              <input type='submit' name='submit' class="button-primary" style='width: 60px;' value='Save' /></li>
            </ul>
          </form>
        </div>
      </div>
    </div>

    <!-- Digital Product Settings -->
    <div class="widgets-holder-wrap <?php echo $setting->lookupValue('product_folder') ? '' : 'closed'; ?>">
      <div class="sidebar-name">
        <div class="sidebar-name-arrow"><br/></div>
        <h3>Digital Product Settings <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
      </div>
      <div class="widget-holder">
        <p class="description">Enter the absolute path to where you want to store your digital products. We suggest you choose a folder that is not
          web accessible. To help you figure out the path to your digital products folder, this is the absolute path to the page you are viewing now.<br/>
          <?php echo realpath('.'); ?><br/>
          Please note you should NOT enter a web url starting with http:// Your filesystem path will start with just a / 
        </p>
        <div>
          <form id="productFolderForm" class="ajaxSettingForm" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>" method='post'>
            <input type='hidden' name='action' value='save_settings' />
            <input type='hidden' name='_success' value='The product folder setting has been saved.'>
            <ul>
              <li><label style="display: inline-block; width: 120px; text-align: right;" for='product_folder'>Product folder:</label>
              <input type='text' name='product_folder' id='product_folder' style='width: 80%;' 
              value='<?php echo $setting->lookupValue('product_folder'); ?>' />
              <?php
                $dir = $setting->lookupValue('product_folder');
                if($dir) {
                  if(!file_exists($dir)) { mkdir($dir, 0700, true); }
                  if(!file_exists($dir)) { echo "<p class='label_desc' style='color: red;'><strong>WARNING:</strong> This directory does not exist.</p>"; }
                  elseif(!is_writable($dir)) { echo "<p class='label_desc' style='color: red;'><strong>WARNING:</strong> WordPress cannot write to this folder.</p>"; }
                }
              ?>
              </li>

              <li><label style="display: inline-block; width: 120px; text-align: right;" for='submit'>&nbsp;</label>
              <input type='submit' name='submit' class="button-primary" style='width: 60px;' value='Save' /></li>
            </ul>
          </form>
        </div>
      </div>
    </div>
    
    
    <!-- Store Home Page -->
    <div class="widgets-holder-wrap <?php echo $setting->lookupValue('store_url') ? '' : 'closed'; ?>">
      <div class="sidebar-name">
        <div class="sidebar-name-arrow"><br/></div>
        <h3>Store Home Page <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
      </div>
      <div class="widget-holder">
        <p class="description">This is the link to the page of your site that you consider to be the home page of your store.
          When a customer views the items in their shopping cart this is the link used by the "continue shopping" button.
          You might set this to be the home page of your website or, perhaps, another page within your website that you consider
          to be the home page of the store section of your website. If you do not set a value here, the home page of your website
          will be used.</p>
        <div>
          <form id="storeHomeForm" class="ajaxSettingForm" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>" method='post'>
            <input type='hidden' name='action' value='save_settings' />
            <input type='hidden' name='_success' value='The store home page setting has been saved.'>            
            <ul>
              
            <li><label style="display: inline-block; width: 120px; text-align: right;" for='store_url'>Store URL:</label>
            <input type='text' name='store_url' id='store_url' style='width: 80%;' value='<?php echo $setting->lookupValue('store_url'); ?>' />
            </li>

            <li><label style="display: inline-block; width: 120px; text-align: right;" for='submit'>&nbsp;</label>
            <input type='submit' name='submit' class="button-primary" style='width: 60px;' value='Save' /></li>
            
            </ul>
          </form>
        </div>
      </div>
    </div>
    
    <!-- Customize Cart Images -->
    <div class="widgets-holder-wrap <?php echo $setting->lookupValue('cart_images_url') ? '' : 'closed'; ?>">
      <div class="sidebar-name">
        <div class="sidebar-name-arrow"><br/></div>
        <h3>Customize Cart Images <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
      </div>
      <div class="widget-holder">
        <p class="description">If you would like to use your own shopping cart images (Add To Cart, Checkout, etc), enter the URL to the directory where you will be storing the images. The path should be outside the plugins/phpurchase directory so that they are not lost when you upgrade your PHPurchase intallation to a new version.</p>
        <p class="description">For example you may want to store your custom cart images here:<br/>
        <?php echo WPCURL ?>/uploads/cart-images/</p>
        <p class="description">Be sure that your path ends in a trailing slash like the example above and that you have all of the image names below in your directory:</p>
        <ul class="description" style='list-style-type: disc; padding: 0px 0px 0px 30px;'>
          <?php
          $dir = new DirectoryIterator(dirname(__FILE__) . '/../images');
          foreach ($dir as $fileinfo) {
              if (substr($fileinfo->getFilename(), -3) == 'png') {
                  echo '<li>' . $fileinfo->getFilename() . '</li>';
              }
          }
          ?>
        </ul>
        <div>
          <form id="cartImageForm" class="ajaxSettingForm" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>" method='post'>
            <input type='hidden' name='action' value='save_settings' />
            <input type='hidden' name='_success' value='The cart images setting has been saved.'>
            <ul>
              
              <li><label style="display: inline-block; width: 150px; text-align: right;" for='styles[url]'>URL to image directory:</label>
              <input type='text' name='cart_images_url' id='cart_images_url' style='width: 375px;' 
              value='<?php echo $setting->lookupValue('cart_images_url'); ?>' /></li>

              <li><label style="display: inline-block; width: 150px; text-align: right;" for='submit'>&nbsp;</label>
              <input type='submit' name='submit' class="button-primary" style='width: 60px;' value='Save' /></li>
            
            </ul>
          </form>
        </div>
      </div>
    </div>
  
    <!-- Customize CSS Styles -->
    <div class="widgets-holder-wrap <?php echo $setting->lookupValue('styles_url') ? '' : 'closed'; ?>">
      <div class="sidebar-name">
        <div class="sidebar-name-arrow"><br/></div>
        <h3>Customize Styles <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
      </div>
      <div class="widget-holder">
        <p class="description">If you would like to override the default styles, you may enter the URL to your custom style sheet.</p>
        <div>
          <form id="cssForm" class="ajaxSettingForm" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>" method='post'>
            <input type='hidden' name='action' value='save_settings' />
            <input type='hidden' name='_success' value='The custom css style setting has been saved.'>
            <ul>
              <li><label style="display: inline-block; width: 120px; text-align: right;" for='styles_url'>URL to CSS:</label>
              <input type='text' name='styles_url' id='styles_url' style='width: 375px;' value='<?php echo $setting->lookupValue('styles_url'); ?>' /></li>

              <li><label style="display: inline-block; width: 120px; text-align: right;" for='submit'>&nbsp;</label>
              <input type='submit' name='submit' class="button-primary" style='width: 60px;' value='Save' /></li>
            </ul>
          </form>
        </div>
      </div>
    </div>
    
    
    <!-- Error Logging -->
    <div class="widgets-holder-wrap <?php echo ($setting->lookupValue('enable_logging') || $setting->lookupValue('paypal_sandbox')) ? '' : 'closed'; ?>">
      <div class="sidebar-name">
        <div class="sidebar-name-arrow"><br/></div>
        <h3>Error Logging &amp; Debugging<span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
      </div>
      <div class="widget-holder">
        <div>
          <form id="debuggingForm" class="ajaxSettingForm" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>" method='post'>
            <input type='hidden' name='action' value='save_settings' />
            <input type='hidden' name='_success' value='The logging and debugging settings have been saved.'>
            <input type="hidden" name="enable_logging" value="" id="enable_logging" />
            <input type="hidden" name="paypal_sandbox" value="" id="paypal_sandbox" />
            <ul>
              <li>
                <label style="display: inline-block; width: 220px; text-align: right;" for='styles_url'>Enable logging:</label>
                <input type='checkbox' name='enable_logging' id='enable_logging' value='1'
                  <?php echo $setting->lookupValue('enable_logging') ? 'checked="checked"' : '' ?>
                />
                <span class="label_desc">Only enable logging when testing your site. The log file will grow quickly.</span>
              </li>
              
              <li>
                <label style="display: inline-block; width: 220px; text-align: right;" for='styles_url'>Use PayPal Sandbox:</label>
                <input type='checkbox' name='paypal_sandbox' id='paypal_sandbox' value='1' 
                  <?php echo $setting->lookupValue('paypal_sandbox') ? 'checked="checked"' : '' ?>
                />
                <span class="label_desc">Send transactions to <a href='https://developer.paypal.com'>PayPal's developer sandbox</a>.</span>
              </li>

              <li><label style="display: inline-block; width: 220px; text-align: right;" for='submit'>&nbsp;</label>
              <input type='submit' name='submit' class="button-primary" style='width: 60px;' value='Save' /></li>
            </ul>
            
          </form>
          
         
          <?php if(PHPurchaseLog::exists()): ?>
            <form action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>" method="post" style="padding: 10px 100px;">
              <input type="hidden" name="phpurchase-action" value="download log file" id="phpurchase-action" />
              <input type="submit" value="Download Log File" class="button-secondary" />
            </form>
          <?php endif; ?>
          
          
        </div>
      </div>
    </div>
    
    
    
  
  </div>
</div>




<script type='text/javascript'>
  $jq = jQuery.noConflict();
  
  $jq(document).ready(function() {
    //$jq(".multiselect").multiselect();
    $jq(".multiselect").multiselect({sortable: true});
    
    
    $jq('.sidebar-name').click(function() {
     $jq(this.parentNode).toggleClass("closed");
    });

    $jq('#international_sales_yes').click(function() {
     $jq('#eligible_countries_block').show();
    });

    $jq('#international_sales_no').click(function() {
     $jq('#eligible_countries_block').hide();
    });

    if($jq('#international_sales_no').attr('checked')) {
     $jq('#eligible_countries_block').hide();
    }
     
  });
  
</script>
