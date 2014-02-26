<?php
class PHPurchaseCartWidget extends WP_Widget {
  
  private $_items = array();
  
  public function PHPurchaseCartWidget() {
    $widget_ops = array('classname' => 'PHPurchaseCartWidget', 'description' => 'Sidebar shopping cart for PHPurchase' );
    $this->WP_Widget('PHPurchaseCartWidget', 'PHPurchase Shopping Cart', $widget_ops);
  }
  
  public function widget($args, $instance) {
    extract($args);
    $title = $instance['title'];
    if(isset($_SESSION['PHPurchaseCart'])) {
      $this->_items = $_SESSION['PHPurchaseCart']->getItems();
    }
    $cartPage = get_page_by_path('store/cart');
    $checkoutPage = get_page_by_path('store/checkout');
    include('views/cart-sidebar.php');
  }
  
  public function update($newInstance, $oldInstance) {
    $instance = $oldInstance;
    $instance['title'] = strip_tags($newInstance['title']);
    return $instance;
  }
  
  public function getItems() {
    if(isset($_SESSION['PHPurchaseCart'])) {
      return $_SESSION['PHPurchaseCart']->getItems();
    }
  }
  
  public function countItems() {
    if(isset($_SESSION['PHPurchaseCart'])) {
      return $_SESSION['PHPurchaseCart']->countItems();
    }
  }
  
  public function getSubTotal() {
    if(isset($_SESSION['PHPurchaseCart'])) {
      return $_SESSION['PHPurchaseCart']->getSubTotal();
    }
  }
  
  public function getDiscountAmount() {
    if(isset($_SESSION['PHPurchaseCart'])) {
      return $_SESSION['PHPurchaseCart']->getDiscountAmount();
    }
  }
  
  public function form($instance) {
    $title = esc_attr($instance['title']);
    ?>
    <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'phpurchase-cart'); ?>
    <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label>
    <?php
  }
}

/**
 * Register PHPurchase cart widget
 */
function PHPurchaseCartInit() {
  register_widget('PHPurchaseCartWidget');
}
add_action('widgets_init', 'PHPurchaseCartInit');