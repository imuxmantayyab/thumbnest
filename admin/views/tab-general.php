<?php
/**
 * General & Post Types Settings tab view.
 *
 * @package ThumbNest
 */

defined( 'ABSPATH' ) || exit;

$settings            = \ThumbNest\Settings::get_all();
$global_image_id     = (int) $settings['global_image_id'];
$global_image_url    = $global_image_id ? wp_get_attachment_image_url( $global_image_id, 'medium' ) : '';
$eligible_post_types = \ThumbNest\Post_Types::get_eligible();
?>

<form method="post" action="options.php" class="thumbnest-form">
	<?php
	settings_fields( 'thumbnest_settings_group' );
	?>

	<!-- 1. Global Fallback Image Section -->
	<div class="thumbnest-card">
		<div class="thumbnest-card-header">
			<div class="thumbnest-card-header-icon"><span class="dashicons dashicons-admin-site-alt3"></span></div>
			<div>
				<h2><?php esc_html_e( 'Global Fallback Image', 'thumbnest' ); ?></h2>
				<p class="description">
					<?php esc_html_e( 'This universal fallback image will automatically be displayed for any post type set to "Use Global Image" when a post does not have a real featured image.', 'thumbnest' ); ?>
				</p>
			</div>
		</div>

		<div class="thumbnest-card-body">
			<div class="thumbnest-media-uploader" data-target="global-image-id">
				<div class="thumbnest-image-preview <?php echo $global_image_url ? 'has-image' : 'no-image'; ?>" id="global-image-preview">
					<?php if ( $global_image_url ) : ?>
						<img src="<?php echo esc_url( $global_image_url ); ?>" alt="<?php esc_attr_e( 'Global Fallback Preview', 'thumbnest' ); ?>" />
					<?php else : ?>
						<div class="thumbnest-preview-placeholder">
							<span class="dashicons dashicons-format-image"></span>
							<span><?php esc_html_e( 'No Global Image Selected', 'thumbnest' ); ?></span>
						</div>
					<?php endif; ?>
				</div>

				<div class="thumbnest-media-actions">
					<input type="hidden" name="thumbnest_settings[global_image_id]" id="global-image-id" value="<?php echo esc_attr( $global_image_id ); ?>" />
					<button type="button" class="button button-primary thumbnest-select-image-btn">
						<span class="dashicons dashicons-upload"></span>
						<?php echo $global_image_id ? esc_html__( 'Replace Image', 'thumbnest' ) : esc_html__( 'Select from Media Library', 'thumbnest' ); ?>
					</button>
					<button type="button" class="button button-secondary thumbnest-remove-image-btn" <?php echo ! $global_image_id ? 'style="display:none;"' : ''; ?>>
						<span class="dashicons dashicons-trash"></span>
						<?php esc_html_e( 'Remove Image', 'thumbnest' ); ?>
					</button>
				</div>
			</div>
		</div>
	</div>

	<!-- 2. Fallback Engine Mode Section -->
	<div class="thumbnest-card">
		<div class="thumbnest-card-header">
			<div class="thumbnest-card-header-icon"><span class="dashicons dashicons-performance"></span></div>
			<div>
				<h2><?php esc_html_e( 'Fallback Engine Mode', 'thumbnest' ); ?></h2>
				<p class="description">
					<?php esc_html_e( 'Choose how ThumbNest delivers fallback featured images.', 'thumbnest' ); ?>
				</p>
			</div>
		</div>

		<div class="thumbnest-card-body">
			<div class="thumbnest-radio-group">
				<label class="thumbnest-radio-card <?php echo ( 'virtual' === $settings['mode'] ) ? 'is-selected' : ''; ?>">
					<input type="radio" name="thumbnest_settings[mode]" value="virtual" <?php checked( $settings['mode'], 'virtual' ); ?> />
					<div class="thumbnest-radio-content">
						<strong><?php esc_html_e( 'Virtual Fallback — (Recommended)', 'thumbnest' ); ?></strong>
						<p><?php esc_html_e( 'Injects the fallback image dynamically via WordPress thumbnail filters at runtime. 100% database-free, zero attachment duplication, and lightning fast.', 'thumbnest' ); ?></p>
					</div>
				</label>

				<label class="thumbnest-radio-card <?php echo ( 'physical' === $settings['mode'] ) ? 'is-selected' : ''; ?>">
					<input type="radio" name="thumbnest_settings[mode]" value="physical" <?php checked( $settings['mode'], 'physical' ); ?> />
					<div class="thumbnest-radio-content">
						<strong><?php esc_html_e( 'Physical Assignment Mode', 'thumbnest' ); ?></strong>
						<p><?php esc_html_e( 'Allows you to use the Batch Assigner (under the "Batch Assigner" tab) to stamp fallback IDs into postmeta for legacy themes or external feed services requiring direct database IDs.', 'thumbnest' ); ?></p>
					</div>
				</label>
			</div>
		</div>
	</div>

	<!-- 3. Per-Post-Type Settings Matrix -->
	<div class="thumbnest-card">
		<div class="thumbnest-card-header">
			<div class="thumbnest-card-header-icon"><span class="dashicons dashicons-admin-post"></span></div>
			<div>
				<h2><?php esc_html_e( 'Post Type Fallback Configuration', 'thumbnest' ); ?></h2>
				<p class="description">
					<?php esc_html_e( 'Configure fallback rules independently for each public post type registered on your website.', 'thumbnest' ); ?>
				</p>
			</div>
		</div>

		<div class="thumbnest-card-body">
			<?php if ( empty( $eligible_post_types ) ) : ?>
				<p><?php esc_html_e( 'No eligible public post types supporting featured images were found.', 'thumbnest' ); ?></p>
			<?php else : ?>
				<div class="thumbnest-post-types-list">
					<?php foreach ( $eligible_post_types as $pt_slug => $pt_obj ) : ?>
						<?php
						$pt_config = \ThumbNest\Settings::get_post_type_settings( $pt_slug );
						$pt_img_id = (int) $pt_config['image_id'];
						$pt_img_url = $pt_img_id ? wp_get_attachment_image_url( $pt_img_id, 'medium' ) : '';
						?>
						<div class="thumbnest-pt-row" data-post-type="<?php echo esc_attr( $pt_slug ); ?>">
							<div class="thumbnest-pt-info">
								<h3 class="thumbnest-pt-name">
									<?php echo esc_html( $pt_obj->labels->singular_name ? $pt_obj->labels->singular_name : $pt_obj->label ); ?>
									<span class="thumbnest-pt-slug">(<code><?php echo esc_html( $pt_slug ); ?></code>)</span>
								</h3>
								<label class="thumbnest-switch">
									<input type="checkbox" name="thumbnest_settings[post_types][<?php echo esc_attr( $pt_slug ); ?>][enabled]" value="1" <?php checked( $pt_config['enabled'], 1 ); ?> class="thumbnest-toggle-enabled" />
									<span class="thumbnest-slider"></span>
									<span class="thumbnest-switch-label"><?php esc_html_e( 'Enable Fallback', 'thumbnest' ); ?></span>
								</label>
							</div>

							<div class="thumbnest-pt-controls">
								<div class="thumbnest-source-select-wrap">
									<label for="source-<?php echo esc_attr( $pt_slug ); ?>"><?php esc_html_e( 'Image Source:', 'thumbnest' ); ?></label>
									<select name="thumbnest_settings[post_types][<?php echo esc_attr( $pt_slug ); ?>][source]" id="source-<?php echo esc_attr( $pt_slug ); ?>" class="thumbnest-source-select">
										<option value="global" <?php selected( $pt_config['source'], 'global' ); ?>><?php esc_html_e( 'Use Global Image', 'thumbnest' ); ?></option>
										<option value="custom" <?php selected( $pt_config['source'], 'custom' ); ?>><?php esc_html_e( 'Use Custom Post-Type Image', 'thumbnest' ); ?></option>
										<option value="none" <?php selected( $pt_config['source'], 'none' ); ?>><?php esc_html_e( 'No Fallback (Disabled)', 'thumbnest' ); ?></option>
									</select>
								</div>

								<div class="thumbnest-custom-image-box <?php echo ( 'custom' === $pt_config['source'] ) ? '' : 'is-hidden'; ?>">
									<div class="thumbnest-media-uploader" data-target="custom-image-<?php echo esc_attr( $pt_slug ); ?>">
										<div class="thumbnest-image-preview thumbnest-preview-small <?php echo $pt_img_url ? 'has-image' : 'no-image'; ?>" id="preview-<?php echo esc_attr( $pt_slug ); ?>">
											<?php if ( $pt_img_url ) : ?>
												<img src="<?php echo esc_url( $pt_img_url ); ?>" alt="" />
											<?php else : ?>
												<div class="thumbnest-preview-placeholder">
													<span class="dashicons dashicons-format-image"></span>
												</div>
											<?php endif; ?>
										</div>
										<div class="thumbnest-media-actions">
											<input type="hidden" name="thumbnest_settings[post_types][<?php echo esc_attr( $pt_slug ); ?>][image_id]" id="custom-image-<?php echo esc_attr( $pt_slug ); ?>" value="<?php echo esc_attr( $pt_img_id ); ?>" />
											<button type="button" class="button button-secondary thumbnest-select-image-btn">
												<?php echo $pt_img_id ? esc_html__( 'Replace', 'thumbnest' ) : esc_html__( 'Select Image', 'thumbnest' ); ?>
											</button>
											<button type="button" class="button button-link-delete thumbnest-remove-image-btn" <?php echo ! $pt_img_id ? 'style="display:none;"' : ''; ?>>
												<?php esc_html_e( 'Remove', 'thumbnest' ); ?>
											</button>
										</div>
									</div>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<!-- 4. Integrations & Compatibility Options -->
	<div class="thumbnest-card">
		<div class="thumbnest-card-header">
			<div class="thumbnest-card-header-icon"><span class="dashicons dashicons-admin-plugins"></span></div>
			<div>
				<h2><?php esc_html_e( 'Integrations & Ecosystem Compatibility', 'thumbnest' ); ?></h2>
				<p class="description">
					<?php esc_html_e( 'Seamlessly integrate ThumbNest with modern page builders and ecosystem endpoints.', 'thumbnest' ); ?>
				</p>
			</div>
		</div>

		<div class="thumbnest-card-body">
			<fieldset class="thumbnest-checkbox-group">
				<label>
					<input type="checkbox" name="thumbnest_settings[enable_rest_api]" value="1" <?php checked( $settings['enable_rest_api'], 1 ); ?> />
					<strong><?php esc_html_e( 'REST API & Gutenberg Support', 'thumbnest' ); ?></strong>
					<span class="description"><?php esc_html_e( 'Expose fallback image fields in the WordPress REST API for decoupled frontends and block templates.', 'thumbnest' ); ?></span>
				</label>
				<label>
					<input type="checkbox" name="thumbnest_settings[enable_elementor]" value="1" <?php checked( $settings['enable_elementor'], 1 ); ?> />
					<strong><?php esc_html_e( 'Elementor Dynamic Widgets', 'thumbnest' ); ?></strong>
					<span class="description"><?php esc_html_e( 'Ensure Elementor post grids, loop builders, and archive templates render fallback images accurately.', 'thumbnest' ); ?></span>
				</label>
				<label>
					<input type="checkbox" name="thumbnest_settings[enable_woocommerce]" value="1" <?php checked( $settings['enable_woocommerce'], 1 ); ?> />
					<strong><?php esc_html_e( 'WooCommerce Compatibility', 'thumbnest' ); ?></strong>
					<span class="description"><?php esc_html_e( 'Allow WooCommerce product catalog items without images to use the configured product fallback.', 'thumbnest' ); ?></span>
				</label>
			</fieldset>
		</div>
	</div>

	<div class="thumbnest-submit-wrap">
		<?php submit_button( esc_html__( 'Save Changes', 'thumbnest' ), 'primary large', 'submit', false ); ?>
	</div>
</form>
