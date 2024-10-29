<?php
/**
 * Add to wishlist template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;

?>

<div class="awwlm-add-button">
	<a href="#" data-product-id="<?php echo esc_attr( $product_id ) ?>" data-original-product-id="<?php echo esc_attr( $parent_product_id ); ?>" class="awwlm_add_to_wishlist <?php echo esc_attr( $button_classes ); ?>" >
		<?php echo $icon; ?>
		<span><?php echo wp_kses_post( $add_to_text ); ?></span>
	</a>
</div>
