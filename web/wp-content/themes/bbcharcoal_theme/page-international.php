<?php
/*
International
*/
get_header();
echo '<div id="international-description" class="product-listings"><div id="international-description-text"><div id="international-image-right"></div>';
if ( have_posts() ) while ( have_posts() ) : the_post();
the_content();
endwhile;
echo '</div></div>';
echo do_shortcode('[CONTACT description="Contact Us For More Information On International." url="international"]');
get_footer();
?>