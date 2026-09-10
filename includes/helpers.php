<?php
/**
 * Global helper functions and template tags for ThumbNest.
 *
 * @package ThumbNest
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'thumbnest_get_fallback_image_id' ) ) {
	/**
	 * Retrieve the fallback attachment ID for a specific post or post type.
	 *
	 * @param int|WP_Post|null $post Optional. Post ID or post object. Defaults to global $post.
	 * @return int Attachment ID, or 0 if no fallback is available.
	 */
	function thumbnest_get_fallback_image_id( $post = null ) {
		return \ThumbNest\Image_Resolver::resolve_fallback_id( $post );
	}
}

if ( ! function_exists( 'thumbnest_has_fallback' ) ) {
	/**
	 * Check whether a post or post type has a valid fallback image available.
	 *
	 * @param int|WP_Post|null $post Optional. Post ID or post object.
	 * @return bool True if a fallback is configured and active.
	 */
	function thumbnest_has_fallback( $post = null ) {
		return thumbnest_get_fallback_image_id( $post ) > 0;
	}
}

if ( ! function_exists( 'thumbnest_get_fallback_image_html' ) ) {
	/**
	 * Get the HTML markup for a post's fallback featured image.
	 *
	 * @param int|WP_Post|null $post Optional. Post ID or post object.
	 * @param string|array     $size Optional. Image size. Default 'post-thumbnail'.
	 * @param string|array     $attr Optional. Query string or array of attributes. Default empty.
	 * @return string Image HTML markup, or empty string on failure.
	 */
	function thumbnest_get_fallback_image_html( $post = null, $size = 'post-thumbnail', $attr = '' ) {
		$image_id = thumbnest_get_fallback_image_id( $post );
		if ( ! $image_id ) {
			return '';
		}

		return wp_get_attachment_image( $image_id, $size, false, $attr );
	}
}

if ( ! function_exists( 'thumbnest_the_fallback_image' ) ) {
	/**
	 * Display the fallback featured image markup for a post.
	 *
	 * @param int|WP_Post|null $post Optional. Post ID or post object.
	 * @param string|array     $size Optional. Image size. Default 'post-thumbnail'.
	 * @param string|array     $attr Optional. Query string or array of attributes.
	 */
	function thumbnest_the_fallback_image( $post = null, $size = 'post-thumbnail', $attr = '' ) {
		echo wp_kses_post( thumbnest_get_fallback_image_html( $post, $size, $attr ) );
	}
}
