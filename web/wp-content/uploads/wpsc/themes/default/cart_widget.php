
<?php if(wpsc_cart_item_count() > 0): ?>
<span class='items'>

	<form action='' method='post' class='wpsc_empty_the_cart'>
		<input type='hidden' name='wpsc_ajax_action' value='empty_cart' />
		<span class='cartcount'>
			<a href='<?php echo get_option('shopping_cart_url'); ?>'><?php echo wpsc_cart_item_count(); ?> <?php echo TXT_WPSC_NUMBEROFITEMS1; ?></a> | <?php echo wpsc_cart_total_widget(); ?> | <a href='<?php echo htmlentities(add_query_arg('wpsc_ajax_action', 'empty_cart', remove_query_arg('ajax')), ENT_QUOTES); ?>'><?php echo TXT_WPSC_CLEARCART; ?></a>
		</span>

	</form>
</span>

<?php else: ?>
	<p class="empty"><?php echo wpsc_cart_item_count(); ?> <?php echo TXT_WPSC_NUMBEROFITEMS; ?> | <?php echo wpsc_cart_total_widget(); ?></p>
<?php endif; ?>

<?php
wpsc_google_checkout();
?>