<?php
/**
 * Status & Diagnostics tab view.
 *
 * @package ThumbNest
 */

defined( 'ABSPATH' ) || exit;

$settings            = \ThumbNest\Settings::get_all();
$global_id           = (int) $settings['global_image_id'];
$eligible_post_types = \ThumbNest\Post_Types::get_eligible();

global $wp_version;
?>

<div class="thumbnest-status-wrap">
	<!-- Summary Card -->
	<div class="thumbnest-card">
		<div class="thumbnest-card-header">
			<div class="thumbnest-card-header-icon"><span class="dashicons dashicons-visibility"></span></div>
			<div>
				<h2><?php esc_html_e( 'Active Fallback Rules Summary', 'thumbnest' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Current configuration overview and post type assignments.', 'thumbnest' ); ?></p>
			</div>
		</div>

		<div class="thumbnest-card-body">
			<table class="widefat striped thumbnest-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Setting Item', 'thumbnest' ); ?></th>
						<th><?php esc_html_e( 'Current Status / Value', 'thumbnest' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><strong><?php esc_html_e( 'Engine Mode', 'thumbnest' ); ?></strong></td>
						<td>
							<?php if ( 'virtual' === $settings['mode'] ) : ?>
								<span class="thumbnest-badge thumbnest-badge-success"><?php esc_html_e( 'Virtual Fallback (Zero Bloat)', 'thumbnest' ); ?></span>
							<?php else : ?>
								<span class="thumbnest-badge thumbnest-badge-warning"><?php esc_html_e( 'Physical Assignment Mode', 'thumbnest' ); ?></span>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<td><strong><?php esc_html_e( 'Global Fallback Image', 'thumbnest' ); ?></strong></td>
						<td>
							<?php if ( $global_id && wp_attachment_is_image( $global_id ) ) : ?>
								<span class="thumbnest-badge thumbnest-badge-success">
									<?php esc_html_e( 'Configured', 'thumbnest' ); ?> (ID: <?php echo esc_html( $global_id ); ?>)
								</span>
							<?php else : ?>
								<span class="thumbnest-badge thumbnest-badge-danger"><?php esc_html_e( 'Not Set', 'thumbnest' ); ?></span>
							<?php endif; ?>
						</td>
					</tr>
					<?php foreach ( $eligible_post_types as $pt_slug => $pt_obj ) : ?>
						<?php
						$pt_config = \ThumbNest\Settings::get_post_type_settings( $pt_slug );
						$is_en     = ! empty( $pt_config['enabled'] );
						$source    = $pt_config['source'];
						$custom_id = (int) $pt_config['image_id'];
						?>
						<tr>
							<td>
								<strong><?php echo esc_html( $pt_obj->labels->singular_name ? $pt_obj->labels->singular_name : $pt_obj->label ); ?></strong>
								<code>(<?php echo esc_html( $pt_slug ); ?>)</code>
							</td>
							<td>
								<?php if ( ! $is_en || 'none' === $source ) : ?>
									<span class="thumbnest-badge thumbnest-badge-neutral"><?php esc_html_e( 'Disabled', 'thumbnest' ); ?></span>
								<?php elseif ( 'custom' === $source ) : ?>
									<span class="thumbnest-badge thumbnest-badge-info">
										<?php esc_html_e( 'Custom Image', 'thumbnest' ); ?>
										<?php echo $custom_id ? '(ID: ' . esc_html( $custom_id ) . ')' : esc_html__( '(No image chosen - falls back to global)', 'thumbnest' ); ?>
									</span>
								<?php else : ?>
									<span class="thumbnest-badge thumbnest-badge-success"><?php esc_html_e( 'Using Global Fallback', 'thumbnest' ); ?></span>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>

	<!-- System Environment Diagnostics -->
	<div class="thumbnest-card">
		<div class="thumbnest-card-header">
			<div class="thumbnest-card-header-icon"><span class="dashicons dashicons-admin-tools"></span></div>
			<div>
				<h2><?php esc_html_e( 'System & Environment Diagnostics', 'thumbnest' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Diagnostic details for troubleshooting and support.', 'thumbnest' ); ?></p>
			</div>
		</div>

		<div class="thumbnest-card-body">
			<table class="widefat striped thumbnest-table">
				<tbody>
					<tr>
						<td><strong><?php esc_html_e( 'ThumbNest Version', 'thumbnest' ); ?></strong></td>
						<td><?php echo esc_html( THUMBNEST_VERSION ); ?></td>
					</tr>
					<tr>
						<td><strong><?php esc_html_e( 'WordPress Version', 'thumbnest' ); ?></strong></td>
						<td><?php echo esc_html( $wp_version ); ?></td>
					</tr>
					<tr>
						<td><strong><?php esc_html_e( 'PHP Version', 'thumbnest' ); ?></strong></td>
						<td><?php echo esc_html( PHP_VERSION ); ?></td>
					</tr>
					<tr>
						<td><strong><?php esc_html_e( 'Active Theme', 'thumbnest' ); ?></strong></td>
						<td><?php echo esc_html( wp_get_theme()->get( 'Name' ) ); ?> (v<?php echo esc_html( wp_get_theme()->get( 'Version' ) ); ?>)</td>
					</tr>
					<tr>
						<td><strong><?php esc_html_e( 'Elementor Page Builder', 'thumbnest' ); ?></strong></td>
						<td>
							<?php if ( defined( 'ELEMENTOR_VERSION' ) ) : ?>
								<span class="thumbnest-badge thumbnest-badge-success"><?php esc_html_e( 'Active', 'thumbnest' ); ?> (v<?php echo esc_html( ELEMENTOR_VERSION ); ?>)</span>
							<?php else : ?>
								<span class="thumbnest-badge thumbnest-badge-neutral"><?php esc_html_e( 'Not Active', 'thumbnest' ); ?></span>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<td><strong><?php esc_html_e( 'WooCommerce', 'thumbnest' ); ?></strong></td>
						<td>
							<?php if ( class_exists( 'WooCommerce' ) ) : ?>
								<span class="thumbnest-badge thumbnest-badge-success"><?php esc_html_e( 'Active', 'thumbnest' ); ?> (v<?php echo esc_html( WC()->version ); ?>)</span>
							<?php else : ?>
								<span class="thumbnest-badge thumbnest-badge-neutral"><?php esc_html_e( 'Not Active', 'thumbnest' ); ?></span>
							<?php endif; ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>

	<!-- Danger Zone: Reset Settings -->
	<div class="thumbnest-card thumbnest-card-danger">
		<div class="thumbnest-card-header">
			<div class="thumbnest-card-header-icon"><span class="dashicons dashicons-warning"></span></div>
			<div>
				<h2><?php esc_html_e( 'Reset Plugin Settings', 'thumbnest' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Restore all ThumbNest options back to clean defaults.', 'thumbnest' ); ?></p>
			</div>
		</div>

		<div class="thumbnest-card-body">
			<p>
				<?php esc_html_e( 'Resetting options will clear the global fallback image selection and all per-post-type configurations. Your uploaded images in the WordPress Media Library will NOT be deleted.', 'thumbnest' ); ?>
			</p>
			<button type="button" class="button button-secondary thumbnest-btn-danger" id="thumbnest-reset-settings-btn">
				<span class="dashicons dashicons-image-rotate"></span>
				<?php esc_html_e( 'Reset All Settings to Defaults', 'thumbnest' ); ?>
			</button>
		</div>
	</div>
</div>
