<?php
/**
 * Minimal WordPress stand-ins used by the ARPVC unit tests.
 *
 * These are intentionally simple and deterministic. They only implement the
 * behaviour that the plugin relies on in the functions under test.
 *
 * @package AdvancedReadingProgressViewCounter
 */

define( 'MINUTE_IN_SECONDS', 60 );
define( 'HOUR_IN_SECONDS', 3600 );
define( 'DAY_IN_SECONDS', 86400 );

/* -------------------------------------------------------------------------
 * State helpers
 * ---------------------------------------------------------------------- */

$GLOBALS['arpvc_test_options']        = array();
$GLOBALS['arpvc_test_postmeta']       = array();
$GLOBALS['arpvc_test_transients']     = array();
$GLOBALS['arpvc_test_hooks']          = array( 'actions' => array(), 'filters' => array(), 'shortcodes' => array() );
$GLOBALS['arpvc_test_routes']         = array();
$GLOBALS['arpvc_test_enqueued']       = array( 'scripts' => array(), 'styles' => array(), 'inline' => array(), 'localized' => array() );
$GLOBALS['arpvc_test_can']            = array();
$GLOBALS['arpvc_test_globals']        = array(
	'user_id'     => 0,
	'ip'          => '127.0.0.1',
	'ua'          => 'Mozilla/5.0 test browser',
	'singular'    => false,
	'post_type'   => 'post',
	'home'        => false,
	'archive'     => false,
	'search'      => false,
	'admin'       => false,
	'request_method' => 'GET',
);

function arpvc_test_reset_state() {
	$GLOBALS['arpvc_test_options']    = array();
	$GLOBALS['arpvc_test_postmeta']   = array();
	$GLOBALS['arpvc_test_transients'] = array();
	$GLOBALS['arpvc_test_routes']     = array();
	$GLOBALS['arpvc_test_enqueued']   = array( 'scripts' => array(), 'styles' => array(), 'inline' => array(), 'localized' => array() );
	$GLOBALS['arpvc_test_can']        = array();
	$GLOBALS['arpvc_test_globals']    = array(
		'user_id'     => 0,
		'ip'          => '127.0.0.1',
		'ua'          => 'Mozilla/5.0 test browser',
		'singular'    => false,
		'post_type'   => 'post',
		'home'        => false,
		'archive'     => false,
		'search'      => false,
		'admin'       => false,
		'request_method' => 'GET',
	);
	$GLOBALS['arpvc_settings_cache']  = null;
	$GLOBALS['arpvc_test_skip_next_update'] = false;
	unset( $_COOKIE );
	$_COOKIE = array();

	$_SERVER['REMOTE_ADDR']     = $GLOBALS['arpvc_test_globals']['ip'];
	$_SERVER['HTTP_USER_AGENT'] = $GLOBALS['arpvc_test_globals']['ua'];
	$_SERVER['REQUEST_METHOD']  = $GLOBALS['arpvc_test_globals']['request_method'];
}

/**
 * Tiny $wpdb stand-in used by the atomic read-count increment.
 */
class WPDB_Stub {
	public $postmeta = 'wp_postmeta';

	public function prepare( $query, ...$args ) {
		foreach ( $args as $arg ) {
			$replacement = is_numeric( $arg ) ? (string) (int) $arg : "'" . addslashes( (string) $arg ) . "'";
			$query       = preg_replace( '/%[ds]/', $replacement, $query, 1 );
		}
		return $query;
	}

	public function query( $sql ) {
		if ( ! empty( $GLOBALS['arpvc_test_skip_next_update'] ) ) {
			$GLOBALS['arpvc_test_skip_next_update'] = false;
			return 0;
		}

		if ( ! preg_match( '/post_id\s*=\s*(\d+)/', $sql, $id_match ) ) {
			return 0;
		}
		if ( ! preg_match( "/meta_key\s*=\s*'([^']+)'/", $sql, $key_match ) ) {
			return 0;
		}

		$post_id = (int) $id_match[1];
		$key     = $key_match[1];

		if ( ! isset( $GLOBALS['arpvc_test_postmeta'][ $post_id ][ $key ] ) ) {
			return 0;
		}

		$GLOBALS['arpvc_test_postmeta'][ $post_id ][ $key ] = (int) $GLOBALS['arpvc_test_postmeta'][ $post_id ][ $key ] + 1;
		return 1;
	}
}

$GLOBALS['wpdb'] = new WPDB_Stub();

/* -------------------------------------------------------------------------
 * Hooks
 * ---------------------------------------------------------------------- */

function add_action( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
	$GLOBALS['arpvc_test_hooks']['actions'][ $hook ][] = array( $callback, $priority, $accepted_args );
	return true;
}

function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
	$GLOBALS['arpvc_test_hooks']['filters'][ $hook ][] = array( $callback, $priority, $accepted_args );
	return true;
}

function add_shortcode( $tag, $callback ) {
	$GLOBALS['arpvc_test_hooks']['shortcodes'][ $tag ] = $callback;
}

function do_action( $hook, ...$args ) {
	if ( empty( $GLOBALS['arpvc_test_hooks']['actions'][ $hook ] ) ) {
		return;
	}
	foreach ( $GLOBALS['arpvc_test_hooks']['actions'][ $hook ] as $entry ) {
		call_user_func_array( $entry[0], array_slice( $args, 0, $entry[2] ) );
	}
}

/* -------------------------------------------------------------------------
 * General WordPress helpers
 * ---------------------------------------------------------------------- */

function __( $text, $domain = '' ) {
	return $text;
}

function esc_html( $value ) {
	return str_replace(
		array( '&', '<', '>', '"', "'" ),
		array( '&#038;', '&lt;', '&gt;', '&quot;', '&#039;' ),
		(string) $value
	);
}

function esc_attr( $value ) {
	return esc_html( $value );
}

function esc_url_raw( $value ) {
	return (string) $value;
}

function sanitize_text_field( $value ) {
	$value = strip_tags( (string) $value );
	$value = preg_replace( '/[\r\n\t ]+/', ' ', $value );
	return trim( $value );
}

function sanitize_key( $value ) {
	return strtolower( preg_replace( '/[^a-z0-9_\-]/i', '', (string) $value ) );
}

function sanitize_hex_color( $color ) {
	$color = trim( (string) $color );
	return preg_match( '/^#(?:[0-9a-f]{3}|[0-9a-f]{6})$/i', $color ) ? $color : null;
}

function absint( $value ) {
	return abs( (int) $value );
}

function wp_unslash( $value ) {
	return is_array( $value ) ? array_map( 'wp_unslash', $value ) : stripslashes( (string) $value );
}

function wp_validate_boolean( $value ) {
	return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
}

function number_format_i18n( $number, $decimals = 0 ) {
	return number_format( (float) $number, $decimals );
}

function shortcode_atts( $pairs, $atts, $shortcode = '' ) {
	$atts = (array) $atts;
	$out  = array();
	foreach ( $pairs as $name => $default ) {
		$out[ $name ] = array_key_exists( $name, $atts ) ? $atts[ $name ] : $default;
	}
	return $out;
}

/* -------------------------------------------------------------------------
 * Options / cache
 * ---------------------------------------------------------------------- */

function get_option( $name, $default = false ) {
	return array_key_exists( $name, $GLOBALS['arpvc_test_options'] ) ? $GLOBALS['arpvc_test_options'][ $name ] : $default;
}

function update_option( $name, $value ) {
	$old = get_option( $name, null );
	$GLOBALS['arpvc_test_options'][ $name ] = $value;
	do_action( 'update_option_' . $name, $old, $value, $name );
	return true;
}

function wp_cache_delete( $key, $group = '' ) {
	return true;
}

function get_transient( $key ) {
	return array_key_exists( $key, $GLOBALS['arpvc_test_transients'] ) ? $GLOBALS['arpvc_test_transients'][ $key ] : false;
}

function set_transient( $key, $value, $expiration = 0 ) {
	$GLOBALS['arpvc_test_transients'][ $key ] = $value;
	return true;
}

/* -------------------------------------------------------------------------
 * Users / capabilities / request context
 * ---------------------------------------------------------------------- */

function current_user_can( $capability ) {
	return ! empty( $GLOBALS['arpvc_test_can'][ $capability ] );
}

function is_user_logged_in() {
	return ! empty( $GLOBALS['arpvc_test_globals']['user_id'] );
}

function get_current_user_id() {
	return (int) $GLOBALS['arpvc_test_globals']['user_id'];
}

function wp_salt( $scheme = 'auth' ) {
	return 'arpvc-test-salt-' . $scheme;
}

function is_admin() {
	return ! empty( $GLOBALS['arpvc_test_globals']['admin'] );
}

function is_ssl() {
	return false;
}

function is_feed() {
	return false;
}

function is_preview() {
	return false;
}

function is_trackback() {
	return false;
}

function is_robots() {
	return false;
}

function wp_doing_ajax() {
	return false;
}

function wp_is_json_request() {
	return false;
}

function is_home() {
	return ! empty( $GLOBALS['arpvc_test_globals']['home'] );
}

function is_archive() {
	return ! empty( $GLOBALS['arpvc_test_globals']['archive'] );
}

function is_search() {
	return ! empty( $GLOBALS['arpvc_test_globals']['search'] );
}

function is_singular( $post_types = '' ) {
	if ( empty( $GLOBALS['arpvc_test_globals']['singular'] ) ) {
		return false;
	}

	if ( empty( $post_types ) ) {
		return true;
	}

	$types = (array) $post_types;
	return in_array( $GLOBALS['arpvc_test_globals']['post_type'], $types, true );
}

function is_front_page() {
	return false;
}

function is_shop() {
	return false;
}

function get_post_types( $args = array(), $output = 'names' ) {
	$types = array( 'post' => 'Post', 'page' => 'Page', 'product' => 'Product' );

	if ( 'objects' !== $output ) {
		return array_keys( $types );
	}

	$objects = array();
	foreach ( $types as $slug => $label ) {
		$object              = new stdClass();
		$object->labels      = new stdClass();
		$object->labels->singular_name = $label;
		$objects[ $slug ]    = $object;
	}
	return $objects;
}

class WP_Post {
	public $ID;
	public $post_status;
	public $post_title;

	public function __construct( $id = 0, $status = 'publish', $title = 'Test post' ) {
		$this->ID          = (int) $id;
		$this->post_status = $status;
		$this->post_title  = $title;
	}
}

function get_queried_object() {
	if ( empty( $GLOBALS['arpvc_test_globals']['singular'] ) ) {
		return null;
	}
	return new WP_Post( 42, 'publish' );
}

function get_queried_object_id() {
	$object = get_queried_object();
	return $object ? $object->ID : 0;
}

function get_post( $post_id = null ) {
	$id = $post_id ? absint( $post_id ) : 42;
	return $id > 0 ? new WP_Post( $id, 'publish' ) : null;
}

function post_password_required( $post = null ) {
	return false;
}

function in_the_loop() {
	return true;
}

/* -------------------------------------------------------------------------
 * Post meta
 * ---------------------------------------------------------------------- */

function get_post_meta( $post_id, $key = '', $single = false ) {
	if ( isset( $GLOBALS['arpvc_test_postmeta'][ $post_id ][ $key ] ) ) {
		return $single ? $GLOBALS['arpvc_test_postmeta'][ $post_id ][ $key ] : array( $GLOBALS['arpvc_test_postmeta'][ $post_id ][ $key ] );
	}
	return $single ? '' : array();
}

function add_post_meta( $post_id, $key, $value, $unique = false ) {
	if ( $unique && isset( $GLOBALS['arpvc_test_postmeta'][ $post_id ][ $key ] ) ) {
		return false;
	}
	$GLOBALS['arpvc_test_postmeta'][ $post_id ][ $key ] = $value;
	return true;
}

/* -------------------------------------------------------------------------
 * REST
 * ---------------------------------------------------------------------- */

class WP_REST_Request {
	private $params = array();

	public function __construct( $params = array() ) {
		$this->params = $params;
	}

	public function has_param( $key ) {
		return array_key_exists( $key, $this->params );
	}

	public function get_param( $key ) {
		return $this->params[ $key ] ?? null;
	}
}

class WP_REST_Server {
	const READABLE  = 'GET';
	const CREATABLE = 'POST';
}

class WP_Error {
	private $code;
	private $message;
	private $data;

	public function __construct( $code = '', $message = '', $data = array() ) {
		$this->code    = $code;
		$this->message = $message;
		$this->data    = $data;
	}

	public function get_error_code() {
		return $this->code;
	}

	public function get_error_message() {
		return $this->message;
	}
}

function register_rest_route( $namespace, $route, $args = array(), $override = false ) {
	$GLOBALS['arpvc_test_routes'][ $namespace . $route ] = $args;
	return true;
}

function rest_url( $path = '' ) {
	return 'http://example.test/wp-json/' . ltrim( $path, '/' );
}

function wp_create_nonce( $action = -1 ) {
	return 'test-nonce';
}

/* -------------------------------------------------------------------------
 * Asset enqueueing (used by the frontend enqueue test)
 * ---------------------------------------------------------------------- */

function plugins_url( $path = '', $plugin = '' ) {
	return 'http://example.test/wp-content/plugins/advanced-reading-progress-view-counter/' . ltrim( $path, '/' );
}

function plugin_dir_path( $file ) {
	return dirname( $file ) . '/';
}

function wp_enqueue_script( $handle, $src = '', $deps = array(), $ver = false, $in_footer = false ) {
	$GLOBALS['arpvc_test_enqueued']['scripts'][ $handle ] = array( 'src' => $src, 'deps' => $deps, 'ver' => $ver, 'footer' => $in_footer );
}

function wp_enqueue_style( $handle, $src = '', $deps = array(), $ver = false ) {
	$GLOBALS['arpvc_test_enqueued']['styles'][ $handle ] = array( 'src' => $src, 'deps' => $deps, 'ver' => $ver );
}

function wp_add_inline_style( $handle, $data ) {
	$GLOBALS['arpvc_test_enqueued']['inline'][ $handle ] = ( $GLOBALS['arpvc_test_enqueued']['inline'][ $handle ] ?? '' ) . $data;
}

function wp_localize_script( $handle, $object_name, $data ) {
	$GLOBALS['arpvc_test_enqueued']['localized'][ $handle ] = array( 'object' => $object_name, 'data' => $data );
}

function wp_set_script_translations( $handle, $domain = '', $path = '' ) {
	return true;
}

function add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $callback = '', $icon_url = '', $position = null ) {
	return $menu_slug;
}
