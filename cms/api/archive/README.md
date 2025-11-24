# Archived Cache/Device Files

**Date:** 2025-11-14
**Reason:** Codebase consolidation to reduce maintenance burden

## Files Archived

### Old Cache Refresh Scripts (3 files)
These have been superseded by the new chunked refresh system which works within web server timeout constraints.

1. **refresh-cache.php** - Original version without transaction safety
2. **refresh-cache-v2.php** - Intermediate version with partial improvements
3. **refresh-cache-cron.php** - Wrapper script for enhanced version

**Replacement:** Use `refresh-cache-chunked.php` + `refresh-cache-runner.php` for reliable cache refresh that works within any web server timeout.

### Diagnostic/Monitoring Dashboards (3 files)
Superseded by cache-status-report.php which provides comprehensive diagnostics.

4. **show-drilldown-count.php** - Simple HTML view
5. **check-counts.php** - CLI version
6. **diagnose-cache-issue.php** - RCA tool

**Replacement:** Use `cache-status-report.php` for all diagnostics and monitoring.

### Device Count/Population Tools (4 files)
Replaced by cache-status-report.php for counts, force-populate-all-drilldowns.php for recovery.

7. **populate-all-devices-now.php** - Duplicates force-populate functionality
8. **count-all-api-devices-simple.php** - Simple count tool
9. **get-actual-api-device-count.php** - API device count
10. **quick-device-count.php** - Quick count tool

**Replacement:** Use `cache-status-report.php` for counts, `force-populate-all-drilldowns.php` for recovery.

### Redundant Device Fetch Endpoints (3 files)
Replaced by get-cached-devices.php with appropriate filters.

11. **get-all-customers-devices.php** - Use get-cached-devices with filter
12. **get-all-devices-all-customers.php** - Use get-cached-devices
13. **get-deleted-devices.php** - Use get-cached-devices with is_uninstalled filter

**Replacement:** Use `get-cached-devices.php` with appropriate query parameters.

## Active Cache Refresh Files

- `refresh-cache-enhanced.php` - Long-running version (still used but subject to timeout)
- `refresh-cache-chunked.php` - NEW: Chunked processor (works with any timeout)
- `refresh-cache-runner.php` - NEW: Auto-refresh orchestrator UI
- `cache-status-report.php` - Primary diagnostic/monitoring tool
- `force-populate-all-drilldowns.php` - Emergency recovery tool

## Notes

- Files are preserved for historical reference
- Do NOT delete archived files
- All active functionality is maintained in non-archived files
- See `context/consolidation-plan.md` for full cleanup strategy
