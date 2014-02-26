<?php
define('DISALLOW_FILE_EDIT', true);

function failed_login() {
     return 'The login information you have entered is incorrect.';
}
 
add_filter('login_errors', 'failed_login');

function remove_wp_version() {
     return '';
}

add_filter('the_generator', 'remove_wp_version');

if ( !is_user_logged_in() ) {
	if ( strpos( $_SERVER['HTTP_USER_AGENT'], 'Googlebot' ) !== false ) {
		//file_put_contents(LOG_BASE.'google-bot.txt', date('m-d-Y h:i:s A').' - '.$_SERVER["REQUEST_URI"]."\n", FILE_APPEND);
	}
	if(!empty($_SERVER["HTTP_REFERER"]) && !preg_match('/simplemap-master/', $_SERVER["REQUEST_URI"]) && !preg_match('/wp-admin/', $_SERVER["REQUEST_URI"])) {
		//file_put_contents(LOG_BASE.'referer.txt', date('m-d-Y h:i:s A').' - '.$_SERVER["HTTP_REFERER"].' - '.$_SERVER["REQUEST_URI"].' - '.$_SERVER["HTTP_USER_AGENT"]."\n", FILE_APPEND);
	}
	if(preg_match('/www\.google\.com/', $_SERVER["HTTP_REFERER"])) {
		preg_match('/&q=.*&source/', $_SERVER["HTTP_REFERER"], $matches);
		$term = urldecode(preg_replace(array('/&q=/', '/&source/', '/\+/'), array('', '', ' '), $matches[0]));
		//file_put_contents(LOG_BASE.'google-searches.txt', date('m-d-Y h:i:s A').' - '.$term."\n", FILE_APPEND);
	}
	if(preg_match('/http/', $_SERVER["HTTP_REFERER"]) && !preg_match('/bbcharcoal\.com/', $_SERVER["HTTP_REFERER"])) {
		//file_put_contents(LOG_BASE.'referering-site.txt', date('m-d-Y h:i:s A').' - '.$_SERVER["HTTP_REFERER"].' - '.$_SERVER["REQUEST_URI"]."\n", FILE_APPEND);	
	}
}

function new_excerpt_more($more) {
       global $post;
	return '<br/><a href="'. get_permalink($post->ID) . '">Read More...</a>';
}
add_filter('excerpt_more', 'new_excerpt_more');

// Shortcode that returns the current url.
function urlShortcode() {
	return URL_BASE;
}
if ( function_exists('register_sidebar') )
    register_sidebar();
register_nav_menu('primary', 'Navigation Menu');
function getProductJavascript($atts) {
	extract(shortcode_atts(array(
		'description' => 'test',
		'price' => '0'
	), $atts));
	
	$id = preg_replace('/ /', '-', $description);
	return "<a href=\"#orderform-notice\" class=\"PHPurchaseButtonPrimary purAddToCart\" id=\"{$id}\" onclick=\"addToOrderForm(this, '{$description}', {$price});\" onMouseDown=\"this.style.backgroundColor = '#DDB242';return false;\">ADD TO LIST</a>";
}
add_shortcode('URL_BASE', 'urlShortcode');
add_shortcode('PRODUCT_JAVASCRIPT', 'getProductJavascript');
add_shortcode('CONTACT', 'displayContactForm');

function displayContactForm($atts) {
	extract(shortcode_atts(array(
		'description' => 'Contact Us',
		'url' => URL_BASE
	), $atts));
	echo '<META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=UTF-8">
<form action="https://www.salesforce.com/servlet/servlet.WebToLead?encoding=UTF-8" method="POST">
<input type=hidden name="oid" value="00DE0000000IoaJ">
<input type=hidden name="retURL" value="http://www.bbcharcoal.com/'.$url.'">
<div class="wpcf7"><div class="contact-form-title"><strong>'.$description.'</strong></div>
<table style="width:70%;"><tr><td>
<label for="first_name">First Name</label><br><input  id="first_name" maxlength="40" name="first_name" size="20" type="text" /><br>
</td><td>
<label for="last_name">Last Name</label><br><input  id="last_name" maxlength="80" name="last_name" size="20" type="text" /><br>
</td></tr>
<tr><td>
<label for="title">Title</label><br><input  id="title" maxlength="40" name="title" size="20" type="text" /><br>
</td><td>
<label for="email">Email</label><br><input  id="email" maxlength="80" name="email" size="20" type="text" /><br>
</td></tr>
<tr><td>
<label for="company">Company</label><br><input  id="company" maxlength="40" name="company" size="20" type="text" /><br>
</td><td>
<label for="URL">Website</label><br><input  id="URL" maxlength="80" name="URL" size="20" type="text" /><br>
</td></tr>
<tr><td>
<label for="city">City</label><br><input  id="city" maxlength="40" name="city" size="20" type="text" /><br>
</td><td>
<label for="state">State/Province</label><br><input  id="state" maxlength="20" name="state" size="20" type="text" /><br>
</td></tr>
<tr><td>
<label for="country">Country</label><br><input  id="country" maxlength="40" name="country" size="20" type="text" /><br>
</td><td>
<label for="industry">Industry</label><br><select  id="industry" name="industry"><option value="">--None--</option><option value="Agriculture">Agriculture</option>
<option value="Apparel">Apparel</option>
<option value="Banking">Banking</option>
<option value="Biotechnology">Biotechnology</option>
<option value="Chemicals">Chemicals</option>
<option value="Communications">Communications</option>
<option value="Construction">Construction</option>
<option value="Consulting">Consulting</option>
<option value="Education">Education</option>
<option value="Electronics">Electronics</option>
<option value="Energy">Energy</option>
<option value="Engineering">Engineering</option>
<option value="Entertainment">Entertainment</option>
<option value="Environmental">Environmental</option>
<option value="Finance">Finance</option>
<option value="Food &amp; Beverage">Food &amp; Beverage</option>
<option value="Government">Government</option>
<option value="Healthcare">Healthcare</option>
<option value="Hospitality">Hospitality</option>
<option value="Insurance">Insurance</option>
<option value="Machinery">Machinery</option>
<option value="Manufacturing">Manufacturing</option>
<option value="Media">Media</option>
<option value="Not For Profit">Not For Profit</option>
<option value="Other">Other</option>
<option value="Recreation">Recreation</option>
<option value="Retail">Retail</option>
<option value="Shipping">Shipping</option>
<option value="Technology">Technology</option>
<option value="Telecommunications">Telecommunications</option>
<option value="Transportation">Transportation</option>
<option value="Utilities">Utilities</option>
</select><br>
</td>
<tr><td valign="top">
<label for="lead_source">Referred By</label><br><select  id="lead_source" name="lead_source"><option value="">--None--</option><option value="Advertisement">Advertisement</option>
<option value="Employee Referral">Employee Referral</option>
<option value="External Referral">External Referral</option>
<option value="Partner">Partner</option>
<option value="Public Relations">Public Relations</option>
<option value="Seminar - Internal">Seminar - Internal</option>
<option value="Seminar - Partner">Seminar - Partner</option>
<option value="Trade Show">Trade Show</option>
<option value="Web">Web</option>
<option value="Word of mouth">Word of mouth</option>
<option value="Other">Other</option>
</select><br>
</td><td valign="top">
Comments or Questions:<br><textarea  id="00NE0000000emzs" name="00NE0000000emzs" type="text" wrap="soft"></textarea><br>
</td></tr>
</table>
<input type="submit" name="submit" value="Send" class="wpcf7-submit">
</div>
</form>';
}

function validateRequest($post) {
	$results = array();
	$errors = array();
	if(empty($post['firstname'])) {
		$results[] = 'firstname';
	} 
	if(empty($post['email']) || !eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $post['email'])) {
		$results[] = 'email';
		$errors[] = 'Invalid Email Address';
	} 
	if(empty($post['phone'])) {
		$results[] = 'phone';
	} 
	if(empty($post['shipping']['address'])) {
		$results[] = 'address1';
	} 
	if(empty($post['shipping']['city'])) {
		$results[] = 'city';
	} 
	if(empty($post['shipping']['state'])) {
		$results[] = 'shipping-state';
	} 
	if(empty($post['shipping']['country'])) {
		$results[] = 'shipping-country';
	} 
	if(empty($post['shipping']['zip'])) {
		$results[] = 'zip';
	}
	return array($results, $errors);
}

function handleRequest($post) {
	$output = "";
	$output .= "THE FOLLOWING PALLET ORDER HAD BEEN PLACED:\n\n";
	$output .= "First Name: ".$post['firstname']."\n";
	$output .= "Last Name: ".$post['lastname']."\n";
	$output .= "Email: ".$post['email']."\n";
	$output .= "Phone: ".$post['phone']."\n\n";
	$output .= "SHIPPING INFO:\n\n";
	$output .= "Address: ".$post['shipping']['address']."\n";
	$output .= "Line 2: ".$post['shipping']['address2']."\n";
	$output .= "City: ".$post['shipping']['city']."\n";
	$output .= "State: ".$post['shipping']['state']."\n";
	$output .= "Country: ".$post['shipping']['country']."\n";
	$output .= "Zip: ".$post['shipping']['zip']."\n\n";
	$output .= "ORDER PLACED\n\n";
	$output .= $post['orderlist2'];
	
	foreach($GLOBALS['INVALID_ADDESS_EMAILS'] as $email) {
		wp_mail($email, 'bbcharcoal.com PALLET ORDER PLACED', $output);
	}
}

function listProducts() {
	global $wp_query;
	$ID = $wp_query->post->ID;
	$product = new PHPurchaseProduct();
	$products = $product->getModels('where post_id = '.$ID.'', 'order by ordinal ASC');

	$row = array();
	$rows = array();
	if(!empty($products)) {
		echo '<table class="product-list-table" border="0" cellpadding="0" cellspacing="0" width="100%">';
		foreach($products as $product) {
			$row[] = '<td>'.PHPurchaseButtonManager::getCartButton($product, array('item'=>$product->id), '').'</td>';
			if(count($row) == 2) {
				$count = 1;
				$rows[] = '<tr>'.implode('', $row).'</tr>';
				$row = array();
			}
		}
		if(!empty($row)) {
			$row[] = '<td></td>';
			$rows[] = '<tr>'.implode('', $row).'</tr>';
		}
		echo implode('', $rows);
		echo '</table>';
	}
}

// get featured image url function
function featured_img_url($featured_img_size) {
	$image_id = get_post_thumbnail_id();
	$image_url = wp_get_attachment_image_src($image_id,$featured_img_size);
	$image_url = $image_url[0];
	return $image_url;
}

add_filter( 'nav_menu_css_class', 'additional_active_item_classes', 10, 2 );

function additional_active_item_classes($classes = array(), $menu_item = false){

	if(in_array('current-menu-item', $menu_item->classes)){
		$classes[] = 'menu-navigation-menu-active';
	}

	return $classes;
}

function getProductSlider() {	
		if ( have_posts() ) while ( have_posts() ) : the_post();
			$content = get_the_content();
			if(!empty($content)) {
			echo '<div id="index-body">';
				the_content();
			echo '</div>';}
		endwhile;
		showProductsSideBar();
		if(is_front_page() ) {
			//echo '<div class="fb-like-box" data-href="https://www.facebook.com/bbcharcoal" data-width="220" data-height="300" data-show-faces="false" data-stream="true" data-show-border="false" data-header="false"></div>';
			echo '<br/><br/>'.do_shortcode('[yop_poll id="1"]');
		}
}

function showProductsSideBar() {
	echo '<div id="index-sidebar">';
	// Drop Down Accordian Box

	// Accordian Box
	/*echo '
		<div id="rounded-top-right"><div id="rounded-top-right"></div></div>
		<div id="accordian-header"><div class="text">Better Burning Products</div></div>
		<div id="product-list">';
		wp_nav_menu( array('menu' => 'Navigation Menu' ));
		echo '</div>
	</div>';*/

	$args = array(
		'depth'        => 0,
		'show_date'    => '',
		'date_format'  => get_option('date_format'),
		'child_of'     => 4,
		'exclude'      => '21,22,23',
		'include'      => '',
		'title_li'     => __(''),
		'echo'         => 1,
		'authors'      => '',
		'sort_column'  => 'menu_order, post_title',
		'link_before'  => '',
		'link_after'   => '',
		'walker'       => '' );
		
	// Accordian Box
	echo '
		<div id="accordian-header"><div class="text"><img src="/media/orange_arrow_down.png">Better Burning Products</div></div>
		<div id="product-list">';
		wp_list_pages($args);
		echo '</div>
		<!--<img id="product-ad" src="'.URL_BASE.'wp-content/uploads/2012/06/free_shipping_ad.jpg" alt="Free Shipping on Charcoalu" title="Free Shipping on Charcoal" />-->
	</div>';
}

?>