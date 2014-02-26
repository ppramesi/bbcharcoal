<?php
/*
Charcoal Products
*/
get_header();
echo '<div id="charcoal-description" class="product-listings"><div id="charcoal-description-text"><div id="charcoal-image-right"></div>';
if ( have_posts() ) while ( have_posts() ) : the_post();
the_content();
endwhile;
echo '</div></div>';

get_footer();
?>