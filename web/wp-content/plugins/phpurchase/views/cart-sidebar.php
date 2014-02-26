<?php echo $before_widget; ?>
  
  <?php echo $before_title . $title . $after_title; ?>
  
  <?php if($this->countItems()): ?>
    <p>
  <a href='<?php echo get_permalink($cartPage->ID) ?>'>
    <?php echo $this->countItems(); ?> 
    <?php echo $this->countItems() > 1 ? ' items' : ' item' ?> &ndash; 
    <?php echo CURRENCY_SYMBOL ?><?php echo number_format($this->getSubTotal() - $this->getDiscountAmount(), 2); ?></a><br/>
  <a href='<?php echo get_permalink($cartPage->ID) ?>'>View Cart</a> |
  <a href='<?php echo get_permalink($checkoutPage->ID) ?>'>Check out</a>
    </p>
  <?php else: ?>
    <p>Your cart is empty.</p>
  <?php endif; ?>

<?php echo $after_widget; ?>