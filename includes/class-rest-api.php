<?php
/**
 * REST API and Block Editor (Gutenberg) integration for ThumbNest.
 *
 * @package ThumbNest
 */

namespace ThumbNest;

defined( 'ABSPATH' ) || exit;

/**
 * Class Rest_API
 */
class Rest_API {

	/**
	 * Initialize REST API hooks.
	 */
	public static function init() {
		if ( ! Settings::get( 'enable_rest_api', 1 ) ) {
			return;
		}

		add_action( 'rest_api_init', array( __CLASS__, 'register_rest_fields' ) );
	}

	/**
	 * Register custom REST fields for eligible post types.
	 */
	public static function register_rest_fields() {
		$eligible_post_types = array_keys( Post_Types::get_eligible() );

		foreach ( $eligible_post_types as $post_type ) {
			register_rest_field(
				$post_type,
				'thumbnest_fallback',
				array(
					'get_callback'    => array( __CLASS__, 'get_rest_fallback_data' ),
					'update_callback' => null,
					'schema'          => array(
						'description' => esc_html__( 'ThumbNest fallback featured image data.', 'thumbnest' ),
						'type'        => 'object',
						'context'     => array( 'view', 'embed' ),
						'properties'  => array(
							'is_fallback' => array(
								'type'        => 'boolean',
								'description' => esc_html__( 'Whether the current featured image is a virtual fallback.', 'thumbnest' ),
							),
							'image_id'    => array(
								'type'        => 'integer',
								'description' => esc_html__( 'Attachment ID of the fallback image.', 'thumbnest' ),
							),
							'image_url'   => array(
								'type'        => 'string',
								'format'      => 'uri',
								'description' => esc_html__( 'URL of the fallback image.', 'thumbnest' ),
							),
						),
					),
				)
			);
		}
	}

	/**
	 * Resolver callback for the `thumbnest_fallback` REST field.
	 *
	 * @param array            $post_data Prepared post array.
	 * @param string           $field_name Field name.
	 * @param \WP_REST_Request $request REST request object.
	 * @return array
	 */
	public static function get_rest_fallback_data( $post_data, $field_name, $request ) {
		$post_id = isset( $post_data['id'] ) ? (int) $post_data['id'] : 0;
		if ( ! $post_id ) {
			return array(
				'is_fallback' => false,
				'image_id'    => 0,
				'image_url'   => '',
			);
		}

		$real_id = Image_Resolver::get_real_thumbnail_id( $post_id );
		if ( $real_id > 0 ) {
			return array(
				'is_fallback' => false,
				'image_id'    => 0,
				'image_url'   => '',
			);
		}

		$fallback_id = Image_Resolver::resolve_fallback_id( $post_id );
		if ( ! $fallback_id ) {
			return array(
				'is_fallback' => false,
				'image_id'    => 0,
				'image_url'   => '',
			);
		}

		$image_src = wp_get_attachment_image_src( $fallback_id, 'full' );

		return array(
			'is_fallback' => true,
			'image_id'    => $fallback_id,
			'image_url'   => $image_src ? $image_src[0] : '',
		);
	}
}
