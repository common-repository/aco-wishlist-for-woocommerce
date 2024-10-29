<?php
/**
 * Wishlist page template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<?php
	if ( $count > 0 ) {
		?>

<div class="head-sect">
	<h2><?php echo $default_wishlist_title; ?></h2>
	<div class="sect-right">
		<?php if( $multi_wishlist ){ ?>
		<div class="search">
			<img src="<?php echo plugin_dir_url( __DIR__ ); ?>/assets/images/search.svg" alt="image">
			<input type="text" placeholder="Search...">
		</div>
		<?php } ?>
		<!-- <button class="btn2 mr16">Ask for an estimate</button> -->
		<!-- <button class="btn mr16"><?php _e( 'Add all to cart', 'aco-wishlist-for-woocommerce' ); ?></button> -->

		<?php if ( $count > 0 && $share_wishlist ) { ?>
			<?php if( !is_user_logged_in() ){ ?>
			<a class="awwlm_popup_login " href="#" >
				<span class="awwlm-share icon-share-2"></span>
			</a>
			<?php } else { ?>
				<?php echo $this->awwlm_get_template('wishlist-share.php', $atts ); ?>
			<?php } ?>
		<?php } ?>

	</div>
</div>
<div class="table-sect">
	<table class="awwlm-table awwlm-table-collection" data-id="<?php echo $wishlist; ?>" >
	  <thead>
	    <tr>
				<th> </th>
	      <th> <?php _e( 'Product name', 'aco-wishlist-for-woocommerce' ); ?> </th>
				<?php if($show_price){ ?>
	      <th> <?php _e( 'Unit price', 'aco-wishlist-for-woocommerce' ); ?> </th>
				<?php } ?>
				<?php if($show_quantity){ ?>
	      <th> <?php _e( 'Quantity', 'aco-wishlist-for-woocommerce' ); ?> </th>
				<?php } ?>
				<?php if($show_date_added){ ?>
	      <th> <?php _e( 'Added on', 'aco-wishlist-for-woocommerce' ); ?> </th>
				<?php } ?>
				<?php if($show_stock){ ?>
	      <th> <?php _e( 'Stock status', 'aco-wishlist-for-woocommerce' ); ?> </th>
				<?php } ?>
	      <th> </th>
	    </tr>
	  </thead>
    <tbody>

			<?php

			if ( $wishlist_items && !empty($wishlist_items) ) {
				foreach ($wishlist_items as $key => $value) {

					global $product;
					$product = $this->awwlm_get_product($key);
					//$product = wc_get_product( $key );

					if ( $product instanceof WC_Product ) {
					$stock_status = $product->get_stock_status();
				?>
	    <tr id="awwlm-row-<?php echo $key; ?>" data-row-id="<?php echo $key; ?>" >
				<td >
					<a href="<?php echo esc_url( get_permalink( $key ) ); ?>">
						<span class="pimg">
							<?php echo $product->get_image(); ?>
						</span>
					</a>
				</td>
				<td data-col="<?php _e( 'Product name', 'aco-wishlist-for-woocommerce' ); ?>">
					<a href="<?php echo esc_url( get_permalink( $key ) ); ?>">
						<?php echo $product->get_title(); ?>
					</a>
						<?php
						if ( $product->is_type( 'variation' ) ) {
							echo wc_get_formatted_variation( $product );
						}
						?>
						<?php
						if( isset($value['formData']) ){
							apply_filters( 'awwlm_wishlist_item_meta_data', $product, $value['formData']);
						}
						?>
				</td>
				<?php if($show_price){ ?>
        <td  data-col="<?php _e( 'Unit price', 'aco-wishlist-for-woocommerce' ); ?>" class="blue">
					<?php
					if( isset($value['formData']) ){
						echo apply_filters( 'awwlm_wishlist_item_price', $product->get_price_html(), $product, $value['formData'] );
					} else {
						echo $product->get_price_html();
					}
					?>
        </td>
				<?php } ?>
				<?php if($show_quantity){ ?>
        <td data-col="<?php _e( 'Quantity', 'aco-wishlist-for-woocommerce' ); ?>">
        <input class="qinput" type="text">
        </td>
				<?php } ?>
				<?php if($show_date_added && $value['date_added'] ){ ?>
        <td data-col="<?php _e( 'Added on', 'aco-wishlist-for-woocommerce' ); ?>">
	        <?php echo $this->awwlm_get_formatted_date( $value['date_added'] ); ?>
        </td>
				<?php } ?>
				<?php if($show_stock){ ?>
        <td data-col="<?php _e( 'Stock status', 'aco-wishlist-for-woocommerce' ); ?>">
				<?php
					echo ($stock_status == 'outofstock') ? '<span class="wishlist-out-of-stock">' . __( 'Out of stock', 'aco-wishlist-for-woocommerce' ) . '</span>' : '<span class="wishlist-in-stock">' . __( 'In Stock', 'aco-wishlist-for-woocommerce' ) . '</span>';
				?>
        </td>
				<?php } ?>
        <td>
	        <div class="actions">
						<?php if($show_add_to_cart){ ?>
							<?php do_action( 'awwlm_before_add_to_cart_button' ); ?>
							<?php
								if( isset($value['formData']) && apply_filters( 'awwlm_wishlist_item_action_add_to_cart', $product, $value['formData'] ) ) {
									?>
									<a href="<?php echo add_query_arg( apply_filters( 'awwlm_wishlist_item_add_to_cart', $product, $value['formData'] ) , get_permalink( $key ) ); ?>" class="button awwlm_add_to_cart_button mr16" ><?php echo $add_to_cart_text; ?></a>
									<?php
								} else {
									woocommerce_template_loop_add_to_cart();
								}
								?>
								<?php do_action( 'awwlm_after_add_to_cart_button' ); ?>
						<?php } ?>
						<?php if($show_remove && $has_permission){ ?>
	          <span class="awwlm-remove delete icon-trash-o awwlm-tooltip mr16" data-tool="<?php _e( 'Delete', 'aco-wishlist-for-woocommerce' ); ?>"> </span>
						<?php } ?>
						<?php if($show_move_wishlist){ ?>
	          <span class="delete icon-file-text awwlm-tooltip mr0" data-tool="<?php _e( 'Move to another list', 'aco-wishlist-for-woocommerce' ); ?>"> </span>
						<?php } ?>
	        </div>
        </td>
      </tr>
			<?php
		}
		}
		}
		?>

    </tbody>
	</table>
	<div class="awwlm-wishlist-message"></div>
</div>


<?php }   ?>
