<?php
/**
 * The main template file.
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query. 
 * E.g., it puts together the home page when no home.php file exists.
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */

get_header(); 
GLOBAL $altImages, $post, $wp_query;
getProductSlider();
$page_id = 27;
$page = get_page($page_id);
//echo '<div id="index-body">'.do_shortcode('[nivo category="press" number=3 speed=2000 pause=4000]').'</div>';
//echo '<div id="home-top-image"><img src="'.IMAGE_BASE.'products_pic.png" alt="'.$altImages['homePageTopRight'].'" height="300px" /></div>';
//echo '<div id="home-top-description"><br/><img src="'.IMAGE_BASE.'experts_choice.png" alt="'.$altImages['homePageTopLeft'].'" height="200px" width="475px" /><br/><br/></div>';
//echo $page->post_content;

echo '	<div class="clear-floats"></div>
	<div id="text" class="gray-box"><img src="media/anniversary_logo.png" style="float:right;margin:13px 10px 0px 10px;" height=210px; />'.$page->post_content.'</div>
	<div id="home-bottom-boxes">
	<div id="home-bottom-left-box">
	<img src="'.IMAGE_BASE.'pallet_pricing.png" alt="charcoal private labeling" /><br/>';
	echo '<div id="home-bottom-left-box-image"></div>';
	$id = 246;
	$post = get_post($id); 
	echo $post->post_content;
	echo '</div>';
	
	echo '<div id="home-bottom-right-box">
	<img src="'.IMAGE_BASE.'become_distributor.png" alt="charcoal services" />';
	echo '<div id="home-bottom-right-box-image"></div>';
	$id = 248;
	$post = get_post($id); 
	echo $post->post_content;
	echo '</div>
	<div class="clear-floats"></div>
	</div></div>';
//get_sidebar(); 
get_footer(); 
?>