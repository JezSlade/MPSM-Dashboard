# Quick Start: System Diagnostics

## Immediate Actions

### 1. View System Health Dashboard
**URL**: https://mpsm.resolutionsbydesign.us/cms/system-diagnostics.php

This page shows:
- ✅ Overall system health status
- ✅ **Exact count of devices with drill-down cache**
- ✅ Cache coverage percentage
- ✅ Breakdown of the 388 invalid JSON errors
- ✅ Panel message statistics

### 2. Key Questions Answered

#### "How many devices have full drill-down cached in our DB?"
**Answer**: Look at the "Drill-Down Cache" card → "With Drill-Down Cache" field

Expected: 80-95% of total devices (based on your cache refresh system)

#### "Why are there 388 invalid JSON payload errors?"
**Answer**: Look at the "Invalid JSON Analysis" card

The errors are categorized as:
- **Actually Invalid JSON**: Malformed syntax (needs investigation)
- **Valid JSON, Wrong Type**: Legitimate rejections (e.g., `null`, strings, numbers)
- **Empty Bodies**: Network/request issues

## What Was Fixed

### 1. Better Error Messages
**Before**: Generic "Invalid JSON payload"

**Now**:
- "Invalid JSON: Syntax error" (actually malformed)
- "Invalid JSON payload: Expected object/array, received NULL" (valid JSON, wrong type)
- "Invalid JSON payload: Expected object/array, received string" (valid JSON, wrong type)

This helps distinguish legitimate rejections from actual problems.

### 2. Comprehensive Diagnostics
New API and UI provide:
- Real-time system health monitoring
- Detailed cache statistics
- Error categorization and analysis
- Actionable recommendations

## Files Created

1. **[cms/system-diagnostics.php](cms/system-diagnostics.php)** - Visual dashboard (web UI)
2. **[cms/api/get-system-diagnostics.php](cms/api/get-system-diagnostics.php)** - API endpoint
3. **[DIAGNOSTICS_IMPROVEMENTS.md](DIAGNOSTICS_IMPROVEMENTS.md)** - Full documentation

## Files Modified

1. **[mps-api/callbacks/panel-message.php](mps-api/callbacks/panel-message.php)** - Enhanced validation (lines 39-54)

## Next Steps

1. ✅ Open https://mpsm.resolutionsbydesign.us/cms/system-diagnostics.php
2. ✅ Review the health status (should be "EXCELLENT")
3. ✅ Note the drill-down cache count
4. ✅ Check the invalid JSON breakdown
5. ✅ Follow any recommendations shown

## Understanding the 388 Errors

Most of these errors are likely:
- **Old test data** from initial webhook setup
- **Legitimate rejections** of non-object/array JSON payloads
- **Network glitches** that sent incomplete data

The system now categorizes them clearly so you can see if they're:
1. **Actual problems** that need fixing, OR
2. **Expected rejections** working as designed

## Health Check

Visit the diagnostics page and verify:
- [ ] Health status is "EXCELLENT" or "WARNING"
- [ ] Drill-down cache coverage is > 70%
- [ ] Panel messages are being received (check "Last Message")
- [ ] Error rate is < 30% of total callbacks

If any issues are detected, the system will show recommendations.

---

**Status**: ✅ All improvements deployed and ready to use.
