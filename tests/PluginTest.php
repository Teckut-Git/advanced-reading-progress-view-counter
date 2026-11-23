<?php
/**
 * Unit tests for the ARPVC plugin logic.
 *
 * @package AdvancedReadingProgressViewCounter
 */

use PHPUnit\Framework\TestCase;

final class PluginTest extends TestCase {
	protected function setUp(): void {
		arpvc_test_reset_state();
	}

	/* -----------------------------------------------------------------
	 * Settings and sanitization
	 * -------------------------------------------------------------- */

	public function test_default_settings_use_the_current_setting_keys(): void {
		$settings = arpvc_default_settings();

		$this->assertIsArray( $settings );
		$this->assertArrayHasKey( 'progress_enabled', $settings );
		$this->assertArrayHasKey( 'read_count_enabled', $settings );
		$this->assertArrayHasKey( 'post_types', $settings );
		$this->assertArrayHasKey( 'read_count_locations', $settings );
		$this->assertTrue( $settings['progress_enabled'] );
		$this->assertFalse( $settings['read_count_enabled'] );
	}

	public function test_sanitize_color_value(): void {
		$this->assertSame( '#ff0000', arpvc_sanitize_color_value( '#ff0000' ) );
		$this->assertSame( '#0073aa', arpvc_sanitize_color_value( 'not-a-color', '#0073aa' ) );
	}

	public function test_sanitize_position(): void {
		$this->assertSame( 'top', arpvc_sanitize_position( 'top' ) );
		$this->assertSame( 'bottom', arpvc_sanitize_position( 'bottom' ) );
		$this->assertNull( arpvc_sanitize_position( 'invalid' ) );
		$this->assertSame( 'top', arpvc_sanitize_position( 'invalid', 'top' ) );
	}

	public function test_sanitize_thickness(): void {
		$this->assertSame( 3, arpvc_sanitize_thickness( 3 ) );
		$this->assertSame( 10, arpvc_sanitize_thickness( '10px' ) );
		$this->assertNull( arpvc_sanitize_thickness( 0 ) );
		$this->assertSame( 1, arpvc_sanitize_thickness( 0, 1 ) );
	}

	public function test_sanitize_post_types_only_keeps_public_types(): void {
		$types = arpvc_sanitize_post_types( array( 'post', 'unknown', 'post', 'page' ) );

		$this->assertSame( array( 'post', 'page' ), $types );
	}

	public function test_sanitize_read_count_locations(): void {
		$locations = arpvc_sanitize_read_count_locations( array( 'home', 'bogus', 'home', 'archive' ) );

		$this->assertSame( array( 'home', 'archive' ), $locations );
	}

	public function test_sanitize_int_quad_normalizes_four_values(): void {
		$this->assertSame( array( 1, 2, 3, 4 ), arpvc_sanitize_int_quad( '1,2,3,4,5' ) );
		$this->assertSame( array( 5, 0, 0, 0 ), arpvc_sanitize_int_quad( array( 5, 'abc' ) ) );
		$this->assertSame( array( 2, 0, 0, 0 ), arpvc_sanitize_int_quad( array( 2 ) ) );
	}

	public function test_normalize_settings_falls_back_for_invalid_values(): void {
		$settings = arpvc_normalize_settings(
			array(
				'foreground_color' => 'garbage',
				'position'         => 'sideways',
				'thickness'        => 0,
				'post_types'       => array( 'no-such-type' ),
				'read_count_prefix' => '<script>alert(1)</script>Views',
			)
		);

		$defaults = arpvc_default_settings();

		$this->assertSame( $defaults['foreground_color'], $settings['foreground_color'] );
		$this->assertSame( $defaults['position'], $settings['position'] );
		$this->assertSame( $defaults['thickness'], $settings['thickness'] );
		$this->assertSame( $defaults['post_types'], $settings['post_types'] );
		$this->assertStringNotContainsString( '<script>', $settings['read_count_prefix'] );
	}

	public function test_get_settings_merges_saved_values_with_defaults(): void {
		update_option( 'arpvc_settings', array( 'foreground_color' => '#00ff00', 'read_count_enabled' => true ) );

		$settings = arpvc_get_settings();

		$this->assertSame( '#00ff00', $settings['foreground_color'] );
		$this->assertTrue( $settings['read_count_enabled'] );
		$this->assertSame( arpvc_default_settings()['position'], $settings['position'] );
	}

	public function test_settings_cache_is_invalidated_after_option_update(): void {
		update_option( 'arpvc_settings', array( 'foreground_color' => '#00ff00' ) );
		$this->assertSame( '#00ff00', arpvc_get_settings()['foreground_color'] );

		update_option( 'arpvc_settings', array( 'foreground_color' => '#0000ff' ) );
		$this->assertSame( '#0000ff', arpvc_get_settings()['foreground_color'] );
	}

	/* -----------------------------------------------------------------
	 * REST API
	 * -------------------------------------------------------------- */

	public function test_rest_routes_are_registered_for_both_namespaces(): void {
		arpvc_register_rest_routes();

		foreach ( array( 'arpvc/v1/settings', 'prb/v1/settings' ) as $route ) {
			$this->assertArrayHasKey( $route, $GLOBALS['arpvc_test_routes'] );
			$endpoints = $GLOBALS['arpvc_test_routes'][ $route ];
			$this->assertCount( 2, $endpoints );
			$this->assertSame( 'arpvc_rest_can_manage_settings', $endpoints[0]['permission_callback'] );
			$this->assertSame( 'arpvc_rest_can_manage_settings', $endpoints[1]['permission_callback'] );
		}
	}

	public function test_rest_permission_requires_manage_options(): void {
		$GLOBALS['arpvc_test_can']['manage_options'] = false;
		$this->assertFalse( arpvc_rest_can_manage_settings( new WP_REST_Request() ) );

		$GLOBALS['arpvc_test_can']['manage_options'] = true;
		$this->assertTrue( arpvc_rest_can_manage_settings( new WP_REST_Request() ) );
	}

	public function test_rest_update_sanitizes_and_persists_settings(): void {
		$GLOBALS['arpvc_test_can']['manage_options'] = true;

		$request = new WP_REST_Request(
			array(
				'foreground_color'  => '#123456',
				'position'          => 'bottom',
				'thickness'         => '12',
				'read_count_prefix' => '<b>Views</b>',
			)
		);

		$result = arpvc_rest_update_settings( $request );

		$this->assertIsArray( $result );
		$this->assertSame( '#123456', $result['foreground_color'] );
		$this->assertSame( 'bottom', $result['position'] );
		$this->assertSame( 12, $result['thickness'] );
		$this->assertSame( 'Views', $result['read_count_prefix'] );
		$this->assertSame( $result, get_option( 'arpvc_settings' ) );
	}

	public function test_rest_update_accepts_short_spacing_arrays(): void {
		$GLOBALS['arpvc_test_can']['manage_options'] = true;

		$result = arpvc_rest_update_settings(
			new WP_REST_Request(
				array(
					'read_count_margin'  => array( 4, 6 ),
					'read_count_padding' => '3,5',
				)
			)
		);

		$this->assertIsArray( $result );
		$this->assertSame( array( 4, 6, 0, 0 ), $result['read_count_margin'] );
		$this->assertSame( array( 3, 5, 0, 0 ), $result['read_count_padding'] );
	}

	public function test_rest_update_rejects_invalid_progress_values(): void {
		$GLOBALS['arpvc_test_can']['manage_options'] = true;

		$invalid = array(
			array( 'foreground_color' => 'nope' ),
			array( 'position' => 'sideways' ),
			array( 'thickness' => 0 ),
			array( 'post_types' => array() ),
			array( 'display_mode' => 'unknown' ),
		);

		foreach ( $invalid as $params ) {
			$params['progress_enabled'] = true;
			$result = arpvc_rest_update_settings( new WP_REST_Request( $params ) );
			$this->assertInstanceOf( WP_Error::class, $result );
		}
	}

	public function test_rest_update_rejects_oversized_font_size(): void {
		$GLOBALS['arpvc_test_can']['manage_options'] = true;

		$result = arpvc_rest_update_settings( new WP_REST_Request( array( 'read_count_font_size' => 100 ) ) );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'arpvc_invalid_font_size', $result->get_error_code() );
	}

	/* -----------------------------------------------------------------
	 * View counter chip, shortcode and locking
	 * -------------------------------------------------------------- */

	public function test_read_count_chip_escapes_prefix_and_suffix_only_once(): void {
		$settings = arpvc_default_settings();
		$settings['read_count_prefix'] = 'Views & "total"';
		$settings['read_count_suffix'] = "<fifteen> views";

		$html = arpvc_render_read_count_chip( 42, $settings );

		$this->assertStringContainsString( 'Views &#038; &quot;total&quot;', $html );
		$this->assertStringContainsString( '&lt;fifteen&gt; views', $html );
		$this->assertStringNotContainsString( '&#038;#038;', $html );
		$this->assertStringNotContainsString( '<fifteen>', $html );
	}

	public function test_read_count_chip_uses_sanitized_colors_and_spacing(): void {
		$html = arpvc_render_read_count_chip( 42, arpvc_default_settings() );

		$this->assertStringContainsString( 'background:#0f766e', $html );
		$this->assertStringContainsString( 'margin:8px 8px 8px 8px', $html );
		$this->assertStringContainsString( 'padding:10px 12px 10px 12px', $html );
	}

	public function test_shortcode_is_disabled_when_view_counter_is_off(): void {
		$this->assertSame( '', arpvc_render_read_count_shortcode() );
	}

	public function test_shortcode_renders_and_accepts_overrides(): void {
		$settings = arpvc_default_settings();
		$settings['read_count_enabled'] = true;
		$GLOBALS['arpvc_test_options']['arpvc_settings'] = $settings;

		$html = arpvc_render_read_count_shortcode( array( 'prefix' => 'Seen', 'suffix' => 'times' ) );

		$this->assertStringContainsString( 'Seen', $html );
		$this->assertStringContainsString( 'times', $html );
	}

	public function test_guest_visitor_token_is_salted_and_does_not_expose_ip(): void {
		$token = arpvc_get_read_count_visitor_token();

		$this->assertStringStartsWith( 'g:', $token );
		$this->assertStringNotContainsString( '127.0.0.1', $token );
		$this->assertSame( 26, strlen( $token ) );
	}

	public function test_logged_in_visitor_token_uses_user_id(): void {
		$GLOBALS['arpvc_test_globals']['user_id'] = 7;

		$this->assertSame( 'u:7', arpvc_get_read_count_visitor_token() );
	}

	public function test_bot_user_agents_are_detected(): void {
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (compatible; Googlebot/2.1)';
		$this->assertTrue( arpvc_is_bot_request() );

		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X)';
		$this->assertFalse( arpvc_is_bot_request() );
	}

	public function test_recent_view_lock_blocks_repeat_counts(): void {
		$settings = arpvc_default_settings();

		$this->assertFalse( arpvc_has_recent_read_count_view( 42, $settings ) );
		arpvc_mark_recent_read_count_view( 42, $settings );
		$this->assertTrue( arpvc_has_recent_read_count_view( 42, $settings ) );
	}

	public function test_zero_lock_window_disables_deduplication(): void {
		$settings                        = arpvc_default_settings();
		$settings['read_count_lock_hours'] = 0;

		arpvc_mark_recent_read_count_view( 42, $settings );

		$this->assertFalse( arpvc_has_recent_read_count_view( 42, $settings ) );
	}

	public function test_increment_read_count_is_atomic_and_clears_cache(): void {
		$GLOBALS['arpvc_test_postmeta'][42]['_arpvc_read_count'] = 4;

		arpvc_increment_read_count( 42 );

		$this->assertSame( 5, arpvc_get_read_count( 42 ) );
	}

	public function test_increment_retries_when_another_request_created_the_meta_row(): void {
		$GLOBALS['arpvc_test_postmeta'][42]['_arpvc_read_count'] = 4;
		$GLOBALS['arpvc_test_skip_next_update']                  = true;

		arpvc_increment_read_count( 42 );

		$this->assertSame( 5, arpvc_get_read_count( 42 ) );
	}

	public function test_maybe_increment_counts_once_and_sets_the_lock(): void {
		$settings                        = arpvc_default_settings();
		$settings['read_count_enabled']  = true;
		$GLOBALS['arpvc_test_options']['arpvc_settings'] = $settings;
		$GLOBALS['arpvc_test_globals']['singular']       = true;
		$_SERVER['REQUEST_METHOD']                       = 'GET';

		arpvc_maybe_increment_read_count();

		$this->assertSame( 1, arpvc_get_read_count( 42 ) );

		$lock_key = arpvc_get_read_count_lock_key( 42, arpvc_get_read_count_visitor_token() );
		$this->assertNotEmpty( get_transient( $lock_key ) );

		arpvc_maybe_increment_read_count();

		$this->assertSame( 1, arpvc_get_read_count( 42 ) );
	}

	/* -----------------------------------------------------------------
	 * Frontend assets
	 * -------------------------------------------------------------- */

	public function test_admin_page_uses_the_live_wporg_mount_point(): void {
		ob_start();
		arpvc_render_settings_page();
		$html = ob_get_clean();

		$this->assertStringContainsString( 'id="arpvc-admin-app"', $html );
		$this->assertStringNotContainsString( 'arpvc-react-settings-root', $html );
	}

	public function test_admin_assets_match_the_live_wporg_wiring(): void {
		arpvc_admin_enqueue_assets( 'toplevel_page_arpvc-settings' );

		$scripts = $GLOBALS['arpvc_test_enqueued']['scripts'];
		$styles  = $GLOBALS['arpvc_test_enqueued']['styles'];

		$this->assertArrayHasKey( 'arpvc-admin-js', $scripts );
		$this->assertStringContainsString( 'build/js/arpvc-admin.min.js', $scripts['arpvc-admin-js']['src'] );
		$this->assertSame( array( 'wp-element', 'wp-components', 'wp-api-fetch', 'wp-i18n' ), $scripts['arpvc-admin-js']['deps'] );

		$this->assertArrayHasKey( 'arpvc-admin-css', $styles );
		$this->assertStringContainsString( 'build/css/admin.css', $styles['arpvc-admin-css']['src'] );
		$this->assertSame( array( 'wp-components' ), $styles['arpvc-admin-css']['deps'] );

		$this->assertArrayHasKey( 'arpvc-admin-js', $GLOBALS['arpvc_test_enqueued']['localized'] );
		$data = $GLOBALS['arpvc_test_enqueued']['localized']['arpvc-admin-js'];
		$this->assertSame( 'arpvcAdmin', $data['object'] );
		$this->assertArrayHasKey( 'restUrl', $data['data'] );
		$this->assertArrayHasKey( 'nonce', $data['data'] );
		$this->assertArrayHasKey( 'defaults', $data['data'] );
		$this->assertArrayHasKey( 'postTypes', $data['data'] );
	}

	public function test_admin_assets_are_not_loaded_on_unrelated_pages(): void {
		arpvc_admin_enqueue_assets( 'index.php' );

		$this->assertArrayNotHasKey( 'arpvc-admin-js', $GLOBALS['arpvc_test_enqueued']['scripts'] );
	}

	public function test_frontend_assets_are_skipped_when_progress_is_disabled(): void {
		$GLOBALS['arpvc_test_globals']['singular'] = true;
		update_option( 'arpvc_settings', array( 'progress_enabled' => false ) );

		arpvc_enqueue_frontend_assets();

		$this->assertArrayNotHasKey( 'arpvc-style', $GLOBALS['arpvc_test_enqueued']['styles'] );
		$this->assertArrayNotHasKey( 'arpvc-scroll-js', $GLOBALS['arpvc_test_enqueued']['scripts'] );
	}

	public function test_frontend_assets_are_loaded_on_target_pages(): void {
		$GLOBALS['arpvc_test_globals']['singular'] = true;
		update_option(
			'arpvc_settings',
			array(
				'progress_enabled' => true,
				'foreground_color' => '#ff0000',
				'background_color' => '#f1f1f1',
				'position'         => 'top',
				'thickness'        => 8,
			)
		);

		arpvc_enqueue_frontend_assets();

		$this->assertArrayHasKey( 'arpvc-style', $GLOBALS['arpvc_test_enqueued']['styles'] );
		$this->assertArrayHasKey( 'arpvc-scroll-js', $GLOBALS['arpvc_test_enqueued']['scripts'] );
		$this->assertStringContainsString( 'build/js/scroll-tracker.min.js', $GLOBALS['arpvc_test_enqueued']['scripts']['arpvc-scroll-js']['src'] );
		$this->assertStringContainsString( 'background-color:#f1f1f1', $GLOBALS['arpvc_test_enqueued']['inline']['arpvc-style'] );
	}

	public function test_frontend_assets_are_skipped_off_target_pages(): void {
		$GLOBALS['arpvc_test_globals']['singular'] = false;

		arpvc_enqueue_frontend_assets();

		$this->assertArrayNotHasKey( 'arpvc-style', $GLOBALS['arpvc_test_enqueued']['styles'] );
	}
}
