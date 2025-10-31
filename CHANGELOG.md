# Changelog

All notable changes to the MPSM Dashboard project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

### Added
- Project documentation suite (Constitution, ADRs, Pain Points, Onboarding)
- PR and Handoff issue templates

---

## [1.3.0] - 2025-10-31

### Added
- **Global Device Search**: Header search bar searches all 3,306 devices across all customers
  - Auto-complete dropdown with top 10 results
  - Searches Equipment ID, Serial Number, Model, Customer, External ID, Asset Number
  - Automatic pagination through all API pages
  - 1-minute cache to improve performance
  - Loading indicator during first search
- **CSS Theme Support**: Missing CSS variables added for proper theming
  - Light and dark theme fully supported
  - Primary color, card background, hover states now work correctly

### Changed
- **Pagination Standardization**: All tables now show 50 rows per page (was inconsistent 5/20/25)
  - Endpoint Catalog: 25 → 50 rows
  - Offline Devices: 25 → 50 rows
  - Print Volume: 5 → 50 rows
  - Integration Connectors: 20 → 50 rows
  - Export Library: 25 → 50 rows
  - OAuth Clients: 25 → 50 rows
  - Dealer Supplies: 25 → 50 rows

### Fixed
- **Search Scope**: Global search now searches entire device database, not just current customer
- **Search Fields**: Search now includes ExternalIdentifier and AssetNumber fields
- **CSS Styling**: Global search bar now matches site theme (light/dark mode)
- **API Enhancement**: Added `allCustomers=true` parameter to search across all customers

### Documentation
- Created SEARCH_FIX_REPORT.md with implementation details
- Updated DEPLOYMENT_COMPLETE.md with verification results
- Created comprehensive battle test report

---

## [1.2.0] - 2025-10-31

### Fixed
- **Bug #1 - Race Conditions**: Added loading flags to prevent concurrent device/alert requests
  - Prevents duplicate API calls when clicking refresh rapidly
  - State corruption resolved
  - UI flicker eliminated
- **Bug #2 - Device Lookup**: Verified device lookup map working correctly (no changes needed)
- **Bug #3 - Alert Summary**: Changed null initialization to empty object `{}`
  - Prevents "Cannot read properties of null" errors
- **Bug #4 - Card Sanitization**: Deferred validation if card registry not loaded yet
  - Prevents race condition on page load
- **Bug #5 - API Error Handling**: Improved error messages and timeout handling
  - Reduced timeout from 30s to 15s
  - Added HTTP status code checking
  - Better error messages for troubleshooting
- **Bug #6 - Token Handling**: Reset static variables on OAuth token failure
  - Prevents stuck state after token expiration
  - Automatic recovery on next request
- **Bug #7 - Total Count**: Improved total count extraction with fallback logic
  - Handles multiple API response formats
  - Falls back to device count if total missing
  - Logging for troubleshooting
- **Bug #8 - Export Download**: Enhanced download trigger with 3-tier strategy
  - Strategy 1: Direct link.click()
  - Strategy 2: dispatchEvent(MouseEvent)
  - Strategy 3: window.open() with instructions
  - Console logging for debugging

### Changed
- **API Timeout**: Reduced from 30 seconds to 15 seconds
- **Error Messages**: More specific error messages for API failures

### Documentation
- Created BUG_FIX_TEST_PLAN.md
- Created LIVE_SITE_TEST_REPORT.md
- Created BATTLE_TEST_REPORT_FINAL.md

---

## [1.1.0] - 2025-10-30

### Added
- **Supply Alerts Modal**: First column now shows consistent Equipment ID format
  - Uses global `getEquipmentIdFromAlert()` function
  - Matches device list format
  - Fallback: AssetNumber → SerialNumber → 'N/A'

### Changed
- **Equipment ID Resolution**: Centralized logic for consistent display
- **Modal Layouts**: Improved consistency across all modals

---

## [1.0.0] - 2024-05-06

### Added
- Initial release of MPSM Dashboard
- Device list with pagination
- Supply alerts tracking
- Print volume metrics
- Dashboard card system
- OAuth authentication with HP SDS API
- Dark/light theme support
- Export functionality
- Modal system for device details
- Integration connector management
- OAuth client management
- Dealer supply tracking

### Architecture
- 3-tier architecture: Browser → CMS → mps-api → HP SDS API
- PHP backend with session management
- JavaScript frontend with dynamic updates
- CSS variables for theming
- FTP deployment pipeline

---

## Version History Summary

| Version | Date | Highlights |
|---------|------|------------|
| 1.3.0 | 2025-10-31 | Global search pagination, CSS fixes, standardized pagination |
| 1.2.0 | 2025-10-31 | 8 bug fixes, improved API error handling, export download reliability |
| 1.1.0 | 2025-10-30 | Supply alerts equipment ID consistency |
| 1.0.0 | 2024-05-06 | Initial release |

---

## Upgrade Guide

### Upgrading to 1.3.0

**No breaking changes.** All changes are additive or internal improvements.

**New Features Available**:
- Use global search bar in header to find devices across all customers
- All tables now show 50 rows per page

**Browser Cache**: Hard refresh (Ctrl+Shift+R) to see styling updates

---

### Upgrading to 1.2.0

**No breaking changes.** All fixes are backward compatible.

**API Changes** (internal only):
- Timeout reduced to 15s (may see faster failures instead of long waits)
- Better error messages (check console for details)

**Browser Cache**: Refresh page to see changes

---

## Changelog Maintenance

### When to Update

Update CHANGELOG.md when:
- Adding a new user-facing feature
- Fixing a bug that affects users
- Changing behavior of existing features
- Deprecating features
- Making breaking changes
- Releasing a new version

### Categories

Use these categories:
- **Added**: New features
- **Changed**: Changes to existing functionality
- **Deprecated**: Features marked for removal
- **Removed**: Removed features
- **Fixed**: Bug fixes
- **Security**: Security fixes

### Format

```markdown
## [Version] - YYYY-MM-DD

### Added
- Feature description with brief explanation
- Use bullet points for multiple items

### Changed
- What changed and why (if not obvious)

### Fixed
- Bug description and how it was fixed
```

---

## License

This project is proprietary software for SYSTEL BUSINESS EQUIPMENT - FAYETTEVILLE.

---

*Last Updated: 2025-10-31*
