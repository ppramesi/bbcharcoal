<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the id=main div and all content
 * after.  Calls sidebar-footer.php for bottom widgets.
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */
?>

<?php
	/* Always have wp_footer() just before the closing </body>
	 * tag of your theme, or you will break many plugins, which
	 * generally use this hook to reference JavaScript files.
	 */

	wp_footer();
?>
<?php if(is_home()) { ?>
<!--<table border="0" cellpadding="0" cellspacing="0" width="100%"><tr>
<td valign="middle" class="home-footer-logo"><div class="AuthorizeNetSeal"> <script type="text/javascript" language="javascript">var ANS_customer_id="bf9aa227-0e2a-47f5-9198-a661aea7c0a0";</script> <script type="text/javascript" language="javascript" src="//verify.authorize.net/anetseal/seal.js" ></script> <a href="http://www.authorize.net/" id="AuthorizeNetText" target="_blank">Web Ecommerce</a> </div></td>
<td valign="middle" class="home-footer-logo verify-seal"><span id="siteseal"><script type="text/javascript" src="https://seal.starfieldtech.com/getSeal?sealID=3cjwvuKwpIgKkHrwwJWBz1uXBdXwqdBSHYX90Jqi8r1HGXDBOwlqyfrCU"></script><br/><a style="font-family: arial; font-size: 9px" href="http://www.starfieldtech.com" target="_blank">Secure Websites</a></span> </td>
<td valign="middle" class="home-footer-logo"><img src="../../../media/LOGO_L.gif" alt="ups charcoal shipping" /></td>
<td valign="middle" class="home-footer-logo"><a href="http://www.nbbqa.org" target="_blank"><img src="../../../media/logos/nbbq.png" alt="charcoal association" /></a></td>
<td valign="middle" class="home-footer-logo"><a href="http://www.hpba.org" target="_blank"><img src="../../../media/logos/hpba.png" alt="bbq organization" /></a></td>
</tr></table>-->
<? } ?>
<div id="footer-box" class="clear-floats">
<?php 
$pages = get_pages(array('include'=>'4,7,9,17,36','sort_column'=>'menu_order'));
echo '<div id="footer-list-1" class="footer-list"><div class="footer-title">Services</div><ul>';
foreach ($pages as $pagg) {
	echo '<li><a href="'.get_page_link($pagg->ID).'" class="nav-link">'.$pagg->post_title.'</a></li>';
}
echo '</ul></div>';

$pages = get_pages(array('include'=>'1159,24,57,59,55,862','sort_column'=>'menu_order'));
echo '<div id="footer-list-2" class="footer-list"><div class="footer-title">Our Company</div><ul>';
foreach ($pages as $pagg) {
	echo '<li><a href="'.get_page_link($pagg->ID).'" class="nav-link">'.$pagg->post_title.'</a></li>';
}
echo '</ul></div>';

$pages = get_pages(array('include'=>'13,2373,2377,15,87,99,2392,2407,101,107,603,2411,51,547','sort_column'=>'menu_order'));
echo '<div id="footer-list-2" class="footer-list"><div class="footer-title">Products</div><ul>';
foreach ($pages as $pagg) {
	echo '<li><a href="'.get_page_link($pagg->ID).'" class="nav-link">'.$pagg->post_title.'</a></li>';
}
echo '</ul></div>';
echo '<div id="footer-list-2" class="footer-list">
<table border="0" cellpadding="0" cellspacing="0" width="200px"><tr>
<td valign="middle" class="home-footer-logo"><div class="AuthorizeNetSeal"> <script type="text/javascript" language="javascript">var ANS_customer_id="bf9aa227-0e2a-47f5-9198-a661aea7c0a0";</script> <script type="text/javascript" language="javascript" src="//verify.authorize.net/anetseal/seal.js" ></script> <a href="http://www.authorize.net/" id="AuthorizeNetText" target="_blank">Web Ecommerce</a> </div></td>
<td valign="middle" class="home-footer-logo verify-seal"><span id="siteseal"><script type="text/javascript" src="https://seal.starfieldtech.com/getSeal?sealID=3cjwvuKwpIgKkHrwwJWBz1uXBdXwqdBSHYX90Jqi8r1HGXDBOwlqyfrCU"></script><br/><a style="font-family: arial; font-size: 9px" href="http://www.starfieldtech.com" target="_blank">Secure Websites</a></span> </td>
</tr><tr>
<td valign="middle" class="home-footer-logo" colspan="2"><img id="ups-logo" src="'.URL_BASE.'media/LOGO_L.gif" alt="ups charcoal shipping" /></td>
</tr><tr>
<td valign="middle" class="home-footer-logo"><a href="http://www.nbbqa.org" target="_blank"><img src="'.URL_BASE.'media/logos/nbbq-small.png" alt="charcoal association" /></a></td>
<td valign="middle" class="home-footer-logo"><a href="http://www.hpba.org" target="_blank"><img src="'.URL_BASE.'media/logos/hpba-small.png" alt="bbq organization" /></a></td>
</tr></table>
</div>';

/*$pages = get_pages(array('include'=>'741,743,745','sort_column'=>'menu_order'));
echo '<div id="footer-list-2" class="footer-list"><div class="footer-title">Accessories</div><ul>';
foreach ($pages as $pagg) {
	echo '<li><a href="'.get_page_link($pagg->ID).'" class="nav-link">'.$pagg->post_title.'</a></li>';
}
echo '</ul></div>';*/

echo '<div id="footer-list-2" class="footer-company">B&amp;B CHARCOAL CO.<br/>
P.O. BOX 230<br/>
WEIMAR, TX   78962<br/>
email: contact@bbcharcoal.com<br/>
phone: <a href="tel:18552272625">1-855-BBQCOAL</a><br/>
<style>
.ig-b-30 { width: 32px; height: 32px; }
@media only screen and (-webkit-min-device-pixel-ratio: 2), only screen and (min--moz-device-pixel-ratio: 2), only screen and (-o-min-device-pixel-ratio: 2 / 1), only screen and (min-device-pixel-ratio: 2), only screen and (min-resolution: 192dpi), only screen and (min-resolution: 2dppx) {</style>
<a href="http://instagram.com/bbcharcoal?ref=badge" class="ig-b-30"><img id="social-link" src="//badges.instagram.com/static/images/ig-badge-32.png" alt="Instagram" /></a>
<a href="http://pinterest.com/bbcharcoal/" target="_blank"><img id="social-link" src="'.URL_BASE.'media/pinterest-button.png" /></a>
<a href="http://www.facebook.com/home.php#!/pages/BB-Charcoal/168884006467774" target="_blank"><img id="social-link" src="'.URL_BASE.'media/facebook.png" /></a>
<a target="_blank" href="http://twitter.com/bbcharcoal"><img id="social-link" src="'.URL_BASE.'media/twitter.png" /></a></div>';
?>
<div class="footer-built-by">Built by <a href="http://joebotdesigns.com/" target="_blank" />Joebot Designs</a></div>
<div class="clear-floats"></div>
</div>
</div>
</div>
<?php

	echo '<div id="navigation-bar">
		<div id="nav-area">';
			$pages = get_pages(array('include'=>'4,2426,24,17,57,1159,59','sort_column'=>'menu_order')); 
			echo '<ul>';
			$dropDownPages = array(36=>110, 7=>131, 9=>133, 4=>151, 2426=>0);
			echo '<li class="nav-item"><a href="'.URL_BASE.'">Home</a>';
			foreach ($pages as $pagg) {
				$current = '';
				if($post->ID == $pagg->ID) {
					$current = ' nav-link-current';
				}
				echo '<li><img src="'.URL_BASE.'media/dotted-divider.png" /></li><li class="nav-item'.$current.'"><a href="'.get_page_link($pagg->ID).'" class="nav-link">'.$pagg->post_title.'</a>';
				
				if(array_key_exists($pagg->ID, $dropDownPages)) { 
					echo '<ul>';
					$pagg_id = $dropDownPages[$pagg->ID];
					if($pagg->ID == 4) {
						echo '<div class="nav-hover-box-products">';
						/*echo '<a href="'.URL_BASE.'products/charcoal"><div class="products-drop-down-link">Charcoal</div></a>
							<a href="'.URL_BASE.'products/wood-chips"><div class="products-drop-down-link">Wood Chips</div></a>
							<a href="'.URL_BASE.'products/wood-chunks"><div class="products-drop-down-link">Wood Chunks</div></a>
							<a href="'.URL_BASE.'products/wood-pellets"><div class="products-drop-down-link">Wood Pellets</div></a>
							<a href="'.URL_BASE.'products/grilling-wood"><div class="products-drop-down-link">Grilling Wood</div></a>
							<a href="'.URL_BASE.'products/package-sets"><div class="products-drop-down-link">Package Sets</div></a>
							<a href="'.URL_BASE.'products/pallet-pricing"><div class="products-drop-down-link">Pallet Pricing</div></a>
							<a href="'.URL_BASE.'products/merchandise"><div class="products-drop-down-link">Merchandise</div></a>';*/
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
							wp_list_pages($args);
						echo '</div>';
					} elseif($pagg->ID == 2426) {
						echo '<div class="nav-hover-box-products">';
						$args = array(
								'depth'        => 0,
								'show_date'    => '',
								'date_format'  => get_option('date_format'),
								'child_of'     => 2426,
								'exclude'      => '',
								'include'      => '',
								'title_li'     => __(''),
								'echo'         => 1,
								'authors'      => '',
								'sort_column'  => 'menu_order, post_title',
								'link_before'  => '',
								'link_after'   => '',
								'walker'       => '' );
							wp_list_pages($args);
						echo '</div>';
					}/*else {
						echo '<div class="nav-hover-box">';
						query_posts('p='.$pagg_id.'');
						if ( have_posts() ) while ( have_posts() ) : the_post();
							echo '<a href="'.get_page_link($pagg->ID).'">';
							the_content();
							echo '</a>';
						endwhile;
						wp_reset_query();
					}*/
					echo '</ul>';
				}
				echo '</li>';
			}
			echo '</ul>
		</div>
	</div><!-- #header -->';
?>


<div class="clear-floats"></div>

<?php
$url = get_permalink($post->ID);
if(preg_match('/receipt/', $url) && !empty($_SESSION['order_successful'])) {
	unset($_SESSION['order_successful']);
?>
	<!-- Google Code for Purchase/ Sale Conversion Page -->
	<script type="text/javascript">
	/* <![CDATA[ */
	var google_conversion_id = 1009375175;
	var google_conversion_language = "en";
	var google_conversion_format = "2";
	var google_conversion_color = "ffffff";
	var google_conversion_label = "s8D0CJnJ9gIQx6-n4QM";
	var google_conversion_value = 0;
	/* ]]> */
	</script>
	<script type="text/javascript" src="http://www.googleadservices.com/pagead/conversion.js">
	</script>
	<noscript>
	<div style="display:inline;">
	<img height="1" width="1" style="border-style:none;" alt="" src="http://www.googleadservices.com/pagead/conversion/1009375175/?label=s8D0CJnJ9gIQx6-n4QM&amp;guid=ON&amp;script=0"/>
	</div>
	</noscript>
<? } elseif(preg_match('/store-locator/', $url)) { ?>
	<!-- Google Code for Store Locater Conversion Page -->
	<script type="text/javascript">
	/* <![CDATA[ */
	var google_conversion_id = 1009375175;
	var google_conversion_language = "en";
	var google_conversion_format = "2";
	var google_conversion_color = "ffffff";
	var google_conversion_label = "Qk85CJHK9gIQx6-n4QM";
	var google_conversion_value = 0;
	/* ]]> */
	</script>
	<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">
	</script>
	<noscript>
	<div style="display:inline;">
	<img height="1" width="1" style="border-style:none;" alt="" src="//www.googleadservices.com/pagead/conversion/1009375175/?value=0&amp;label=Qk85CJHK9gIQx6-n4QM&amp;guid=ON&amp;script=0"/>
	</div>
	</noscript>
<?php } ?>
</body>
</html>
