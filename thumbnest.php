<?php
/**
 * Plugin Name:       ThumbNest – Smart Fallback Featured Images
 * Plugin URI:        https://github.com/imuxmantayyab/thumbnest
 * Description:       Intelligently provides lightweight, virtual fallback featured images across posts, pages, and custom post types without database bloat. Includes optional non-destructive bulk assignment.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Usman Tayyab
 * Author URI:        https://www.linkedin.com/in/imuxmantayyab/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       thumbnest
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/imuxmantayyab/thumbnest
 *
 * @package           ThumbNest
 */

defined( 'ABSPATH' ) || exit;

// Define plugin version and directory constants.
define( 'THUMBNEST_VERSION', '1.0.0' );
define( 'THUMBNEST_PLUGIN_FILE', __FILE__ );
define( 'THUMBNEST_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'THUMBNEST_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'THUMBNEST_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Autoload core classes and include necessary files.
 */
require_once THUMBNEST_PLUGIN_DIR . 'includes/helpers.php';
require_once THUMBNEST_PLUGIN_DIR . 'includes/class-cache.php';
require_once THUMBNEST_PLUGIN_DIR . 'includes/class-settings.php';
require_once THUMBNEST_PLUGIN_DIR . 'includes/class-post-types.php';
require_once THUMBNEST_PLUGIN_DIR . 'includes/class-image-resolver.php';
require_once THUMBNEST_PLUGIN_DIR . 'includes/class-fallback-engine.php';
require_once THUMBNEST_PLUGIN_DIR . 'includes/class-bulk-processor.php';
require_once THUMBNEST_PLUGIN_DIR . 'includes/class-rest-api.php';
require_once THUMBNEST_PLUGIN_DIR . 'integrations/class-elementor.php';
require_once THUMBNEST_PLUGIN_DIR . 'integrations/class-woocommerce.php';
require_once THUMBNEST_PLUGIN_DIR . 'admin/class-admin.php';
require_once THUMBNEST_PLUGIN_DIR . 'includes/class-thumbnest.php';

/**
 * Main plugin bootstrap function.
 *
 * @return \ThumbNest\Plugin
 */
function thumbnest() {
	return \ThumbNest\Plugin::get_instance();
}

// Initialize the plugin.
add_action( 'plugins_loaded', 'thumbnest' );

/**
 * Activation hook callback.
 */
function thumbnest_activate() {
	// Initialize default options if not present.
	\ThumbNest\Settings::set_defaults();
}
register_activation_hook( __FILE__, 'thumbnest_activate' );

/**
 * Deactivation hook callback.
 */
function thumbnest_deactivate() {
	// Clean up runtime caches/transients.
	\ThumbNest\Cache::flush();
}
register_deactivation_hook( __FILE__, 'thumbnest_deactivate' );
