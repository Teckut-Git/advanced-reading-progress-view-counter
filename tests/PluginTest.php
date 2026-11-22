<?php
/**
 * Basic plugin tests
 */
class PluginTest extends WP_UnitTestCase {

    public function test_plugin_constants_defined() {
        $this->assertTrue(defined('ARPVC_VERSION'));
        $this->assertEquals('1.0.2', ARPVC_VERSION);
    }

    public function test_plugin_file_exists() {
        $plugin_file = dirname(__DIR__) . '/advanced-reading-progress-view-counter.php';
        $this->assertFileExists($plugin_file);
    }

    public function test_default_settings() {
        $settings = arpvc_default_settings();
        $this->assertIsArray($settings);
        $this->assertArrayHasKey('enable_progress_bar', $settings);
        $this->assertArrayHasKey('enable_view_counter', $settings);
    }

    public function test_sanitize_color() {
        $this->assertEquals('#ff0000', arpvc_sanitize_color_value('#ff0000'));
        $this->assertEquals('#0073aa', arpvc_sanitize_color_value('invalid', '#0073aa'));
    }

    public function test_sanitize_position() {
        $this->assertEquals('top', arpvc_sanitize_position('top'));
        $this->assertEquals('bottom', arpvc_sanitize_position('bottom'));
        $this->assertEquals('top', arpvc_sanitize_position('invalid'));
    }

    public function test_sanitize_thickness() {
        $this->assertEquals(3, arpvc_sanitize_thickness(3));
        $this->assertEquals(1, arpvc_sanitize_thickness(0));
        $this->assertEquals(10, arpvc_sanitize_thickness(15));
        $this->assertEquals(3, arpvc_sanitize_thickness('invalid'));
    }
}
