=== Advanced Reading Progress & View Counter ===
Contributors: teckut
Tags: reading progress, progress bar, post views, view counter, reading indicator
Requires at least: 6.7
Tested up to: 7.1
Requires PHP: 7.4
WC tested up to: 11.3.1
WC requires at least: 6.5
Stable tag: 1.0.3
Donate link: https://teckut.com/
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add beautiful reading progress bars and smart post view counters to improve engagement and user experience on your WordPress site.

== Description ==

Advanced Reading Progress & View Counter helps improve user engagement by showing visitors:

✔ How much content they have read
✔ Which posts are most popular
✔ A cleaner and more interactive reading experience

The plugin combines a customizable reading progress indicator with a lightweight post view counter that includes anti-refresh protection.

Perfect for:

* Blogs
* News websites
* Tutorial sites
* Documentation websites
* Magazine-style WordPress websites

= Why Use This Plugin? =

Many readers leave long articles before finishing them.

A reading progress bar encourages visitors to continue scrolling and improves content interaction. At the same time, the built-in view counter helps display content popularity and social proof.

This plugin is lightweight, easy to configure, and works without external tracking services.

= Reading Progress Features =

* Top reading progress bar
* Circular/radial progress indicator
* Custom colors and thickness
* Multiple display positions
* Mobile-friendly design
* Select supported post types
* Lightweight frontend assets

= Post View Counter Features =

* Smart anti-refresh protection
* Configurable lock window
* Custom prefix & suffix
* Multiple display positions
* Manual shortcode placement
* Support for specific post IDs
* Hide zero-view counts

= Easy Customization =

* Beginner-friendly settings
* Clean WordPress admin UI
* No coding required
* Lightweight and performance-focused

== Shortcodes ==

Display the counter anywhere manually:

`[arpvc_view_counter]`

Alias shortcode:

`[arpvc_read_count]`

Show views for a specific post:

`[arpvc_view_counter post_id="123"]`

Add custom text:

`[arpvc_view_counter prefix="Views:" suffix="Total"]`

Hide counter when views are zero:

`[arpvc_view_counter show_zero="0"]`

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/advanced-reading-progress-view-counter/`
2. Activate the plugin from the **Plugins** page in WordPress
3. Open **Reading Progress & Views** from the admin menu
4. Configure progress bar settings
5. Configure view counter settings
6. Save changes and start tracking engagement

== Screenshots ==

1. Reading progress bar displayed at the top of a blog post
2. Circular reading progress indicator example
3. Post view counter displayed on a single post
4. Reading Progress settings page
5. View Counter settings page

== Frequently Asked Questions ==

= How does the anti-refresh protection work? =

The plugin stores a temporary cooldown period per visitor and post.

Example:

If the lock window is set to `24 hours`, refreshing the same post multiple times within that period will not increase the view count.

= Can I disable the lock window? =

Yes.

Simply set the lock window value to `0`.

= Can I manually place the counter? =

Yes.

Set the counter position to `Shortcode Only` and place:

`[arpvc_view_counter]`

anywhere inside posts, pages, widgets, Elementor, Gutenberg blocks, or page builders.

= Does the plugin require external services? =

No.

Everything works directly inside WordPress without third-party APIs or tracking systems.

= Is the plugin lightweight? =

Yes.

The plugin is optimized for performance and loads only the required frontend assets.

== Development ==

Source code is available on GitHub:

https://github.com/Teckut-Git/advanced-reading-progress-view-counter

Production assets included in the plugin are generated using npm and gulp.

== Changelog ==

= 1.0.3 =

* Fixed a fatal error when view counter margin or padding settings contained fewer than four values.
* Fixed stale settings being used later in a request after an update.
* Improved view counting accuracy under concurrent requests and completed the anti-refresh lock before counting.
* Hardened the view-tracking cookie with an explicit SameSite attribute.
* Updated tested compatibility to WordPress 7.1 and WooCommerce 11.3.1.
* Added automated tests and CI checks to protect plugin stability.

= 1.0.2 =

* Added a WordPress Playground live preview demo with sample content.
* Added a preconfigured demo setup for the reading progress indicator and view counter.

= 1.0.1 =

* Improved WordPress.org plugin page content
* Enhanced plugin documentation and readability
* Improved shortcode explanations
* Updated compatibility for latest WordPress and WooCommerce versions
* UI and presentation improvements

= 1.0.0 =

* Initial release
* Reading progress indicator
* Circular progress display
* Post view counter
* Shortcode support
* Anti-refresh lock system

== Upgrade Notice ==

= 1.0.3 =
Stability and compatibility update. Fixes a fatal error with short spacing values and improves view-count accuracy.

= 1.0.2 =
Added a WordPress Playground live preview demo with sample content and preconfigured plugin settings.

= 1.0.1 =
Improved plugin page readability, documentation, and latest compatibility updates.

= 1.0.0 =
Initial public release.
