<?php
/**
 * Image priority and fallback ID resolution engine for ThumbNest.
 *
 * @package ThumbNest
 */

namespace ThumbNest;

defined( 'ABSPATH' ) || exit;

/**
 * Class Image_Resolver
 */
class Image_Resolver {

	/**
	 * Check if a post has a genuine, manually assigned featured image.
	 *
	 * @param int $post_id Post ID.
	 * @return int Attachment ID if real, 0 otherwise.
	 */
	public static function get_real_thumbnail_id( $post_id ) {
		$post_id = absint( $post_id );
		if ( ! $post_id ) {
			return 0;
		}

		// Direct fetch from postmeta bypassing filters.
		$thumbnail_id = (int) get_post_meta( $post_id, '_thumbnail_id', true );
		if ( $thumbnail_id <= 0 ) {
			return 0;
		}

		// Check if this thumbnail was marked as an auto-assigned fallback.
		$is_plugin_fallback = (bool) get_post_meta( $post_id, '_thumbnest_is_fallback', true );
		if ( $is_plugin_fallback ) {
			return 0;
		}

		// Verify that the attachment still exists and is an image.
		if ( ! wp_attachment_is_image( $thumbnail_id ) ) {
			return 0;
		}

		return $thumbnail_id;
	}

	/**
	 * Resolve the fallback image attachment ID for a post or post type based on priority rules.
	 *
	 * Priority Rules:
	 * 1. Post type must be eligible and enabled.
	 * 2. If post type uses 'custom' and has valid image_id -> return custom image ID.
	 * 3. If post type uses 'global' and global_image_id is valid -> return global image ID.
	 * 4. Otherwise return 0.
	 *
	 * @param int|\WP_Post|null $post Optional. Post ID, post object, or null for current global post.
	 * @param string|null       $post_type Optional. Explicit post type if post is null.
	 * @return int Fallback attachment ID, or 0 if none.
	 */
	public static function resolve_fallback_id( $post = null, $post_type = null ) {
		$post_obj = get_post( $post );
		$pt       = '';

		if ( $post_obj instanceof \WP_Post ) {
			$pt = $post_obj->post_type;
		} elseif ( ! empty( $post_type ) ) {
			$pt = sanitize_key( $post_type );
		}

		if ( empty( $pt ) || ! Post_Types::is_eligible( $pt ) ) {
			return 0;
		}

		// Check runtime cache for this post type.
		$cache_key = 'resolved_fallback_' . $pt;
		$cached_id = Cache::get( $cache_key );
		if ( null !== $cached_id ) {
			return $cached_id;
		}

		$pt_settings = Settings::get_post_type_settings( $pt );

		// If disabled or set to 'none', no fallback.
		if ( empty( $pt_settings['enabled'] ) || 'none' === $pt_settings['source'] ) {
			Cache::set( $cache_key, 0 );
			return 0;
		}

		$fallback_id = 0;

		// 1. Check for custom post-type image.
		if ( 'custom' === $pt_settings['source'] && ! empty( $pt_settings['image_id'] ) ) {
			$custom_id = absint( $pt_settings['image_id'] );
			if ( wp_attachment_is_image( $custom_id ) ) {
				$fallback_id = $custom_id;
			}
		}

		// 2. Fall back to global image if no custom image resolved.
		if ( 0 === $fallback_id ) {
			$global_id = absint( Settings::get( 'global_image_id', 0 ) );
			if ( $global_id > 0 && wp_attachment_is_image( $global_id ) ) {
				$fallback_id = $global_id;
			}
		}

		/**
		 * Filter the resolved fallback image ID.
		 *
		 * @param int         $fallback_id Resolved fallback attachment ID.
		 * @param int|null    $post_id Post ID if available.
		 * @param string      $pt Post type slug.
		 */
		$post_id     = $post_obj ? $post_obj->ID : 0;
		$fallback_id = (int) apply_filters( 'thumbnest_fallback_image_id', $fallback_id, $post_id, $pt );

		Cache::set( $cache_key, $fallback_id );
		return $fallback_id;
	}

	/**
	 * Get the effective thumbnail ID for a post (real if present, otherwise fallback).
	 *
	 * @param int|\WP_Post|null $post Post ID or post object.
	 * @return int Attachment ID, or 0 if none.
	 */
	public static function get_effective_thumbnail_id( $post = null ) {
		$post_obj = get_post( $post );
		if ( ! $post_obj instanceof \WP_Post ) {
			return 0;
		}

		// Check for real featured image first.
		$real_id = self::get_real_thumbnail_id( $post_obj->ID );
		if ( $real_id > 0 ) {
			return $real_id;
		}

		// Otherwise resolve fallback.
		return self::resolve_fallback_id( $post_obj );
	}
}
