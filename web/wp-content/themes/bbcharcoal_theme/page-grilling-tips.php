<?php
get_header(); 
query_posts('cat=7');
echo '<div id="grilling-tips-content">';
while ( have_posts() ) : the_post(); ?>

<?php /* How to display posts in the Gallery category. */ ?>
		<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<h2 class="entry-title"><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
			<div class="entry-content">
				<?php the_content(); ?>
			</div><!-- .entry-content -->
		</div><!-- #post-## -->

<?php endwhile; // End the loop. Whew. 
echo '</div>';
get_footer(); 
?>