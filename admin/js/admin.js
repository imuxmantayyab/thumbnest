/**
 * ThumbNest Admin JavaScript
 * Handles Media Library frame selection, post type controls, and AJAX batch processing.
 */

/* global jQuery, wp, thumbnestVars */
(function ($) {
	'use strict';

	$(document).ready(function () {
		var mediaFrames = {};
		var isAssigning = false;
		var isRollingBack = false;

		/**
		 * 1. WordPress Media Library Frame Handler
		 */
		$(document).on('click', '.thumbnest-select-image-btn', function (e) {
			e.preventDefault();

			var $btn = $(this);
			var $uploader = $btn.closest('.thumbnest-media-uploader');
			var targetInputId = $uploader.data('target');
			var $hiddenInput = $('#' + targetInputId);
			var $preview = $uploader.find('.thumbnest-image-preview');
			var $removeBtn = $uploader.find('.thumbnest-remove-image-btn');

			// If frame exists, open it.
			if (mediaFrames[targetInputId]) {
				mediaFrames[targetInputId].open();
				return;
			}

			// Create new media frame.
			mediaFrames[targetInputId] = wp.media({
				title: thumbnestVars.i18n.selectImage,
				button: {
					text: thumbnestVars.i18n.useThisImage
				},
				multiple: false,
				library: {
					type: 'image'
				}
			});

			// On select callback.
			mediaFrames[targetInputId].on('select', function () {
				var attachment = mediaFrames[targetInputId].state().get('selection').first().toJSON();

				if (attachment && attachment.id) {
					$hiddenInput.val(attachment.id);

					var imgUrl = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;

					$preview.removeClass('no-image').addClass('has-image').html('<img src="' + imgUrl + '" alt="" />');
					$removeBtn.show();
					$btn.text(thumbnestVars.i18n.useThisImage || 'Replace Image');
				}
			});

			mediaFrames[targetInputId].open();
		});

		/**
		 * 2. Remove Image Handler
		 */
		$(document).on('click', '.thumbnest-remove-image-btn', function (e) {
			e.preventDefault();

			var $removeBtn = $(this);
			var $uploader = $removeBtn.closest('.thumbnest-media-uploader');
			var targetInputId = $uploader.data('target');
			var $hiddenInput = $('#' + targetInputId);
			var $preview = $uploader.find('.thumbnest-image-preview');
			var $selectBtn = $uploader.find('.thumbnest-select-image-btn');

			$hiddenInput.val('0');
			$preview.removeClass('has-image').addClass('no-image').html(
				'<div class="thumbnest-preview-placeholder"><span class="dashicons dashicons-format-image"></span></div>'
			);
			$removeBtn.hide();
			$selectBtn.text('Select Image');
		});

		/**
		 * 3. Fallback Source Change Handler
		 */
		$(document).on('change', '.thumbnest-source-select', function () {
			var $select = $(this);
			var val = $select.val();
			var $row = $select.closest('.thumbnest-pt-row');
			var $customBox = $row.find('.thumbnest-custom-image-box');

			if (val === 'custom') {
				$customBox.removeClass('is-hidden');
			} else {
				$customBox.addClass('is-hidden');
			}
		});

		/**
		 * 4. Engine Mode Radio Card Selection
		 */
		$(document).on('change', '.thumbnest-radio-card input', function () {
			$('.thumbnest-radio-card').removeClass('is-selected');
			$(this).closest('.thumbnest-radio-card').addClass('is-selected');
		});

		/**
		 * 5. Post Type Toggle Enable/Disable
		 */
		$(document).on('change', '.thumbnest-toggle-enabled', function () {
			var $toggle = $(this);
			var $row = $toggle.closest('.thumbnest-pt-row');
			var $controls = $row.find('.thumbnest-pt-controls');

			if ($toggle.is(':checked')) {
				$controls.css('opacity', '1').find('select, button').prop('disabled', false);
			} else {
				$controls.css('opacity', '0.5').find('select, button').prop('disabled', true);
			}
		});

		/**
		 * 6. Refresh Statistics
		 */
		function fetchStats() {
			var $btn = $('#thumbnest-refresh-stats');
			$btn.prop('disabled', true).find('.dashicons').addClass('dashicons-spin');

			$.ajax({
				url: thumbnestVars.ajaxUrl,
				type: 'POST',
				data: {
					action: 'thumbnest_get_stats',
					nonce: thumbnestVars.nonce
				},
				success: function (res) {
					if (res.success && res.data) {
						$('#stat-total-posts').text(res.data.total_posts.toLocaleString());
						$('#stat-with-thumb').text(res.data.with_thumbnail.toLocaleString());
						$('#stat-without-thumb').text(res.data.without_thumb.toLocaleString());
						$('#stat-plugin-assigned').text(res.data.plugin_assigned.toLocaleString());
					}
				},
				complete: function () {
					$btn.prop('disabled', false).find('.dashicons').removeClass('dashicons-spin');
				}
			});
		}

		$('#thumbnest-refresh-stats').on('click', function (e) {
			e.preventDefault();
			fetchStats();
		});

		/**
		 * 7. AJAX Batch Assignment Loop
		 */
		$('#thumbnest-start-assign-btn').on('click', function (e) {
			e.preventDefault();

			var postType = $('#thumbnest-bulk-post-type').val();
			isAssigning = true;

			$('#assign-progress-box').removeClass('is-hidden');
			$('#thumbnest-start-assign-btn').addClass('is-hidden');
			$('#thumbnest-cancel-assign-btn').removeClass('is-hidden');

			var totalAssigned = 0;

			function processAssignBatch() {
				if (!isAssigning) {
					$('#assign-progress-status').text('Operation cancelled.');
					$('#thumbnest-start-assign-btn').removeClass('is-hidden');
					$('#thumbnest-cancel-assign-btn').addClass('is-hidden');
					return;
				}

				$.ajax({
					url: thumbnestVars.ajaxUrl,
					type: 'POST',
					data: {
						action: 'thumbnest_bulk_assign_step',
						nonce: thumbnestVars.nonce,
						post_type: postType
					},
					success: function (res) {
						if (res.success && res.data) {
							totalAssigned += res.data.assigned;
							$('#assign-progress-status').text('Assigned fallbacks: ' + totalAssigned + ' | Remaining: ' + res.data.remaining);
							$('#assign-progress-bar').css('width', '50%');

							if (res.data.done) {
								$('#assign-progress-bar').css('width', '100%');
								$('#assign-progress-status').text('Completed! Total assigned: ' + totalAssigned);
								$('#thumbnest-start-assign-btn').removeClass('is-hidden');
								$('#thumbnest-cancel-assign-btn').addClass('is-hidden');
								isAssigning = false;
								fetchStats();
							} else {
								// Process next batch.
								setTimeout(processAssignBatch, 300);
							}
						} else {
							$('#assign-progress-status').text(res.data && res.data.message ? res.data.message : thumbnestVars.i18n.error);
							$('#thumbnest-start-assign-btn').removeClass('is-hidden');
							$('#thumbnest-cancel-assign-btn').addClass('is-hidden');
							isAssigning = false;
						}
					},
					error: function () {
						$('#assign-progress-status').text(thumbnestVars.i18n.error);
						$('#thumbnest-start-assign-btn').removeClass('is-hidden');
						$('#thumbnest-cancel-assign-btn').addClass('is-hidden');
						isAssigning = false;
					}
				});
			}

			processAssignBatch();
		});

		$('#thumbnest-cancel-assign-btn').on('click', function () {
			isAssigning = false;
		});

		/**
		 * 8. AJAX Batch Rollback Loop
		 */
		$('#thumbnest-start-rollback-btn').on('click', function (e) {
			e.preventDefault();

			if (!confirm(thumbnestVars.i18n.confirmRollback)) {
				return;
			}

			isRollingBack = true;

			$('#rollback-progress-box').removeClass('is-hidden');
			$('#thumbnest-start-rollback-btn').addClass('is-hidden');
			$('#thumbnest-cancel-rollback-btn').removeClass('is-hidden');

			var totalReverted = 0;

			function processRollbackBatch() {
				if (!isRollingBack) {
					$('#rollback-progress-status').text('Operation cancelled.');
					$('#thumbnest-start-rollback-btn').removeClass('is-hidden');
					$('#thumbnest-cancel-rollback-btn').addClass('is-hidden');
					return;
				}

				$.ajax({
					url: thumbnestVars.ajaxUrl,
					type: 'POST',
					data: {
						action: 'thumbnest_bulk_rollback_step',
						nonce: thumbnestVars.nonce
					},
					success: function (res) {
						if (res.success && res.data) {
							totalReverted += res.data.reverted;
							$('#rollback-progress-status').text('Reverted fallbacks: ' + totalReverted + ' | Remaining: ' + res.data.remaining);
							$('#rollback-progress-bar').css('width', '50%');

							if (res.data.done) {
								$('#rollback-progress-bar').css('width', '100%');
								$('#rollback-progress-status').text('Rollback completed! Total fallbacks removed: ' + totalReverted);
								$('#thumbnest-start-rollback-btn').removeClass('is-hidden');
								$('#thumbnest-cancel-rollback-btn').addClass('is-hidden');
								isRollingBack = false;
								fetchStats();
							} else {
								setTimeout(processRollbackBatch, 300);
							}
						} else {
							$('#rollback-progress-status').text(res.data && res.data.message ? res.data.message : thumbnestVars.i18n.error);
							$('#thumbnest-start-rollback-btn').removeClass('is-hidden');
							$('#thumbnest-cancel-rollback-btn').addClass('is-hidden');
							isRollingBack = false;
						}
					},
					error: function () {
						$('#rollback-progress-status').text(thumbnestVars.i18n.error);
						$('#thumbnest-start-rollback-btn').removeClass('is-hidden');
						$('#thumbnest-cancel-rollback-btn').addClass('is-hidden');
						isRollingBack = false;
					}
				});
			}

			processRollbackBatch();
		});

		$('#thumbnest-cancel-rollback-btn').on('click', function () {
			isRollingBack = false;
		});

		/**
		 * 9. Reset Settings AJAX Handler
		 */
		$('#thumbnest-reset-settings-btn').on('click', function (e) {
			e.preventDefault();

			if (!confirm(thumbnestVars.i18n.confirmReset)) {
				return;
			}

			var $btn = $(this);
			$btn.prop('disabled', true);

			$.ajax({
				url: thumbnestVars.ajaxUrl,
				type: 'POST',
				data: {
					action: 'thumbnest_reset_settings',
					nonce: thumbnestVars.nonce
				},
				success: function (res) {
					if (res.success) {
						window.location.reload();
					} else {
						alert(res.data && res.data.message ? res.data.message : 'Error resetting settings.');
						$btn.prop('disabled', false);
					}
				},
				error: function () {
					alert('Connection error.');
					$btn.prop('disabled', false);
				}
			});
		});
	});
})(jQuery);
