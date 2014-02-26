<?php

class PHPurchaseShortcodeManager {

  /**
   * Short code for displaying shopping cart including the number of items in the cart and links to view cart and checkout
   */
  public function shoppingCart($attrs) {
    $cartPage = get_page_by_path('store/cart');
    $checkoutPage = get_page_by_path('store/cart');
    $cart = $_SESSION['PHPurchaseCart'];
    if($cart->countItems()) {
      /*?>
      <p>
        <a href='<?php echo get_permalink($cartPage->ID) ?>'>
          <?php echo $cart->countItems(); ?> 
          <?php echo $cart->countItems() > 1 ? ' items' : ' item' ?> &ndash;
          <?php echo CURRENCY_SYMBOL ?><?php echo number_format($cart->getSubTotal() - $cart->getDiscountAmount(), 2); ?></a><br/>
        <a href='<?php echo get_permalink($cartPage->ID) ?>'>View Cart</a> |
        <?php if($attrs['checkout'] == 'hide'): ?>
          <a href='<?php echo get_permalink($checkoutPage->ID) ?>'>Check out</a>
        <?php endif; ?>
      </p>
      <?php*/
	  echo '<div id="shopping-cart-button-float"><a href="'.get_permalink($checkoutPage->ID).'"><div id="shopping-cart-button"><div id="shopping-cart-button-count">'.$cart->countItems().'</div></div></a></div>';
    }
    else {
      /*?> <p>Your cart is empty.</p> <?php*/
    }
  }

  public static function showCartButton($attrs, $content) {
    $product = new PHPurchaseProduct();
    $product->loadFromShortcode($attrs);
    return PHPurchaseButtonManager::getCartButton($product, $attrs, $content);
  }

  public function showCart($attrs, $content) {
    if(isset($_REQUEST['phpurchase-task']) && $_REQUEST['phpurchase-task'] == 'remove-attached-form') {
      $entryId = $_REQUEST['entry'];
      if(is_numeric($entryId)) {
        $_SESSION['PHPurchaseCart']->detachFormEntry($entryId);
      }
    }
    $view = PHPurchaseCommon::getView('views/cart.php', $attrs);
    return $view;
  }

  public function showReceipt($attrs) {
    $view = PHPurchaseCommon::getView('views/receipt.php', $attrs);
    return $view;
  }

  public function paypalCheckout($attrs) {
    if(!$_SESSION['PHPurchaseCart']->hasSubscriptionProducts()) {
      if($_SESSION['PHPurchaseCart']->getGrandTotal()) {
        $view = PHPurchaseCommon::getView('views/paypal-checkout.php', $attrs);
        return $view;
      }
      else {
        return $this->freeCheckout();
      }
    }
  }

  public function freeCheckout($attrs=null) {
    PHPurchaseCommon::log("Running free gateway code");
    require_once(WP_PLUGIN_DIR. "/phpurchase/gateways/free.php");

    global $freeCheckoutSet;
    if($freeCheckoutSet == 0) {
      $freeCheckoutSet = 1;
      $view = PHPurchaseCommon::getView('views/free-checkout.php', $attrs);
      return $view;
    }
  }

  public function authCheckout($attrs) {
    require_once(WP_PLUGIN_DIR. "/phpurchase/gateways/authnet.php");
    
    $setting = new PHPurchaseSetting();
    $ssl = $setting->lookupValue('auth_force_ssl');
    
    if($ssl == 'yes') {
      if($_SERVER["HTTPS"] != "on") {
        $sslUrl = "https://" . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
        wp_redirect($sslUrl);
        exit();
      }
    }
    
    if($_SESSION['PHPurchaseCart']->getGrandTotal()==0 && !$_SESSION['PHPurchaseCart']->hasSubscriptionProducts()) {
      $view = $this->freeCheckout($attrs);
    }
    else {
      $view = PHPurchaseCommon::getView('views/auth-checkout.php');
    }

    return $view;
  }

  public function payPalProCheckout($attrs) {
    $view = '';
    if($_SESSION['PHPurchaseCart']->getGrandTotal() > 0) {
      if(!$_SESSION['PHPurchaseCart']->hasSubscriptionProducts()) {
        require_once(WP_PLUGIN_DIR. "/phpurchase/gateways/paypalpro.php");

        $setting = new PHPurchaseSetting();
        $ssl = $setting->lookupValue('auth_force_ssl');
        if($ssl == 'yes') {
          if($_SERVER["HTTPS"] != "on") {
            $sslUrl = "https://" . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
            header("Location: $sslUrl");
            exit();
          }
        }

        if($_SESSION['PHPurchaseCart']->getGrandTotal()==0) {
          $view = $this->freeCheckout($attrs);
        }
        else {
          $view = PHPurchaseCommon::getView('views/paypalpro-checkout.php');
        }

      }
      else {
        $view = 'PHPurchase cannot sell subscriptions through the PayPal Pro gateway';
      }
    }

    return $view;
  }

  public function payPalExpressCheckout($attrs) {
    if(!$_SESSION['PHPurchaseCart']->hasSubscriptionProducts()) {
      require_once(WP_PLUGIN_DIR. "/phpurchase/gateways/paypalpro.php");
    
      $view = PHPurchaseCommon::getView('views/paypal-expresscheckout.php', $attrs);
      return $view;
    }
  }

  public function payPalExpress($attrs) {
    require_once(WP_PLUGIN_DIR. "/phpurchase/gateways/paypalpro.php");
    $view = PHPurchaseCommon::getView('views/paypal-express.php', $attrs);
    return $view;
  }

  public function processIPN($attrs) {
    require_once(WP_PLUGIN_DIR. '/phpurchase/views/ipn.php');
  }

  public function phpurchaseTests() {
    $view = PHPurchaseCommon::getView('tests/tests.php');
    $view = "<pre>$view</pre>";
    return $view;
  }

  public function clearCart() {
    unset($_SESSION['PHPurchaseCart']);
  }


}