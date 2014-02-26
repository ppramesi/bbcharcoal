<?php
/*
Charcoal Products
*/
if(!empty($_GET)) {
	exit();
}
include(LIB_BASE . 'scheduleShipment.php');

get_header(); 


$result = NULL;
$error = '';
$success = NULL;
if($_SERVER['REQUEST_METHOD'] == "POST" && !empty($_POST)) {
	$s['state'] = $_POST['shipping']['state'];
	$s['country'] = $_POST['shipping']['country'];
	$result = validateRequest($_POST);
	$jqErrors = $result[0];
	if(empty($result[0])) {
		//$isValidAddress = validateStreetAddress();
		//if($isValidAddress[0] != FALSE) {
			handleRequest($_POST);
			$success = '<div style="margin:0 0 10px 0;color:blue;">You order has been requested.</div>
				We will contact you with a quote that includes the shipping charges.<br/>
				We do not calculate shipping of pallets through our website.';
		/*} else {
			$jqErrors[] = 'address1';
			$jqErrors[] = 'city';
			$jqErrors[] = 'shipping-state';
			$jqErrors[] = 'shipping-country';
			$jqErrors[] = 'zip';
			$error = '<div class="pallet-error">Invalid Shipping Address.</div>';
		}*/
	} elseif(empty($result[1])) {
		$error = '<div class="pallet-error">Please provide the field(s) below.</div>';
	} else {
		$error = '<div class="pallet-error">'.implode('<br/>',$result[1]).'</div>';
	}
}

include(WP_PLUGIN_DIR . '/phpurchase/views/client/checkout.php'); // Include jquery for shipping form states.

if(!empty($_POST['order-total'])) {
	$total = $_POST['order-total'];
} else {
	$total = 0;
}
?>
<div id="pallet-products-content">
<div id="pallet-order-form-content">

<?php if(!empty($success)) { 
	echo $success;
} else { ?>
	<noscript class="no-script">Please Enable Javascript. For information how to enable javascript please click <a href="http://help.yahoo.com/l/us/yahoo/help/faq/browsers/browsers-63474.html" target="_blank">here.</a></noscript>
	<form action="" method="POST" name="orderform" id="orderform">
	<div id="orderform-notice">
		<?php echo $error; ?>
		We do not calculate shipping of pallets through our website.<br/>
		Please fill out this order form and submit it to us. We will contact you with a quote that includes the shipping charges.</div><br/>
			Order List: &nbsp;&nbsp;&nbsp;<span id="clear-link">[<a href="#" onClick="document.getElementById('orderlist').innerHTML = '';document.getElementById('order-total').innerHTML = '0';document.getElementById('orderlist2').innerHTML = '';return false;">clear</a>]</span>
			<div id="orderlist" name="orderlist"><?php echo $_POST['orderlist2']; ?></div><textarea type="hidden" id="orderlist2" name="orderlist2" value="<?php echo $_POST['orderlist2']; ?>" style="display:none" /></textarea>
			Order Total: $<span id="order-total"><?php echo $total; ?></span><input type="hidden" value="<?php echo $total; ?>" name="order-total" id="order-total-hidden" /><br/><br/>
			<ul id="shippingAddress" class="shortLabels" style="width: 275px;">
			<li>
				<label>First name:</label>
				<input type="text" id="firstname" name="firstname" value="<?php echo $_POST['firstname']; ?>" class="">
			</li>
			<li>
				<label>Last name:</label>
				<input type="text" id="lastname" name="lastname" value="<?php echo $_POST['lastname']; ?>">
			</li>
			<li>
				<label>Phone:</label>
				<input type="text" id="phone" name="phone" value="<?php echo $_POST['phone']; ?>"  class="">
			</li>
			<li>
				<label>Email:</label>
				<input type="text" id="email" name="email" value="<?php echo $_POST['email']; ?>"  class="">
			</li>
			<li><br/><h2>Shipping:</h2></li>
			<li>
				<label>Address:</label>
				<input type="text" id="address1" name="shipping[address]" value="<?php echo $_POST['shipping']['address']; ?>">
			</li>
			<li>
				<label>&nbsp;</label>
				<input type="text" id="address2" name="shipping[address2]" value="<?php echo $_POST['shipping']['address2']; ?>">
			</li>
			<li>
				<label>City:</label>
				<input type="text" id="city" name="shipping[city]" value="<?php echo $_POST['shipping']['city']; ?>">
			</li>
			<li>
				<label class="short">State:</label>
				<select style="min-width: 125px;" id="shipping-state" class="required" title="State shipping address" name="shipping[state]"></select>
			</li>
			<li>
				<label>Zip code:</label>
				<input type="text" id="zip" name="shipping[zip]" value="<?php echo $_POST['shipping']['zip']; ?>">
			</li>
			<li>
				<label class="short">Country:</label>
				<select title="country" id="shipping-country" name="shipping[country]">
				<?php foreach(PHPurchaseCommon::getCountries() as $code => $name): ?>
				<option value="<?php echo $code ?>"><?php echo $name ?></option>
				<?php endforeach; ?>
				</select>
			</li>
		</ul><br/>
		<input type="submit" value="Complete Order" name="submit" class="pallet-add-to-order" id="PHPurchaseCheckoutButton">
	<?php
	echo '</form>';
	echo '<script>document.getElementById(\'orderform\').style.display=\'block\';</script>';
}
echo '</div>';
echo '<div id="pallet-product-list">';
if ( have_posts() ) while ( have_posts() ) : the_post();
the_content();
endwhile;
echo '</div>';
echo '</div>';

get_footer(); 
?>