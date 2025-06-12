# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]
### Added
- New feature or functionality.
### Changed
- Modifications to existing features.
### Fixed
- Bug fixes.
### Removed
- Features or files that have been taken out.
### Security
- Security vulnerability fixes or improvements.

## [0.1.2] - 2025-06-12
### Changed
- Updated footer to include dynamic APP_NAME and APP_VERSION.
### Fixed
- Corrected duplicate navigation issue by consolidating nav logic into `includes/navigation.php`.
- Eliminated empty `<main></main>` tag and ensured single main content area.
- Added missing `</div>` for `#wrapper` in `includes/footer.php`.
- Removed "Select Customer" label and "Apply Filter" button from `includes/header.php`.
- Removed "Logout" link from `includes/navigation.php`.
- Restored "Dashboard Overview" and "Reports & Analytics" links in navigation.

## [0.1.1] - 2025-06-11
### Added
- Initial project structure with `index.php`, `config.php`, `functions.php`, `header.php`, `footer.php`, `navigation.php`, `views/dashboard.php`, `views/reports.php`.
- Basic routing and view rendering logic.
- Database and API status indicators.
- Theme toggle functionality.
- Customer selection dropdown and search input.
- Debug logging functionality.
### Fixed
- Resolved `APP_NAME` undefined constant error by ensuring `config.php` loads .env.
- Replaced `sanitize_url` with `urlencode`.
- Corrected stylesheet link from `style.css` to `styles.css`.
- Ensured `script.js` is correctly linked.