<?php
/**
 * Elementor Page Builder integration for ThumbNest.
 *
 * @package ThumbNest
 */

namespace ThumbNest\Integrations;

defined( 'ABSPATH' ) || exit;

/**
 * Class Elementor
 */
class Elementor {

	/**
	 * Initialize Elementor compatibility hooks.
	 */
	public static function init() {
		if ( ! \ThumbNest\Settings::get( 'enable_elementor', 1 ) ) {
			return;
		}

		// Hook into Elementor image rendering filter.
		add_filter( 'elementor/image_size/get_attachment_image_html', array( __CLASS__, 'filter_elementor_image_html' ), 10, 4 );
	}

	/**
	 * Filter Elementor attachment image HTML when post featured image is requested.
	 *
	 * @param string       $html Existing HTML.
	 * @param array        $settings Elementor widget settings.
	 * @param string       $image_size_key Setting key for size.
	 * @param string|array $image_size Image size name or dimensions.
	 * @return string
	 */
	public static function filter_elementor_image_html( $html, $settings, $image_size_key, $image_size ) {
		// If HTML is already rendered, let it pass.
		if ( ! empty( $html ) ) {
			return $html;
		}

		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return $html;
		}

		// Check if real thumbnail exists.
		if ( \ThumbNest\Image_Resolver::get_real_thumbnail_id( $post_id ) > 0 ) {
			return $html;
		}

		$fallback_id = \ThumbNest\Image_Resolver::resolve_fallback_id( $post_id );
		if ( ! $fallback_id ) {
			return $html;
		}

		$attr = array(
			'class' => 'elementor-post-thumbnail thumbnest-fallback-image',
		);

		return wp_get_attachment_image( $fallback_id, $image_size, false, $attr );
	}
}
