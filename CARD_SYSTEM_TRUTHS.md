# CMS Card System - Implementation Truths

**Created**: 2025-10-24
**Session**: Card System Refactoring
**Status**: Production Deployed ✅

---

## 🎨 CMS Dashboard Architecture

### Frontend Stack
**Location**: `/cms/`

**Core Files**:
- `index.php` - Main entry point with tab navigation
- `assets/js/api.js` - MPS API client wrapper
- `assets/js/app.js` - Application state and initialization
- `assets/js/card-registry.js` - Card definitions (9 pre-built cards)
- `assets/js/card-manager.js` - Card lifecycle management
- `assets/css/styles.css` - Main theme styles (light/dark)
- `assets/css/card-management.css` - Card admin UI styles

**Backend**:
- `/mps-api/` - PHP proxy to external API (handles OAuth)
- `api/card-preferences.php` - Persistent card settings storage
- `data/` - JSON storage for user preferences

---

## 🔧 Critical API Client Pattern

### ⚠️ TRUTH: Use MPSApi.request() NOT MPSApi.query()

**The Public API** (api.js line 630-675):
```javascript
const MPSApi = (function() {
    // ... internal makeRequest function ...

    return {
        // Specific methods
        getDevicesByCustomer,
        getCustomerDashboard,
        getDealerHierarchy,
        // ... more methods ...

        // Raw request method (USE THIS!)
        request: makeRequest  // ✅ This is the correct method
    };
})();
```

### ❌ WRONG: Calling MPSApi.query()
```javascript
// This method DOESN'T EXIST
fetchData: async (params) => {
    return await MPSApi.query('Device/List', {...});  // ❌ TypeError: MPSApi.query is not a function
}
```

### ✅ CORRECT: Using MPSApi.request()
```javascript
// This is the correct public method
fetchData: async (params) => {
    return await MPSApi.request('Device/List', {...});  // ✅ Works!
}
```

**Root Cause Discovered**: Oct 24, 2025
- All 9 cards were calling non-existent `MPSApi.query()`
- Cards showed "Loading data..." forever
- Fix: Global search/replace in card-registry.js
- **Critical for all future card development**

---

## 📦 Card System Architecture

### Card Registry Pattern

**File**: `cms/assets/js/card-registry.js`

**Card Definition Structure**:
```javascript
cards.set('card-id', {
    id: 'card-id',
    name: 'Card Display Name',
    description: 'What this card shows',
    category: 'Devices|Customer|Alerts|...',
    endpoint: 'API/Action/Path',
    icon: '🖨️',
    defaultVisible: true,
    defaultOrder: 1,
    requiresParams: ['dealerId', 'deviceId'],

    // Fetch data from API
    fetchData: async (params) => {
        return await MPSApi.request('Device/List', {
            FilterDealerId: params.dealerId,
            pageNumber: 1,
            pageRows: 100
        });
    },

    // Render compact snapshot view
    renderSnapshot: function(data, container) {
        container.innerHTML = `
            <div class="card-snapshot">
                <div class="snapshot-stat-grid">
                    <div class="snapshot-stat">
                        <div class="snapshot-value">${data.length}</div>
                        <div class="snapshot-label">Total</div>
                    </div>
                </div>
                <div class="card-click-hint">Click for details</div>
            </div>
        `;
    },

    // Render full detail modal
    renderModal: function(data, container) {
        // Render full table/details
    }
});
```

### Pre-Built Cards (9 Total)

| Card ID | Category | Endpoint | Requires |
|---------|----------|----------|----------|
| `printers` | Devices | Device/List | dealerId |
| `device-supplies` | Devices | Device/GetSuppliesDetailsSummary | deviceId |
| `meter-reads` | Counters | Counter/Device/List | deviceId |
| `device-alerts` | Alerts | Device/MaintenanceAlerts/List | idInstalledProduct |
| `customer-dashboard` | Customer | CustomerDashboard/Get | customerCode |
| `dealer-supplies` | Dealer | DealerSupply/List | dealerCode |
| `analytics-reports` | Analytics | Analytics/GetReportFileResult | idReport |
| `explorer-data` | Integration | Explorer/GetExplorerDatas | customerCode |
| `api-clients` | System | ApiClient/List | none |

---

## 🐛 Critical Bugs Fixed

### 1. MPSApi.query() Doesn't Exist
**Symptom**: All cards stuck on "Loading data..."
**Cause**: Calling non-existent `MPSApi.query()` method
**Fix**: Replace with `MPSApi.request()` in card-registry.js
**Commit**: bf2cc7e

### 2. Missing deviceId Parameter
**Symptom**: "Missing required parameters: deviceId"
**Cause**: CardManager.setParams() called before devices loaded
**Fix**: Load devices FIRST in loadDashboard(), then set params
**Commit**: 302a39e

### 3. Dark Theme Not Working
**Symptom**: Cards stayed light in dark theme
**Cause**: 58 hardcoded colors in card-management.css
**Fix**: Replace all hardcoded colors with CSS variables
**Commit**: d779750

### 4. No Cards Visible on Dashboard
**Symptom**: "No cards configured. Go to Admin to enable cards."
**Cause**: cms/data/ directory missing, preferences.order empty
**Fix**: Create data dir, check order array, save after reset
**Commit**: 97eb65d

---

## 📋 Admin Interface Structure

### Tab Navigation

**Tabs**:
1. **Dashboard** - Main card view
2. **Settings** - Dealer/customer defaults
3. **Card Management** - Customize dashboard cards
4. **Cache** - Cache statistics and management
5. **Traffic** - Visitor metrics

### Card Management Interface

**Layout**: Compact grid of draggable tiles (200px each)

**Features**:
- Drag tiles to reorder (2D grid)
- Click eye icon to toggle visibility (👁️/🚫)
- Visual states: visible (blue border) / hidden (dashed, faded)
- 💾 Save button - persists to backend
- 🔄 Reset button - restore defaults

**Commit**: 28ddef6

---

**Production URL**: https://mpsm.resolutionsbydesign.us/cms/
**Documentation**: See API_VERIFIED_TRUTHS.md for API details
