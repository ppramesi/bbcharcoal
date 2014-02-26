<?php
get_header(); 
?>

<div id="blog-content">
	<div id="blog-entries">
		<?php
		$temp = $wp_query;
		$wp_query= null;
		$wp_query = new WP_Query();
		$wp_query->query('showposts=3'.'&paged='.$paged);
		?>
		<?php while ($wp_query->have_posts()) : $wp_query->the_post(); ?>
			<div class="single-post">
				<div class="post-title"><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></div>
				<div class="post-date"><?php the_time('F jS, Y') ?></div>
				<div class="post-content"><?php the_excerpt() ?>
				<div class="entry-utility">
				<?php if ( count( get_the_category() ) ) : ?>
					<span class="cat-links">
						<?php printf( __( '<span class="%1$s">Posted in</span> %2$s', 'twentyten' ), 'entry-utility-prep entry-utility-prep-cat-links', get_the_category_list( ', ' ) ); ?>
					</span>
					<span class="meta-sep">|</span>
				<?php endif; ?>
				<?php
					$tags_list = get_the_tag_list( '', ', ' );
					if ( $tags_list ):
				?>
					<span class="tag-links">
						<?php printf( __( '<span class="%1$s">Tagged</span> %2$s', 'twentyten' ), 'entry-utility-prep entry-utility-prep-tag-links', $tags_list ); ?>
					</span>
					<!--<span class="meta-sep">|</span>-->
				<?php endif; ?>
				<!--<span class="comments-link"><?php comments_popup_link( __( 'Leave a comment', 'twentyten' ), __( '1 Comment', 'twentyten' ), __( '% Comments', 'twentyten' ) ); ?></span>-->
				<?php edit_post_link( __( 'Edit', 'twentyten' ), '<span class="meta-sep">|</span> <span class="edit-link">', '</span>' ); ?>
				</div><!-- .entry-utility -->
				</div>
			</div>
		<?php endwhile; ?>
		</ul>
		<?php if (  $wp_query->max_num_pages > 1 ) : ?>
				<div id="nav-below" class="navigation">
					<div class="nav-previous"><?php next_posts_link( __( 'Older posts <span class="meta-nav">&rarr;</span>', 'twentyten' ) ); ?></div>
					<div class="nav-next"><?php previous_posts_link( __( '<span class="meta-nav">&larr;</span> Newer posts', 'twentyten' ) ); ?></div>
				</div><!-- #nav-below -->
<?php endif; ?>
		<?php $wp_query = null; $wp_query = $temp;?>
		
		<?php
		wp_reset_query();
		?>
	</div>
	<?php get_sidebar(); ?>
	<div style="clear:both;"></div>
</div>
<?php
get_footer(); 
?>