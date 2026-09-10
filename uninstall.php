<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package ThumbNest
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

// Delete plugin options.
delete_option( 'thumbnest_settings' );
delete_option( 'thumbnest_version' );

// Clean up any remaining transients and caches.
delete_transient( 'thumbnest_counts_cache' );

// Optional: Clean up plugin-generated fallback postmeta flags.
// We remove the fallback flag, and if the user wants to leave physical assignments intact or clean them up,
// we ensure the database has no orphaned thumbnest meta.
global $wpdb;
$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->postmeta} WHERE meta_key = %s",
		'_thumbnest_is_fallback'
	)
);
