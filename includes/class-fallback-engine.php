<?php
/**
 * Frontend and Virtual Fallback hook engine for ThumbNest.
 *
 * @package ThumbNest
 */

namespace ThumbNest;

defined( 'ABSPATH' ) || exit;

/**
 * Class Fallback_Engine
 */
class Fallback_Engine {

	/**
	 * Re-entrancy guard to prevent recursion in metadata filters.
	 *
	 * @var bool
	 */
	private static $in_filter = false;

	/**
	 * Register all core WordPress thumbnail filters.
	 */
	public static function init() {
		// Only run virtual fallback if mode is virtual (or as safety layer).
		add_filter( 'has_post_thumbnail', array( __CLASS__, 'filter_has_post_thumbnail' ), 10, 3 );
		add_filter( 'post_thumbnail_id', array( __CLASS__, 'filter_post_thumbnail_id' ), 10, 2 );
		add_filter( 'get_post_metadata', array( __CLASS__, 'filter_get_post_metadata' ), 10, 4 );
		add_filter( 'post_thumbnail_html', array( __CLASS__, 'filter_post_thumbnail_html' ), 10, 5 );
		add_filter( 'wp_get_attachment_image_attributes', array( __CLASS__, 'filter_image_attributes' ), 10, 3 );
	}

	/**
	 * Check if the current context is an administrative post edit screen.
	 * In post edit screens, we should not trick the Featured Image metabox into showing
	 * the virtual fallback as if it were physically saved.
	 *
	 * @return bool
	 */
	public static function is_admin_post_edit_screen() {
		if ( ! is_admin() ) {
			return false;
		}

		// Allow fallback in frontend, AJAX rendering, and REST if requested.
		if ( wp_doing_ajax() ) {
			return false;
		}

		global $pagenow;
		if ( in_array( $pagenow, array( 'post.php', 'post-new.php' ), true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Filter `has_post_thumbnail`.
	 *
	 * @param bool             $has_thumbnail True if post has thumbnail, false otherwise.
	 * @param int|\WP_Post|null $post Post ID or post object.
	 * @param int|string       $thumbnail_id Attachment ID if any.
	 * @return bool
	 */
	public static function filter_has_post_thumbnail( $has_thumbnail, $post, $thumbnail_id ) {
		if ( self::is_admin_post_edit_screen() ) {
			return $has_thumbnail;
		}

		// If it already has a real thumbnail verified, keep true.
		if ( $has_thumbnail && $thumbnail_id > 0 && ! get_post_meta( ( is_object( $post ) ? $post->ID : $post ), '_thumbnest_is_fallback', true ) ) {
			return true;
		}

		// Check if a fallback is available.
		$fallback_id = Image_Resolver::resolve_fallback_id( $post );
		if ( $fallback_id > 0 ) {
			return true;
		}

		return $has_thumbnail;
	}

	/**
	 * Filter `post_thumbnail_id`.
	 *
	 * @param int|string       $thumbnail_id Current thumbnail attachment ID.
	 * @param int|\WP_Post|null $post Post ID or post object.
	 * @return int
	 */
	public static function filter_post_thumbnail_id( $thumbnail_id, $post ) {
		if ( self::is_admin_post_edit_screen() ) {
			return (int) $thumbnail_id;
		}

		$post_obj = get_post( $post );
		if ( ! $post_obj instanceof \WP_Post ) {
			return (int) $thumbnail_id;
		}

		// If real thumbnail exists, return it.
		$real_id = Image_Resolver::get_real_thumbnail_id( $post_obj->ID );
		if ( $real_id > 0 ) {
			return $real_id;
		}

		// Otherwise return fallback ID if available.
		$fallback_id = Image_Resolver::resolve_fallback_id( $post_obj );
		if ( $fallback_id > 0 ) {
			return $fallback_id;
		}

		return (int) $thumbnail_id;
	}

	/**
	 * Filter `get_post_metadata` for `_thumbnail_id`.
	 *
	 * @param mixed  $value Original value (null by default).
	 * @param int    $post_id Post ID.
	 * @param string $meta_key Meta key.
	 * @param bool   $single Whether single value requested.
	 * @return mixed
	 */
	public static function filter_get_post_metadata( $value, $post_id, $meta_key, $single ) {
		if ( '_thumbnail_id' !== $meta_key || self::$in_filter || self::is_admin_post_edit_screen() ) {
			return $value;
		}

		self::$in_filter = true;

		$real_id = Image_Resolver::get_real_thumbnail_id( $post_id );

		self::$in_filter = false;

		if ( $real_id > 0 ) {
			return $value; // Let standard WordPress handling return real ID.
		}

		$post_obj = get_post( $post_id );
		if ( ! $post_obj ) {
			return $value;
		}

		$fallback_id = Image_Resolver::resolve_fallback_id( $post_obj );
		if ( $fallback_id > 0 ) {
			return $single ? (string) $fallback_id : array( (string) $fallback_id );
		}

		return $value;
	}

	/**
	 * Filter `post_thumbnail_html`.
	 * Ensures clean HTML markup is returned with requested sizes and proper classes.
	 *
	 * @param string       $html Existing thumbnail HTML.
	 * @param int          $post_id Post ID.
	 * @param int          $post_thumbnail_id Thumbnail ID.
	 * @param string|array $size Requested image size.
	 * @param string|array $attr Attributes.
	 * @return string
	 */
	public static function filter_post_thumbnail_html( $html, $post_id, $post_thumbnail_id, $size, $attr ) {
		if ( self::is_admin_post_edit_screen() ) {
			return $html;
		}

		// If HTML is already present and represents a real thumbnail, keep it.
		$real_id = Image_Resolver::get_real_thumbnail_id( $post_id );
		if ( ! empty( $html ) && $real_id > 0 ) {
			return $html;
		}

		// Check if we have a fallback image.
		$fallback_id = Image_Resolver::resolve_fallback_id( $post_id );
		if ( ! $fallback_id ) {
			return $html;
		}

		// Build attributes array.
		$default_attr = array(
			'class' => 'attachment-' . ( is_array( $size ) ? implode( 'x', $size ) : $size ) . ' size-' . ( is_array( $size ) ? implode( 'x', $size ) : $size ) . ' wp-post-image thumbnest-fallback-image',
		);

		if ( is_array( $attr ) ) {
			if ( isset( $attr['class'] ) ) {
				$attr['class'] .= ' thumbnest-fallback-image';
			}
			$attr = array_merge( $default_attr, $attr );
		} else {
			$attr = $default_attr;
		}

		$fallback_html = wp_get_attachment_image( $fallback_id, $size, false, $attr );

		/**
		 * Filter the generated fallback post thumbnail HTML markup.
		 *
		 * @param string       $fallback_html The rendered image markup.
		 * @param int          $post_id Post ID.
		 * @param int          $fallback_id Fallback attachment ID.
		 * @param string|array $size Image size.
		 * @param array|string $attr Attributes.
		 */
		return apply_filters( 'thumbnest_post_thumbnail_html', $fallback_html, $post_id, $fallback_id, $size, $attr );
	}

	/**
	 * Filter attachment image attributes to ensure accessible alt text.
	 *
	 * @param array        $attr Attributes for the image markup.
	 * @param \WP_Post     $attachment Attachment post object.
	 * @param string|array $size Requested size.
	 * @return array
	 */
	public static function filter_image_attributes( $attr, $attachment, $size ) {
		if ( empty( $attr['alt'] ) && $attachment instanceof \WP_Post ) {
			// If attachment has no alt text, check attachment title or leave empty.
			$alt = get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true );
			if ( ! empty( $alt ) ) {
				$attr['alt'] = trim( strip_tags( $alt ) );
			}
		}

		return $attr;
	}
}
