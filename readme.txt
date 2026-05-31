=== Advanced Reading Progress & View Counter ===
Contributors: teckut
Tags: progress bar, reading, post views, view counter, shortcode
Requires at least: 6.7
Tested up to:      6.9
Requires PHP:      7.4
WC tested up to:   10.5.3
WC requires at least: 6.5
Stable tag: 1.0.0
Donate link: https://teckut.com/
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add a reading progress indicator and an anti-refresh post view counter with flexible placement and shortcode support.

== Description ==

Advanced Reading Progress & View Counter helps you improve reading experience and track post views more reliably.

Features:

* Reading progress indicator with `bar` and `radial` display modes.
* Color, position, thickness, and post type targeting controls.
* Post view counter badge with configurable prefix/suffix, colors, and position.
* Anti-refresh lock window to reduce inflated counts from repeated refreshes.
* Lock window can be disabled by setting it to `0`.
* Shortcode support for manual counter placement.

Shortcodes:

* `[arpvc_view_counter]`
* `[arpvc_read_count]` (alias)
* `[arpvc_view_counter post_id="123"]`
* `[arpvc_view_counter prefix="Views:" suffix="total"]`
* `[arpvc_view_counter show_zero="0"]`

Notes:

* If badge position is set to `Shortcode only`, the badge is rendered only where shortcode is used.
* Anti-refresh lock logic still applies to counting unless lock window is set to `0`.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/advanced-reading-progress-view-counter/`.
2. Activate the plugin from the **Plugins** screen in WordPress.
3. Open **Reading Progress & Views** in wp-admin.
4. Configure progress indicator settings in **Progress Settings** tab.
5. Configure counter settings in **View Counter** tab.

== Development & Source Code ==

This plugin uses a modern build process (npm + gulp) to generate optimized production assets.

The full human-readable, uncompiled development source code is publicly available at:

https://github.com/Teckut-Git/advanced-reading-progress-view-counter

The plugin distributed via WordPress.org contains compiled production assets located in:

* build/js/
* build/css/

Developers can review, study, modify, and rebuild the original source code using the instructions below.

= Rebuild Instructions =

To regenerate compiled assets from source:

1. Clone the repository:
   git clone https://github.com/Teckut-Git/advanced-reading-progress-view-counter

2. Navigate into the project directory:
   cd advanced-reading-progress-view-counter

3. Install dependencies:
   npm install

4. Build production files:
   npx gulp build

This ensures transparency and compliance with WordPress.org plugin guidelines.

== Frequently Asked Questions ==

= How does "Lock window (hours)" work? =

It is a cooldown per visitor per post.

* Example: if set to `24`, the same visitor adds at most one view to that post every 24 hours.
* Repeated refreshes inside the window do not increase the count.

= Can I disable the lock window? =

Yes. Set `Lock window (hours)` to `0` to disable lock-based deduplication.

= How do I place the view counter manually? =

Set badge position to `Shortcode only`, then place `[arpvc_view_counter]` where needed.

= Does this plugin count bot traffic? =

The plugin includes basic request filtering to skip obvious bot-like user agents and non-standard request contexts.

= Can I show the counter on a specific post from another page? =

Yes, use:

`[arpvc_view_counter post_id="123"]`

== Changelog ==

= 1.0.0 =
* Initial release.
* Progress indicator settings panel.
* Post view counter with lock window and shortcode support.

== Upgrade Notice ==

= 1.0.0 =
Initial public release.
