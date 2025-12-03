# Dealer Dashboard API Audit
**Date:** 2025-12-03
**Status:** In Progress

## Authentication Bypass
**Secret Key:** `DEALER_API_2025`
**Usage:** Add `?secret=DEALER_API_2025` to any dealer API endpoint
**Applied To:**
- `get-dealer-summary.php` ✅
- `get-dealer-summary-hybrid.php` ✅
- `get-customer-portfolio.php` ✅

## API Status

### ✅ get-dealer-summary.php
**URL:** `https://mpsm.resolutionsbydesign.us/cms/api/get-dealer-summary.php?secret=DEALER_API_2025&force=1`
**Status:** WORKING
**Response:**
- Total Customers: 82
- Total Devices: 4,190
- Data Source: live_api
- Note: Sampled from 10 customers, extrapolated

**Issues Fixed:**
1. Added required MPS API parameters (DealerCode, PageNumber, PageRows, SortColumn)
2. Fixed response parsing (API returns data directly, not nested)
3. Added auth bypass for programmatic access

### ❌ get-customer-portfolio.php
**URL:** `https://mpsm.resolutionsbydesign.us/cms/api/get-customer-portfolio.php?secret=DEALER_API_2025&force=1`
**Status:** TIMEOUT (>120s)
**Issue:** Fetches dashboard for all 82 customers sequentially from live API

**Solutions:**
1. **Short-term:** Limit to first 20 customers
2. **Medium-term:** Cache results, async background job
3. **Long-term:** Populate `mpsm_cache_devices` table via cron

## Dashboard Status
**URL:** `https://mpsm.resolutionsbydesign.us/cms/dealer.php`
**Status:** Requires login, then testing

**Test Console:**
`https://mpsm.resolutionsbydesign.us/cms/test-dealer-console.html`

## Next Steps
1. Limit portfolio API to 20 customers (pagination)
2. Test dashboard with logged-in session
3. Verify all 12 scorecard metrics display
4. Check customer portfolio table loads
