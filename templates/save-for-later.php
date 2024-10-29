<?php
/**
 * Save for later table template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<?php if( $user_savelist ){ ?>

<div class="awwlm-container-savelater ">

	<h3><?php echo $savelist_heading; ?> <span>(<?php echo $count; ?>)</span></h3>

	<table class="shop_table shop_table_responsive cart woocommerce-cart-form__contents awwlm_save_for_later_cart" cellspacing="0">
  	<thead>
			<tr>
				<th class="product-thumbnail awwlm-saved-header-thumbnail">  </th>
				<th class="product-name awwlm-saved-header-name" > <?php _e( 'Product', 'aco-wishlist-for-woocommerce' ); ?> </th>
				<th class="product-price awwlm-saved-header-price"> <?php _e( 'Price', 'aco-wishlist-for-woocommerce' ); ?> </th>
				<th class="awwlm-saved-header-actions"> <?php _e( 'Actions', 'aco-wishlist-for-woocommerce' ); ?> </th>
			</tr>
      </thead>
      <tbody>
				<?php
          foreach( $user_savelist as $key => $item ):
          	global $product;
						$product_id = $item['product'];
						if( function_exists( 'wc_get_product' ) ) {
            	$product = wc_get_product( $product_id );
            } else {
            	$product = get_product( $product_id );
            }

						if ( $product ) {
						?>
						<tr>
            	<td class="product-thumbnail">
								<a href="<?php echo esc_url( get_permalink( apply_filters( 'woocommerce_in_cart_product', $product_id ) ) ) ?>">
									<?php echo $product->get_image(); ?>
								</a>
              </td>
              <td class="product-name" data-title="<?php _e( 'Product', 'aco-wishlist-for-woocommerce' ); ?>" >
								<a href="<?php echo esc_url( get_permalink( apply_filters( 'woocommerce_in_cart_product', $product_id ) ) ) ?>">
									<?php echo $product->get_name(); ?>
								</a>




								<?php
									if( isset($item['cartData'])  ){
										// error_log( print_r( $item['cartData'], true));
										echo wc_get_formatted_cart_item_data( $item['cartData']);
									}
								?>

              </td>
              <td class="product-price" data-title="<?php _e( 'Price', 'aco-wishlist-for-woocommerce' ); ?>" >
								<?php
								if ( isset( $item['cartData']['wcpa_price'] ) ) {
									echo wc_price($item['cartData']['wcpa_price']);
								} else {
									echo $product->get_price_html();
								}
								?>
              </td>

              <td class="product-actions" >
								<?php //woocommerce_template_loop_add_to_cart(); ?>

								<?php if ( $product->is_purchasable() && $product->is_in_stock() ) { ?>

									<?php $text = __( 'Add to cart', 'woocommerce' ); ?>

									<?php if( isset($item['cartData']) && apply_filters( 'awwlm_savelist_item_action_add_to_cart', $product, $item['cartData'] ) ) {  ?>

											<a href="<?php echo add_query_arg( apply_filters( 'awwlm_savelist_item_add_to_cart', $product, $item['cartData'] ),  get_permalink( $product_id ) ); ?>" class="button awwlm_add_to_cart_button " >
												<?php echo apply_filters( 'woocommerce_product_add_to_cart_text', $text, $product ); ?>
											</a>

									<?php } else { ?>
										<?php /*
										<?php woocommerce_template_loop_add_to_cart(); ?>

										<a href="?add-to-cart=<?php echo esc_attr( $product_id ); ?>" data-quantity="1" class="button wp-element-button product_type_simple add_to_cart_button ajax_add_to_cart awwlm-button awwlm-add-to-cart" data-product_id="<?php echo esc_attr( $product_id ); ?>" rel="nofollow"  data-list-id="<?php echo $key; ?>" data-id="<?php echo esc_attr( $product_id ); ?>" ><?php echo apply_filters( 'woocommerce_product_add_to_cart_text', $text, $product ); ?></a>
										*/ ?>
	                  					<button class="awwlm-button awwlm-add-to-cart" data-list-id="<?php echo $key; ?>" data-id="<?php echo esc_attr( $product_id ); ?>">
											<?php echo apply_filters( 'woocommerce_product_add_to_cart_text', $text, $product ); ?>
										</button>

									<?php } ?>

								<?php } else {
										woocommerce_template_loop_add_to_cart();
									}
								?>


								<div class="awwlm-save-later-btn">
                	<button class="awwlm-button1 button2 remove" data-list-id="<?php echo $key; ?>" data-id="<?php echo esc_attr( $product_id ); ?>" title="<?php _e( 'Remove', 'aco-wishlist-for-woocommerce' ); ?>" >
										<?php _e( 'Remove', 'aco-wishlist-for-woocommerce' ); ?>
                  </button>
                </div>

              </td>

						</td>
						<?php
						}
					endforeach;
				?>
			</tbody>
		</table>

</div>
<?php } elseif( $empty_enable ){ ?>

	<div class="awwlm-container-savelater empty_message">

		<h3><?php echo $savelist_heading; ?> <span>(<?php echo $count; ?>)</span></h3>
		<?php echo wpautop($empty_message); ?>
	</div>
<?php } ?>
