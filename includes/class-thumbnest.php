<?php
/**
 * Main Plugin Class for ThumbNest.
 *
 * @package ThumbNest
 */

namespace ThumbNest;

defined( 'ABSPATH' ) || exit;

/**
 * Class Plugin
 */
final class Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return Plugin
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->load_textdomain();
		$this->init_components();
	}

	/**
	 * Load translation files.
	 */
	private function load_textdomain() {
		load_plugin_textdomain(
			'thumbnest',
			false,
			dirname( THUMBNEST_PLUGIN_BASENAME ) . '/languages'
		);
	}

	/**
	 * Initialize plugin components and integrations.
	 */
	private function init_components() {
		// Initialize Virtual Fallback Engine.
		Fallback_Engine::init();

		// Initialize Bulk Processor.
		Bulk_Processor::init();

		// Initialize REST API.
		Rest_API::init();

		// Initialize Integrations.
		Integrations\Elementor::init();
		Integrations\WooCommerce::init();

		// Initialize Admin Interface if in admin context.
		if ( is_admin() ) {
			Admin\Admin::init();
		}

		/**
		 * Action triggered once ThumbNest is fully initialized.
		 *
		 * @param Plugin $this Main plugin instance.
		 */
		do_action( 'thumbnest_loaded', $this );
	}
}
