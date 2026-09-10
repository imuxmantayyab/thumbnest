<?php
/**
 * Settings manager and sanitizer for ThumbNest.
 *
 * @package ThumbNest
 */

namespace ThumbNest;

defined( 'ABSPATH' ) || exit;

/**
 * Class Settings
 */
class Settings {

	/**
	 * Option key in wp_options.
	 */
	const OPTION_NAME = 'thumbnest_settings';

	/**
	 * Retrieve all plugin settings merged with defaults.
	 *
	 * @return array
	 */
	public static function get_all() {
		$cached = Cache::get( 'all_settings' );
		if ( null !== $cached ) {
			return $cached;
		}

		$options  = get_option( self::OPTION_NAME, array() );
		$defaults = self::get_defaults();

		$settings = wp_parse_args( $options, $defaults );

		// Ensure post_types array is well-formed.
		if ( ! isset( $settings['post_types'] ) || ! is_array( $settings['post_types'] ) ) {
			$settings['post_types'] = array();
		}

		Cache::set( 'all_settings', $settings );
		return $settings;
	}

	/**
	 * Retrieve a specific setting value by key.
	 *
	 * @param string $key Setting key.
	 * @param mixed  $default Fallback if key not found.
	 * @return mixed
	 */
	public static function get( $key, $default = null ) {
		$settings = self::get_all();
		return array_key_exists( $key, $settings ) ? $settings[ $key ] : $default;
	}

	/**
	 * Retrieve settings for a specific post type.
	 *
	 * @param string $post_type Post type slug.
	 * @return array Array containing 'enabled', 'source', 'image_id'.
	 */
	public static function get_post_type_settings( $post_type ) {
		$settings = self::get_all();
		$pt_data  = isset( $settings['post_types'][ $post_type ] ) ? $settings['post_types'][ $post_type ] : array();

		return wp_parse_args(
			$pt_data,
			array(
				'enabled'  => 1,
				'source'   => 'global',
				'image_id' => 0,
			)
		);
	}

	/**
	 * Default settings structure.
	 *
	 * @return array
	 */
	public static function get_defaults() {
		return array(
			'global_image_id'    => 0,
			'mode'               => 'virtual', // 'virtual' or 'physical'
			'enable_rest_api'    => 1,
			'enable_elementor'   => 1,
			'enable_woocommerce' => 1,
			'post_types'         => array(
				'post' => array(
					'enabled'  => 1,
					'source'   => 'global',
					'image_id' => 0,
				),
				'page' => array(
					'enabled'  => 1,
					'source'   => 'global',
					'image_id' => 0,
				),
			),
		);
	}

	/**
	 * Seed initial default options into database if missing.
	 */
	public static function set_defaults() {
		if ( false === get_option( self::OPTION_NAME ) ) {
			update_option( self::OPTION_NAME, self::get_defaults() );
		}
	}

	/**
	 * Sanitize and validate settings before saving.
	 *
	 * @param array $input Raw form input.
	 * @return array Sanitized settings.
	 */
	public static function sanitize( $input ) {
		$clean = self::get_defaults();

		if ( ! is_array( $input ) ) {
			return $clean;
		}

		// Global fallback image ID.
		if ( isset( $input['global_image_id'] ) ) {
			$clean['global_image_id'] = absint( $input['global_image_id'] );
			// Verify attachment exists and is an image if ID > 0.
			if ( $clean['global_image_id'] > 0 && ! wp_attachment_is_image( $clean['global_image_id'] ) ) {
				$clean['global_image_id'] = 0;
			}
		}

		// Fallback mode ('virtual' or 'physical').
		if ( isset( $input['mode'] ) && in_array( $input['mode'], array( 'virtual', 'physical' ), true ) ) {
			$clean['mode'] = sanitize_key( $input['mode'] );
		} else {
			$clean['mode'] = 'virtual';
		}

		// Integrations toggles.
		$clean['enable_rest_api']    = ! empty( $input['enable_rest_api'] ) ? 1 : 0;
		$clean['enable_elementor']   = ! empty( $input['enable_elementor'] ) ? 1 : 0;
		$clean['enable_woocommerce'] = ! empty( $input['enable_woocommerce'] ) ? 1 : 0;

		// Post Types configuration.
		$clean['post_types'] = array();
		$eligible_post_types = Post_Types::get_eligible();

		if ( isset( $input['post_types'] ) && is_array( $input['post_types'] ) ) {
			foreach ( $input['post_types'] as $pt_slug => $pt_config ) {
				$pt_slug = sanitize_key( $pt_slug );
				if ( ! isset( $eligible_post_types[ $pt_slug ] ) ) {
					continue;
				}

				$enabled  = ! empty( $pt_config['enabled'] ) ? 1 : 0;
				$source   = isset( $pt_config['source'] ) && in_array( $pt_config['source'], array( 'global', 'custom', 'none' ), true ) ? sanitize_key( $pt_config['source'] ) : 'global';
				$image_id = isset( $pt_config['image_id'] ) ? absint( $pt_config['image_id'] ) : 0;

				// Verify custom image exists and is an image.
				if ( 'custom' === $source && $image_id > 0 && ! wp_attachment_is_image( $image_id ) ) {
					$image_id = 0;
				}

				$clean['post_types'][ $pt_slug ] = array(
					'enabled'  => $enabled,
					'source'   => $source,
					'image_id' => $image_id,
				);
			}
		}

		// Invalidate caches.
		Cache::flush();

		/**
		 * Filter the sanitized settings before saving.
		 *
		 * @param array $clean Sanitized settings.
		 * @param array $input Raw input.
		 */
		return apply_filters( 'thumbnest_sanitized_settings', $clean, $input );
	}

	/**
	 * Reset all settings back to default.
	 *
	 * @return bool
	 */
	public static function reset_to_defaults() {
		Cache::flush();
		return update_option( self::OPTION_NAME, self::get_defaults() );
	}
}
