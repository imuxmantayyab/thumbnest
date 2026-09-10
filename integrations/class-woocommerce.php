<?php
/**
 * WooCommerce integration for ThumbNest.
 *
 * @package ThumbNest
 */

namespace ThumbNest\Integrations;

defined( 'ABSPATH' ) || exit;

/**
 * Class WooCommerce
 */
class WooCommerce {

	/**
	 * Initialize WooCommerce compatibility hooks.
	 */
	public static function init() {
		if ( ! \ThumbNest\Settings::get( 'enable_woocommerce', 1 ) ) {
			return;
		}

		// Ensure WooCommerce fallback image works in product catalogs and single product loops.
		add_filter( 'woocommerce_placeholder_img_src', array( __CLASS__, 'filter_woocommerce_placeholder_src' ), 10, 1 );
	}

	/**
	 * Filter WooCommerce default placeholder image URL when product has no image.
	 *
	 * @param string $src Default placeholder URL.
	 * @return string
	 */
	public static function filter_woocommerce_placeholder_src( $src ) {
		// Only override if 'product' post type is enabled in ThumbNest.
		if ( ! \ThumbNest\Post_Types::is_enabled( 'product' ) ) {
			return $src;
		}

		$fallback_id = \ThumbNest\Image_Resolver::resolve_fallback_id( null, 'product' );
		if ( $fallback_id > 0 ) {
			$img_data = wp_get_attachment_image_src( $fallback_id, 'woocommerce_thumbnail' );
			if ( $img_data && ! empty( $img_data[0] ) ) {
				return $img_data[0];
			}
		}

		return $src;
	}
}
