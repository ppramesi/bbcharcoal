<?php
/*
International
*/
get_header();
echo '<table border="0" cell-padding="0" cell-spacing="0" width="100%" id="merchandise-table"><tr>
<td><a href="'.URL_BASE.'merchandise/nfl-accessories"><div id="merchandise-box-1" class="merchandise-box"><div id="merchandise-box-1-description" class="merchandise-box-description">'.$charcoal.'</div></div></a></td>
<td><a href="'.URL_BASE.'merchandise/mlb-accessories"><div id="merchandise-box-2" class="merchandise-box"><div id="merchandise-box-2-description" class="merchandise-box-description">'.$pellets.'</div></div></a></td>
<td><a href="'.URL_BASE.'merchandise/ncaa-accessories"><div id="merchandise-box-3" class="merchandise-box"><div id="merchandise-box-3-description" class="merchandise-box-description">'.$logs.'</div></div></a></td>
</tr></table>';
echo '<div id="merchandise-team-description">';
if ( have_posts() ) while ( have_posts() ) : the_post();
the_content();
endwhile;
echo '</div>';
listProducts();
get_footer();
?>