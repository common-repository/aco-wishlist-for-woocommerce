<?php
/**
 * Add to wishlist template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;
?>

<div class="awwlm-add-to-wishlist-wrap awwlm_add_to_wishlist_<?php echo esc_attr( $product_id ); ?> <?php echo esc_attr( $container_classes ); ?>" data-exists="<?php echo ($exists== 1 ? 'default' : $exists); ?>" >

		<?php if( !( $require_login == 1 && !is_user_logged_in() ) ){ ?>
			<?php echo $this->awwlm_get_template('add-to-wishlist-'.$template.'.php', $atts ); ?>
		<?php } else { ?>

			<div class="awwlm-add-button">

				<?php if( $redirect_login == 1 ){ ?>
					<a class="awwlm_normal_link <?php echo esc_attr( $button_classes ); ?>" href="<?php echo esc_url( add_query_arg( array( 'wishlist_notice' => '1', 'add_to_wishlist' => $product_id ), get_permalink( wc_get_page_id( 'myaccount' ) ) ) )?>" >
						<?php echo $icon;  ?>
						<span><?php echo esc_html( $add_to_text ); ?></span>
					</a>
				<?php //} else if( $success_popup == 1 ){ ?>
				<?php } else { ?>
					<a class="awwlm_popup_login <?php echo esc_attr( $button_classes ); ?>" href="#" >
						<?php echo $icon; ?>
						<span><?php echo esc_html( $add_to_text ); ?></span>
					</a>
				<?php } ?>

			</div>

		<?php } ?>

</div>
