<?php
  global $wpdb;
  $order = new PHPurchaseOrder();
  $orderRows = $order->getOrderRows();
  $search = null;
  if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['phpurchase-task']) && $_POST['phpurchase-task'] == 'search orders') {
      $search = $wpdb->escape($_POST['search']) . '%';
      $where = "WHERE ship_last_name LIKE '$search' OR bill_last_name LIKE '$search' OR email LIKE '$search' or trans_id LIKE '$search'";
      $orderRows = $order->getOrderRows($where);
    }
  }
  else {
    if(isset($_GET['status'])) {
      $status = $wpdb->escape($_GET['status']);
      $orderRows = $order->getOrderRows("WHERE status='$status'");
    }
  }
  
  
?>

<div class='wrap'>
  <form class='phorm' action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>" method="post">
    <input type='hidden' name='phpurchase-task' value='search orders'/>
    <input type='text' name='search'>
    <input type='submit' class='button-secondary' value='Search' style='width: auto;'>
    <br/>
    <p style="float: left; color: #999; font-size: 11px; margin-top: 0;">Search by last name, email, or order number</p>
  </form>
  
  <?php
    $setting = new PHPurchaseSetting();
    $stats = trim($setting->lookupValue('status_options'));
    if(strlen($stats) >= 1 ) {
      $stats = explode(',', $stats);
  ?>
      <p style="float: left; clear: both; margin-top:0; padding-top: 0;">Filter Orders:
        <?php
          foreach($stats as $s) {
            $tmpRows = $order->getOrderRows("WHERE status='$s'");
            $n = count($tmpRows);
            if($n > 0) {
              echo "<a href=\"?page=phpurchase/phpurchase.php&status=$s\">$s (" . count($tmpRows) . ")</a> &nbsp;|&nbsp; ";
            }
            else {
              echo "$s (0) &nbsp;|&nbsp;";
            }
          }
        ?>
        <a href="?page=phpurchase/phpurchase.php">All (<?php echo count($order->getOrderRows()) ?>)</a>
      </p>
  <?php
    }
    else {
      echo "<p style=\"float: left; clear: both; color: #999; font-size: 11px; both; margin-top:0; padding-top: 0;\">
        You should consider setting order status options such as new and complete on the 
        <a href='?page=phpurchase-settings'>PHPurchase Settings page</a>.</p>";
    }
  
  ?>
  
  <?php if(isset($search)): ?>
    <p style='float:left; clear: both;'><strong>Search String:</strong> <?php echo PHPurchaseCommon::postVal('search'); ?></p>
  <?php endif; ?>
</div>

<table class="widefat" style="width: auto;">
<thead>
	<tr>
	  <th>Order Number</th>
		<th>Name</th>
		<th>Amount</th>
		<th>Date</th>
    <th>Delivery</th>
		<th>Status</th>
		<th>Actions</th>
	</tr>
</thead>
<?php

foreach($orderRows as $row) {
  ?>
  <tr>
    <td><?php echo $row->trans_id ?></td>
    <td><?php echo $row->ship_first_name ?> <?php echo $row->ship_last_name?></td>
    <td><?php echo CURRENCY_SYMBOL ?><?php echo $row->total ?></td>
    <td><?php echo date('m/d/Y', strtotime($row->ordered_on)) ?></td>
    <td><?php echo $row->shipping_method ?></td>
    <td><?php echo $row->status ?></td>
    <td>
      <a href='?page=phpurchase/phpurchase.php&task=view&id=<?php echo $row->id ?>'>View</a> | 
      <a class='delete' href='?page=phpurchase/phpurchase.php&task=delete&id=<?php echo $row->id ?>'>Delete</a>
    </td>
    
  </tr>
  <?php
}
?>
</table>

<script language='javascript'>
  $jq = jQuery.noConflict();
  
  $jq('.delete').click(function() {
    return confirm('Are you sure you want to delete this item?');
  });
</script>
