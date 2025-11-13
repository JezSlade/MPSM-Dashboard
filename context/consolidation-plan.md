# Dashboard PHP File Consolidation Plan

**Generated:** 2025-11-13
**Status:** Pending Implementation

## Files to Consolidate

### Diagnostic/Monitoring Dashboards (8 files → 1)

**Keep:**
- `cms/api/cache-status-report.php` (Enhanced version with logs & errors)

**Deprecate/Archive:**
- `cms/show-drilldown-count.php` - Simple HTML view, duplicates cache-status-report
- `cms/check-counts.php` - CLI version, subset of cache-status-report
- `cms/api/diagnose-cache-issue.php` - RCA tool, overlaps with cache-status-report
- `cms/api/cache-audit.php` - Older audit tool, replaced by cache-status-report
- `cms/system-diagnostics.php` - Large HTML dashboard, consider consolidating into command-center

### Cache Refresh Scripts (4 files → 1)

**Keep:**
- `cms/api/refresh-cache-enhanced.php` (Transaction-safe, incremental, with retry logic)

**Deprecate/Archive:**
- `cms/api/refresh-cache.php` - Old version without transaction safety
- `cms/api/refresh-cache-v2.php` - Intermediate version
- `cms/api/refresh-cache-cron.php` - Wrapper, just call enhanced version

### Device Count/Population Tools (5 files → 2)

**Keep:**
- `cms/api/force-populate-all-drilldowns.php` - Manual recovery tool (keep for emergencies)
- `cms/api/get-cached-devices.php` - Primary read endpoint

**Deprecate/Archive:**
- `cms/api/populate-all-devices-now.php` - Duplicates force-populate functionality
- `cms/api/count-all-api-devices-simple.php` - Use cache-status-report instead
- `cms/api/get-actual-api-device-count.php` - Use cache-status-report instead
- `cms/api/quick-device-count.php` - Use cache-status-report instead

### Device Fetch Endpoints (4 files → 2)

**Keep:**
- `cms/api/get-devices.php` - Legacy endpoint for backward compat
- `cms/api/get-cached-devices.php` - New optimized endpoint

**Deprecate/Archive:**
- `cms/api/get-all-customers-devices.php` - Use get-cached-devices with filter
- `cms/api/get-all-devices-all-customers.php` - Use get-cached-devices
- `cms/api/get-deleted-devices.php` - Use get-cached-devices with is_uninstalled filter

## Implementation Plan

1. **Phase 1: Mark files as deprecated** (Add headers)
2. **Phase 2: Redirect users to new endpoints** (Add HTTP redirects or notices)
3. **Phase 3: Archive to `/archive/deprecated-YYYY-MM-DD/`** (After 30 days)
4. **Phase 4: Update all references** (Search codebase for calls to deprecated files)

## Files to Keep As-Is

- `cms/api/device-list.php` - Used by device lifecycle feature
- `cms/api/device-create.php` - CRUD operations
- `cms/api/device-update.php` - CRUD operations
- `cms/api/device-delete.php` - CRUD operations
- `cms/api/get-device-deep-dive.php` - Primary drill-down endpoint
- `cms/api/get-device-details.php` - Simple detail view
- `cms/api/get-device-panel-history.php` - Panel history specific
- `cms/api/search-devices.php` - Search functionality
- `cms/api/clear-cache.php` - Admin utility

## Net Result

**Before:** 26 cache/device-related PHP files
**After:** 11 core files + 15 archived
**Reduction:** 58% fewer active files to maintain
