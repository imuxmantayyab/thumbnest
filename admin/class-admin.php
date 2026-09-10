<?php
/**
 * Admin interface manager for ThumbNest.
 *
 * @package ThumbNest
 */

namespace ThumbNest\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Class Admin
 */
class Admin {

	/**
	 * Settings page hook suffix.
	 *
	 * @var string
	 */
	private static $page_hook = '';

	/**
	 * Initialize admin hooks and menu.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		add_filter( 'plugin_action_links_' . THUMBNEST_PLUGIN_BASENAME, array( __CLASS__, 'add_action_links' ) );
		add_action( 'wp_ajax_thumbnest_reset_settings', array( __CLASS__, 'ajax_reset_settings' ) );
	}

	/**
	 * Register settings submenu under Settings menu.
	 */
	public static function register_menu() {
		self::$page_hook = add_options_page(
			esc_html__( 'ThumbNest Settings', 'thumbnest' ),
			esc_html__( 'ThumbNest', 'thumbnest' ),
			'manage_options',
			'thumbnest',
			array( __CLASS__, 'render_settings_page' )
		);
	}

	/**
	 * Register settings in the WordPress Settings API.
	 */
	public static function register_settings() {
		register_setting(
			'thumbnest_settings_group',
			\ThumbNest\Settings::OPTION_NAME,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( '\ThumbNest\Settings', 'sanitize' ),
				'default'           => \ThumbNest\Settings::get_defaults(),
			)
		);
	}

	/**
	 * Enqueue admin scripts and stylesheets on the ThumbNest settings page.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public static function enqueue_assets( $hook ) {
		if ( $hook !== self::$page_hook ) {
			return;
		}

		// Enqueue WordPress Media Library modal assets.
		wp_enqueue_media();

		// Enqueue custom admin styles.
		wp_enqueue_style(
			'thumbnest-admin-css',
			THUMBNEST_PLUGIN_URL . 'admin/css/admin.css',
			array(),
			THUMBNEST_VERSION
		);

		// Enqueue custom admin script.
		wp_enqueue_script(
			'thumbnest-admin-js',
			THUMBNEST_PLUGIN_URL . 'admin/js/admin.js',
			array( 'jquery' ),
			THUMBNEST_VERSION,
			true
		);

		// Localize script with nonce, strings, and ajax URL.
		wp_localize_script(
			'thumbnest-admin-js',
			'thumbnestVars',
			array(
				'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
				'nonce'     => wp_create_nonce( 'thumbnest_admin_nonce' ),
				'i18n'      => array(
					'selectImage'        => esc_html__( 'Select Fallback Image', 'thumbnest' ),
					'useThisImage'       => esc_html__( 'Use this image', 'thumbnest' ),
					'confirmReset'       => esc_html__( 'Are you sure you want to reset all ThumbNest settings to their defaults? This action cannot be undone.', 'thumbnest' ),
					'processing'         => esc_html__( 'Processing...', 'thumbnest' ),
					'complete'           => esc_html__( 'Batch completed successfully!', 'thumbnest' ),
					'error'              => esc_html__( 'An error occurred during processing.', 'thumbnest' ),
					'confirmRollback'    => esc_html__( 'Are you sure you want to remove all plugin-assigned fallback images? Manually assigned featured images will NOT be affected.', 'thumbnest' ),
				),
			)
		);
	}

	/**
	 * Add direct "Settings" link on Plugins management screen.
	 *
	 * @param array $links Array of plugin action links.
	 * @return array
	 */
	public static function add_action_links( $links ) {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'options-general.php?page=thumbnest' ) ),
			esc_html__( 'Settings', 'thumbnest' )
		);
		array_unshift( $links, $settings_link );
		return $links;
	}

	/**
	 * AJAX endpoint to reset settings to defaults.
	 */
	public static function ajax_reset_settings() {
		check_ajax_referer( 'thumbnest_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Permission denied.', 'thumbnest' ) ), 403 );
		}

		\ThumbNest\Settings::reset_to_defaults();

		wp_send_json_success( array( 'message' => esc_html__( 'Settings have been reset to defaults.', 'thumbnest' ) ) );
	}

	/**
	 * Render the main plugin settings page.
	 */
	public static function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Ensure settings exist.
		$settings = \ThumbNest\Settings::get_all();
		$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'general';

		include THUMBNEST_PLUGIN_DIR . 'admin/views/settings-page.php';
	}
}
