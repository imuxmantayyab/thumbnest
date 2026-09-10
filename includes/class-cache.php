<?php
/**
 * In-memory and transient cache manager for ThumbNest.
 *
 * @package ThumbNest
 */

namespace ThumbNest;

defined( 'ABSPATH' ) || exit;

/**
 * Class Cache
 */
class Cache {

	/**
	 * In-memory runtime storage for fast resolution during the request.
	 *
	 * @var array<string, mixed>
	 */
	private static $runtime_cache = array();

	/**
	 * Get an item from the runtime memory cache.
	 *
	 * @param string $key Cache key.
	 * @return mixed|null Null if not set.
	 */
	public static function get( $key ) {
		return isset( self::$runtime_cache[ $key ] ) ? self::$runtime_cache[ $key ] : null;
	}

	/**
	 * Store an item in the runtime memory cache.
	 *
	 * @param string $key Cache key.
	 * @param mixed  $value Value to store.
	 */
	public static function set( $key, $value ) {
		self::$runtime_cache[ $key ] = $value;
	}

	/**
	 * Flush runtime and persistent caches.
	 */
	public static function flush() {
		self::$runtime_cache = array();
		delete_transient( 'thumbnest_counts_cache' );
		delete_transient( 'thumbnest_post_types' );
	}
}
