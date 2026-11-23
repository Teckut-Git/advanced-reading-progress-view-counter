<?php
/**
 * PHPUnit bootstrap for ARPVC unit tests.
 *
 * The plugin logic is mostly made up of pure functions. To keep the test suite
 * runnable without a full WordPress installation (e.g. in CI), this bootstrap
 * provides small, deterministic stand-ins for the WordPress functions the
 * plugin calls. Integration tests against a real WordPress install can still
 * be added separately.
 *
 * @package AdvancedReadingProgressViewCounter
 */

define( 'ABSPATH', __DIR__ . '/' );

require_once __DIR__ . '/wp-stubs.php';
require_once dirname( __DIR__ ) . '/advanced-reading-progress-view-counter.php';
