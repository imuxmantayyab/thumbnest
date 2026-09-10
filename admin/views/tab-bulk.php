<?php
/**
 * Bulk Assignment & Rollback tab view.
 *
 * @package ThumbNest
 */

defined( 'ABSPATH' ) || exit;

$eligible_post_types = \ThumbNest\Post_Types::get_eligible();
$stats               = \ThumbNest\Bulk_Processor::get_stats();
?>

<div class="thumbnest-bulk-wrap">
	<!-- Notice Card -->
	<div class="thumbnest-card thumbnest-card-accent">
		<div class="thumbnest-card-header">
			<div class="thumbnest-card-header-icon"><span class="dashicons dashicons-info-outline"></span></div>
			<div>
				<h2><?php esc_html_e( 'Batch Assignment & Rollback Engine', 'thumbnest' ); ?></h2>
				<p class="description">
					<?php esc_html_e( 'If your theme or third-party RSS/feed tools require real database records for _thumbnail_id, you can run the safe batch assigner below. This process runs in small asynchronous batches to prevent server timeouts.', 'thumbnest' ); ?>
				</p>
			</div>
		</div>
		<div class="thumbnest-card-body">
			<p>
				<strong><?php esc_html_e( 'Safety Guarantee:', 'thumbnest' ); ?></strong>
				<?php esc_html_e( 'ThumbNest never overwrites manually assigned featured images. Every batch-assigned image is marked with a special metadata flag so it can be completely reverted at any time without touching your real thumbnails.', 'thumbnest' ); ?>
			</p>
		</div>
	</div>

	<!-- Stats Overview Card -->
	<div class="thumbnest-card">
		<div class="thumbnest-card-header">
			<div class="thumbnest-card-header-icon"><span class="dashicons dashicons-chart-pie"></span></div>
			<div>
				<h2><?php esc_html_e( 'Current Site Thumbnail Status', 'thumbnest' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Live summary of published posts across eligible post types.', 'thumbnest' ); ?></p>
			</div>
			<div class="thumbnest-header-actions">
				<button type="button" class="button button-secondary" id="thumbnest-refresh-stats">
					<span class="dashicons dashicons-update"></span>
					<?php esc_html_e( 'Refresh Stats', 'thumbnest' ); ?>
				</button>
			</div>
		</div>

		<div class="thumbnest-card-body">
			<div class="thumbnest-stats-grid">
				<div class="thumbnest-stat-card">
					<span class="thumbnest-stat-label"><?php esc_html_e( 'Total Eligible Posts', 'thumbnest' ); ?></span>
					<span class="thumbnest-stat-number" id="stat-total-posts"><?php echo esc_html( number_format_i18n( $stats['total_posts'] ) ); ?></span>
				</div>
				<div class="thumbnest-stat-card stat-success">
					<span class="thumbnest-stat-label"><?php esc_html_e( 'With Featured Image', 'thumbnest' ); ?></span>
					<span class="thumbnest-stat-number" id="stat-with-thumb"><?php echo esc_html( number_format_i18n( $stats['with_thumbnail'] ) ); ?></span>
				</div>
				<div class="thumbnest-stat-card stat-warning">
					<span class="thumbnest-stat-label"><?php esc_html_e( 'Without Featured Image', 'thumbnest' ); ?></span>
					<span class="thumbnest-stat-number" id="stat-without-thumb"><?php echo esc_html( number_format_i18n( $stats['without_thumb'] ) ); ?></span>
				</div>
				<div class="thumbnest-stat-card stat-info">
					<span class="thumbnest-stat-label"><?php esc_html_e( 'Plugin-Assigned Fallbacks', 'thumbnest' ); ?></span>
					<span class="thumbnest-stat-number" id="stat-plugin-assigned"><?php echo esc_html( number_format_i18n( $stats['plugin_assigned'] ) ); ?></span>
				</div>
			</div>
		</div>
	</div>

	<div class="thumbnest-grid-two-col">
		<!-- Batch Assigner Tool -->
		<div class="thumbnest-card">
			<div class="thumbnest-card-header">
				<div class="thumbnest-card-header-icon"><span class="dashicons dashicons-images-alt2"></span></div>
				<div>
					<h2><?php esc_html_e( 'Assign Fallbacks in Batches', 'thumbnest' ); ?></h2>
					<p class="description"><?php esc_html_e( 'Safely assign configured fallbacks to posts currently missing a featured image.', 'thumbnest' ); ?></p>
				</div>
			</div>

			<div class="thumbnest-card-body">
				<div class="thumbnest-field-group">
					<label for="thumbnest-bulk-post-type"><strong><?php esc_html_e( 'Select Target Post Type:', 'thumbnest' ); ?></strong></label>
					<select id="thumbnest-bulk-post-type" class="widefat">
						<option value="all"><?php esc_html_e( 'All Enabled Post Types', 'thumbnest' ); ?></option>
						<?php foreach ( $eligible_post_types as $pt_slug => $pt_obj ) : ?>
							<option value="<?php echo esc_attr( $pt_slug ); ?>">
								<?php echo esc_html( $pt_obj->labels->singular_name ? $pt_obj->labels->singular_name : $pt_obj->label ); ?> (<?php echo esc_html( $pt_slug ); ?>)
							</option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="thumbnest-progress-box is-hidden" id="assign-progress-box">
					<div class="thumbnest-progress-bar-wrap">
						<div class="thumbnest-progress-bar" id="assign-progress-bar" style="width: 0%;"></div>
					</div>
					<div class="thumbnest-progress-status" id="assign-progress-status">
						<?php esc_html_e( 'Preparing batch...', 'thumbnest' ); ?>
					</div>
				</div>

				<div class="thumbnest-actions-row">
					<button type="button" class="button button-primary button-large" id="thumbnest-start-assign-btn">
						<span class="dashicons dashicons-controls-play"></span>
						<?php esc_html_e( 'Start Batch Assignment', 'thumbnest' ); ?>
					</button>
					<button type="button" class="button button-secondary button-large is-hidden" id="thumbnest-cancel-assign-btn">
						<span class="dashicons dashicons-controls-pause"></span>
						<?php esc_html_e( 'Cancel', 'thumbnest' ); ?>
					</button>
				</div>
			</div>
		</div>

		<!-- Rollback Tool -->
		<div class="thumbnest-card">
			<div class="thumbnest-card-header">
				<div class="thumbnest-card-header-icon"><span class="dashicons dashicons-undo"></span></div>
				<div>
					<h2><?php esc_html_e( 'Rollback Plugin Fallbacks', 'thumbnest' ); ?></h2>
					<p class="description"><?php esc_html_e( 'Safely remove only the fallback featured images that were stamped by ThumbNest.', 'thumbnest' ); ?></p>
				</div>
			</div>

			<div class="thumbnest-card-body">
				<p class="description">
					<?php esc_html_e( 'This operation will query all posts flagged with _thumbnest_is_fallback and safely delete the assigned thumbnail ID. It will never touch posts where an image was manually uploaded or assigned.', 'thumbnest' ); ?>
				</p>

				<div class="thumbnest-progress-box is-hidden" id="rollback-progress-box">
					<div class="thumbnest-progress-bar-wrap">
						<div class="thumbnest-progress-bar thumbnest-progress-bar-rollback" id="rollback-progress-bar" style="width: 0%;"></div>
					</div>
					<div class="thumbnest-progress-status" id="rollback-progress-status">
						<?php esc_html_e( 'Preparing rollback...', 'thumbnest' ); ?>
					</div>
				</div>

				<div class="thumbnest-actions-row">
					<button type="button" class="button button-secondary button-large thumbnest-btn-danger" id="thumbnest-start-rollback-btn">
						<span class="dashicons dashicons-trash"></span>
						<?php esc_html_e( 'Remove Plugin-Assigned Fallbacks', 'thumbnest' ); ?>
					</button>
					<button type="button" class="button button-secondary button-large is-hidden" id="thumbnest-cancel-rollback-btn">
						<span class="dashicons dashicons-controls-pause"></span>
						<?php esc_html_e( 'Cancel', 'thumbnest' ); ?>
					</button>
				</div>
			</div>
		</div>
	</div>
</div>
