/**
 * ARPVC Admin - Settings page enhancements
 */
(function($) {
  'use strict';

  $(document).ready(function() {
    // Color picker initialization
    $('.arpvc-color-picker').wpColorPicker();

    // Toggle view counter settings
    $('#arpvc_enable_view_counter').on('change', function() {
      $('.arpvc-view-counter-settings').toggle(this.checked);
    }).trigger('change');

    // Toggle reading progress settings
    $('#arpvc_enable_progress_bar').on('change', function() {
      $('.arpvc-progress-settings').toggle(this.checked);
    }).trigger('change');

    // Post type selector - limit selections
    $('#arpvc_post_types').on('change', function() {
      const selected = $(this).val() || [];
      if (selected.length > 5) {
        alert('Maximum 5 post types allowed');
        $(this).val(selected.slice(0, 5)).trigger('change');
      }
    });

    // Reset to defaults confirmation
    $('#arpvc_reset_defaults').on('click', function(e) {
      if (!confirm('Are you sure you want to reset all settings to defaults?')) {
        e.preventDefault();
      }
    });
  });
})(jQuery);
