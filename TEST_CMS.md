# CMS Battle Test Plan

## Setup Complete ✅

### Files Created/Modified:
1. ✅ `cms/assets/js/card-registry.js` - 9 pre-built cards
2. ✅ `cms/assets/js/card-manager.js` - Management system
3. ✅ `cms/assets/css/card-management.css` - Styling
4. ✅ `cms/api/card-preferences.php` - Backend API
5. ✅ `cms/index.php` - Integrated card system
6. ✅ `cms/assets/js/app.js` - Initialize CardManager
7. ✅ `documentation/Endpoints/EndpointSampleCatalog.html` - Grid format

### Catalog Improvements:
- ✅ Compact grid layout (3-4 cards per row)
- ✅ Collapsible groups by endpoint prefix (Account, Device, Customer, etc.)
- ✅ Shortened descriptions (60 char limit)
- ✅ Metadata badges (HTTP status, response time, item count)
- ✅ "View Data" expandable for full response
- ✅ Much less screen space per endpoint

## How to Test CMS

### 1. Open the CMS
Navigate to your local server:
```
http://localhost/MPSM-Dashboard/cms/
```
Or if using a different setup, adjust the URL accordingly.

### 2. Test Dashboard Tab

**Expected Behavior:**
- CardManager should initialize automatically
- Should see loading state
- Cards should render dynamically based on preferences
- Each card should fetch real data from API

**Cards to Test:**
1. **Customer Overview** - Should show customer stats
2. **Printer List** - Should show devices with pagination
3. **Device Supply Levels** - Should show toner levels
4. **Meter Readings** - Should show historical counter data
5. **Device Alerts** - Should show maintenance warnings

**Test Actions:**
- Click refresh icon on any card → should reload data
- Click collapse icon → should minimize card
- Verify data is displaying correctly
- Check for JavaScript errors in console (F12)

### 3. Test Admin Tab

**Expected Behavior:**
- Click "Admin" tab
- Should see "Dashboard Card Management" section
- Should see all 9 cards grouped by category

**Test Card Management:**
1. **Toggle Visibility:**
   - Click eye icon (👁️) to hide a card
   - Icon should change to 🚫
   - Click "Save Changes"
   - Go back to Dashboard tab
   - Card should be hidden

2. **Reorder Cards:**
   - Drag a card to a new position
   - Should see smooth drag feedback
   - Click "Save Changes"
   - Go back to Dashboard tab
   - Cards should be in new order

3. **Reset to Defaults:**
   - Click "Reset to Defaults"
   - Confirm the dialog
   - All cards should return to original state

### 4. Test Persistence

**Test Backend API:**
1. Make changes in admin panel
2. Click "Save Changes"
3. Refresh the entire page (F5)
4. Go to Admin tab
5. Changes should persist

**Verify Data File:**
Check that `cms/data/card-preferences.json` was created:
```bash
cat cms/data/card-preferences.json
```

Should contain:
```json
{
  "default": {
    "cards": {
      "customer-dashboard": {"visible": true, "order": 0},
      ...
    },
    "order": ["customer-dashboard", "printers", ...]
  }
}
```

### 5. Test API Integration

**Test Individual Endpoints:**

Open browser console (F12) and run:
```javascript
// Test if CardRegistry loaded
console.log('Cards:', CardRegistry.getAll().length);

// Test if CardManager loaded
console.log('Visible cards:', CardManager.getVisibleCards().length);

// Test API call
MPSApi.query('Device/List', {
    FilterDealerId: 'SZ13qRwU5GtFLj0i_CbEgQ2',
    pageNumber: 1,
    pageRows: 5
}).then(data => console.log('Device data:', data));

// Test preferences API
fetch('api/card-preferences.php')
    .then(r => r.json())
    .then(data => console.log('Preferences:', data));
```

### 6. Battle Test Scenarios

**Scenario 1: No Internet/API Down**
- Disconnect network
- Refresh dashboard
- Should show error messages gracefully
- Cards should show "Failed to load data" instead of crashing

**Scenario 2: Missing Parameters**
- Go to Admin tab
- Create data directory if needed
- Check cards with `requiresParams`
- Should show "Missing required parameters" message

**Scenario 3: Large Data Sets**
- Load printer list card with 100+ devices
- Should paginate correctly
- Table should be searchable and sortable
- No performance issues

**Scenario 4: Rapid Refreshing**
- Click refresh on multiple cards quickly
- Should handle concurrent requests
- Loading spinners should work correctly
- No race conditions

**Scenario 5: Browser Compatibility**
- Test in Chrome ✓
- Test in Firefox ✓
- Test in Edge ✓
- All features should work

### 7. Check for Errors

**JavaScript Console:**
- Should see no errors in console
- Look for successful API calls
- Check CardManager initialization message

**Network Tab:**
- API calls should return 200 status
- Check response times
- Verify data is being cached appropriately

**PHP Errors:**
- Check error logs if any
- Verify `card-preferences.php` works
- Test GET and POST requests

## Common Issues & Solutions

### Issue: Cards Not Rendering
**Solution:**
1. Check console for JavaScript errors
2. Verify all files loaded (card-registry.js, card-manager.js)
3. Check that API constants are set correctly in app.js

### Issue: Preferences Not Saving
**Solution:**
1. Check `cms/data/` directory exists
2. Verify write permissions on directory
3. Check network tab for API POST request
4. Look for PHP errors in response

### Issue: No Data Showing
**Solution:**
1. Verify API credentials in `api.js`
2. Check network tab for failed API calls
3. Verify customer code and dealer ID are set
4. Check CORS headers if needed

### Issue: Drag and Drop Not Working
**Solution:**
1. Check that cards have `draggable="true"` attribute
2. Verify event listeners are attached
3. Try in different browser
4. Check for JavaScript errors

## Performance Benchmarks

**Expected Performance:**
- Initial page load: < 3 seconds
- Card render time: < 500ms per card
- API calls: < 2 seconds each
- Drag and drop: Smooth 60fps
- Card refresh: < 1 second

**Monitor:**
```javascript
// Add to console to monitor performance
performance.measure('card-load');
```

## Success Criteria

✅ All 9 cards render successfully
✅ Card management admin panel works
✅ Preferences persist across page refreshes
✅ Drag and drop reordering works
✅ Show/hide toggle works
✅ Real API data displays correctly
✅ No JavaScript errors in console
✅ Responsive design works on mobile
✅ All buttons and interactions work
✅ Performance is acceptable

## Next Steps After Testing

1. **Add More Cards:**
   - Create cards for additional endpoints
   - Follow pattern in card-registry.js

2. **Customize Styling:**
   - Adjust colors in card-management.css
   - Add company branding

3. **Add Features:**
   - Card size options (small/medium/large)
   - Export dashboard as PDF
   - Share dashboard with other users
   - Schedule auto-refresh per card

4. **Production Deploy:**
   - Minify JavaScript files
   - Enable caching headers
   - Set up proper authentication
   - Configure production API endpoints

## Testing Checklist

- [ ] CMS opens without errors
- [ ] Dashboard tab renders cards
- [ ] All 9 cards load successfully
- [ ] API calls return data
- [ ] Admin tab shows card management
- [ ] Visibility toggle works
- [ ] Drag and drop works
- [ ] Save changes persists
- [ ] Reset to defaults works
- [ ] Preferences file created
- [ ] Refresh buttons work
- [ ] Collapse buttons work
- [ ] No console errors
- [ ] Mobile responsive
- [ ] Fast performance

---

## Quick Test Commands

```bash
# Check files exist
ls -la cms/assets/js/card*.js
ls -la cms/api/card*.php

# View preferences
cat cms/data/card-preferences.json

# Test PHP API
curl http://localhost/MPSM-Dashboard/cms/api/card-preferences.php

# Check for errors
tail -f /path/to/php/error.log
```

## Report Issues

If you encounter any issues:
1. Check console for errors
2. Check network tab for failed requests
3. Verify file paths are correct
4. Check PHP error logs
5. Report findings with screenshots
