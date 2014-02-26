<?php
/*
Template Name: Products
*/
get_header(); 

getProductSlider();
if(!is_front_page()) {
	echo '<div class="fb-like" data-send="true" data-width="620" data-show-faces="false" data-font="arial"></div>';
	listProducts();
}
get_footer(); 
?>