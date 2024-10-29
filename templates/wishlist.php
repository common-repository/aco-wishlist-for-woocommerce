<?php
/**
 * Wishlist page template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="awwlm-container-wishlistlisting">
	<div class="container">


		<?php if( !( $require_login == 1 && !is_user_logged_in() ) ){ ?>
			<?php echo $this->awwlm_get_template('wishlist-'.$template.'.php', $atts ); ?>
		<?php } else { ?>

		<div class="empty">
			<h4><?php echo $login_message; ?></h4>
			<a href="<?php echo esc_url( add_query_arg( array( 'wishlist_notice' => '1' ), get_permalink( wc_get_page_id( 'myaccount' ) ) ) )?>" class="btn2" > <?php echo __( 'Login', 'aco-wishlist-for-woocommerce' ); ?> </a>
		</div>

		<?php }  ?>


	</div>
</div>
