<?php
/**
 * The Template for displaying all single posts.
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */

get_header(); 
echo '<div id="press-content">';
echo '<a href="'.URL_BASE.'blog" style="font-size: 10pt;color:#666;border:1px solid #888;padding:6px;text-decoration:none;background:#EEE;border-radius:4px;">Back To Blog</a><br/><br/>';
while ( have_posts() ) : the_post(); ?>

<?php /* How to display posts in the Gallery category. */ ?>
		<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<h2 class="entry-title"><?php the_title(); ?></h2>
			<div class="newsreldettrans">
			<iframe src="http://www.facebook.com/plugins/like.php?href=<?php echo the_permalink(); ?>" scrolling="no" frameborder="0" style="margin-top:5px;border:none; width:450px; height:35px"></iframe>
			</div>
			<div class="entry-content">
				<?php the_content(); ?>
			</div><!-- .entry-content -->
		</div><!-- #post-## -->

<?php endwhile; // End the loop. Whew. 
echo '</div>';
get_footer(); 
