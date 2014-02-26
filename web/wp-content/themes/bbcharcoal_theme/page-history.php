<?php
/*
History
*/
get_header();
echo '<div id="history-description"><div id="history-description-text"><div id="history-image-right"></div>';
if ( have_posts() ) while ( have_posts() ) : the_post();
the_content();
endwhile;
echo '</div></div>';
get_footer();
?>