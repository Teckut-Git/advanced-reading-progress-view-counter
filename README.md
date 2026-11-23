# Advanced Reading Progress & View Counter

A lightweight WordPress plugin that displays a reading progress bar and tracks post views with customizable settings.

## Features

- 📖 **Reading Progress Bar** - Visual indicator of reading progress
- 👁️ **View Counter** - Track and display post view counts
- ⚙️ **Highly Customizable** - Colors, positions, thickness, post types
- 🎨 **Color Picker** - Native WordPress color picker integration
- 🌐 **Translation Ready** - Full i18n support
- 📱 **Responsive** - Works on all devices
- ⚡ **Performance Optimized** - Minimal footprint, uses `requestAnimationFrame`

## Installation

### From WordPress.org
1. Go to **Plugins → Add New**
2. Search for "Advanced Reading Progress & View Counter"
3. Click **Install Now** → **Activate**

### Manual Installation
1. Download the latest release
2. Upload `advanced-reading-progress-view-counter` folder to `/wp-content/plugins/`
3. Activate via **Plugins** menu

## Requirements

- WordPress 6.7+
- PHP 7.4+
- WooCommerce 6.5+ (for WC compatibility features)

## Configuration

Navigate to **Settings → Reading Progress** to configure:

### Progress Bar Settings
- **Enable/Disable** progress bar
- **Position**: Top or Bottom of viewport
- **Color**: Custom color via color picker
- **Thickness**: 1-10px
- **Post Types**: Select which post types show the bar

### View Counter Settings
- **Enable/Disable** view counter
- **Display Position**: Before/After content, Manual (shortcode)
- **Count Type**: Unique views or total views
- **Post Types**: Select which post types track views

## Shortcodes

```php
// Display view counter manually
[arpvc_view_counter]

// Display reading progress bar manually
[arpvc_progress_bar]
```

## Template Tags

```php
// View counter
if (function_exists('arpvc_get_view_count')) {
    echo arpvc_get_view_count(get_the_ID());
}

// Progress bar
if (function_exists('arpvc_get_progress_bar')) {
    echo arpvc_get_progress_bar();
}
```

## Filters & Hooks

### Filters
- `arpvc_progress_bar_color` - Modify progress bar color
- `arpvc_progress_bar_position` - Modify position (top/bottom)
- `arpvc_view_count` - Modify view count output
- `arpvc_post_types` - Modify supported post types

### Actions
- `arpvc_before_progress_bar` - Before progress bar output
- `arpvc_after_progress_bar` - After progress bar output
- `arpvc_view_counter_increment` - When view is counted

## Development

### Setup
```bash
# Clone repository
git clone https://github.com/Teckut-Git/advanced-reading-progress-view-counter.git

# Install PHP test/lint dependencies
composer install

# Verify the shipped assets are present
npm run build

# Run tests
composer run test

# Lint and static analysis
composer run lint
composer run phpstan
```

> **Note:** the compiled files in `build/` are the canonical, WP.org-shipped
> assets. The older files in `src/` do not reproduce them, so `npm run build`
> verifies the shipped assets instead of overwriting them. Update files in
> `build/` directly (and keep the WP.org copy in sync) when changing the UI.

### Project Structure
```
├── advanced-reading-progress-view-counter.php  # Main plugin file
├── readme.txt                                   # WordPress.org readme
├── composer.json                                # PHP dependencies
├── package.json                                 # Asset verification script
├── build/                                       # Shipped assets (canonical)
│   ├── css/
│   └── js/
├── src/                                         # Legacy reference sources
│   ├── js/
│   └── css/
├── scripts/verify-assets.js                     # Checks shipped assets exist
├── languages/                                   # Translation files
├── .github/
│   ├── workflows/                               # GitHub Actions CI/CD
│   ├── ISSUE_TEMPLATE/                          # Issue templates
│   └── PULL_REQUEST_TEMPLATE.md                 # PR template
└── tests/                                       # PHPUnit tests + WP stubs
```

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

## Changelog

See [readme.txt](readme.txt) for full changelog.

## License

GPL-2.0-or-later © [Teckut](https://github.com/Teckut-Git)

## Support

- [WordPress.org Support Forum](https://wordpress.org/support/plugin/advanced-reading-progress-view-counter/)
- [GitHub Issues](https://github.com/Teckut-Git/advanced-reading-progress-view-counter/issues)
- [GitHub Discussions](https://github.com/Teckut-Git/advanced-reading-progress-view-counter/discussions)
