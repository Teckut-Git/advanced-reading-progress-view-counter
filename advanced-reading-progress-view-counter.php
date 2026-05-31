<?php
/**
 * Advanced Reading Progress & View Counter
 *
 * @package           AdvancedReadingProgressViewCounter
 * @author            Teckut
 * @copyright         2026 Teckut
 * @license           GPL-2.0-or-later
 *
 * Plugin Name:       Advanced Reading Progress & View Counter
 * Description:       Advanced reading progress bar with optional view counter controls.
 * Version:           1.0.0
 * Requires at least: 6.7
 * Tested up to:      6.9
 * Requires PHP:      7.4
 * WC tested up to:   10.5.3
 * WC requires at least: 6.5
 * Author:            Teckut
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       advanced-reading-progress-view-counter
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Declare WooCommerce feature compatibility.
 *
 * This plugin does not modify order data structures and is compatible with
 * WooCommerce HPOS and Cart/Checkout Blocks.
 *
 * @return void
 */
function arpvc_declare_wc_feature_compatibility() {
	if ( ! class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		return;
	}

	\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
}
add_action( 'before_woocommerce_init', 'arpvc_declare_wc_feature_compatibility' );

/**
 * Default settings for the progress bar.
 *
 * @return array
 */
function arpvc_default_settings() {
	return array(
		'progress_enabled' => true,
		'foreground_color' => '#ff0000',
		'background_color' => '#f1f1f1',
		'position'         => 'top',
		'thickness'        => 8,
		'post_types'       => array( 'post', 'page' ),
		'display_mode'     => 'bar',
		// View counter chip defaults.
		'read_count_enabled'      => false,
		'read_count_locations'    => array( 'post' ),
		'read_count_position'     => 'before-content',
		'read_count_prefix'       => __( 'Views:', 'advanced-reading-progress-view-counter' ),
		'read_count_suffix'       => '',
		'read_count_lock_hours'   => 24,
		'read_count_wpm'          => 200,
		'read_count_font_size'    => 14,
		'read_count_margin'       => array( 8, 8, 8, 8 ),
		'read_count_padding'      => array( 10, 12, 10, 12 ),
		'read_count_bg_color'     => '#0f766e',
		'read_count_text_color'   => '#ffffff',
	);
}

/**
 * Allowed bar positions.
 *
 * @return array
 */
function arpvc_allowed_positions() {
	return array(
		'top',
		'bottom',
		'top-right',
		'top-left',
		'bottom-right',
		'bottom-left',
	);
}

/**
 * Allowed read count positions.
 *
 * @return array
 */
function arpvc_allowed_read_count_positions() {
	return array(
		'before-title',
		'after-title',
		'before-content',
		'after-content',
		'shortcode-only',
	);
}

/**
 * Allowed post types (public).
 *
 * @return array
 */
function arpvc_allowed_post_types() {
	static $allowed = null;

	if ( null === $allowed ) {
		$objects = get_post_types(
			array(
				'public' => true,
			),
			'objects'
		);

		$allowed = array_keys( $objects );
	}

	return $allowed;
}

/**
 * Sanitize a hex color or return the provided fallback.
 *
 * @param string|null $color    Raw color value.
 * @param string|null $fallback Fallback color.
 *
 * @return string|null
 */
function arpvc_sanitize_color_value( $color, $fallback = null ) {
	$maybe_color = sanitize_hex_color( $color );

	if ( $maybe_color ) {
		return $maybe_color;
	}

	return $fallback;
}

/**
 * Sanitize position or return fallback.
 *
 * @param string|null $position Raw position value.
 * @param string|null $fallback  Fallback position.
 *
 * @return string|null
 */
function arpvc_sanitize_position( $position, $fallback = null ) {
	return in_array( $position, arpvc_allowed_positions(), true ) ? $position : $fallback;
}

/**
 * Sanitize thickness (px) or return fallback.
 *
 * @param int|string|null $thickness Raw thickness value.
 * @param int|null        $fallback  Fallback thickness.
 *
 * @return int|null
 */
function arpvc_sanitize_thickness( $thickness, $fallback = null ) {
	$maybe_thickness = absint( $thickness );

	if ( $maybe_thickness > 0 ) {
		return $maybe_thickness;
	}

	return $fallback;
}

/**
 * Sanitize selected post types list.
 *
 * @param array|string|null $post_types Raw post types.
 * @param array|null        $fallback   Fallback list.
 *
 * @return array|null
 */
function arpvc_sanitize_post_types( $post_types, $fallback = null ) {
	$allowed = arpvc_allowed_post_types();
	$list    = array();

	if ( is_string( $post_types ) ) {
		$post_types = array( $post_types );
	}

	if ( is_array( $post_types ) ) {
		foreach ( $post_types as $type ) {
			if ( in_array( $type, $allowed, true ) ) {
				$list[] = $type;
			}
		}
	}

	$list = array_values( array_unique( $list ) );

	if ( ! empty( $list ) ) {
		return $list;
	}

	return $fallback;
}

/**
 * Sanitize read count positions list.
 *
 * @param array|string|null $locations Raw locations.
 * @param array|null        $fallback  Fallback list.
 *
 * @return array|null
 */
function arpvc_sanitize_read_count_locations( $locations, $fallback = null ) {
	$allowed = array( 'post', 'page', 'home', 'archive' );
	$list    = array();

	if ( is_string( $locations ) ) {
		$locations = array( $locations );
	}

	if ( is_array( $locations ) ) {
		foreach ( $locations as $loc ) {
			if ( in_array( $loc, $allowed, true ) ) {
				$list[] = $loc;
			}
		}
	}

	$list = array_values( array_unique( $list ) );

	if ( ! empty( $list ) ) {
		return $list;
	}

	return $fallback;
}

/**
 * Sanitize read count position.
 *
 * @param string|null $position Raw position.
 * @param string|null $fallback Fallback.
 *
 * @return string|null
 */
function arpvc_sanitize_read_count_position( $position, $fallback = null ) {
	return in_array( $position, arpvc_allowed_read_count_positions(), true ) ? $position : $fallback;
}

/**
 * Sanitize the view lock window in hours.
 *
 * @param int|string|null $hours    Raw value.
 * @param int|null        $fallback Fallback.
 *
 * @return int|null
 */
function arpvc_sanitize_read_count_lock_hours( $hours, $fallback = null ) {
	if ( null === $hours || '' === $hours ) {
		return $fallback;
	}

	return max( 0, min( 720, absint( $hours ) ) );
}

/**
 * Sanitize integer list (e.g., margin/padding).
 *
 * @param array|string|null $values   Raw values.
 * @param array|null        $fallback Fallback.
 *
 * @return array|null
 */
function arpvc_sanitize_int_quad( $values, $fallback = null ) {
	if ( is_string( $values ) ) {
		$values = array_map( 'trim', explode( ',', $values ) );
	}

	if ( is_array( $values ) ) {
		$out = array();
		foreach ( array_slice( $values, 0, 4 ) as $val ) {
			$out[] = max( 0, absint( $val ) );
		}
		$limit = 4;
		$current_count = count( $out );

		while ( $current_count < $limit ) {
			$out[] = 0;
		}
		return $out;
	}

	return $fallback;
}

/**
 * Sanitize display mode.
 *
 * @param string|null $mode     Raw mode.
 * @param string|null $fallback Fallback.
 *
 * @return string|null
 */
function arpvc_sanitize_display_mode( $mode, $fallback = null ) {
	$allowed = array( 'bar', 'radial' );

	return in_array( $mode, $allowed, true ) ? $mode : $fallback;
}

/**
 * Normalize settings with sanitization and optional fallbacks.
 *
 * @param array $settings      Raw settings (may be partial).
 * @param bool  $with_fallback Whether to use defaults when a value is missing/invalid.
 *
 * @return array
 */
function arpvc_normalize_settings( array $settings, $with_fallback = true ) {
	$defaults      = arpvc_default_settings();
	$fallbacks     = $with_fallback ? $defaults : array_fill_keys( array_keys( $defaults ), null );

	return array(
		'progress_enabled' => (bool) ( $settings['progress_enabled'] ?? $fallbacks['progress_enabled'] ),
		'foreground_color' => arpvc_sanitize_color_value( $settings['foreground_color'] ?? null, $fallbacks['foreground_color'] ),
		'background_color' => arpvc_sanitize_color_value( $settings['background_color'] ?? null, $fallbacks['background_color'] ),
		'position'         => arpvc_sanitize_position( $settings['position'] ?? null, $fallbacks['position'] ),
		'thickness'        => arpvc_sanitize_thickness( $settings['thickness'] ?? null, $fallbacks['thickness'] ),
		'post_types'       => arpvc_sanitize_post_types( $settings['post_types'] ?? null, $fallbacks['post_types'] ),
		'display_mode'     => arpvc_sanitize_display_mode( $settings['display_mode'] ?? null, $fallbacks['display_mode'] ),
		'read_count_enabled'    => (bool) ( $settings['read_count_enabled'] ?? $fallbacks['read_count_enabled'] ),
		'read_count_locations'  => arpvc_sanitize_read_count_locations( $settings['read_count_locations'] ?? null, $fallbacks['read_count_locations'] ),
		'read_count_position'   => arpvc_sanitize_read_count_position( $settings['read_count_position'] ?? null, $fallbacks['read_count_position'] ),
		'read_count_prefix'     => sanitize_text_field( $settings['read_count_prefix'] ?? $fallbacks['read_count_prefix'] ),
		'read_count_suffix'     => sanitize_text_field( $settings['read_count_suffix'] ?? $fallbacks['read_count_suffix'] ),
		'read_count_lock_hours' => arpvc_sanitize_read_count_lock_hours( $settings['read_count_lock_hours'] ?? null, $fallbacks['read_count_lock_hours'] ),
		'read_count_wpm'        => max( 50, min( 1000, absint( $settings['read_count_wpm'] ?? $fallbacks['read_count_wpm'] ) ) ),
		'read_count_font_size'  => absint( $settings['read_count_font_size'] ?? $fallbacks['read_count_font_size'] ),
		'read_count_margin'     => arpvc_sanitize_int_quad( $settings['read_count_margin'] ?? null, $fallbacks['read_count_margin'] ),
		'read_count_padding'    => arpvc_sanitize_int_quad( $settings['read_count_padding'] ?? null, $fallbacks['read_count_padding'] ),
		'read_count_bg_color'   => arpvc_sanitize_color_value( $settings['read_count_bg_color'] ?? null, $fallbacks['read_count_bg_color'] ),
		'read_count_text_color' => arpvc_sanitize_color_value( $settings['read_count_text_color'] ?? null, $fallbacks['read_count_text_color'] ),
	);
}

/**
 * Setting keys convenience helper.
 *
 * @return array
 */
function arpvc_setting_keys() {
	return array_keys( arpvc_default_settings() );
}

/**
 * Retrieve saved settings merged with defaults and sanitized.
 *
 * @return array
 */
function arpvc_get_settings() {
	static $cached = null;

	if ( null !== $cached ) {
		return $cached;
	}

	$saved = get_option( 'arpvc_settings', null );

	// Migrate legacy option name once, but keep reading it as a fallback.
	if ( null === $saved ) {
		$legacy = get_option( 'prb_settings', array() );

		if ( ! empty( $legacy ) ) {
			$saved = $legacy;
			update_option( 'arpvc_settings', $legacy );
		} else {
			$saved = array();
		}
	}

	$cached = arpvc_normalize_settings( $saved );

	return $cached;
}

/**
 * Capability check for protected settings REST endpoints.
 *
 * @param WP_REST_Request $request Request.
 *
 * @return bool
 */
function arpvc_rest_can_manage_settings( WP_REST_Request $request ) {
	unset( $request );

	return current_user_can( 'manage_options' );
}

/**
 * Register REST API routes for reading/updating settings.
 */
function arpvc_register_rest_routes() {
	$namespaces = array( 'arpvc/v1', 'prb/v1' ); // Register legacy namespace for backward compatibility.

	foreach ( $namespaces as $namespace ) {
		register_rest_route(
			$namespace,
			'/settings',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => 'arpvc_rest_get_settings',
					'permission_callback' => 'arpvc_rest_can_manage_settings',
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => 'arpvc_rest_update_settings',
					'permission_callback' => 'arpvc_rest_can_manage_settings',
					'args'                => array(
						'progress_enabled' => array(
							'type'     => 'boolean',
							'required' => false,
						),
						'foreground_color' => array(
							'type'              => 'string',
							'required'          => false,
							'sanitize_callback' => 'sanitize_hex_color',
						),
						'background_color' => array(
							'type'              => 'string',
							'required'          => false,
							'sanitize_callback' => 'sanitize_hex_color',
						),
						'position'         => array(
							'type'              => 'string',
							'required'          => false,
							'enum'              => arpvc_allowed_positions(),
						),
						'thickness'        => array(
							'type'              => 'integer',
							'required'          => false,
							'minimum'           => 1,
							'maximum'           => 300,
							'sanitize_callback' => 'absint',
						),
						'post_types'       => array(
							'type'              => 'array',
							'required'          => false,
							'items'             => array(
								'type' => 'string',
							),
						),
						'display_mode'     => array(
							'type'              => 'string',
							'required'          => false,
							'enum'              => array( 'bar', 'radial' ),
						),
						'read_count_enabled'    => array(
							'type'     => 'boolean',
							'required' => false,
						),
						'read_count_locations'  => array(
							'type'     => 'array',
							'required' => false,
							'items'    => array(
								'type' => 'string',
							),
						),
						'read_count_position'   => array(
							'type'     => 'string',
							'required' => false,
							'enum'     => arpvc_allowed_read_count_positions(),
						),
						'read_count_prefix'     => array(
							'type'     => 'string',
							'required' => false,
						),
						'read_count_suffix'     => array(
							'type'     => 'string',
							'required' => false,
						),
						'read_count_lock_hours' => array(
							'type'     => 'integer',
							'required' => false,
							'minimum'  => 0,
							'maximum'  => 720,
						),
						'read_count_wpm'        => array(
							'type'     => 'integer',
							'required' => false,
							'minimum'  => 50,
							'maximum'  => 1000,
						),
						'read_count_font_size'  => array(
							'type'     => 'integer',
							'required' => false,
							'minimum'  => 8,
							'maximum'  => 96,
						),
						'read_count_margin'     => array(
							'type'     => 'array',
							'required' => false,
							'items'    => array(
								'type' => 'integer',
							),
						),
						'read_count_padding'    => array(
							'type'     => 'array',
							'required' => false,
							'items'    => array(
								'type' => 'integer',
							),
						),
						'read_count_bg_color'   => array(
							'type'              => 'string',
							'required'          => false,
							'sanitize_callback' => 'sanitize_hex_color',
						),
						'read_count_text_color' => array(
							'type'              => 'string',
							'required'          => false,
							'sanitize_callback' => 'sanitize_hex_color',
						),
					),
				),
			)
		);
	}
}
add_action( 'rest_api_init', 'arpvc_register_rest_routes' );

/**
 * REST callback: fetch settings.
 */
function arpvc_rest_get_settings() {
	return arpvc_get_settings();
}

/**
 * REST callback: update settings.
 *
 * @param WP_REST_Request $request Request.
 *
 * @return array|WP_Error
 */
function arpvc_rest_update_settings( WP_REST_Request $request ) {
	$current = arpvc_get_settings();
	$merged  = $current;

	foreach ( arpvc_setting_keys() as $key ) {
		if ( $request->has_param( $key ) ) {
			$merged[ $key ] = $request->get_param( $key );
		}
	}

	$sanitized_for_validation = arpvc_normalize_settings( $merged, false );

	if ( ! empty( $sanitized_for_validation['progress_enabled'] ) ) {
		if ( ! $sanitized_for_validation['foreground_color'] || ! $sanitized_for_validation['background_color'] ) {
			return new WP_Error( 'arpvc_invalid_color', __( 'Please provide valid hex colors.', 'advanced-reading-progress-view-counter' ), array( 'status' => 400 ) );
		}

		if ( ! $sanitized_for_validation['position'] ) {
			return new WP_Error( 'arpvc_invalid_position', __( 'Position must be top or bottom.', 'advanced-reading-progress-view-counter' ), array( 'status' => 400 ) );
		}

		if ( ! $sanitized_for_validation['thickness'] ) {
			return new WP_Error( 'arpvc_invalid_thickness', __( 'Thickness must be a positive integer.', 'advanced-reading-progress-view-counter' ), array( 'status' => 400 ) );
		}

		if ( empty( $sanitized_for_validation['post_types'] ) ) {
			return new WP_Error( 'arpvc_invalid_post_types', __( 'Please choose at least one valid post type.', 'advanced-reading-progress-view-counter' ), array( 'status' => 400 ) );
		}

		if ( ! $sanitized_for_validation['display_mode'] ) {
			return new WP_Error( 'arpvc_invalid_display_mode', __( 'Display mode must be bar or radial.', 'advanced-reading-progress-view-counter' ), array( 'status' => 400 ) );
		}
	}

	// Basic validation for read count fields.
	if ( isset( $sanitized_for_validation['read_count_font_size'] ) && $sanitized_for_validation['read_count_font_size'] > 96 ) {
		return new WP_Error( 'arpvc_invalid_font_size', __( 'Font size must be 96px or below.', 'advanced-reading-progress-view-counter' ), array( 'status' => 400 ) );
	}

	$settings = arpvc_normalize_settings( $merged );

	update_option( 'arpvc_settings', $settings );

	return $settings;
}

/**
 * Enqueues scripts and styles for the frontend.
 *
 * @return void
 */
function arpvc_enqueue_frontend_assets() {
	$settings   = arpvc_get_settings();
	$enabled    = ! empty( $settings['progress_enabled'] );
	$foreground = $settings['foreground_color'];
	$background = $settings['background_color'];
	$position   = $settings['position'];
	$thickness  = $settings['thickness'];
	$post_types = $settings['post_types'];
	$display    = $settings['display_mode'];

	$on_target_page = is_singular( $post_types ) || is_front_page() || is_home() || ( function_exists( 'is_shop' ) && is_shop() );

	if ( ! $enabled || ! $on_target_page ) {
		return;
	}

	// Block-level overrides removed; global settings apply everywhere.

	wp_enqueue_style( 'arpvc-style', plugins_url( 'build/css/main.css', __FILE__ ), array(), file_exists( __DIR__ . '/build/css/main.css' ) ? filemtime( __DIR__ . '/build/css/main.css' ) : null );

	$custom_css  = '#arpvc-progress-bar {';
	$custom_css .= 'background-color:' . esc_attr( $background ) . '!important;';
	$custom_css .= 'height:' . absint( $thickness ) . 'px!important;';
	$custom_css .= ( 'bottom' === $position ) ? 'top:auto;bottom:0;' : 'top:0;bottom:auto;';
	$custom_css .= '}';
	$custom_css .= '#arpvc-progress-fill { background-color:' . esc_attr( $foreground ) . '!important; height:100%!important; }';

	wp_add_inline_style( 'arpvc-style', $custom_css );

	wp_enqueue_script(
		'arpvc-scroll-js',
		plugins_url( 'build/js/scroll-tracker.min.js', __FILE__ ),
		array(),
		file_exists( __DIR__ . '/build/js/scroll-tracker.min.js' ) ? filemtime( __DIR__ . '/build/js/scroll-tracker.min.js' ) : time(),
		true
	);

	wp_localize_script(
		'arpvc-scroll-js',
		'ARPVC_SETTINGS',
		array(
			'foreground' => $foreground,
			'background' => $background,
			'position'   => $position,
			'thickness'  => absint( $thickness ),
			'display'    => $display,
			'postTypes'  => $post_types,
		)
	);
}
add_action( 'wp_enqueue_scripts', 'arpvc_enqueue_frontend_assets' );

/**
 * Register admin page.
 */
function arpvc_register_admin_page() {
	add_menu_page(
		__( 'Advanced Reading Progress & View Counter', 'advanced-reading-progress-view-counter' ),
		__( 'Reading Progress & Views', 'advanced-reading-progress-view-counter' ),
		'manage_options',
		'arpvc-settings',
		'arpvc_render_settings_page',
		'dashicons-editor-alignleft',
		65
	);
}
add_action( 'admin_menu', 'arpvc_register_admin_page' );

/**
 * Enqueue assets for admin React app.
 *
 * @param string $hook Current page hook.
 */
function arpvc_admin_enqueue_assets( $hook ) {
	if ( 'toplevel_page_arpvc-settings' !== $hook ) {
		return;
	}

	$post_type_objects = get_post_types(
		array(
			'public' => true,
		),
		'objects'
	);

	$post_type_choices = array();

	foreach ( $post_type_objects as $slug => $object ) {
		$post_type_choices[] = array(
			'slug'  => $slug,
			'label' => $object->labels->singular_name ?? $slug,
		);
	}

	wp_enqueue_script( 'wp-api-fetch' );
	wp_enqueue_script(
		'arpvc-admin-js',
		plugins_url( 'build/js/arpvc-admin.min.js', __FILE__ ),
		array( 'wp-element', 'wp-components', 'wp-api-fetch', 'wp-i18n' ),
		file_exists( __DIR__ . '/build/js/arpvc-admin.min.js' ) ? filemtime( __DIR__ . '/build/js/arpvc-admin.min.js' ) : time(),
		true
	);
	wp_set_script_translations(
		'arpvc-admin-js',
		'advanced-reading-progress-view-counter',
		plugin_dir_path( __FILE__ ) . 'languages'
	);

	wp_enqueue_style(
		'arpvc-admin-css',
		plugins_url( 'build/css/admin.css', __FILE__ ),
		array( 'wp-components' ),
		file_exists( __DIR__ . '/build/css/admin.css' ) ? filemtime( __DIR__ . '/build/css/admin.css' ) : null
	);

	wp_localize_script(
		'arpvc-admin-js',
		'arpvcAdmin',
		array(
			'restUrl' => esc_url_raw( rest_url( 'arpvc/v1/settings' ) ),
			'nonce'   => wp_create_nonce( 'wp_rest' ),
			'defaults' => arpvc_get_settings(),
			'postTypes' => $post_type_choices,
		)
	);
}
add_action( 'admin_enqueue_scripts', 'arpvc_admin_enqueue_assets' );

/**
 * Render root element for React admin.
 */
function arpvc_render_settings_page() {
	echo '<div class="wrap"><div id="arpvc-admin-app"></div></div>';
}

/**
 * Determine if read count should show on current request.
 *
 * @param array $settings Plugin settings.
 * @return bool
 */
function arpvc_should_show_read_count( array $settings ) {
	if ( empty( $settings['read_count_enabled'] ) ) {
		return false;
	}

	$locations = $settings['read_count_locations'] ?? array();

	if ( is_singular( 'post' ) && in_array( 'post', $locations, true ) ) {
		return true;
	}

	if ( is_singular( 'page' ) && in_array( 'page', $locations, true ) ) {
		return true;
	}

	if ( is_home() && in_array( 'home', $locations, true ) ) {
		return true;
	}

	if ( ( is_archive() || is_search() ) && in_array( 'archive', $locations, true ) ) {
		return true;
	}

	return false;
}

/**
 * Compute the lock window in seconds.
 *
 * @param array $settings Plugin settings.
 * @return int
 */
function arpvc_get_read_count_lock_seconds( array $settings ) {
	$hours = arpvc_sanitize_read_count_lock_hours( $settings['read_count_lock_hours'] ?? null, 24 );

	return $hours * HOUR_IN_SECONDS;
}

/**
 * Build the read count cookie name for a post.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function arpvc_get_read_count_cookie_name( $post_id ) {
	return 'arpvc_viewed_' . absint( $post_id );
}

/**
 * Build a lightweight visitor token.
 *
 * Logged-in users use user IDs. Guests use a salted hash of IP + UA.
 *
 * @return string
 */
function arpvc_get_read_count_visitor_token() {
	if ( is_user_logged_in() ) {
		return 'u:' . get_current_user_id();
	}

	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';

	if ( '' === $ip && '' === $ua ) {
		return '';
	}

	return 'g:' . substr( hash_hmac( 'sha256', $ip . '|' . $ua, wp_salt( 'auth' ) ), 0, 24 );
}

/**
 * Build transient lock key for a post + visitor.
 *
 * @param int    $post_id       Post ID.
 * @param string $visitor_token Visitor token.
 * @return string
 */
function arpvc_get_read_count_lock_key( $post_id, $visitor_token ) {
	$fingerprint = substr( hash( 'sha1', $visitor_token ), 0, 12 );

	return sprintf( 'arpvc_vc_%d_%s', absint( $post_id ), $fingerprint );
}

/**
 * Quick check to skip obvious bot/user-agent requests.
 *
 * @return bool
 */
function arpvc_is_bot_request() {
	$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) ) : '';

	if ( '' === $ua ) {
		return false;
	}

	$signatures = array(
		'bot',
		'spider',
		'crawl',
		'slurp',
		'headless',
		'lighthouse',
		'facebookexternalhit',
		'curl/',
		'wget/',
		'python-requests',
	);

	foreach ( $signatures as $signature ) {
		if ( false !== strpos( $ua, $signature ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Check whether this visitor recently viewed the current post.
 *
 * @param int   $post_id  Post ID.
 * @param array $settings Plugin settings.
 * @return bool
 */
function arpvc_has_recent_read_count_view( $post_id, array $settings ) {
	if ( arpvc_get_read_count_lock_seconds( $settings ) < 1 ) {
		return false;
	}

	$cookie_name = arpvc_get_read_count_cookie_name( $post_id );

	$cookie_value = isset( $_COOKIE[ $cookie_name ] ) ? sanitize_text_field( wp_unslash( $_COOKIE[ $cookie_name ] ) ) : '';

	if ( '1' === $cookie_value ) {
		return true;
	}

	$visitor_token = arpvc_get_read_count_visitor_token();

	if ( '' === $visitor_token ) {
		return false;
	}

	$lock_key = arpvc_get_read_count_lock_key( $post_id, $visitor_token );

	return (bool) get_transient( $lock_key );
}

/**
 * Persist short-lived anti-refresh lock markers.
 *
 * @param int   $post_id  Post ID.
 * @param array $settings Plugin settings.
 * @return void
 */
function arpvc_mark_recent_read_count_view( $post_id, array $settings ) {
	$ttl          = arpvc_get_read_count_lock_seconds( $settings );

	if ( $ttl < 1 ) {
		return;
	}

	$expires      = time() + $ttl;
	$cookie_name  = arpvc_get_read_count_cookie_name( $post_id );
	$cookie_path  = defined( 'COOKIEPATH' ) && COOKIEPATH ? COOKIEPATH : '/';
	$cookie_domain = defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '';

	if ( ! headers_sent() ) {
		setcookie( $cookie_name, '1', $expires, $cookie_path, $cookie_domain, is_ssl(), true );

		if ( defined( 'SITECOOKIEPATH' ) && SITECOOKIEPATH && SITECOOKIEPATH !== $cookie_path ) {
			setcookie( $cookie_name, '1', $expires, SITECOOKIEPATH, $cookie_domain, is_ssl(), true );
		}
	}

	$_COOKIE[ $cookie_name ] = '1';

	$visitor_token = arpvc_get_read_count_visitor_token();

	if ( '' !== $visitor_token ) {
		$lock_key = arpvc_get_read_count_lock_key( $post_id, $visitor_token );
		set_transient( $lock_key, 1, $ttl );
	}
}

/**
 * Increment post view count with a fast SQL update + fallback insert.
 *
 * @param int $post_id Post ID.
 * @return void
 */
function arpvc_increment_read_count( $post_id ) {
	global $wpdb;

	$post_id = absint( $post_id );

	if ( $post_id < 1 ) {
		return;
	}

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Atomic prepared increment avoids race conditions for concurrent requests.
	$updated = (int) $wpdb->query(
		$wpdb->prepare(
			"UPDATE {$wpdb->postmeta} SET meta_value = CAST(meta_value AS UNSIGNED) + 1 WHERE post_id = %d AND meta_key = %s",
			$post_id,
			'_arpvc_read_count'
		)
	);

	if ( $updated < 1 ) {
		add_post_meta( $post_id, '_arpvc_read_count', 1, true );
	}

	wp_cache_delete( $post_id, 'post_meta' );
}

/**
 * Retrieve current read count.
 *
 * @param int $post_id Post ID.
 * @return int
 */
function arpvc_get_read_count( $post_id ) {
	return max( 0, (int) get_post_meta( $post_id, '_arpvc_read_count', true ) );
}

/**
 * Increment read count for singular content when enabled.
 */
function arpvc_maybe_increment_read_count() {
	if ( is_admin() || ! is_singular() || is_feed() || is_preview() || is_trackback() || is_robots() ) {
		return;
	}

	if ( wp_doing_ajax() || ( function_exists( 'wp_is_json_request' ) && wp_is_json_request() ) || arpvc_is_bot_request() ) {
		return;
	}

	$method = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( sanitize_key( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) : 'GET';

	if ( 'GET' !== $method ) {
		return;
	}

	$settings = arpvc_get_settings();

	if ( ! arpvc_should_show_read_count( $settings ) ) {
		return;
	}

	$post = get_queried_object();
	if ( ! ( $post instanceof WP_Post ) || 'publish' !== $post->post_status || post_password_required( $post ) ) {
		return;
	}

	if ( arpvc_has_recent_read_count_view( $post->ID, $settings ) ) {
		return;
	}

	arpvc_increment_read_count( $post->ID );
	arpvc_mark_recent_read_count_view( $post->ID, $settings );
}
add_action( 'template_redirect', 'arpvc_maybe_increment_read_count' );

/**
 * Build read count chip HTML.
 *
 * @param int   $post_id  Post ID.
 * @param array $settings Plugin settings.
 * @return string
 */
function arpvc_render_read_count_chip( $post_id, array $settings ) {
	$count   = arpvc_get_read_count( $post_id );
	$prefix  = esc_html( $settings['read_count_prefix'] ?? '' );
	$suffix  = esc_html( $settings['read_count_suffix'] ?? '' );
	$font    = absint( $settings['read_count_font_size'] ?? 14 );
	$margin  = $settings['read_count_margin'] ?? array( 8, 8, 8, 8 );
	$padding = $settings['read_count_padding'] ?? array( 10, 12, 10, 12 );
	$bg      = esc_attr( $settings['read_count_bg_color'] ?? '#0f766e' );
	$text    = esc_attr( $settings['read_count_text_color'] ?? '#ffffff' );

	$margin_css  = sprintf( '%dpx %dpx %dpx %dpx', $margin[0] ?? 0, $margin[1] ?? 0, $margin[2] ?? 0, $margin[3] ?? 0 );
	$padding_css = sprintf( '%dpx %dpx %dpx %dpx', $padding[0] ?? 0, $padding[1] ?? 0, $padding[2] ?? 0, $padding[3] ?? 0 );

	$style = sprintf(
		'display:inline-flex;align-items:center;gap:6px;background:%1$s;color:%2$s;font-weight:700;font-size:%3$dpx;border-radius:999px;margin:%4$s;padding:%5$s;line-height:1;',
		$bg,
		$text,
		$font,
		$margin_css,
		$padding_css
	);

	return sprintf(
		'<span class="arpvc-read-count" style="%1$s">%2$s <strong style="color:%3$s;">%4$s</strong> %5$s</span>',
		esc_attr( $style ),
		esc_html( $prefix ),
		esc_attr( $text ),
		esc_html( number_format_i18n( $count ) ),
		esc_html( $suffix )
	);
}

/**
 * Resolve a post ID for shortcode rendering.
 *
 * @param int $requested_post_id Post ID from shortcode attrs.
 * @return int
 */
function arpvc_get_read_count_shortcode_post_id( $requested_post_id ) {
	$post_id = absint( $requested_post_id );

	if ( $post_id > 0 ) {
		return $post_id;
	}

	if ( is_singular() ) {
		$post_id = get_queried_object_id();

		if ( $post_id > 0 ) {
			return $post_id;
		}
	}

	$post = get_post();

	return $post ? (int) $post->ID : 0;
}

/**
 * Render view counter via shortcode.
 *
 * Usage:
 * - [arpvc_view_counter]
 * - [arpvc_view_counter post_id="123"]
 * - [arpvc_view_counter prefix="Views:" suffix="total" show_zero="0"]
 *
 * @param array  $atts    Shortcode attributes.
 * @param string $content Shortcode content (unused).
 * @param string $tag     Shortcode tag.
 * @return string
 */
function arpvc_render_read_count_shortcode( $atts = array(), $content = '', $tag = '' ) {
	$settings = arpvc_get_settings();

	if ( empty( $settings['read_count_enabled'] ) ) {
		return '';
	}

	$atts = shortcode_atts(
		array(
			'post_id'   => 0,
			'prefix'    => '',
			'suffix'    => '',
			'show_zero' => '1',
		),
		(array) $atts,
		$tag
	);

	$post_id = arpvc_get_read_count_shortcode_post_id( $atts['post_id'] );

	if ( $post_id < 1 ) {
		return '';
	}

	$post = get_post( $post_id );

	if ( ! ( $post instanceof WP_Post ) || 'publish' !== $post->post_status ) {
		return '';
	}

	$count     = arpvc_get_read_count( $post_id );
	$show_zero = wp_validate_boolean( $atts['show_zero'] );

	if ( ! $show_zero && $count < 1 ) {
		return '';
	}

	if ( '' !== $atts['prefix'] ) {
		$settings['read_count_prefix'] = sanitize_text_field( $atts['prefix'] );
	}

	if ( '' !== $atts['suffix'] ) {
		$settings['read_count_suffix'] = sanitize_text_field( $atts['suffix'] );
	}

	return arpvc_render_read_count_chip( $post_id, $settings );
}
add_shortcode( 'arpvc_view_counter', 'arpvc_render_read_count_shortcode' );
add_shortcode( 'arpvc_read_count', 'arpvc_render_read_count_shortcode' );

/**
 * Inject read count into content based on position.
 *
 * @param string $content Post content.
 * @return string
 */
function arpvc_filter_the_content( $content ) {
	if ( is_admin() || is_feed() ) {
		return $content;
	}

	$settings = arpvc_get_settings();

	if ( ! arpvc_should_show_read_count( $settings ) ) {
		return $content;
	}

	$post = get_post();
	if ( ! $post ) {
		return $content;
	}

	$chip     = arpvc_render_read_count_chip( $post->ID, $settings );
	$position = $settings['read_count_position'] ?? 'before-content';

	if ( 'before-content' === $position ) {
		return $chip . $content;
	}

	if ( 'after-content' === $position ) {
		return $content . $chip;
	}

	return $content;
}
add_filter( 'the_content', 'arpvc_filter_the_content', 20 );

/**
 * Inject read count near the title when requested.
 *
 * @param string $title Title.
 * @param int    $post_id Post ID.
 * @return string
 */
function arpvc_filter_the_title( $title, $post_id = 0 ) {
	if ( is_admin() || is_feed() || ! in_the_loop() ) {
		return $title;
	}

	$settings = arpvc_get_settings();

	if ( ! arpvc_should_show_read_count( $settings ) ) {
		return $title;
	}

	if ( ! is_singular() ) {
		return $title;
	}

	$position = $settings['read_count_position'] ?? 'before-content';
	if ( ! in_array( $position, array( 'before-title', 'after-title' ), true ) ) {
		return $title;
	}

	$chip = arpvc_render_read_count_chip( $post_id, $settings );

    $safe_title = esc_html( $title );

	if ( 'before-title' === $position ) {
		return $chip . ' ' . $safe_title;
	}

	return $safe_title . ' ' . $chip;
}
add_filter( 'the_title', 'arpvc_filter_the_title', 10, 2 );
