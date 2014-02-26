<?php
/**
 * The Sidebar containing the primary and secondary widget areas.
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */
?>
<div id="blog-sidebar">
		<h3 class="sidebar-title">Recent Articles</h3>
		<?php
		$temp = $wp_query;
		$wp_query= null;
		$wp_query = new WP_Query();
		$wp_query->query('showposts=10&paged='.$paged);
		?>
		<ul class="sidebar-list">
		<?php while ($wp_query->have_posts()) : $wp_query->the_post(); ?>
			<li><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></li>
		<?php endwhile; ?>
		</ul>
		<?php $wp_query = null; $wp_query = $temp;?>

		<h3 class="sidebar-title">Categories</h3>
		<?php $args = array(
			'show_option_all'    => '',
			'orderby'            => 'name',
			'order'              => 'ASC',
			'style'              => 'list',
			'show_count'         => 0,
			'hide_empty'         => 1,
			'use_desc_for_title' => 1,
			'child_of'           => 0,
			'feed'               => '',
			'feed_type'          => '',
			'feed_image'         => '',
			'exclude'            => '',
			'exclude_tree'       => '',
			'include'            => '',
			'hierarchical'       => true,
			'title_li'           => __( '' ),
			'show_option_none'   => __('No categories'),
			'number'             => null,
			'echo'               => 1,
			'depth'              => 0,
			'current_category'   => 0,
			'pad_counts'         => 0,
			'taxonomy'           => 'category',
			'walker'             => 'Walker_Category'
		); ?>
		<ul class="sidebar-list">
		<?php echo wp_list_categories( $args ); ?>
		</ul>
		
		<h3 class="sidebar-title">Archives</h3>
		<?php $args = array(
			'type'            => 'monthly',
			'limit'           => '',
			'format'          => 'html', 
			'before'          => '',
			'after'           => '',
			'show_post_count' => false,
			'echo'            => 1
		); ?>
		<ul class="sidebar-list">
		<?php echo wp_get_archives( $args ); ?> 
		</ul>
		</div>