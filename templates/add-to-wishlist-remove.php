<?php
/**
 * Remove wishlist template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;

?>

<div class="awwlm-add-button" >
	<a href="#" data-product-id="<?php echo esc_attr( $product_id ) ?>" data-original-product-id="<?php echo esc_attr( $parent_product_id ); ?>" class="awwlm_remove_wishlist <?php echo esc_attr( $button_classes ); ?>" >
		<?php echo $icon_added;  ?>
		<span><?php echo wp_kses_post( $remove_text ); ?></span>
	</a>
</div>
