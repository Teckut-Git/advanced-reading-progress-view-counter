# Contributing to Advanced Reading Progress & View Counter

Thank you for your interest in contributing! This document outlines the process for contributing to this plugin.

## Getting Started

1. Fork the repository
2. Clone your fork locally
3. Create a feature branch: `git checkout -b feature/your-feature-name`
4. Make your changes
5. Run tests and linting
6. Submit a Pull Request

## Development Setup

### Prerequisites
- PHP 7.4+
- Composer
- Node.js 18+ and npm
- WordPress development environment (Local, Docker, VVV, etc.)

### Install Dependencies
```bash
# PHP dependencies (for linting, static analysis, testing)
composer install

# JavaScript dependencies (for building assets)
npm install
```

### Available Commands
```bash
# PHP
composer run lint        # Run PHP_CodeSniffer (WordPress standards)
composer run lint:fix    # Auto-fix coding standard issues
composer run phpstan     # Run static analysis
composer run test        # Run PHPUnit tests

# JavaScript
npm run dev             # Development build with watch
npm run build           # Production build
npm run lint:js         # Lint JavaScript
npm run lint:css        # Lint CSS
```

## Coding Standards

### PHP
- Follow [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/)
- Use `composer run lint` to check
- Use `composer run lint:fix` to auto-fix

### JavaScript
- ES6+ syntax
- Follow WordPress JavaScript coding standards
- Use `npm run lint:js` to check

### CSS
- Follow WordPress CSS coding standards
- Use `npm run lint:css` to check

## Pull Request Process

1. **Update documentation** - Update readme.txt and any relevant docs
2. **Add tests** - Add unit tests for new functionality
3. **Run CI locally** - Ensure all checks pass:
   ```bash
   composer run lint && composer run phpstan && composer run test
   npm run build
   ```
4. **Write clear commit messages** - Follow conventional commits:
   - `feat: add new feature`
   - `fix: resolve bug`
   - `docs: update documentation`
   - `refactor: improve code structure`
   - `test: add tests`
   - `chore: maintenance tasks`
5. **Reference issues** - Link related issues in PR description

## Testing

### Unit Tests
```bash
composer run test
```

### Manual Testing Checklist
- [ ] WordPress 6.7+ (latest)
- [ ] PHP 7.4, 8.0, 8.1, 8.2, 8.3
- [ ] Default theme (Twenty Twenty-Four)
- [ ] Popular themes (Astra, GeneratePress, OceanWP)
- [ ] WooCommerce compatibility (if applicable)
- [ ] No PHP errors/warnings
- [ ] No JavaScript console errors
- [ ] Reading progress bar displays correctly
- [ ] View counter increments correctly
- [ ] Admin settings save/load properly
- [ ] Translations work

## Release Process

1. Version bump in `advanced-reading-progress-view-counter.php` and `package.json`
2. Update `readme.txt` changelog
3. Build production assets: `npm run build`
4. Create release tag: `git tag v1.x.x`
5. Push tag: `git push origin v1.x.x`
6. GitHub Actions deploys to WordPress.org automatically

## Reporting Issues

Use the issue templates:
- **Bug Report** - For bugs and regressions
- **Feature Request** - For new features and enhancements

## Code of Conduct

This project follows the [WordPress Code of Conduct](https://make.wordpress.org/community/code-of-conduct/).

## Questions?

Open a [Discussion](https://github.com/Teckut-Git/advanced-reading-progress-view-counter/discussions) or reach out to the maintainers.
