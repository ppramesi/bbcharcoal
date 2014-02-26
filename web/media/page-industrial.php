<?php
/*
International
*/
get_header();
echo '<div id="industrial-description"><div id="industrial-description-text"><div id="industrial-image-right"></div>';
if ( have_posts() ) while ( have_posts() ) : the_post();
the_content();
endwhile;
echo '</div></div>';
get_footer();
?>