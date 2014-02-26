<?php
// Get a list of all products
$product = new PHPurchaseProduct();
$products = $product->getModels('where id>0', 'order by name');
$save = false;

if($_SERVER['REQUEST_METHOD'] == "POST" && $_POST['phpurchase-task'] == 'save-inventory-form') {
  $save = true;
  ?>
  <script type="text/javascript">
  var $jq = jQuery.noConflict();
  $jq(document).ready(function() {
    setTimeout('$jq("#PHPurchaseSuccessBox").fadeOut(800);', 1000);
  });
  </script>
  <div id='PHPurchaseSuccessBox' style='width: 300px;'><p class='PHPurchaseSuccess'>Inventory updated</p></div>
  <?php
}

$setting = new PHPurchaseSetting();
$track = $setting->lookupValue('track_inventory');
$wpurl = get_bloginfo('wpurl');
if($track != 1): ?>
  
  <div class="PHPurchaseError" style="width: 500px;">
    <h1>Inventory Tracking Is Not Active</h1>
    <p>You must enable inventory tracking in in the <a href="<?php echo $wpurl ?>/wp-admin/admin.php?page=phpurchase-settings">settings panel</a>.</p>
  </div>
  
<?php endif; ?>

<p style="width: 400px;">Track your inventory by selecting the checkbox next to the products you want to track and enter the quantity you have in stock in the text field. If you are tracking inventory for a product, PHPurchase will check the inventory levels every time a product is added to the shopping cart, every time the quantity of a product in the shopping cart is changed, and on the checkout page. Inventory is reduced after a successful sale, not when a product is added to the shopping cart.</p>

<?php if(count($products)): ?>
<form action="" method="post">
  <input type="hidden" name="phpurchase-task" value="save-inventory-form" id="phpurchase-task" />
  <table class="widefat" style="margin: 0px; width: auto;">
  <thead>
  	<tr>
  	  <th>Track</th>
  	  <th>Product Name</th>
  		<th>Product Variation</th>
  		<th>Quantity</th>
  	</tr>
  </thead>
  <tfoot>
      <tr>
        <th>Track</th>
    		<th>Product Name</th>
    		<th>Product Variation</th>
    		<th>Quantity</th>
    	</tr>
  </tfoot>
  <tbody>
    <?php
      $ikeyList = array();
      foreach($products as $p) {
        $p->insertInventoryData();
        $combos = $p->getAllOptionCombinations();
        if(count($combos)) {
          foreach($combos as $c) {
            $k = $p->getInventoryKey($c);
            $ikeyList[] = $k;
            if($save) { $p->updateInventoryFromPost($k); }
            ?>
            <tr>
              <td><input type="checkbox" name="track_<?php echo $k ?>" value="1" id="track_<?php echo $k ?>" <?php echo ($p->isInventoryTracked($k)) ? 'checked="checked"' : ''; ?>/></td>
              <td><?php echo $p->name ?></td>
              <td><?php echo $c ?></td>
              <td><input type="text" name="qty_<?php echo $k ?>" value="<?php echo $p->getInventoryCount($k); ?>" id="qty_<?php echo $k ?>" style="width:50px;" />
            </tr>
            <?php
          }
        }
        else {
          $k = $p->getInventoryKey();
          $ikeyList[] = $k;
          if($save) { $p->updateInventoryFromPost($k); }
          ?>
            <tr>
              <td><input type="checkbox" name="track_<?php echo $k ?>" value="1" id="track_<?php echo $k ?>" <?php echo ($p->isInventoryTracked($k)) ? 'checked="checked"' : ''; ?>/></td>
              <td><?php echo $p->name ?></td>
              <td>&nbsp;</td>
              <td><input type="text" name="qty_<?php echo $k ?>" value="<?php echo $p->getInventoryCount($k); ?>" id="qty_<?php echo $k ?>" style="width:50px;" />
            </tr>
          <?php          
        }
      }
      
      if($save) { $p->pruneInventory($ikeyList); }
    ?>
  </tbody>
  </table>
  
  <input type="submit" name="submit" value="Save" id="submit" style="width: 80px; margin-top: 20px;" class="button-primary" />
</form>
<?php else: ?>
  <p>You do not have any products</p>
<?php endif; ?>