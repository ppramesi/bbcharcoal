<?php
class PHPurchaseButtonManager {

  /**
   * Return the HTML for rendering the add to cart buton for the given product id
   */
  public static function getCartButton(PHPurchaseProduct $product, $attrs) {
    $view = "<p>Could not load product information</p>";
    if($product->id > 0) {

      // Set CSS style if available
      $style = isset($attrs['style']) ? 'style="' . $attrs['style'] . '"' : '';

      $price = '';
      $showPrice = isset($attrs['showprice']) ? strtolower($attrs['showprice']) : 'yes';
      if($showPrice == 'yes' || $showPrice == 'only') {
        $price = CURRENCY_SYMBOL . number_format($product->price, 2);
        // Check for recurring price
        if($product->recurring_interval > 0) {
          $price .= ' / ' .  $product->getRecurringIntervalDisplay();
        }
      }

      $data = array(
        'price' => $price,
        'showPrice' => $showPrice,
        'style' => $style,
        'addToCartPath' => self::getAddToCartImagePath($attrs),
        'cartImgPath' => $cartImgPath,
        'product' => $product,
        'productOptions' => $product->getOptions()
      );
      $view = PHPurchaseCommon::getView('views/cart-button.php', $data);
    }
    return $view;
  }

  /**
   * Return the image path for the add to cart button or false if no path is available
   */
  public function getAddToCartImagePath($attrs) {
    $path = false;

    if(isset($attrs['img'])) {
      // Look for custom image for this instance of the button
      $path = $attrs['img'];
    }
    else {
      // Look for common images
      $setting = new PHPurchaseSetting();
      $cartImgPath = $setting->lookupValue('cart_images_url');
      if($cartImgPath) {
        $cartImgPath = PHPurchaseCommon::scrubPath($cartImgPath);
        $path = $cartImgPath . 'add-to-cart.png';
      }
    }

    return $path;
  }

}
