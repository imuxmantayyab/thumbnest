# ThumbNest – User Guide & Documentation

**ThumbNest** is an intelligent, high-performance WordPress plugin that provides automatic fallback featured images for posts, pages, and custom post types when no manual featured image has been assigned.

---

## Table of Contents

1. [Quick Start Guide](#quick-start-guide)
2. [How ThumbNest Works](#how-thumbnest-works)
   - [Virtual Fallback Mode (Recommended)](#virtual-fallback-mode-recommended)
   - [Physical Assignment Mode](#physical-assignment-mode)
   - [Image Priority Hierarchy](#image-priority-hierarchy)
3. [Configuration Guide](#configuration-guide)
   - [Global Fallback Image](#global-fallback-image)
   - [Per-Post-Type Settings](#per-post-type-settings)
   - [Integrations & Compatibility](#integrations--compatibility)
4. [Batch Assigner & Rollback Tool](#batch-assigner--rollback-tool)
   - [Running Batch Assignment](#running-batch-assignment)
   - [Rolling Back Plugin Fallbacks](#rolling-back-plugin-fallbacks)
5. [Page Builders & Theme Compatibility](#page-builders--theme-compatibility)
   - [Elementor](#elementor)
   - [Gutenberg & Classic Editor](#gutenberg--classic-editor)
   - [WooCommerce](#woocommerce)
6. [Developer Reference](#developer-reference)
   - [Template Tags](#template-tags)
   - [Action & Filter Hooks](#action--filter-hooks)
   - [REST API](#rest-api)
7. [Frequently Asked Questions](#frequently-asked-questions)
8. [Troubleshooting & Support](#troubleshooting--support)

---

## Quick Start Guide

Setting up ThumbNest takes less than 2 minutes:

1. **Install and Activate**
   - Go to **Plugins > Add New > Upload Plugin** in your WordPress dashboard.
   - Upload `thumbnest.zip` and click **Activate**.
2. **Set Your Global Fallback Image**
   - Navigate to **Settings > ThumbNest**.
   - Under **Global Fallback Image**, click **Select from Media Library**.
   - Choose or upload an image and click **Use this image**.
3. **Save Changes**
   - Click **Save Changes** at the bottom of the page.

That's it! Any post, page, or custom post type that doesn't have a featured image will now automatically display your configured fallback image.

---

## How ThumbNest Works

### Virtual Fallback Mode (Recommended)

By default, ThumbNest uses a **Virtual Fallback Engine**. 

* **Zero Database Writes:** ThumbNest intercepts WordPress thumbnail display functions (`has_post_thumbnail()`, `get_the_post_thumbnail()`, etc.) dynamically in memory.
* **No Database Bloat:** Even if your website has 50,000 posts with no featured image, ThumbNest does not duplicate files or write 50,000 rows into the database. All posts reference the single Media Library image at runtime.
* **Instant Updates:** If you change your fallback image in the settings, every post on your site reflects the new image immediately.

### Physical Assignment Mode

If you use a legacy theme, custom SQL queries, or third-party RSS/feed tools that require a physical `_thumbnail_id` saved in the database, you can switch to **Physical Assignment Mode** and use the **Batch Assigner** tool.

* **Safe & Non-Destructive:** Physical assignment only targets posts without a real featured image.
* **Tracking Flag:** Every image assigned by the plugin is stamped with `_thumbnest_is_fallback = 1`.
* **100% Reversible:** You can roll back all plugin-assigned images at any time with a single click.

---

### Image Priority Hierarchy

Whenever a post is displayed, ThumbNest follows a strict 5-level decision tree:

```
                      [Featured Image Requested]
                                  │
                                  ▼
           ┌──────────────────────────────────────────────┐
           │ Does the post have a manually uploaded/real  │
           │               featured image?                │
           └──────────────────────┬───────────────────────┘
                                  │
                    ┌─────────────┴─────────────┐
                   YES                          NO
                    │                           │
                    ▼                           ▼
        [Display Real Image]        ┌────────────────────────┐
     (ThumbNest NEVER touches)      │ Is fallback enabled    │
                                    │  for this post type?   │
                                    └───────────┬────────────┘
                                                │
                                  ┌─────────────┴─────────────┐
                                 YES                          NO
                                  │                           │
                                  ▼                           ▼
                      ┌──────────────────────┐          [No Fallback]
                      │ Does this post type  │        (Returns empty)
                      │ have a custom image? │
                      └───────────┬──────────┘
                                  │
                    ┌─────────────┴─────────────┐
                   YES                          NO
                    │                           │
                    ▼                           ▼
          [Display Post-Type]          [Display Global]
            Custom Fallback             Fallback Image
```

---

## Configuration Guide

Navigate to **Settings > ThumbNest** to access the settings panel.

### Global Fallback Image

* **Select Image:** Opens the native WordPress Media Library modal to pick an existing image or upload a new one.
* **Replace Image:** Change the global fallback at any time.
* **Remove Image:** Clears the global fallback setting.

---

### Per-Post-Type Settings

ThumbNest dynamically detects all registered public post types (Posts, Pages, WooCommerce Products, Portfolio, News, Team, etc.).

For each post type, you can configure:

1. **Enable Fallback (Toggle):** Turn fallback support ON or OFF for this specific post type.
2. **Image Source (Dropdown):**
   - **Use Global Image:** Uses the universal fallback image configured at the top.
   - **Use Custom Post-Type Image:** Reveals an image uploader specifically for this post type (e.g., a custom graphic for News articles, a document placeholder for Pages).
   - **No Fallback (Disabled):** Disables fallbacks for this post type entirely.

---

### Integrations & Compatibility

Under **Integrations & Ecosystem Compatibility**, toggle optional enhancements:

* **REST API & Gutenberg Support:** Exposes `thumbnest_fallback` data in the WordPress REST API for block themes and headless applications.
* **Elementor Dynamic Widgets:** Ensures Elementor post grids, loop builders, and archive cards render fallback images seamlessly.
* **WooCommerce Compatibility:** Allows products without an image to use the configured product fallback.

---

## Batch Assigner & Rollback Tool

Under the **Batch Assigner & Rollback** tab (**Settings > ThumbNest > Batch Assigner & Rollback**):

### Running Batch Assignment

1. Select your target post type (e.g., *All Enabled Post Types* or *Posts*).
2. Click **Start Batch Assignment**.
3. The tool runs asynchronously via AJAX in batches of 50 posts per step:
   - A real-time progress bar tracks progress.
   - You can pause or cancel at any time.
   - Server timeouts and memory exhaustion are completely avoided.

### Rolling Back Plugin Fallbacks

If you ever want to remove physically assigned fallbacks:

1. Click **Remove Plugin-Assigned Fallbacks**.
2. Confirm the security prompt.
3. The rollback engine queries posts stamped with `_thumbnest_is_fallback = 1` and removes the assignments.
4. **Safety Guarantee:** Posts where you manually uploaded or assigned a featured image are **never touched or removed**.

---

## Page Builders & Theme Compatibility

### Elementor
* Works out of the box with Elementor's **Posts**, **Portfolio**, **Archive Posts**, and **Loop Grid** widgets.
* Fallback images automatically inherit the image sizes and CSS classes configured in your Elementor widgets.

### Gutenberg & Classic Editor
* ThumbNest protects the editor screen: when creating or editing a post, the Featured Image box remains empty so authors clearly understand no real image has been uploaded yet.
* As soon as an author uploads a real featured image and saves the post, the real image immediately takes priority on the frontend.

### WooCommerce
* If enabled, products with no featured image display the fallback in product catalogs, related product grids, and shop loops.
* Product image galleries are never overwritten or modified.

### Responsive Images & Image Sizes
* ThumbNest respects requested WordPress image dimensions (`thumbnail`, `medium`, `large`, `full`, or custom registered theme sizes).
* Generates standard `srcset` and `sizes` attributes for crisp display on high-DPI (Retina) screens.

### Accessibility & Alt Text
* Inherits the **Alternative Text** (`alt`) set in the WordPress Media Library for the fallback attachment.
* If no alt text is set on the media attachment, it cleanly defaults to the post title.

---

## Developer Reference

### Template Tags

Use these helper functions anywhere in your theme templates:

#### 1. Retrieve Fallback Attachment ID
```php
$image_id = thumbnest_get_fallback_image_id( $post_id );
```

#### 2. Check If a Post Has a Fallback Available
```php
if ( thumbnest_has_fallback( $post_id ) ) {
    // Fallback is active
}
```

#### 3. Get Fallback Image HTML Markup
```php
$html = thumbnest_get_fallback_image_html( $post_id, 'medium', array( 'class' => 'custom-class' ) );
echo $html;
```

#### 4. Display Fallback Image Markup Directly
```php
thumbnest_the_fallback_image( $post_id, 'large' );
```

---

### Action & Filter Hooks

#### `thumbnest_fallback_image_id` (Filter)
Customize the resolved fallback image ID dynamically based on custom business logic (e.g. categories, tags, or authors):

```php
add_filter( 'thumbnest_fallback_image_id', function( $fallback_id, $post_id, $post_type ) {
    // Use a special image for posts in the 'Technology' category
    if ( 'post' === $post_type && has_category( 'technology', $post_id ) ) {
        return 123; // Attachment ID of tech banner
    }
    return $fallback_id;
}, 10, 3 );
```

#### `thumbnest_eligible_post_types` (Filter)
Filter which post types are supported by ThumbNest:

```php
add_filter( 'thumbnest_eligible_post_types', function( $eligible, $all_post_types ) {
    // Unset a specific custom post type from receiving fallbacks
    unset( $eligible['internal_docs'] );
    return $eligible;
}, 10, 2 );
```

#### `thumbnest_post_thumbnail_html` (Filter)
Modify the rendered fallback image HTML markup before output:

```php
add_filter( 'thumbnest_post_thumbnail_html', function( $html, $post_id, $fallback_id, $size, $attr ) {
    return '<div class="fallback-wrapper">' . $html . '</div>';
}, 10, 5 );
```

---

### REST API

When REST API support is enabled in settings, GET requests to `/wp/v2/posts/<id>` include a `thumbnest_fallback` field:

```json
{
  "id": 42,
  "title": { "rendered": "Hello World" },
  "featured_media": 0,
  "thumbnest_fallback": {
    "is_fallback": true,
    "image_id": 105,
    "image_url": "https://example.com/wp-content/uploads/2026/09/default-fallback.jpg"
  }
}
```

---

## Frequently Asked Questions

#### Will ThumbNest replace my existing featured images?
**No.** ThumbNest strictly respects manual featured images. If a post has a featured image, ThumbNest will never touch or replace it.

#### Will ThumbNest slow down my website?
**No.** ThumbNest includes an in-memory runtime cache. Resolving a fallback image takes a fraction of a millisecond and executes zero extra SQL queries on the frontend.

#### What happens if I delete my fallback image from the Media Library?
ThumbNest verifies attachment validity before rendering. If the fallback media attachment is deleted, ThumbNest fails gracefully and outputs no image rather than a broken image icon.

#### Does ThumbNest work on WordPress Multisite?
**Yes.** Settings are configured per-site, allowing each subsite in your network to have its own unique global and per-post-type fallback images.

---

## Troubleshooting & Support

| Issue | Cause | Solution |
| :--- | :--- | :--- |
| **Fallback image is not showing on the frontend.** | Post type fallback is set to "Disabled" or no global image has been selected. | Go to **Settings > ThumbNest**, ensure a Global Fallback Image is selected, and verify the post type toggle is **Enabled**. |
| **Theme uses custom thumbnail functions.** | Some legacy themes don't use standard `the_post_thumbnail()` functions. | Switch to **Physical Assignment Mode** and run the **Batch Assigner** under the Batch Assigner tab. |
| **Old image still appears after changing fallback.** | Page caching plugin (WP Rocket, LiteSpeed, Cloudflare) is serving cached HTML. | Clear your site's page cache and CDN cache. |

---

### Clean Uninstallation
If you ever deactivate and delete ThumbNest via the WordPress **Plugins** screen:
- All plugin options (`thumbnest_settings`) are automatically removed from `wp_options`.
- All transient caches are deleted.
- Your uploaded images in the Media Library remain safe and intact.
