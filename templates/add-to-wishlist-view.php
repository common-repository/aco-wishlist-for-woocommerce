<?php
/**
 * view wishlist template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;

?>

<div class="awwlm-add-button awwlm-wishlist-exist" data-product-id="<?php echo esc_attr( $product_id ); ?>" data-original-product-id="<?php echo esc_attr( $parent_product_id ); ?>">

	<a class="view-link <?php echo esc_attr( $button_classes ); ?>" href="<?php echo esc_url( $wishlist_url ); ?>" >
		<?php echo $icon_added; ?>
		<span><?php echo wp_kses_post( $browse_text ); ?></span>
	</a>
</div>
