<?php
require_once dirname(__FILE__) . '/../models/setting.php';
$setting = new PHPurchaseSetting();
?>

<div id="saveResult"></div>

<h2>PHPurchase Membership Settings</h2>

<div id="widgets-left">
  <div id="available-widgets">
    
    <!-- Password reset email settings -->
    <div class="widgets-holder-wrap">
      <div class="sidebar-name">
        <div class="sidebar-name-arrow"><br/></div>
        <h3>Reset Password Email Settings<span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
      </div>
      <div class="widget-holder">
        <p class="description">Customize the email that gets sent when a member resets their password.</p>
        <div>
          <form id='resetForm' class="ajaxSettingForm" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>" method='post'>
            <input type='hidden' name='action' value='save_settings' />
            <input type='hidden' name='_success' value='Reset password email settings saved.'>
            <ul>
              
              <li><label style="display: inline-block; width: 120px; text-align: right;" for='reset_from_name'>From Name:</label>
              <input type='text' name='reset_from_name' id='reset_from_name' style='width: 375px;' 
              value='<?php echo $setting->lookupValue('reset_from_name'); ?>' />
              <p style="margin-left: 125px;" class="description">The name of the person from whom the email will be sent. 
                You may want this to be your company name.</p></li>

              <li><label style="display: inline-block; width: 120px; text-align: right;" for='reset_from_address'>From Address:</label>
              <input type='text' name='reset_from_address' id='reset_from_address' style='width: 375px;' 
              value='<?php echo $setting->lookupValue('reset_from_address'); ?>' />
              <p  style="margin-left: 125px;" class="description">The email address the email will be from.</p>
              </li>

              <li><label style="display: inline-block; width: 120px; text-align: right;" for='reset_subject'>Email Subject:</label>
              <input type='text' name='reset_subject' id='reset_subject' style='width: 375px;' 
              value='<?php echo $setting->lookupValue('reset_subject'); ?>' />
              <p style="margin-left: 125px;" class="description">The subject of the email</p></li>
            
              <li><label style="display: inline-block; width: 120px; text-align: right; margin-top: 0px;" for='reset_intro'>Email Intro:</label>
              <br/><textarea style="width: 375px; height: 140px; margin-left: 125px; margin-top: -20px;" 
              name='reset_intro'><?php echo $setting->lookupValue('reset_intro'); ?></textarea>
              <p style="margin-left: 125px;" class="description">This text will appear at the top of the reset email message above the newly generated password.</p></li>
              
              <li><label style="display: inline-block; width: 120px; text-align: right;" for='submit'>&nbsp;</label>
              <input id='submitResetForm' type='submit' name='_submit' class="button-primary" style='width: 60px;' value='Save' /></li>
              
            </ul>
          </form>
        </div>
      </div>
    </div>
    
    <!-- Credit card is about to expire email settings -->
    <div class="widgets-holder-wrap">
      <div class="sidebar-name">
        <div class="sidebar-name-arrow"><br/></div>
        <h3>Recurring Charge Receipt Email Settings<span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
      </div>
      <div class="widget-holder">
        <p class="description">Customize the email that gets sent when a member's credit card is auto-billed.</p>
        <div>
          <form id='recurringChargeForm' class="ajaxSettingForm" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>" method='post'>
            <input type='hidden' name='action' value='save_settings' />
            <input type='hidden' name='_success' value='Recurring charge email settings saved.'>
            <ul>
              
              <li><label style="display: inline-block; width: 120px; text-align: right;" for='auto_charge_from_name'>From Name:</label>
              <input type='text' name='auto_charge_from_name' id='auto_charge_from_name' style='width: 375px;' 
              value='<?php echo $setting->lookupValue('auto_charge_from_name'); ?>' />
              <p style="margin-left: 125px;" class="description">The name of the person from whom the email will be sent. 
                You may want this to be your company name.</p></li>

              <li><label style="display: inline-block; width: 120px; text-align: right;" for='auto_charge_from_address'>From Address:</label>
              <input type='text' name='auto_charge_from_address' id='auto_charge_from_address' style='width: 375px;' 
              value='<?php echo $setting->lookupValue('auto_charge_from_address'); ?>' />
              <p  style="margin-left: 125px;" class="description">The email address the email will be from.</p>
              </li>

              <li><label style="display: inline-block; width: 120px; text-align: right;" for='auto_charge_subject'>Email Subject:</label>
              <input type='text' name='auto_charge_subject' id='auto_charge_subject' style='width: 375px;' 
              value='<?php echo $setting->lookupValue('auto_charge_subject'); ?>' />
              <p style="margin-left: 125px;" class="description">The subject of the email</p></li>
            
              <li><label style="display: inline-block; width: 120px; text-align: right; margin-top: 0px;" for='auto_charge_intro'>Email Message:</label>
              <br/><textarea style="width: 375px; height: 140px; margin-left: 125px; margin-top: -20px;" 
              name='auto_charge_intro'><?php echo $setting->lookupValue('auto_charge_intro'); ?></textarea>
              
              <li><label style="display: inline-block; width: 120px; text-align: right;" for='submit'>&nbsp;</label>
              <input id='submitResetForm' type='submit' name='_submit' class="button-primary" style='width: 60px;' value='Save' /></li>
              
            </ul>
          </form>
        </div>
      </div>
    </div>
    
    <!-- Credit card repeat transaction failed email settings -->
    <div class="widgets-holder-wrap">
      <div class="sidebar-name">
        <div class="sidebar-name-arrow"><br/></div>
        <h3>Recurring Transaction Failed Email Settings<span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
      </div>
      <div class="widget-holder">
        <p class="description">Customize the email that gets sent when a member's credit card transaction fails when it is auto-charged during a subscription.</p>
        <div>
          <form id='failedTransactionForm' class="ajaxSettingForm" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>" method='post'>
            <input type='hidden' name='action' value='save_settings' />
            <input type='hidden' name='_success' value='Recurring transaction charge failed email settings saved.'>
            <ul>
              
              <li><label style="display: inline-block; width: 120px; text-align: right;" for='auto_charge_failed_from_name'>From Name:</label>
              <input type='text' name='auto_charge_failed_from_name' id='auto_charge_failed_from_name' style='width: 375px;' 
              value='<?php echo $setting->lookupValue('auto_charge_failed_from_name'); ?>' />
              <p style="margin-left: 125px;" class="description">The name of the person from whom the email will be sent. 
                You may want this to be your company name.</p></li>

              <li><label style="display: inline-block; width: 120px; text-align: right;" for='auto_charge_failed_from_address'>From Address:</label>
              <input type='text' name='auto_charge_failed_from_address' id='auto_charge_failed_from_address' style='width: 375px;' 
              value='<?php echo $setting->lookupValue('auto_charge_failed_from_address'); ?>' />
              <p  style="margin-left: 125px;" class="description">The email address the email will be from.</p>
              </li>

              <li><label style="display: inline-block; width: 120px; text-align: right;" for='auto_charge_failed_subject'>Email Subject:</label>
              <input type='text' name='auto_charge_failed_subject' id='auto_charge_failed_subject' style='width: 375px;' 
              value='<?php echo $setting->lookupValue('auto_charge_failed_subject'); ?>' />
              <p style="margin-left: 125px;" class="description">The subject of the email</p></li>
            
              <li><label style="display: inline-block; width: 120px; text-align: right; margin-top: 0px;" for='auto_charge_failed_intro'>Email Intro:</label>
              <br/><textarea style="width: 375px; height: 140px; margin-left: 125px; margin-top: -20px;" 
              name='auto_charge_failed_intro'><?php echo $setting->lookupValue('auto_charge_failed_intro'); ?></textarea>
              <p style="margin-left: 125px;" class="description">This text will appear at the top of the email message above the description 
                of what you attempted charge to your customer.</p></li>
              
              <li><label style="display: inline-block; width: 120px; text-align: right;" for='submit'>&nbsp;</label>
              <input id='submitResetForm' type='submit' name='_submit' class="button-primary" style='width: 60px;' value='Save' /></li>
              
            </ul>
          </form>
        </div>
      </div>
    </div>
    
    
    <!-- Credit card is about to expire email settings -->
    <div class="widgets-holder-wrap">
      <div class="sidebar-name">
        <div class="sidebar-name-arrow"><br/></div>
        <h3>Expiring Credit Card Settings<span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
      </div>
      <div class="widget-holder">
        <p class="description">Customize the email that gets sent when a member's credit card is nearing expiration.</p>
        <div>
          <form id='expiringForm' class="ajaxSettingForm" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>" method='post'>
            <input type='hidden' name='action' value='save_settings' />
            <input type='hidden' name='_success' value='Expiring credit card email settings saved.'>
            <ul>
              
              <li><label style="display: inline-block; width: 120px; text-align: right;" for='expiring_from_name'>From Name:</label>
              <input type='text' name='expiring_from_name' id='expiring_from_name' style='width: 375px;' 
              value='<?php echo $setting->lookupValue('expiring_from_name'); ?>' />
              <p style="margin-left: 125px;" class="description">The name of the person from whom the email will be sent. 
                You may want this to be your company name.</p></li>

              <li><label style="display: inline-block; width: 120px; text-align: right;" for='expiring_from_address'>From Address:</label>
              <input type='text' name='expiring_from_address' id='expiring_from_address' style='width: 375px;' 
              value='<?php echo $setting->lookupValue('expiring_from_address'); ?>' />
              <p  style="margin-left: 125px;" class="description">The email address the email will be from.</p>
              </li>

              <li><label style="display: inline-block; width: 120px; text-align: right;" for='expiring_subject'>Email Subject:</label>
              <input type='text' name='expiring_subject' id='expiring_subject' style='width: 375px;' 
              value='<?php echo $setting->lookupValue('expiring_subject'); ?>' />
              <p style="margin-left: 125px;" class="description">The subject of the email</p></li>
            
              <li><label style="display: inline-block; width: 120px; text-align: right; margin-top: 0px;" for='expiring_intro'>Email Message:</label>
              <br/><textarea style="width: 375px; height: 140px; margin-left: 125px; margin-top: -20px;" 
              name='expiring_intro'><?php echo $setting->lookupValue('expiring_intro'); ?></textarea>
              
              <li><label style="display: inline-block; width: 120px; text-align: right;" for='submit'>&nbsp;</label>
              <input id='submitResetForm' type='submit' name='_submit' class="button-primary" style='width: 60px;' value='Save' /></li>
              
            </ul>
          </form>
        </div>
      </div>
    </div>
    
    
    <div class="widgets-holder-wrap">
      <div class="sidebar-name">
        <div class="sidebar-name-arrow"><br/></div>
        <h3>Other Membership Options<span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
      </div>
      <div class="widget-holder">
        <div>
          <form id='membershipOptions' class="ajaxSettingForm" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>" method='post'>
            <input type='hidden' name='action' value='save_settings' />
            <input type='hidden' name='_success' value='Membership option settings saved.'>
            <ul>
              
              <li><label style="display: inline-block; width: 120px; text-align: right;" for='reset_from_name'>Logout link:</label>
                <input type='radio' name='auto_logout_link' value='1' <?php echo $setting->lookupValue('auto_logout_link') ? 'checked="checked"' : '' ?> /> Yes
                <input type='radio' name='auto_logout_link' value=''  <?php echo $setting->lookupValue('auto_logout_link') ? '' : 'checked="checked"' ?> /> No
                <p style="margin-left: 125px;" class="description">Automatically add a logout link as the last link in your navigation</p></li>
              
              <li><label style="display: inline-block; width: 120px; text-align: right;" for='submit'>&nbsp;</label>
              <input id='submitResetForm' type='submit' name='_submit' class="button-primary" style='width: 60px;' value='Save' /></li>
              
            </ul>
          </form>
        </div>
      </div>
    </div>
    
    
    
  </div>
</div>
