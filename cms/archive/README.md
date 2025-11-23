# CMS Archive

This directory contains one-time scripts and legacy code that are no longer actively used but kept for reference.

## Archived Files

### One-Time Fix Scripts
These were used to fix specific issues and should not be run again:

- **fix-config-session.php** - Fixed SESSION configuration for CLI mode
- **fix-config-web-check.php** - Web endpoint to check config fix status
- **patch-config-cli.php** - Patched config.php for CLI compatibility
- **patch-oauth-credentials.php** - One-time OAuth credential update

### Testing Scripts
- **cron-heartbeat.php** - Simple CRON execution test (functionality now in cron-router.php)

## When to Use
These files are archived because:
1. They were one-time fixes that have been applied
2. Their functionality is now handled elsewhere
3. They are kept for historical reference only

## Do Not Run
These scripts should NOT be executed in production as they may:
- Overwrite current configurations
- Cause unexpected behavior
- Have already been superseded by permanent fixes

---
*Archived: 2025-11-22*
