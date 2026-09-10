=== ThumbNest – Smart Fallback Featured Images ===
Contributors: imuxmantayyab
Donate link: https://www.linkedin.com/in/imuxmantayyab/
Tags: featured image, post thumbnail, fallback image, default thumbnail, automatic featured image
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Intelligently provides lightweight, virtual fallback featured images across posts, pages, and custom post types without database bloat. Includes optional non-destructive bulk assignment.

== Description ==

**ThumbNest** is a modern, high-performance WordPress plugin that provides automatic fallback featured images for posts, pages, and custom post types when no manual featured image has been assigned.

Designed from the ground up for enterprise performance and simplicity, ThumbNest uses a **Virtual Fallback Engine** by default. It resolves fallback images at runtime through WordPress core thumbnail filters—meaning **zero database writes**, zero media duplication, and instant performance across catalogs with 1,000 to 100,000+ posts.

### Why Choose ThumbNest?

* **Smart Priority Resolution:** Always respects manually assigned featured images. If a post has a genuine featured image, ThumbNest leaves it untouched.
* **Global & Per-Post-Type Control:** Define a universal global fallback image or specify tailored fallback images for Posts, Pages, News, Products, Portfolio, or any registered Custom Post Type.
* **Dynamic Post Type Discovery:** Automatically detects any public post type supporting featured images (`post`, `page`, WooCommerce `product`, custom portfolio/news plugins, etc.).
* **Virtual Fallback (Zero Bloat):** Displays fallback thumbnails seamlessly in themes and queries without modifying post records or creating duplicate attachments.
* **Optional Batch Assignment Engine:** Need physical `_thumbnail_id` assignments for legacy themes or external feed tools? Use our paginated AJAX batch processor with built-in rollback protection.
* **100% Reversible:** Plugin-assigned fallbacks are uniquely stamped with metadata. Reverting leaves user-uploaded featured images untouched.
* **Page Builder & Block Ready:** Fully compatible with Gutenberg (Block Editor), Classic Editor, Elementor widgets, and WooCommerce.
* **Accessible & Standard Compliant:** Automatically inherits media library Alt text or generates clean contextual alt attributes.
* **Developer Friendly:** Clean object-oriented architecture, PSR compliant, strictly sanitized, and extensible via rich hooks and filters.

== Features ==

* **Global Fallback Image:** Set one universal image for your entire site.
* **Custom Post Type Overrides:** Assign distinct fallbacks for different post types.
* **Selective Enable/Disable:** Turn fallback support on or off per post type with a single click.
* **Real Image Priority:** Explicit user-assigned thumbnails are never replaced.
* **Native Media Library Modal:** Intuitive image selection, live preview, replacement, and removal.
* **Safe Batch Processing:** Paginated bulk assigner handles large sites (10k+ posts) without timeouts or memory exhaustion.
* **Safe One-Click Revert:** Remove plugin-generated fallbacks without affecting real images.
* **REST API & Gutenberg Compatible:** Cleanly exposes fallbacks in the WordPress REST API without corrupting schemas.
* **Multi-Size Support:** Honors standard and custom image sizes (`thumbnail`, `medium`, `large`, custom sizes).
* **Multisite Compatible:** Per-site configuration support out of the box.

== Installation ==

1. Upload the `thumbnest` folder to the `/wp-content/plugins/` directory, or install the ZIP file via **Plugins > Add New > Upload Plugin**.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Navigate to **Settings > ThumbNest** in your WordPress administration dashboard.
4. Select or upload your **Global Fallback Image**.
5. Customize individual post type settings as desired (e.g., set custom fallbacks for Pages or News).
6. Click **Save Settings**.

== Frequently Asked Questions ==

= Will ThumbNest replace my existing featured images? =
No. ThumbNest strictly adheres to an image priority rule: if a post has a manually assigned featured image, that image is always used. ThumbNest only intervenes when a post has no featured image.

= Does ThumbNest bloat the database by copying images? =
No. By default, ThumbNest uses a Virtual Fallback mechanism that injects the fallback image dynamically on display. Even if you have 10,000 posts without featured images, they all reference a single Media Library attachment in memory.

= What happens if I assign a real featured image to a post later? =
The new real featured image will immediately take precedence on the frontend. No cache clearing or re-syncing is required.

= Can I use different fallback images for Posts and Custom Post Types? =
Yes. You can select "Use Global Image", "Use Custom Image", or "Disabled" independently for each eligible post type.

= Is ThumbNest compatible with Elementor and WooCommerce? =
Yes. ThumbNest filters standard WordPress thumbnail APIs (`has_post_thumbnail()`, `get_post_thumbnail_id()`, `get_the_post_thumbnail()`, `wp_get_attachment_image_src()`) which Elementor, WooCommerce, and modern themes rely upon.

= How does the Batch Assignment feature work? =
If your theme or feed plugin requires a physical `_thumbnail_id` in the database, the optional Batch Assigner processes posts in small AJAX batches (e.g., 50 posts per step). Each assignment is stamped with `_thumbnest_is_fallback` metadata so it can be safely rolled back at any time.

== Screenshots ==

1. Global & Per-Post-Type Settings configuration panel.
2. Native WordPress Media Library selector with instant live preview.
3. Diagnostic overview and post statistics dashboard.
4. Batch Processing tool with real-time progress bar.

== Changelog ==

= 1.0.0 =
* Initial official release.
* Virtual Fallback engine with zero-database overhead.
* Dynamic post type detection and per-post-type configuration.
* Native Media Library integration for global and post-type fallbacks.
* Non-destructive batch assignment and rollback tools.
* REST API, Gutenberg, Elementor, and WooCommerce compatibility layers.
* Full internationalization and strict security hardening.

== Upgrade Notice ==

= 1.0.0 =
Initial release of ThumbNest.
