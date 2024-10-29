<?php
/**
 * Wishlist page template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

		<div class="empty">
			<div class="notfoundimg">
				<img src="<?php echo plugin_dir_url( __DIR__ ); ?>/assets/images/notfound.svg" alt="image">
			</div>
			<h4><?php echo apply_filters( 'awwlm_no_product_wishlist_text', __( 'Your Wishlist is currently empty', 'aco-wishlist-for-woocommerce' ) ); ?></h4>
			<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="btn2" > <?php echo __( 'Return to shop', 'aco-wishlist-for-woocommerce' ); ?> </a>
		</div>
