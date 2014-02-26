<?php
/*
Template Name: Products-Level-1
*/
get_header(); 
if ( have_posts() ) while ( have_posts() ) : the_post();
the_content();
endwhile;
get_footer();
?>
