<?php
/**
 * Main Settings Page wrapper template.
 *
 * @package ThumbNest
 */

defined( 'ABSPATH' ) || exit;

$tabs = array(
	'general' => array(
		'label' => esc_html__( 'General & Post Types', 'thumbnest' ),
		'icon'  => 'dashicons-admin-settings',
	),
	'bulk'    => array(
		'label' => esc_html__( 'Batch Assigner & Rollback', 'thumbnest' ),
		'icon'  => 'dashicons-image-rotate',
	),
	'status'  => array(
		'label' => esc_html__( 'Status & Diagnostics', 'thumbnest' ),
		'icon'  => 'dashicons-dashboard',
	),
);
?>
<div class="wrap thumbnest-admin-wrap">
	<header class="thumbnest-header">
		<div class="thumbnest-header-content">
			<h1 class="thumbnest-title">
				<span class="dashicons dashicons-format-image thumbnest-title-icon"></span>
				<?php esc_html_e( 'ThumbNest', 'thumbnest' ); ?>
				<span class="thumbnest-version">v<?php echo esc_html( THUMBNEST_VERSION ); ?></span>
			</h1>
			<p class="thumbnest-subtitle">
				<?php esc_html_e( 'Smart Fallback Featured Images without database bloat.', 'thumbnest' ); ?>
			</p>
		</div>
		<div class="thumbnest-header-author">
			<a href="<?php echo esc_url( 'https://www.linkedin.com/in/imuxmantayyab/' ); ?>" target="_blank" rel="noopener noreferrer" class="thumbnest-author-badge">
				<?php esc_html_e( 'Developed by Usman Tayyab', 'thumbnest' ); ?> &rarr;
			</a>
		</div>
	</header>

	<nav class="nav-tab-wrapper thumbnest-tabs">
		<?php foreach ( $tabs as $tab_id => $tab_meta ) : ?>
			<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'thumbnest', 'tab' => $tab_id ), admin_url( 'options-general.php' ) ) ); ?>" 
			   class="nav-tab <?php echo ( $active_tab === $tab_id ) ? 'nav-tab-active' : ''; ?>">
				<span class="dashicons <?php echo esc_attr( $tab_meta['icon'] ); ?>"></span>
				<?php echo esc_html( $tab_meta['label'] ); ?>
			</a>
		<?php endforeach; ?>
	</nav>

	<div class="thumbnest-tab-content">
		<?php
		switch ( $active_tab ) {
			case 'bulk':
				include THUMBNEST_PLUGIN_DIR . 'admin/views/tab-bulk.php';
				break;
			case 'status':
				include THUMBNEST_PLUGIN_DIR . 'admin/views/tab-status.php';
				break;
			case 'general':
			default:
				include THUMBNEST_PLUGIN_DIR . 'admin/views/tab-general.php';
				break;
		}
		?>
	</div>
</div>
