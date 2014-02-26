<?php
/*
Private Labeling
*/
get_header();
echo '<div id="privatelabeling-description"><div id="privatelabeling-description-text"><div id="privatelabeling-image-right"></div>';
if ( have_posts() ) while ( have_posts() ) : the_post();
the_content();
endwhile;
echo '</div></div>';
echo do_shortcode('[CONTACT description="Contact Us For More Information On Private Labeling." url="private-labeling"]');
get_footer();
?>