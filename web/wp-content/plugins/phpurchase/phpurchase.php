<?php
/*
Plugin Name: PHPurchase
Plugin URI: http://www.cart66.com
Description: Wordpress Shopping Cart
Version: 2.6.7
Author: Reality 66
Author URI: http://www.reality66.com

------------------------------------------------------------------------
Copyright 2011 Reality 66 LLC

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/


require_once(WP_PLUGIN_DIR. "/" . basename(dirname(__FILE__)) . "/cart-widget.php");
require_once(WP_PLUGIN_DIR. "/" . basename(dirname(__FILE__)) . "/models/common.php");
require_once(WP_PLUGIN_DIR. "/" . basename(dirname(__FILE__)) . "/models/model-abstract.php");
require_once(WP_PLUGIN_DIR. "/" . basename(dirname(__FILE__)) . "/models/setting.php");

ob_start();
$setting = new PHPurchaseSetting();
$proPath = WP_PLUGIN_DIR . '/phpurchase/pro';
define('PHPURCHASEPRO', file_exists($proPath));
define("PHPURCHASE_VERSION_NUMBER", '2.6.7');

/**
 * Require PHPurchase core models
 */ 
require_once(WP_PLUGIN_DIR. "/" . basename(dirname(__FILE__)) . "/models/product.php");
require_once(WP_PLUGIN_DIR. "/" . basename(dirname(__FILE__)) . "/models/cart.php");
require_once(WP_PLUGIN_DIR. "/" . basename(dirname(__FILE__)) . "/models/shipping-rule.php");
require_once(WP_PLUGIN_DIR. "/" . basename(dirname(__FILE__)) . "/models/shipping-method.php");
require_once(WP_PLUGIN_DIR. "/" . basename(dirname(__FILE__)) . "/models/shipping-rate.php");
require_once(WP_PLUGIN_DIR. "/" . basename(dirname(__FILE__)) . "/models/tax-rate.php");
require_once(WP_PLUGIN_DIR. "/" . basename(dirname(__FILE__)) . "/models/order.php");
require_once(WP_PLUGIN_DIR. "/" . basename(dirname(__FILE__)) . "/models/promotion.php");
require_once(WP_PLUGIN_DIR. "/" . basename(dirname(__FILE__)) . "/models/shortcode.php");
require_once(WP_PLUGIN_DIR. "/" . basename(dirname(__FILE__)) . "/models/button-manager.php");
require_once(WP_PLUGIN_DIR. "/" . basename(dirname(__FILE__)) . "/models/access-manager.php");
require_once(WP_PLUGIN_DIR. "/" . basename(dirname(__FILE__)) . "/models/ups.php");
require_once(WP_PLUGIN_DIR. "/" . basename(dirname(__FILE__)) . "/models/live-rates.php");
require_once(WP_PLUGIN_DIR. "/" . basename(dirname(__FILE__)) . "/models/log.php");
require_once(WP_PLUGIN_DIR. "/" . basename(dirname(__FILE__)) . "/models/exception.php");

if(!defined("IS_ADMIN"))
define("IS_ADMIN",  is_admin());
define("PHPURCHASE_SUPPORTED_WP_VERSION", version_compare(get_bloginfo("version"), '2.8.0', '>='));
define("CALLBACK_URL", "http://www.cart66.com");

define("WPCURL", PHPurchaseCommon::getWpContentUrl());
define("WPURL", PHPurchaseCommon::getWpUrl());

// Currency Symbols

$cs = $setting->lookupValue('currency_symbol');
$cs = $cs ? $cs : '$';
$cst = $setting->lookupValue('currency_symbol_text');
$cst = $cst ? $cst : '$';
$ccd = $setting->lookupValue('currency_code');
$ccd = $ccd ? $ccd : 'USD';
define("CURRENCY_SYMBOL", $cs);
define("CURRENCY_SYMBOL_TEXT", $cst);
define("CURRENCY_CODE", $ccd);

$phpurchaseLogging = $setting->lookupValue('enable_logging') ? true : false;
define("PHPURCHASE_DEBUG", $phpurchaseLogging);

$sandbox = $setting->lookupValue('paypal_sandbox') ? true : false;
define("SANDBOX", $sandbox);



function PHPurchaseCustomScripts() {
  $path = WPCURL . '/plugins/phpurchase/js/ajax-setting-form.js';
  wp_enqueue_script('ajax-setting-form', $path);
  
  // Include jquery-multiselect and jquery-ui
  wp_enqueue_script('jquery');
  wp_enqueue_script('jquery-ui-core');
  wp_enqueue_script('jquery-ui-sortable');
  $path = WPCURL . '/plugins/phpurchase/js/ui.multiselect.js';
  wp_enqueue_script('jquery-multiselect', $path, null, null, true);
}
add_action('admin_init', 'PHPurchaseCustomScripts');

/**
 * Link in the admin style sheet
 */
function PHPurchaseAdminRegisterStyles() {
  $widgetCss = WPURL . '/wp-admin/css/widgets.css';
  echo "<link rel='stylesheet' type='text/css' href='$widgetCss' />\n";
  
	$adminCss = WPCURL . '/plugins/phpurchase/admin/admin-styles.css';
  echo "<link rel='stylesheet' type='text/css' href='$adminCss' />\n";
  
  // echo "<link rel='stylesheet' type='text/css' href='https://ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/themes/base/ui.all.css' />\n";
  
  $phorm2Css = WPCURL . '/plugins/phpurchase/views/phorm2.css';
  echo "<link rel='stylesheet' type='text/css' href='$phorm2Css' />\n";
  
  $uiCss = WPCURL . '/plugins/phpurchase/views/jquery-ui-1.7.1.custom.css';
  echo "<link rel='stylesheet' type='text/css' href='$uiCss' />\n";
}
add_action('admin_head', 'PHPurchaseAdminRegisterStyles');

/**
 * Link in the public styles
 */
function PHPurchaseAddHeaderCode() {
  if(PHPURCHASEPRO) {
    echo '<meta name="generator" content="PHPurchase Professional ' . PHPURCHASE_VERSION_NUMBER . '" />' . "\n";
  }
  else {
    echo '<meta name="generator" content="PHPurchase ' . PHPURCHASE_VERSION_NUMBER . '" />' . "\n";
  }
  echo '<link rel="stylesheet" href="' . WPCURL . '/plugins/phpurchase/phpurchase.css" type="text/css" media="screen" />' . "\n";
  echo '<link rel="stylesheet" href="' . WPCURL . '/plugins/phpurchase/views/phorm2.css" type="text/css" media="screen" />' . "\n";
  echo '<link rel="stylesheet" href="' . WPCURL . '/plugins/phpurchase/views/buttons.css" type="text/css" media="screen" />' . "\n";

  $setting = new PHPurchaseSetting();
  if($css = $setting->lookupValue('styles_url')) {
    echo '<link rel="stylesheet" href="' . $css . '" type="text/css" media="screen" />';
  }

  // Check inventory on the checkout page
  if($_SERVER['REQUEST_METHOD'] == 'GET') {
    global $post;
    $checkoutPage = get_page_by_path('store/checkout');
    if($post->ID == $checkoutPage->ID) {
      $inventoryMessage = $_SESSION['PHPurchaseCart']->checkCartInventory();
      if(!empty($inventoryMessage)) { $_SESSION['PHPurchaseInventoryWarning'] = $inventoryMessage; }
    }
  }
  
}
add_action('wp_head', 'PHPurchaseAddHeaderCode');


if(PHPURCHASEPRO) {
  $gatewayName = PHPurchaseCommon::gatewayName();
  if($gatewayName == 'quantum') {
    require_once(WP_PLUGIN_DIR. "/" . basename(dirname(__FILE__)) . "/pro/Quantum/vault.php");
  }
  elseif($gatewayName == 'authnet') {
    require_once(WP_PLUGIN_DIR. "/phpurchase/pro/Authnet/cim.php");
  }
  require_once(WP_PLUGIN_DIR. "/" . basename(dirname(__FILE__)) . "/models/gravity-reader.php");
}

/**
 * Load the jquery library
 */
function PHPurchaseLoadJquery() { 
  wp_enqueue_script( 'jquery' ); 
} 
add_action('wp_print_scripts', 'PHPurchaseLoadJquery');

/**
 *  Add PHPurchase to the TinyMCE editor
 */
function PHPurchaseEditorAddButtons() {
  // Don't bother doing this stuff if the current user lacks permissions
  if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
  return;

  // Add only in Rich Editor mode
  if ( get_user_option('rich_editing') == 'true') {
    add_filter("mce_external_plugins", "PHPurchaseAddTinymcePlugin");
    add_filter('mce_buttons', 'PHPurchaseRegisterButton');
  }
}
 
function PHPurchaseRegisterButton($buttons) {
  array_push($buttons, "|", "phpurchase");
  return $buttons;
}
 
function PHPurchaseAddTinymcePlugin($plugin_array) {
  $plugin_array['phpurchase'] = plugins_url().'/phpurchase/js/editor_plugin.js';
  return $plugin_array;
}

// init process for button control
add_action('init', 'PHPurchaseEditorAddButtons');

/**
 * Load the cart from the session or put a new cart in the session
 */
function PHPurchaseInitCart() {
  $setting = new PHPurchaseSetting();
  $path = WP_PLUGIN_URL . '/phpurchase/js/phpurchase-library.js';
  $forceHttps = $setting->lookupValue('auth_force_ssl');
  if($forceHttps == 'yes' || $_SERVER['HTTPS'] == 'on' || $_SERVER['SERVER_PORT'] == 443) {
    $path = str_replace('http:', 'https:', $path);
  }
  
  wp_enqueue_script('phpurchase-library', $path, array('jquery'), '1.0');
  
  session_start();
  
  if(!isset($_SESSION['PHPurchaseCart'])) {
    $_SESSION['PHPurchaseCart'] = new PHPurchaseCart();
  }
  
  if(isset($_POST['task'])) {
    if($_POST['task'] == 'addToCart') {
      $itemId = PHPurchaseCommon::postVal('phpurchaseItemId');
      PHPurchaseCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Adding item to cart: $itemId");
      
      
      if(isset($_POST['options_1'])) {
        $options = PHPurchaseCommon::postVal('options_1');
      }
      if(isset($_POST['options_2'])) {
        $options .= '~' . PHPurchaseCommon::postVal('options_2');
      }
      
      
      // Update the page used for the Continue Shopping link
      $_SESSION['PHPurchaseLastPage'] = $_SERVER['HTTP_REFERER'];
      
      if(PHPurchaseProduct::confirmInventory($itemId, $options)) {
        $_SESSION['PHPurchaseCart']->addItem($itemId, 1, $options);
      }
      else {
        PHPurchaseCommon::log("Item not added due to inventory failure");
        wp_redirect($_SERVER['HTTP_REFERER']);
      }
    }
    elseif($_POST['task'] == 'updateCart') {
      
      if($_POST['updateCart'] == 'Calculate Shipping') {
        $_SESSION['phpurchase_shipping_zip'] = $_POST['shipping_zip'];
		$_SESSION['freight_loc_type'] = $_POST['locationType'];
		$_SESSION['liftGate'] = $_POST['liftGate'];
        $_SESSION['phpurchase_shipping_country_code'] = $_POST['shipping_country_code'];
      }
      
      
      // Not using live rates
      if(isset($_POST['shipping_method_id'])) {
        $shippingMethodId = $_POST['shipping_method_id'];
        $_SESSION['PHPurchaseCart']->setShippingMethod($shippingMethodId);
      }
      // Using live rates
      elseif(isset($_POST['live_rates'])) {
        if(isset($_SESSION['PHPurchaseLiveRates'])) {
          $_SESSION['PHPurchaseLiveRates']->setSelected($_POST['live_rates']);
        }
      }
      
      $qtys = PHPurchaseCommon::postVal('quantity');
      if(is_array($qtys)) {
        foreach($qtys as $itemIndex => $qty) {
          $item = $_SESSION['PHPurchaseCart']->getItem($itemIndex);
          if(!is_null($item) && get_class($item) == 'PHPurchaseCartItem') {
            if(PHPurchaseProduct::confirmInventory($item->getProductId(), $item->getOptionInfo(), $qty)) {
              $_SESSION['PHPurchaseCart']->setItemQuantity($itemIndex, $qty);
            }
            else {
              $qtyAvailable = PHPurchaseProduct::checkInventoryLevelForProduct($item->getProductId(), $item->getOptionInfo());
              $_SESSION['PHPurchaseCart']->setItemQuantity($itemIndex, $qtyAvailable);
              if(empty($_SESSION['PHPurchaseInventoryWarning'])) { $_SESSION['PHPurchaseInventoryWarning'] = ''; }
              $_SESSION['PHPurchaseInventoryWarning'] .= '<p>The quantity for ' . $item->getFullDisplayName() . " could not be changed to $qty because 
                we only have $qtyAvailable in stock.</p>";
              PHPurchaseCommon::log("Quantity available ($qtyAvailable) cannot meet desired quantity ($qty)");
            }
          }
        }
      }
      
      // Set custom values for individual products in the cart
      $custom = PHPurchaseCommon::postVal('customFieldInfo');
      if(is_array($custom)) {
        foreach($custom as $itemIndex => $info) {
          $_SESSION['PHPurchaseCart']->setCustomFieldInfo($itemIndex, $info);
        }
      }
      
      if($_POST['couponCode'] != '') {
        $couponCode = PHPurchaseCommon::postVal('couponCode');
        $_SESSION['PHPurchaseCart']->applyPromotion($couponCode);
      }
      else {
        $_SESSION['PHPurchaseCart']->resetPromotionStatus();
      }
      
    } // end elseif updateCart
  } // end if post task is set
  elseif(isset($_GET['task'])) {
    if($_GET['task']=='removeItem') {
      $itemIndex = PHPurchaseCommon::getVal('itemIndex');
      $_SESSION['PHPurchaseCart']->removeItem($itemIndex);
    } // end if remove item
  } // end if get task is set
  elseif(isset($_POST['phpurchase-action'])) {
    $task = PHPurchaseCommon::postVal('phpurchase-action');
    if($task == 'authcheckout') {
      $inventoryMessage = $_SESSION['PHPurchaseCart']->checkCartInventory();
      if(!empty($inventoryMessage)) { $_SESSION['PHPurchaseInventoryWarning'] = $inventoryMessage; }
    }
  }
  elseif(isset($_GET['phpurchase-task'])) {
    $task = PHPurchaseCommon::getVal('phpurchase-task');
    if($task == 'logout') {
      PHPurchaseLogout(); // TODO: What's this all about?
    }
  }
  
}
add_action('init', 'PHPurchaseInitCart');

/**
 * Put PHPurchase in the admin menu
 */
function PHPurchaseAdminMenu() {
  $icon = WPCURL . '/plugins/phpurchase/images/phpurchase_logo_16.gif';
  add_menu_page('PHPurchase', 'PHPurchase', '8', __FILE__, null, $icon);
  add_submenu_page(__FILE__, 'Orders', 'Orders', '8', __FILE__, 'PHPurchaseOrders');
  add_submenu_page(__FILE__, 'Products', 'Products', '7', 'phpurchase-products', 'PHPurchaseProducts');

  add_submenu_page(__FILE__, 'Inventory', 'Inventory', '8', 'phpurchase-inventory', 'PHPurchaseInventory');
  
  add_submenu_page(__FILE__, 'Promotions', 'Promotions', '8', 'phpurchase-promotions', 'PHPurchasePromotions');
  add_submenu_page(__FILE__, 'Shipping', 'Shipping', '8', 'phpurchase-shipping', 'PHPurchaseShipping');
  add_submenu_page(__FILE__, 'Settings', 'Settings', '8', 'phpurchase-settings', 'PHPurchaseSettings');
  if(PHPURCHASEPRO) {
    add_submenu_page(__FILE__, 'Members', 'Members', '8', 'phpurchase-members', 'PHPurchaseMembers');
    add_submenu_page(__FILE__, 'Members Settings', 'Members Settings', '8', 'phpurchase-members-settings', 'PHPurchaseMembersSettings');
  }
  add_submenu_page(__FILE__, 'Reports', 'Reports', '8', 'phpurchase-reports', 'PHPurchaseReports');
  add_submenu_page(__FILE__, 'Help', 'Help', '8', 'phpurchase-help', 'PHPurchaseHelp');
}
add_action('admin_menu', 'PHPurchaseAdminMenu');

function PHPurchaseOrders() {
  require_once(WP_PLUGIN_DIR. "/" . basename(dirname(__FILE__)) . "/models/order.php");
  
  echo "<h2>PHPurchase Orders</h2>";
    
  if($_SERVER['REQUEST_METHOD'] == 'GET' && $_GET['task'] == 'view') {
    $order = new PHPurchaseOrder($_GET['id']);
    $view = PHPurchaseCommon::getView('admin/order-view.php', array('order'=>$order)); 
  }
  elseif($_SERVER['REQUEST_METHOD'] == 'GET' && $_GET['task'] == 'delete') {
    $order = new PHPurchaseOrder($_GET['id']);
    $order->deleteMe();
    $view = PHPurchaseCommon::getView('admin/orders.php'); 
  }
  elseif($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['task'] == 'update order status') {
    $order = new PHPurchaseOrder($_POST['order_id']);
    $order->updateStatus(PHPurchaseCommon::postVal('status'));
    $view = PHPurchaseCommon::getView('admin/orders.php');
  }
  else {
    $view = PHPurchaseCommon::getView('admin/orders.php'); 
  }
  
  echo $view;
}

function PHPurchaseProducts() {
  echo "<h2>PHPurchase Products</h2>";
  $view = PHPurchaseCommon::getView('admin/products.php');
  echo $view; 
}

function PHPurchaseInventory() {
  echo "<h2>PHPurchase Inventory</h2>";
  $view = PHPurchaseCommon::getView('admin/inventory.php');
  echo $view; 
}

function PHPurchasePromotions() {
  echo "<h2>PHPurchase Promotions</h2>";
  $view = PHPurchaseCommon::getView('admin/promotions.php');
  echo $view;
}

function PHPurchaseSettings() {
  echo "<h2>PHPurchase Settings</h2>";
  $view = PHPurchaseCommon::getView('admin/settings.php');
  echo $view;
}

function PHPurchaseShipping() {
  echo "<h2>PHPurchase Settings</h2>";
  $view = PHPurchaseCommon::getView('admin/shipping.php');
  echo $view;
}

function PHPurchaseHelp() {
  $setting = new PHPurchaseSetting();
  define('HELP_URL', "http://www.cart66.com/support");
  $view = PHPurchaseCommon::getView('admin/help.php');
  echo $view;
}

function PHPurchaseMembers() {
  $id = PHPurchaseCommon::getVal('id');
  if(isset($_GET['phpurchase-task']) && $_GET['phpurchase-task'] == 'viewMember') {
    $view = PHPurchaseCommon::getView('admin/member-view.php', array('id' => $id));
  }
  elseif(isset($_GET['phpurchase-task']) && $_GET['phpurchase-task'] == 'delete-subscription') {
    $view = PHPurchaseCommon::getView('admin/member-view.php', array('id' => $id));
  }
  elseif(isset($_REQUEST['phpurchase-task']) && $_REQUEST['phpurchase-task'] == 'update-sub-stats') {
    $view = PHPurchaseCommon::getView('admin/member-view.php', array('id' => $_REQUEST['id']));
  }
  elseif(isset($_REQUEST['phpurchase-task']) && $_REQUEST['phpurchase-task'] == 'remove-account') {
    $id = $_REQUEST['id'];
    if(is_numeric($id)) {
      $gatewayName = PHPurchaseCommon::gatewayName();
      if($gatewayName == 'quantum') {
        $customer = new Quantum_VaultCustomer();
      }
      elseif($gatewayName == 'authnet') {
        $customer = new CIM();
      }
      
      if($customer->load($id)) {
        $customer->delete();
      }
    }
    $view = PHPurchaseCommon::getView('admin/members.php');    
  }
  else {
    $view = PHPurchaseCommon::getView('admin/members.php');
  }
  echo "<p style='float: right; margin: 20px 40px 0px 0px;'>
    Color key: 
    <span class='PHPurchaseActive'>Active</span>
    <span class='PHPurchaseComplete'>Complete</span>
    <span class='PHPurchaseOverdue'>Overdue</span>
    <span class='PHPurchaseSuspended'>Suspended</span>
    <span class='PHPurchaseCanceled'>Canceled</span>
  </p>";
  echo $view;
}

function PHPurchaseMembersSettings() {
  $view = PHPurchaseCommon::getView('admin/members-settings.php');
  echo $view;
}

function PHPurchaseReports() {
  $view = PHPurchaseCommon::getView('admin/reports.php');
  echo $view;
}

function PHPurchaseInstall() {
  global $wpdb;
  $prefix = PHPurchaseCommon::getTablePrefix();
  $sqlFile = WP_PLUGIN_DIR. "/" . basename(dirname(__FILE__)) . "/sql/database.sql";
  $sql = str_replace('[prefix]', $prefix, file_get_contents($sqlFile));
  $queries = explode(";\n", $sql);
  foreach($queries as $sql) {
    if(strlen($sql) > 5) {
      $wpdb->query($sql);
      //PHPurchaseCommon::log("Running: $sql");
    }
  }
  
  require_once(WP_PLUGIN_DIR. "/" . basename(dirname(__FILE__)) . "/create-pages.php");
  
  // Set the version number for this version of PHPurchase
  require_once(WP_PLUGIN_DIR. "/" . basename(dirname(__FILE__)) . "/models/setting.php");
  $setting = new PHPurchaseSetting();
  $setting->key = 'version';
  $setting->value = PHPURCHASE_VERSION_NUMBER;
  $setting->save();
}
register_activation_hook(__FILE__, 'PHPurchaseInstall');

function PHPurchaseMembershipExcludes($excludes) {
  global $wpdb;
  $hidePrivate = true;
  $mySubItemNums = array();

  if(PHPURCHASEPRO && isset($_SESSION['PHPurchaseMember'])) {
    $member = PHPurchaseCommon::gatewayName() == 'quantum' ? new Quantum_VaultCustomer() : new CIM();
    if($member->load($_SESSION['PHPurchaseMember'], false)) {
    	$hidePrivate = false; //NTS: do not hide pages where phpurchase_access = private
    	// Build array of active subscription item ids
    	$mySubItemNums = $member->getMySubscriptionItemNumbers('active');
    }
    
    // Optionally add the logout link to the end of the navigation
    $setting = new PHPurchaseSetting();
    if($setting->lookupValue('auto_logout_link')) {
      add_filter('wp_list_pages', 'PHPurchaseAddLogoutLink');
    }
    
    // Hide guest only pages
    $guestOnlyPageIds = PHPurchaseAccessManager::getGuestOnlyPageIds();
    $excludes = array_merge($excludes, $guestOnlyPageIds);
  }

  if(PHPURCHASEPRO) {
    // Hide pages requiring an item number to which the member is not subscribed
    $hiddenPages = PHPurchaseAccessManager::hideSubscriptionPages($mySubItemNums);
    if(count($hiddenPages)) {
      $excludes = array_merge($excludes, $hiddenPages);
    }
  }
  
  if($hidePrivate) {
    // Build list of private page ids
    $privatePageIds = PHPurchaseAccessManager::getPrivatePageIds();
    $excludes = array_merge($excludes, $privatePageIds);
  }
  
  
  // Merge private page ids with other excluded pages
  if(is_array(get_option('exclude_pages'))){
		$excludes = array_merge(get_option('exclude_pages'), $excludes );
	}
  
  sort($excludes);
  return $excludes;
}

function PHPurchaseBlockPrivate() {
  global $wp_query;
  $pid = $wp_query->post->ID;
  
  PHPurchaseAccessManager::verifyPageAccessRights($pid);

  // block subscription pages from non-subscribers
  if(PHPURCHASEPRO) {
    $gw = PHPurchaseCommon::gatewayName();
    if($gw == 'quantum') {
      $member = new Quantum_VaultCustomer();
    }
    elseif($gw == 'authnet') {
      $member = new CIM();
    }
    $member->load($_SESSION['PHPurchaseMember']);
    
    $mySubItemNums = $member->getMySubscriptionItemNumbers('active');

    // Get a list of the required subscription ids
    $requiredIds = PHPurchaseAccessManager::getRequiredItemNumbersForPage($pid);
    if(count($requiredIds)) {
      // Check to see if the logged in user has one of the required subscriptions
      if(!PHPurchaseAccessManager::allowSubscriptionAccess($mySubItemNums, $requiredIds)) {
        // If logged in user doesn't have required subscription redirect to denied page
        wp_redirect(PHPurchaseAccessManager::getDeniedLink());
      }
    }

  }
}
add_action('template_redirect', 'PHPurchaseBlockPrivate');

function PHPurchaseAddLogoutLink($output) {
  $output .= "<li><a href='?phpurchase-task=logout'>Log out</a></li>";
  return $output;
}

if(IS_ADMIN) {

  //Plugin update actions
  add_action('update_option__transient_update_plugins', array('PHPurchaseCommon', 'checkUpdate')); //used by WP 2.8
  add_filter('pre_set_site_transient_update_plugins', array('PHPurchaseCommon', 'getUpdatePluginsOption')); //used by WP 3.0
  
  add_action('install_plugins_pre_plugin-information', array('PHPurchaseCommon', 'showChangelog'));
}
else {
  add_filter('wp_list_pages_excludes', 'PHPurchaseMembershipExcludes');
  add_filter('wp_list_pages_excludes', 'PHPurchaseHideStorePages');
}

function PHPurchaseHideStorePages($excludes) {
  $setting = new PHPurchaseSetting();
  
  if($setting->lookupValue('hide_system_pages') == 1) {
    $store = get_page_by_path('store');
    $excludes[] = $store->ID;

    $cart = get_page_by_path('store/cart');
    $excludes[] = $cart->ID;

    $checkout = get_page_by_path('store/checkout');
    $excludes[] = $checkout->ID;
  }
  
  $express = get_page_by_path('store/express');
  $excludes[] = $express->ID;
  
  $ipn = get_page_by_path('store/ipn');
  $excludes[] = $ipn->ID;
  
  $receipt = get_page_by_path('store/receipt');
  $excludes[] = $receipt->ID;
  
  
  if(is_array(get_option('exclude_pages'))){
		$excludes = array_merge(get_option('exclude_pages'), $excludes );
	}
	sort($excludes);
	return $excludes;
}


function PHPurchaseForceDownload() {
  
  ob_end_clean();
  
  if($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['phpurchase_action'] == 'export_csv') {
    require_once(WP_PLUGIN_DIR. "/phpurchase/models/exporter.php");
    $start = PHPurchaseCommon::postVal('start_date');
    $end = PHPurchaseCommon::postVal('end_date');

    $start = $_POST['start_date'];
    $end = $_POST['end_date'];
    $report = PHPurchaseExporter::exportOrders($start, $end);
    
    header('Content-Type: application/csv'); 
    header('Content-Disposition: inline; filename="PHPurchaseReport.csv"');
    echo $report;
    die();
  }
  elseif($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['phpurchase-action'] == 'download log file') {
    
    $logFilePath = PHPurchaseLog::getLogFilePath();
    if(file_exists($logFilePath)) {
      $logData = file_get_contents($logFilePath);
      $cartSettings = PHPurchaseLog::getCartSettings();
      
      header('Content-Description: File Transfer');
      header('Content-Type: text/plain');
      header('Content-Disposition: attachment; filename=PHPurchaseLogFile.txt');
      header('Expires: 0');
      header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
      header('Pragma: public');
      echo $cartSettings . "\n\n";
      echo $logData;
      die();
    }
  }
}
add_action('admin_init', PHPurchaseForceDownload);
/*************************************************************************
 * Short codes
 *************************************************************************/

$sc = new PHPurchaseShortcodeManager();
add_shortcode('shoppingcart', array($sc, 'shoppingCart'));
add_shortcode('cart-button', array($sc, 'showCartButton'));
add_shortcode('cart', array($sc, 'showCart'));
add_shortcode('receipt', array($sc, 'showReceipt'));
add_shortcode('paypalcheckout', array($sc, 'paypalCheckout'));
add_shortcode('freecheckout', array($sc, 'freeCheckout'));
add_shortcode('manualcheckout', array($sc, 'freeCheckout')); // alias for free checkout
add_shortcode('authcheckout', array($sc, 'authCheckout'));
add_shortcode('paypalprocheckout', array($sc, 'payPalProCheckout'));
add_shortcode('expresscheckout', array($sc, 'payPalExpressCheckout'));
add_shortcode('express', array($sc, 'payPalExpress'));
add_shortcode('ipn', array($sc, 'processIPN'));
add_shortcode('clearcart', array($sc, 'clearCart'));
add_shortcode('phpurchase-tests', array($sc, 'phpurchaseTests'));

/**********************************************************************
 * Ajax functions for PHPurchase Standard
 **********************************************************************/
add_action('wp_ajax_save_settings', 'ajaxSaveSettings');
function ajaxSaveSettings() {
  $setting = new PHPurchaseSetting();
  $error = '';
  foreach($_REQUEST as $key => $value) {
    if($key[0] != '_' && $key != 'action' && $key != 'submit') {
      if(is_array($value)) {
        $value = implode('~', $value);
      }
      
      if($key == 'home_country') {
        $hc = $setting->lookupValue('home_country');
        if($hc != $value) {
          $method = new PHPurchaseShippingMethod();
          $method->clearAllLiveRates();
        }
      }
      elseif($key == 'countries') {
        if(strpos($value, '~') === false) {
          PHPurchaseCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] country list value: $value");
          $value = '';
        }
      }
      elseif($key == 'enable_logging') {
        try {
          PHPurchaseLog::createLogFile();
          PHPurchaseCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Created the log file");
        }
        catch(PHPurchaseException $e) {
          $error = '<span style="color: red;">' . $e->getMessage() . '</span>';
          PHPurchaseCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Caught PHPurchase exception: " . $e->getMessage());
        }
      }
      
      $setting->key = $key;
      $setting->value = stripslashes($value);
      $setting->save();

      if($key == 'order_number') {
        $versionInfo = PHPurchaseCommon::getVersionInfo();
        if(!$versionInfo) {
          $setting->value = '';
          $setting->save();
          $error = '<span style="color: red;">Invalid Order Number</span>';
        }
      }
    }
  }


  if($error) {
    $result[0] = 'PHPurchaseErrorModal';
    $result[1] = "<strong style='color: red;'>Warning</strong><br/>$error";
  }
  else {
    $result[0] = 'PHPurchaseSuccessModal';
    $result[1] = '<strong>Success</strong><br/>' . $_REQUEST['_success'] . '<br>'; 
  }

  $out = json_encode($result);
  echo $out;
  die();
}

add_action('wp_ajax_update_gravity_product_quantity_field', 'ajaxUpdateGravityProductQuantityField');
function ajaxUpdateGravityProductQuantityField() {
  $formId = PHPurchaseCommon::getVal('formId');
  $gr = new GravityReader($formId);
  $fields = $gr->getStandardFields();
  header('Content-type: application/json');
  echo json_encode($fields);
  die();
}

add_action('wp_ajax_check_inventory_on_add_to_cart', 'PHPurchaseCheckInventoryOnAddToCart');
add_action('wp_ajax_nopriv_check_inventory_on_add_to_cart', 'PHPurchaseCheckInventoryOnAddToCart');
function PHPurchaseCheckInventoryOnAddToCart() {
  $result = array(true);
  $itemId = PHPurchaseCommon::postVal('phpurchaseItemId');
  $options = '';
  $optionsMsg = '';
  
  $opt1 = PHPurchaseCommon::postVal('options_1');
  $opt2 = PHPurchaseCommon::postVal('options_2');
  
  if(!empty($opt1)) {
    $options = $opt1;
    $optionsMsg = trim(preg_replace('/\s*([+-])[^$]*\$.*$/', '', $opt1));
  }
  if(!empty($opt2)) {
    $options .= '~' . $opt2;
    $optionsMsg .= ', ' . trim(preg_replace('/\s*([+-])[^$]*\$.*$/', '', $opt2));
  }
  
  $scrubbedOptions = PHPurchaseProduct::scrubVaritationsForIkey($options);
  if(!PHPurchaseProduct::confirmInventory($itemId, $scrubbedOptions)) {
    $result[0] = false;
    $p = new PHPurchaseProduct($itemId);
    
    $counts = $p->getInventoryNamesAndCounts();
    $out = '';
    
    if(count($counts)) {
      $out = '<table class="inventory_count_table_modal">';
      $out .= '<tr><td colspan="2"><strong>Currently In Stock</strong></td></tr>';
      foreach($counts as $name => $qty) {
        $out .= '<tr>';
        $out .= "<td>$name</td><td>$qty</td>";
        $out .= '</tr>';
      }
      $out .= '</table>';
    }
    
    $result[1] = $p->name . " " . $optionsMsg . " is&nbsp;out&nbsp;of&nbsp;stock $out";
  }
  
  $result = json_encode($result);
  //PHPurchaseCommon::log("AJAX checking inventory levels: $result");
  echo $result;
  die();
}



/**********************************************************************
 * Include additional functionality for PHPurchase Professional
 **********************************************************************/
if(PHPURCHASEPRO) {
  require_once(WP_PLUGIN_DIR. "/" . basename(dirname(__FILE__)) . "/pro/hooks.php");
}

/**
 * Prevent the link rel="next" content from showing up in the wordpress header 
 * because it can potentially prefetch the [clearcart] pages
 */
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head');
