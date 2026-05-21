# Notification System - Quick Reference

## System Status
✅ **OPERATIONAL** - 132 active notifications | 97.8% test pass rate

## Key URLs

### Live Dashboards
- Desktop: https://mpsm.resolutionsbydesign.us/cms/index.php
- Mobile: https://mpsm.resolutionsbydesign.us/cms/mobile.php

### Diagnostic Tools
- Full test suite: https://mpsm.resolutionsbydesign.us/cms/regression-test-suite.php
- API test: https://mpsm.resolutionsbydesign.us/cms/test-notifications-api.php
- Rule analysis: https://mpsm.resolutionsbydesign.us/cms/deep-rule-analysis.php
- Trigger count verify: https://mpsm.resolutionsbydesign.us/cms/verify-trigger-counts.php
- Search test: https://mpsm.resolutionsbydesign.us/cms/test-search-simple.php

## Active Rules

| Rule | Pattern | Threshold | Type |
|------|---------|-----------|------|
| Repeated JAM Alerts | 808 | 3x in 24h | same_device ✅ |
| Persistent Sensor Failures | 807 | 5x in 48h | same_device ✅ |
| Widespread Communication Loss | 80% | 10x in 1h | same_alert ⚠️ |

⚠️ Rule #40 has inaccurate trigger counts - recommend disable

## Database Tables
- `mpsm_dashboard_notifications` - 132 active notifications
- `mpsm_notification_rules` - 3 active rules
- `mpsm_alert_definitions` - Alert code mappings
- `mpsm_panel_messages` - Source data
- `mpsm_cache_devices` - **Empty** (search slow)

## Key Files
- **Engine**: `mps-api/callbacks/command-center-engine.php`
- **API**: `cms/api/command-center.php`
- **Desktop JS**: `cms/assets/hero-notifications.js`
- **Mobile JS**: `cms/assets/mobile.js`

## Known Issues
1. **Trigger counts inaccurate** for Rule #40 (same_alert type)
   - Shows system-wide count instead of device-specific
   - Fix: Change frequency_type or split count fields

2. **Device cache empty**
   - Search falls back to API (slower but works)
   - Run cache population to improve

## Quick Troubleshooting

**No notifications showing?**
1. Check customer code: `check-customer-session.php`
2. Does customer have notifications for that code?
3. Browser console errors?

**Search not working?**
1. Run `test-search-simple.php`
2. Check if cache empty (expected)
3. API fallback should work (slower)

**Wrong trigger counts?**
- This is a known issue with Rule #40
- Counts are system-wide, not device-specific
- See NOTIFICATION-SYSTEM-CONTEXT.md for details

## Regression Testing
Run: https://mpsm.resolutionsbydesign.us/cms/regression-test-suite.php

Expected: 45/46 tests pass (cache empty is expected)

## Customer Filtering
Notifications are **per-customer**:
- User sees only their customer's notifications
- Customer code from session (`$_SESSION['customer_code']`)
- Test customer W9OPXL0YDK has 22 active notifications

## Performance Benchmarks
- Notification API: <100ms
- Device search (cache): <100ms (when populated)
- Device search (API): 1-2s (current)
- Auto-refresh: Every 30s

## Documentation
Full details: [NOTIFICATION-SYSTEM-CONTEXT.md](NOTIFICATION-SYSTEM-CONTEXT.md)
