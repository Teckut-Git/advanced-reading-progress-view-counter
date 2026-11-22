<?php
// Bootstrap file for PHPUnit tests
// This loads WordPress test environment

if (!defined('ABSPATH')) {
    define('ABSPATH', '/tmp/wordpress/');
}

// Load WordPress test setup if available
$wp_tests_dir = getenv('WP_TESTS_DIR') ?: '/tmp/wordpress-tests-lib';
if (file_exists($wp_tests_dir . '/includes/functions.php')) {
    require_once $wp_tests_dir . '/includes/functions.php';
    tests_add_filter('muplugins_loaded', function() {
        require_once dirname(__DIR__) . '/advanced-reading-progress-view-counter.php';
    });
    require_once $wp_tests_dir . '/includes/bootstrap.php';
}
