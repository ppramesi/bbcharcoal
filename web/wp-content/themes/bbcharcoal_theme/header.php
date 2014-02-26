<?php 
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="main">
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<title><?php

	/*
	 * Print the <title> tag based on what is being viewed.
	 */
	global $page, $paged;

	echo wp_title('', FALSE).' | B&B Charcoal';

	// Add the blog name.
	//bloginfo('name');

	// Add the blog description for the home/front page.
	/*$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		echo " | $site_description";*/

	// Add a page number if necessary:
	if ( $paged >= 2 || $page >= 2 )
		echo ' | ' . sprintf( __( 'Page %s', 'twentyten' ), max( $paged, $page ) );

	?></title>
<link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo( 'stylesheet_url' ); ?>" />
<!--<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />-->
<?php
	/* We add some JavaScript to pages with the comment form
	 * to support sites with threaded comments (when in use).
	 */
	if ( is_singular() && get_option( 'thread_comments' ) )
		wp_enqueue_script( 'comment-reply' );

	/* Always have wp_head() just before the closing </head>
	 * tag of your theme, or you will break many plugins, which
	 * generally use this hook to add elements to <head> such
	 * as styles, scripts, and meta tags.
	 */
	wp_head();
?>
<script type="text/javascript" src="<?php echo URL_BASE; ?>media/javascript/site.js"></script>
<?php
if ( !is_user_logged_in() ) {
	echo "<script type='text/javascript'>
		var _gaq = _gaq || [];
		_gaq.push(['_setAccount', 'UA-33940364-1']);
		_gaq.push(['_trackPageview']);
		var _gaq = _gaq || [];
		_gaq.push(['_setAccount', 'UA-18483026-5']);
		_gaq.push(['_trackPageview']);
	</script>";
}
?>
<meta property="og:image" content="<?php echo URL_BASE; ?>media/new/bb_logo_flame_homepage.png"/>
<meta name="msvalidate.01" content="D70D3958FF0A401DD6E96928D0B7AC0D" />
<meta name="sitelock-site-verification" content="5025" />
<link href="https://plus.google.com/117436346281975277115" rel="publisher" />
<div id="fb-root"></div>
</head>
<?php 
GLOBAL $ALT_IMAGES;
$cartPage = get_page_by_path('store/cart');

//$h1Tag = get_post_meta($post->ID, "h1", true);
$h1Tag = get_the_title($post->ID);
if(is_category()) {
	$h1Tag = single_cat_title( '', false );	
} elseif(is_archive()) {
	$h1Tag = get_the_date('F, Y').' Articles';	
} elseif(empty($h1Tag)) {
	$h1Tag = '';
}	

echo '<body '; body_class(); echo '>
<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=437145609706856";
  fjs.parentNode.insertBefore(js, fjs);
}(document, \'script\', \'facebook-jssdk\'));</script>

<div id="page-body">
	<a href="'.URL_BASE.'"><div id="logo"></div></a>
	<h1 id="header-title">'.$h1Tag.'</h1>
	<div id="header-banner">
		<div id="social-links">
			<style>.ig-b- { display: inline-block; }
.ig-b- img { visibility: hidden; }
.ig-b-:hover { background-position: 0 -60px; } .ig-b-:active { background-position: 0 -120px; }
.ig-b-32 { width: 32px; height: 32px; background: url(//badges.instagram.com/static/images/ig-badge-sprite-32.png) no-repeat 0 0; }
@media only screen and (-webkit-min-device-pixel-ratio: 2), only screen and (min--moz-device-pixel-ratio: 2), only screen and (-o-min-device-pixel-ratio: 2 / 1), only screen and (min-device-pixel-ratio: 2), only screen and (min-resolution: 192dpi), only screen and (min-resolution: 2dppx) {
.ig-b-32 { background-image: url(//badges.instagram.com/static/images/ig-badge-sprite-32@2x.png); background-size: 60px 178px; } }</style>
<a href="http://instagram.com/bbcharcoal?ref=badge" target="_blank" class="ig-b- ig-b-32"><img src="//badges.instagram.com/static/images/ig-badge-32.png" alt="Instagram" /></a>
<a href="http://pinterest.com/bbcharcoal/" target="_blank"><img src="'.URL_BASE.'media/pinterest-button.png" /></a>
<a href="http://www.facebook.com/home.php#!/pages/BB-Charcoal/168884006467774" target="_blank"><img src="'.URL_BASE.'media/facebook.png" /></a>
<a target="_blank" href="http://twitter.com/bbcharcoal"><img src="'.URL_BASE.'media/twitter.png" /></a>';
			/*if(!is_home()) {
				do_shortcode('[shoppingcart]');
			}*/
		echo '</div>
	</div>';
	if(!is_home()) {
		do_shortcode('[shoppingcart]');
	}
	echo '<div id="page-container">
	<div id="content-container">';
?>