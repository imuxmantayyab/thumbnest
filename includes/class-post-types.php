<?php
/**
 * Post Type discovery and eligibility manager for ThumbNest.
 *
 * @package ThumbNest
 */

namespace ThumbNest;

defined( 'ABSPATH' ) || exit;

/**
 * Class Post_Types
 */
class Post_Types {

	/**
	 * List of internal or excluded post types that should never have fallbacks.
	 *
	 * @var array<string>
	 */
	private static $excluded_post_types = array(
		'attachment',
		'revision',
		'nav_menu_item',
		'custom_css',
		'customize_changeset',
		'oembed_cache',
		'user_request',
		'wp_block',
		'wp_template',
		'wp_template_part',
		'wp_navigation',
		'wp_font_family',
		'wp_font_face',
		'wp_global_styles',
	);

	/**
	 * Retrieve all eligible public post types that support featured images.
	 *
	 * @return array<string, \WP_Post_Type> Associative array of post type slug => WP_Post_Type object.
	 */
	public static function get_eligible() {
		$cached = Cache::get( 'eligible_post_types' );
		if ( null !== $cached ) {
			return $cached;
		}

		$all_post_types = get_post_types( array( 'public' => true ), 'objects' );
		$eligible       = array();

		foreach ( $all_post_types as $slug => $pt_object ) {
			// Skip explicitly excluded system types.
			if ( in_array( $slug, self::$excluded_post_types, true ) ) {
				continue;
			}

			// Ensure the post type supports post-thumbnails / featured images.
			if ( ! post_type_supports( $slug, 'thumbnail' ) ) {
				continue;
			}

			$eligible[ $slug ] = $pt_object;
		}

		/**
		 * Filter the list of eligible post types for ThumbNest fallbacks.
		 *
		 * @param array<string, \WP_Post_Type> $eligible Associative array of eligible post types.
		 * @param array<string, \WP_Post_Type> $all_post_types All registered public post types.
		 */
		$eligible = apply_filters( 'thumbnest_eligible_post_types', $eligible, $all_post_types );

		Cache::set( 'eligible_post_types', $eligible );
		return $eligible;
	}

	/**
	 * Check if a specific post type is eligible for fallbacks.
	 *
	 * @param string $post_type Post type slug.
	 * @return bool True if eligible.
	 */
	public static function is_eligible( $post_type ) {
		$eligible = self::get_eligible();
		return isset( $eligible[ $post_type ] );
	}

	/**
	 * Check if fallbacks are enabled for a specific post type in settings.
	 *
	 * @param string $post_type Post type slug.
	 * @return bool True if enabled.
	 */
	public static function is_enabled( $post_type ) {
		if ( ! self::is_eligible( $post_type ) ) {
			return false;
		}

		$pt_settings = Settings::get_post_type_settings( $post_type );
		return ! empty( $pt_settings['enabled'] ) && 'none' !== $pt_settings['source'];
	}
}
