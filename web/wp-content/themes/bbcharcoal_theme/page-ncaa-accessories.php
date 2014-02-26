<?php
/*
International
*/
get_header();
echo '<div id="merchandise-ncaa-description"><div id="merchandise-ncaa-description-text"><div id="merchandise-ncaa-image-right"></div>';
echo '</div></div>';
echo '<div id="generic-product-wrapper">';
if ( have_posts() ) while ( have_posts() ) : the_post();
the_content();
endwhile;
echo '<div class="clear-floats"></div>';
echo '</div>';
get_footer();
?>