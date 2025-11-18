# Deploy Log

## 2025-11-18 20:30 UTC
- **Command:** `curl -T cms/CRON-SETUP.md ftp://ftp.resolutionsbydesign.us/cms/CRON-SETUP.md` (authenticated with existing FTP credentials)
- **Result:** Success (file overwritten on live FTP server, matching local changes)
- **Notes:** FTP upload performed per user request instead of GitHub push.

## 2025-11-18 21:05 UTC
- **Command:** `curl -T cms/api/refresh-cache-chunked.php ftp://ftp.resolutionsbydesign.us/cms/api/refresh-cache-chunked.php` (FTP credentials already stored)
- **Result:** Success (updated live chunked refresh script to the version that uses `serial_number`/`drilldown_data`)
- **Notes:** This eliminates the cron SQLSTATE 42S22 column-not-found failures.

/*
CHANGELOG
2025-11-18 Codex
- Logged FTP deployments of `cms/CRON-SETUP.md` and `cms/api/refresh-cache-chunked.php` to capture the cron instructions and cache schema fixes.
*/
