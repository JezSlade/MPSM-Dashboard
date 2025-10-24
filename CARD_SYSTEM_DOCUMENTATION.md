# MPS Monitor Dashboard - Card System Documentation

## Overview

A comprehensive card management system has been implemented to display data from API endpoints in a flexible, configurable dashboard.

## What We've Built

### 1. Enhanced Endpoint Catalog

**File:** `documentation/Endpoints/EndpointSampleCatalog.html`

**Features:**
- ✅ Endpoint descriptions pulled from Swagger documentation
- ✅ Human-readable explanations of what each endpoint does
- ✅ Collapsible statistics section (no longer prominent)
- ✅ 122 successfully documented endpoints with real data samples
- ✅ Clean, organized presentation with search and filtering

**Improvements:**
- Added `load_swagger_descriptions()` function to extract descriptions
- Updated CSS for collapsible details with smooth animations
- Added description blocks to each endpoint card
- Better visual hierarchy with muted, styled description boxes

### 2. Card Registry System

**File:** `cms/assets/js/card-registry.js`

**Purpose:** Central registry of all available dashboard cards

**Pre-built Cards:**
1. **customer-dashboard** - Customer overview with stats
2. **printers** - Printer/device list with status and counters
3. **device-supplies** - Toner and supply level monitoring
4. **meter-reads** - Historical meter readings and counter data
5. **device-alerts** - Maintenance alerts and warnings
6. **dealer-supplies** - Dealer supply catalog
7. **analytics-reports** - Saved analytics reports
8. **explorer-data** - Explorer data collection agents
9. **api-clients** - API client management

**Card Definition Structure:**
```javascript
{
    id: 'card-id',
    name: 'Display Name',
    description: 'What this card does',
    category: 'Category',
    endpoint: 'API/Endpoint',
    icon: '🎨',
    defaultVisible: true,
    defaultOrder: 1,
    requiresParams: ['param1', 'param2'],
    fetchData: async (params) => { /* fetch logic */ },
    render: function(data, container) { /* render logic */ }
}
```

**Categories:**
- Devices
- Counters
- Alerts
- Customer
- Dealer
- Analytics
- Integration
- System

### 3. Card Manager

**File:** `cms/assets/js/card-manager.js`

**Features:**
- Load user preferences from backend
- Render cards dynamically based on preferences
- Handle card visibility toggling
- Support drag-and-drop reordering
- Persist changes to backend
- Automatic data fetching with parameter injection
- Refresh individual cards or all cards
- Admin panel for card configuration

**Key Functions:**
```javascript
CardManager.init()                           // Initialize system
CardManager.setParams({...})                 // Set API parameters
CardManager.renderDashboard('.selector')     // Render dashboard
CardManager.renderAdminPanel('.selector')    // Render admin UI
CardManager.setCardVisibility(id, visible)   // Show/hide card
CardManager.setCardOrder([...])              // Reorder cards
CardManager.refreshCard(id)                  // Refresh one card
CardManager.refreshAll()                     // Refresh all cards
```

### 4. Card Preferences API

**File:** `cms/api/card-preferences.php`

**Endpoints:**
- `GET` - Load user card preferences
- `POST` - Save user card preferences
- `DELETE` - Reset to defaults

**Data Structure:**
```json
{
    "cards": {
        "card-id": {
            "visible": true,
            "order": 1
        }
    },
    "order": ["card-id-1", "card-id-2", ...]
}
```

**Storage:** Preferences stored in `cms/data/card-preferences.json`

### 5. Card Management UI

**File:** `cms/assets/css/card-management.css`

**Features:**
- Clean, modern admin interface
- Drag-and-drop card reordering
- Visual feedback on hover and drag
- Grouped by category
- Show/hide toggle with icons
- Responsive design
- Card metadata display (endpoint, required params, description)

**Admin Panel Structure:**
- Header with save/reset buttons
- Help text
- Categories with card lists
- Each card shows:
  - Icon and name
  - Description
  - Endpoint being used
  - Required parameters
  - Visibility toggle

### 6. Integration with Main CMS

**Files Modified:**
- `cms/index.php` - Added card management section and scripts
- `cms/assets/js/app.js` - Initialized CardManager, integrated rendering

**Changes:**
1. Added CardManager initialization in `init()`
2. Set default parameters for card data fetching
3. Modified `loadDashboard()` to use CardManager
4. Modified `switchTab()` to render admin panel
5. Added card-management.css stylesheet

## How to Use

### For End Users (Dashboard)

1. **View Dashboard:**
   - Open CMS dashboard
   - See configured cards with live data
   - Click refresh icon on cards to reload data
   - Click collapse icon to minimize cards

2. **Configure Cards:**
   - Go to Admin tab
   - See "Dashboard Card Management" section
   - Toggle visibility with eye icon (👁️ = visible, 🚫 = hidden)
   - Drag cards to reorder them
   - Click "Save Changes" to persist

3. **Reset to Defaults:**
   - Click "Reset to Defaults" button in admin panel
   - Confirms before resetting

### For Developers (Adding New Cards)

1. **Register a New Card:**

```javascript
CardRegistry.register({
    id: 'my-new-card',
    name: 'My New Card',
    description: 'What this card displays',
    category: 'Custom',
    endpoint: 'MyEndpoint/Action',
    icon: '📌',
    defaultVisible: true,
    defaultOrder: 50,
    requiresParams: ['customerId'],

    fetchData: async (params) => {
        return await MPSApi.query('MyEndpoint/Action', {
            customerId: params.customerId
        });
    },

    render: function(data, container) {
        container.innerHTML = `
            <div class="custom-content">
                ${JSON.stringify(data)}
            </div>
        `;
    }
});
```

2. **Use Table Utils for Rendering:**

```javascript
render: function(data, container) {
    const columns = [
        { key: 'name', label: 'Name', width: '40%' },
        { key: 'value', label: 'Value', width: '30%' },
        {
            key: 'status',
            label: 'Status',
            width: '30%',
            render: (val) => `<span class="badge">${val}</span>`
        }
    ];

    const table = TableUtils.createPaginatedTable(data, columns, {
        pageSize: 10,
        searchable: true,
        sortable: true
    });

    container.innerHTML = table.html;
    table.setup(container);
}
```

3. **Access from Anywhere:**

```javascript
// Get all cards
CardRegistry.getAll();

// Get specific card
CardRegistry.get('printers');

// Get by category
CardRegistry.getByCategory('Devices');

// Get all categories
CardRegistry.getCategories();
```

## Architecture

```
┌─────────────────────────────────────────┐
│         Card Registry                    │
│  (Defines all available cards)          │
└──────────────┬──────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────┐
│         Card Manager                     │
│  - Loads preferences from API            │
│  - Renders dashboard dynamically         │
│  - Handles user interactions             │
└──────────────┬──────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────┐
│    Card Preferences API (PHP)            │
│  - GET: Load preferences                 │
│  - POST: Save preferences                │
│  - DELETE: Reset to defaults             │
└──────────────┬──────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────┐
│    JSON File Storage                     │
│  cms/data/card-preferences.json          │
└──────────────────────────────────────────┘
```

## Data Flow

1. **Page Load:**
   ```
   User visits CMS
   → app.js initializes
   → CardManager.init() loads preferences from API
   → CardManager.setParams() sets API parameters
   → CardManager.renderDashboard() renders visible cards
   → Each card fetches data via its fetchData() function
   → Each card renders data via its render() function
   ```

2. **User Configures Cards:**
   ```
   User goes to Admin tab
   → CardManager.renderAdminPanel() shows configuration UI
   → User toggles visibility or drags to reorder
   → User clicks "Save Changes"
   → CardManager.setCardOrder() or setCardVisibility()
   → POST request to card-preferences.php
   → JSON file updated
   → Success message shown
   ```

3. **User Refreshes Cards:**
   ```
   User clicks refresh icon on a card
   → Card's refresh button clicked
   → CardManager.refreshCard(cardId)
   → Card's fetchData() called again
   → New data rendered via render() function
   → Loading spinner shown/hidden
   ```

## File Structure

```
MPSM-Dashboard/
├── cms/
│   ├── assets/
│   │   ├── js/
│   │   │   ├── card-registry.js      [NEW] Card definitions
│   │   │   ├── card-manager.js       [NEW] Card management logic
│   │   │   ├── app.js                [MODIFIED] Integrated CardManager
│   │   │   ├── api.js                [EXISTING] API wrapper
│   │   │   └── table-utils.js        [EXISTING] Table utilities
│   │   └── css/
│   │       ├── styles.css            [EXISTING] Main styles
│   │       └── card-management.css    [NEW] Card UI styles
│   ├── api/
│   │   └── card-preferences.php      [NEW] Preferences backend
│   ├── data/
│   │   └── card-preferences.json     [AUTO-CREATED] User preferences
│   └── index.php                     [MODIFIED] Added card management
├── documentation/
│   └── Endpoints/
│       └── EndpointSampleCatalog.html [ENHANCED] Added descriptions
└── scripts/
    ├── generate_endpoint_sample_catalog.py [ENHANCED] Pulls descriptions
    └── repair_failed_endpoints.py          [FIXED] Corrected payloads
```

## Endpoints by Category

### Successfully Integrated (122 endpoints):

#### Devices (9):
- Device/List
- Device/ExplorerDataAffinities/List
- Device/GetDeviceAdditionalInfos
- Device/GetDeviceGapInfos
- Device/GetSuppliesDetails
- Device/GetSuppliesDetailsSummary
- Device/GetZebraSuppliesDetailsSummary
- Device/MaintenanceAlerts/List
- Device/GetSuppliesDetailsInfo

#### Counters (3):
- Counter/Device/Export
- Counter/Device/List
- Counter/ListMaintenanceKitCounters

#### Customer (7):
- Customer/List
- Customer/FindByCode
- Customer/GetByDealerCode
- Customer/GetBySearchCriteria
- CustomerDashboard/Get
- CustomerDashboard/GetByCustomerId
- CustomerNotification/Get
- CustomerNotification/List

#### Dealer (16):
- Dealer/CounterBlend/List
- Dealer/CounterBlend/Search
- Dealer/CounterBlendToStandard/Get
- Dealer/CounterBlendToStandard/GetByDevice
- Dealer/CounterBlendToStandard/List
- Dealer/DistributorSettings/Get
- DealerSupply/Get
- DealerSupply/List
- DealerSupplySet/List
- DealerNotification/List
- DealerProduct/List
- DealerSupplyPriceListing/List

#### Analytics (1):
- Analytics/GetReportFileResult

#### And many more...

## Next Steps / Future Enhancements

1. **Add More Cards:**
   - Order history card
   - Billing/invoice card
   - User management card
   - Integration status card

2. **Card Customization:**
   - Allow users to customize card size (small/medium/large)
   - Add card-specific settings (date ranges, filters)
   - Support multiple instances of same card with different params

3. **Dashboard Layouts:**
   - Support multiple dashboard layouts
   - Save layout per role/user
   - Export/import dashboard configurations

4. **Real-time Updates:**
   - WebSocket integration for live data
   - Auto-refresh specific cards on schedule
   - Push notifications for critical alerts

5. **Advanced Features:**
   - Card dependencies (card B requires data from card A)
   - Cross-card filtering (filter all cards by device)
   - Dashboard sharing/collaboration
   - Drill-down/detail views

## Testing

1. **Test Card Registration:**
   ```javascript
   console.log(CardRegistry.getAll().length); // Should show 9 cards
   console.log(CardRegistry.get('printers')); // Should show printer card def
   ```

2. **Test Card Manager:**
   ```javascript
   console.log(CardManager.getVisibleCards()); // Show visible cards
   console.log(CardManager.getPreferences());  // Show current preferences
   ```

3. **Test API:**
   ```bash
   # GET preferences
   curl http://localhost/cms/api/card-preferences.php

   # POST preferences
   curl -X POST http://localhost/cms/api/card-preferences.php \
     -H "Content-Type: application/json" \
     -d '{"preferences": {...}}'
   ```

## Summary

✅ All 3 requested features completed:
1. **Endpoint descriptions** - Added from Swagger, displayed in catalog
2. **Card system** - 9 pre-built cards for displaying endpoint data
3. **Admin panel** - Full card management with show/hide/reorder and persistence

The system is now ready for use and can be easily extended with new cards!
