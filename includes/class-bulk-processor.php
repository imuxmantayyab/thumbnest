<?php
/**
 * Safe, paginated bulk assignment and rollback processor for ThumbNest.
 *
 * @package ThumbNest
 */

namespace ThumbNest;

defined( 'ABSPATH' ) || exit;

/**
 * Class Bulk_Processor
 */
class Bulk_Processor {

	/**
	 * Default batch size for processing.
	 */
	const BATCH_SIZE = 50;

	/**
	 * Register AJAX endpoints.
	 */
	public static function init() {
		add_action( 'wp_ajax_thumbnest_get_stats', array( __CLASS__, 'ajax_get_stats' ) );
		add_action( 'wp_ajax_thumbnest_bulk_assign_step', array( __CLASS__, 'ajax_bulk_assign_step' ) );
		add_action( 'wp_ajax_thumbnest_bulk_rollback_step', array( __CLASS__, 'ajax_bulk_rollback_step' ) );
	}

	/**
	 * Retrieve site-wide thumbnail statistics.
	 * Cached in a transient to prevent expensive queries on high-traffic sites.
	 *
	 * @param bool $force_refresh Whether to bypass cache.
	 * @return array
	 */
	public static function get_stats( $force_refresh = false ) {
		$cache_key = 'thumbnest_counts_cache';
		if ( ! $force_refresh ) {
			$cached = get_transient( $cache_key );
			if ( false !== $cached && is_array( $cached ) ) {
				return $cached;
			}
		}

		global $wpdb;
		$eligible_pts = array_keys( Post_Types::get_eligible() );
		if ( empty( $eligible_pts ) ) {
			return array(
				'total_posts'     => 0,
				'with_thumbnail'  => 0,
				'without_thumb'   => 0,
				'plugin_assigned' => 0,
				'breakdown'       => array(),
			);
		}

		$placeholders = implode( ', ', array_fill( 0, count( $eligible_pts ), '%s' ) );

		// 1. Total posts per post type.
		$query = $wpdb->prepare(
			"SELECT post_type, COUNT(ID) as total_count 
			FROM {$wpdb->posts} 
			WHERE post_status = 'publish' 
			AND post_type IN ($placeholders) 
			GROUP BY post_type",
			$eligible_pts
		);
		$total_results = $wpdb->get_results( $query, OBJECT_K );

		// 2. Posts with real featured image.
		$query_with_thumb = $wpdb->prepare(
			"SELECT p.post_type, COUNT(DISTINCT p.ID) as thumb_count 
			FROM {$wpdb->posts} p 
			INNER JOIN {$wpdb->postmeta} pm ON (p.ID = pm.post_id AND pm.meta_key = '_thumbnail_id' AND pm.meta_value > 0) 
			WHERE p.post_status = 'publish' 
			AND p.post_type IN ($placeholders) 
			GROUP BY p.post_type",
			$eligible_pts
		);
		$thumb_results = $wpdb->get_results( $query_with_thumb, OBJECT_K );

		// 3. Posts with plugin-assigned fallbacks.
		$query_plugin = $wpdb->prepare(
			"SELECT p.post_type, COUNT(DISTINCT p.ID) as plugin_count 
			FROM {$wpdb->posts} p 
			INNER JOIN {$wpdb->postmeta} pm ON (p.ID = pm.post_id AND pm.meta_key = '_thumbnest_is_fallback' AND pm.meta_value = '1') 
			WHERE p.post_status = 'publish' 
			AND p.post_type IN ($placeholders) 
			GROUP BY p.post_type",
			$eligible_pts
		);
		$plugin_results = $wpdb->get_results( $query_plugin, OBJECT_K );

		$total_posts     = 0;
		$with_thumb      = 0;
		$plugin_assigned = 0;
		$breakdown       = array();

		foreach ( $eligible_pts as $pt ) {
			$pt_total   = isset( $total_results[ $pt ] ) ? (int) $total_results[ $pt ]->total_count : 0;
			$pt_thumbs  = isset( $thumb_results[ $pt ] ) ? (int) $thumb_results[ $pt ]->thumb_count : 0;
			$pt_plugin  = isset( $plugin_results[ $pt ] ) ? (int) $plugin_results[ $pt ]->plugin_count : 0;
			$pt_without = max( 0, $pt_total - $pt_thumbs );

			$total_posts     += $pt_total;
			$with_thumb      += $pt_thumbs;
			$plugin_assigned += $pt_plugin;

			$breakdown[ $pt ] = array(
				'total'           => $pt_total,
				'with_thumbnail'  => $pt_thumbs,
				'without_thumb'   => $pt_without,
				'plugin_assigned' => $pt_plugin,
			);
		}

		$stats = array(
			'total_posts'     => $total_posts,
			'with_thumbnail'  => $with_thumb,
			'without_thumb'   => max( 0, $total_posts - $with_thumb ),
			'plugin_assigned' => $plugin_assigned,
			'breakdown'       => $breakdown,
		);

		set_transient( $cache_key, $stats, 10 * MINUTE_IN_SECONDS );
		return $stats;
	}

	/**
	 * AJAX endpoint to retrieve stats.
	 */
	public static function ajax_get_stats() {
		check_ajax_referer( 'thumbnest_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Permission denied.', 'thumbnest' ) ), 403 );
		}

		$stats = self::get_stats( true );
		wp_send_json_success( $stats );
	}

	/**
	 * AJAX endpoint: Process a single batch step of bulk assigning fallbacks.
	 */
	public static function ajax_bulk_assign_step() {
		check_ajax_referer( 'thumbnest_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Permission denied.', 'thumbnest' ) ), 403 );
		}

		$post_type = isset( $_POST['post_type'] ) ? sanitize_key( $_POST['post_type'] ) : 'all';
		$batch     = self::BATCH_SIZE;

		$eligible_pts = array_keys( Post_Types::get_eligible() );
		$target_pts   = ( 'all' === $post_type ) ? $eligible_pts : array( $post_type );

		// Filter out any non-eligible types.
		$target_pts = array_intersect( $target_pts, $eligible_pts );
		if ( empty( $target_pts ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'No valid post types selected.', 'thumbnest' ) ) );
		}

		// Find posts without a real featured image.
		$args = array(
			'post_type'      => $target_pts,
			'post_status'    => 'publish',
			'posts_per_page' => $batch,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'     => '_thumbnail_id',
					'compare' => 'NOT EXISTS',
				),
			),
			'no_found_rows'  => false,
		);

		$query = new \WP_Query( $args );
		$post_ids    = $query->posts;
		$total_found = $query->found_posts;

		$assigned_count = 0;

		foreach ( $post_ids as $post_id ) {
			$post_id = (int) $post_id;

			// Verify post doesn't already have a real featured image.
			if ( Image_Resolver::get_real_thumbnail_id( $post_id ) > 0 ) {
				continue;
			}

			// Resolve the configured fallback image.
			$fallback_id = Image_Resolver::resolve_fallback_id( $post_id );
			if ( $fallback_id > 0 ) {
				update_post_meta( $post_id, '_thumbnail_id', $fallback_id );
				update_post_meta( $post_id, '_thumbnest_is_fallback', 1 );
				$assigned_count++;
			}
		}

		// Clear transient count cache.
		delete_transient( 'thumbnest_counts_cache' );

		$remaining = max( 0, $total_found - count( $post_ids ) );
		$is_done   = empty( $post_ids ) || 0 === $remaining;

		wp_send_json_success(
			array(
				'processed' => count( $post_ids ),
				'assigned'  => $assigned_count,
				'remaining' => $remaining,
				'done'      => $is_done,
			)
		);
	}

	/**
	 * AJAX endpoint: Process a single batch step of rolling back plugin-assigned fallbacks.
	 */
	public static function ajax_bulk_rollback_step() {
		check_ajax_referer( 'thumbnest_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Permission denied.', 'thumbnest' ) ), 403 );
		}

		$batch = self::BATCH_SIZE;

		// Query posts marked with `_thumbnest_is_fallback => 1`.
		$args = array(
			'post_type'      => array_keys( Post_Types::get_eligible() ),
			'post_status'    => 'any',
			'posts_per_page' => $batch,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'     => '_thumbnest_is_fallback',
					'value'   => '1',
					'compare' => '=',
				),
			),
			'no_found_rows'  => false,
		);

		$query       = new \WP_Query( $args );
		$post_ids    = $query->posts;
		$total_found = $query->found_posts;

		$reverted_count = 0;

		foreach ( $post_ids as $post_id ) {
			$post_id = (int) $post_id;
			// Only remove if it was genuinely stamped by the plugin.
			if ( get_post_meta( $post_id, '_thumbnest_is_fallback', true ) ) {
				delete_post_meta( $post_id, '_thumbnail_id' );
				delete_post_meta( $post_id, '_thumbnest_is_fallback' );
				$reverted_count++;
			}
		}

		delete_transient( 'thumbnest_counts_cache' );

		$remaining = max( 0, $total_found - count( $post_ids ) );
		$is_done   = empty( $post_ids ) || 0 === $remaining;

		wp_send_json_success(
			array(
				'processed' => count( $post_ids ),
				'reverted'  => $reverted_count,
				'remaining' => $remaining,
				'done'      => $is_done,
			)
		);
	}
}
