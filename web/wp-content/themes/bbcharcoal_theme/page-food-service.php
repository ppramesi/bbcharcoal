<?php
/*
Food Service
*/
get_header();
echo '<div id="foodservice-description"><div id="foodservice-description-text"><div id="foodservice-image-right"></div>';
if ( have_posts() ) while ( have_posts() ) : the_post();
the_content();
endwhile;
echo '</div></div>';
echo do_shortcode('[CONTACT description="Contact Us For More Information On Food Services." url="food-service"]');
get_footer();
?>