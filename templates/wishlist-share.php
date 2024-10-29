<?php
/**
 * Wishlist page template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="awwlm-social-icons">

	<div class="awwlm-share icon-share-2"></div>
	<div class="awwlm-icon-list shlist">
	    <ul>
				<?php if ( $share_facebook ){ ?>
	        <li>
						<a target="_blank" href="https://www.facebook.com/sharer.php?u=<?php echo urlencode( $share_link ); ?>&p[title]=<?php echo esc_attr( $share_title ); ?>" title="Facebook" >
							<i class="fab fa-facebook-f"></i>
						</a>
					</li>
					<?php } ?>
					<?php if ( $share_twitter ){ ?>
	        <li>
						<a target="_blank" href="https://twitter.com/share?url=<?php echo urlencode( $share_link ); ?>&amp;text=<?php echo esc_attr( $share_text ); ?>" title="Twitter" >
							<i class="fab fa-twitter"></i>
						</a>
					</li>
					<?php } ?>
					<?php if ( $share_pinterest ){ ?>
	        <li>
						<a target="_blank" href="http://pinterest.com/pin/create/button/?url=<?php echo urlencode( $share_link ); ?>&amp;description=<?php echo esc_attr( $share_text ); ?>&amp;media=<?php echo esc_attr( $share_link_url ); ?>" title="Pinterest" >
							<i class="fab fa-pinterest-square"></i>
						</a>
					</li>
					<?php } ?>
					<?php if ( $share_email ){ ?>
	        <li>
						<a target="_blank" href="mailto:?subject=<?php echo esc_attr( $share_title ); ?>&amp;body=<?php echo urlencode( $share_link ); ?>&amp;title=<?php echo esc_attr( $share_title ); ?>" title="Email" >
							<i class="fab icon-envelop"></i>
						</a>
					</li>
					<?php } ?>
					<?php if ( $share_whatsapp ){ ?>
	        <li>
						<a target="_blank" href="https://api.whatsapp.com/send?text=<?php echo esc_attr( $share_title ); ?>–<?php echo urlencode( $share_link ); ?>" title="WhatsApp" >
							<i class="fab fa-whatsapp"></i>
						</a>
					</li>
					<?php } ?>
					<?php if ( $share_url ){ ?>
	        <li>
						<a href="<?php echo esc_attr( $share_link ); ?>" class="copy-target" title="Clipboard" >
							<i class="fab icon-files-empty"></i>
						</a>
					</li>
					<?php } ?>
	    </ul>
	</div>

</div>
