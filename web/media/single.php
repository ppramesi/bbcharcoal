<?php
/**
 * The Template for displaying all single posts.
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */

get_header(); /*?>
<?php 

if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

				<div id="post-<?php the_ID(); ?>" <?php post_class('content-box'); ?>>
					<h1 class="entry-title"><a href="<?php get_permalink(); ?>"><?php the_title(); ?></a></h1>
				<div class="post-meta">Posted on <a href="<?php the_time('Y/m/d'); ?>"><?php the_time('m.d.y'); ?></a> from <?php the_category(', ');?> </div>
				<div class="entry-content">
					<?php the_content();
					//wp_link_pages( array( 'before' => '<div class="page-link">' . __( 'Pages:', 'slopness' ), 'after' => '</div>' ) );
					//edit_post_link( __( 'Edit', 'slopness' ), '<span class="edit-link">', '</span>' ); 
					outputCommentLink(basename(get_permalink()));
					?>
					</div><!-- .entry-content -->

<?php if ( get_the_author_meta( 'description' ) ) : // If a user has filled out their description, show a bio on their entries  ?>
					<div id="entry-author-info">
						<div id="author-avatar">
							<?php echo get_avatar( get_the_author_meta( 'user_email' ), apply_filters( 'twentyten_author_bio_avatar_size', 60 ) ); ?>
						</div><!-- #author-avatar -->
						<div id="author-description">
							<h2><?php printf( esc_attr__( 'About %s', 'twentyten' ), get_the_author() ); ?></h2>
							<?php the_author_meta( 'description' ); ?>
							<div id="author-link">
								<a href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) ); ?>">
									<?php printf( __( 'View all posts by %s <span class="meta-nav">&rarr;</span>', 'twentyten' ), get_the_author() ); ?>
								</a>
							</div><!-- #author-link	-->
						</div><!-- #author-description -->
					</div><!-- #entry-author-info -->
<?php endif; ?>
					<!--<div id="nav-below" class="navigation">
						<div class="nav-previous"><?php //previous_post_link( '%link', '<span class="meta-nav">' . _x( '&larr;', 'Previous post link', 'twentyten' ) . '</span> %title' ); ?></div>
						<div class="nav-next"><?php //next_post_link( '%link', '%title <span class="meta-nav">' . _x( '&rarr;', 'Next post link', 'twentyten' ) . '</span>' ); ?></div>
					</div>--><!-- #nav-below -->
				</div><!-- #post-## -->

<?php endwhile; // end of the loop. ?>
<?php comments_template( '', true ); ?>
<?php */ get_footer(); ?>
