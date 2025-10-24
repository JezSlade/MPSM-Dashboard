# Live Battle Test - Production CMS

## ✅ Deployment Status: LIVE

**Deployed**: October 24, 2025 at 8:09 PM UTC (commit 4278221)
**Status**: GitHub Actions deployment successful
**Production URL**: https://mpsm.resolutionsbydesign.us/

---

## 🎯 What's Now Live

### New Files Deployed:
- ✅ `cms/assets/js/card-registry.js` - Verified live
- ✅ `cms/assets/js/card-manager.js` - Deployed
- ✅ `cms/assets/css/card-management.css` - Deployed
- ✅ `cms/api/card-preferences.php` - **TESTED: Returns default preferences!**
- ✅ `documentation/Endpoints/EndpointSampleCatalog.html` - Grid format deployed

### Modified Files:
- ✅ `cms/index.php` - Card management section added
- ✅ `cms/assets/js/app.js` - CardManager initialization
- ✅ `scripts/generate_endpoint_sample_catalog.py` - Modal details
- ✅ `scripts/repair_failed_endpoints.py` - Fixed payloads

---

## 🧪 Battle Test Checklist

### Test 1: Endpoint Catalog Grid ⬜
**URL**: https://mpsm.resolutionsbydesign.us/documentation/Endpoints/EndpointSampleCatalog.html

**Check:**
- [ ] Grid layout displays (3-4 cards per row)
- [ ] Collapsible groups by category (Account, Device, Customer, etc.)
- [ ] Click "View Details" button on any endpoint
- [ ] Modal popup opens full-screen
- [ ] Dark blur backdrop appears
- [ ] Two tabs visible: "Response Data" and "Parameters"
- [ ] Switch between tabs works
- [ ] Close with X button works
- [ ] Close with Escape key works
- [ ] Body scroll disabled when modal open
- [ ] Data is readable and properly formatted

**Expected Issues:**
- None - This is pure HTML/CSS/JS

---

### Test 2: CMS Dashboard - Card System ⬜
**URL**: https://mpsm.resolutionsbydesign.us/cms/

#### Dashboard Tab

**Check Loading:**
- [ ] Open browser console (F12) - Check for JavaScript errors
- [ ] Page loads without errors
- [ ] CardManager initializes (check console for "CardManager" messages)
- [ ] Cards start rendering

**Expected Cards (5 visible by default):**
- [ ] 🏢 Customer Overview card
- [ ] 🖨️ Printer List card
- [ ] 💧 Device Supply Levels card
- [ ] 📊 Meter Readings card
- [ ] ⚠️ Device Alerts card

**For Each Card:**
- [ ] Card header shows icon and title
- [ ] Refresh button (🔄) visible
- [ ] Collapse button (▼) visible
- [ ] Loading state appears initially
- [ ] Data populates or shows error message
- [ ] Click refresh button → data reloads
- [ ] Click collapse → card body hides

**Expected Issues:**
- Cards may show "Missing required parameters" if API params not set correctly
- Some cards may show empty data (this is normal if no data exists)
- Network errors if API is down

---

### Test 3: Admin Panel - Card Management ⬜
**URL**: https://mpsm.resolutionsbydesign.us/cms/ → Admin Tab

**Check UI:**
- [ ] Click "Admin" tab
- [ ] "Dashboard Card Management" section appears
- [ ] All 9 cards listed by category:
  - Customer (1 card)
  - Devices (2 cards)
  - Counters (1 card)
  - Alerts (1 card)
  - Dealer (1 card)
  - Analytics (1 card)
  - Integration (1 card)
  - System (1 card)
- [ ] Each card shows:
  - Drag handle (⋮⋮)
  - Icon and name
  - Description
  - Endpoint name
  - Required parameters
  - Visibility toggle (👁️ or 🚫)

**Check Visibility Toggle:**
- [ ] Click eye icon on "Dealer Supply Catalog" (currently hidden)
- [ ] Icon changes from 🚫 to 👁️
- [ ] Click "Save Changes"
- [ ] Success notification appears
- [ ] Go to Dashboard tab
- [ ] "Dealer Supply Catalog" card now visible

**Check Reordering:**
- [ ] Go back to Admin tab
- [ ] Drag "Device Alerts" card to top position
- [ ] Visual feedback during drag (opacity, transform)
- [ ] Drop in new position
- [ ] Click "Save Changes"
- [ ] Go to Dashboard tab
- [ ] Cards in new order

**Check Reset:**
- [ ] Go back to Admin tab
- [ ] Click "Reset to Defaults"
- [ ] Confirm dialog appears
- [ ] Click OK
- [ ] All cards return to original state
- [ ] Success notification appears

**Expected Issues:**
- Drag-and-drop may not work in some browsers (try Chrome)
- Save may fail if PHP backend has permission issues

---

### Test 4: Persistence ⬜

**Check Preferences API:**
- [ ] Open new browser tab
- [ ] Navigate to: https://mpsm.resolutionsbydesign.us/cms/api/card-preferences.php
- [ ] JSON response shows default preferences
- [ ] Structure includes "cards" and "order" properties

**Check Persistence:**
- [ ] Make changes in Admin panel (hide a card)
- [ ] Save changes
- [ ] Refresh page (F5)
- [ ] Go to Admin tab
- [ ] Changes persisted (card still hidden)
- [ ] Go to Dashboard tab
- [ ] Card not showing

**Check Data File:**
- [ ] SSH to server or FTP
- [ ] Check if `cms/data/card-preferences.json` exists
- [ ] Verify JSON structure

**Expected Issues:**
- If `cms/data/` directory doesn't exist, save will fail
- If PHP doesn't have write permissions, save will fail

---

### Test 5: API Integration ⬜

**Browser Console Tests:**
```javascript
// Test 1: Verify CardRegistry loaded
console.log('Total cards:', CardRegistry.getAll().length);
// Expected: 9

// Test 2: Verify CardManager loaded
console.log('Visible cards:', CardManager.getVisibleCards().length);
// Expected: 5 (default visible)

// Test 3: Get card details
console.log('Printer card:', CardRegistry.get('printers'));
// Expected: Object with card definition

// Test 4: Test API call
MPSApi.query('Device/List', {
    FilterDealerId: 'SZ13qRwU5GtFLj0i_CbEgQ2',
    pageNumber: 1,
    pageRows: 5
}).then(data => console.log('Devices:', data));
// Expected: Array of device objects

// Test 5: Test preferences API
fetch('api/card-preferences.php')
    .then(r => r.json())
    .then(data => console.log('Prefs:', data));
// Expected: Preferences object

// Test 6: Refresh a card
CardManager.refreshCard('printers');
// Expected: Card reloads data
```

**Expected Issues:**
- `CardRegistry` or `CardManager` undefined = Script not loaded
- API errors = Network/CORS issues
- Empty data = Normal if no data exists

---

### Test 6: Performance ⬜

**Metrics to Check:**
- [ ] Initial page load < 5 seconds
- [ ] Card render < 1 second per card
- [ ] API calls < 3 seconds each
- [ ] Modal opens instantly
- [ ] Drag-and-drop smooth (no lag)
- [ ] No memory leaks (check DevTools Memory)

**Network Tab:**
- [ ] Open DevTools → Network tab
- [ ] Refresh page
- [ ] Check API calls to `/mps-api/query`
- [ ] Verify 200 status codes
- [ ] Check response times
- [ ] Verify caching working (304 responses)

---

### Test 7: Error Handling ⬜

**Disconnect Network:**
- [ ] Disable network in DevTools
- [ ] Try refreshing a card
- [ ] Should show "Failed to load data" message
- [ ] Re-enable network
- [ ] Refresh card → data loads

**Invalid Parameters:**
- [ ] Open console
- [ ] Run: `CardManager.setParams({})`
- [ ] Refresh page
- [ ] Cards should show "Missing required parameters"

**API Down:**
- [ ] If mps-api server is down
- [ ] Cards should show error messages gracefully
- [ ] No JavaScript crashes

---

## 🐛 Common Issues & Solutions

### Issue: CardRegistry not defined
**Solution:**
- Check if `card-registry.js` loaded (Network tab)
- Verify script tag in `cms/index.php`
- Check browser console for loading errors

### Issue: Cards not rendering
**Solution:**
- Check console for JavaScript errors
- Verify CardManager initialized
- Check if API parameters set correctly
- Look for CORS errors

### Issue: Admin panel not showing
**Solution:**
- Check if card-management-container exists in HTML
- Verify CardManager.renderAdminPanel() called
- Check CSS loaded (card-management.css)

### Issue: Preferences not saving
**Solution:**
- Check if `cms/data/` directory exists
- Verify PHP has write permissions
- Check Network tab for failed POST requests
- Look at PHP error logs

### Issue: Drag-and-drop not working
**Solution:**
- Try in Chrome (best support)
- Check if draggable="true" attribute present
- Verify event listeners attached
- Check console for errors

---

## 📊 Success Criteria

### Minimum Viable:
- ✅ Endpoint catalog displays in grid format
- ✅ Modal details work for catalog
- ✅ CMS loads without JavaScript errors
- ✅ At least 3 cards render with data
- ✅ Admin panel visible and functional

### Full Success:
- ✅ All 9 cards defined in registry
- ✅ 5 cards visible by default
- ✅ Show/hide toggle works
- ✅ Drag-and-drop reordering works
- ✅ Preferences persist across refreshes
- ✅ All API calls return data
- ✅ No console errors
- ✅ Performance acceptable

### Extra Credit:
- ✅ Works on mobile
- ✅ All browsers (Chrome, Firefox, Edge)
- ✅ Handles errors gracefully
- ✅ Fast performance (<3s load)
- ✅ Data displays beautifully

---

## 📝 Test Results Log

**Tester**: _________________
**Date**: _________________
**Browser**: _________________
**OS**: _________________

### Quick Summary:
- [ ] Catalog working
- [ ] CMS dashboard working
- [ ] Admin panel working
- [ ] Persistence working
- [ ] No critical errors

### Issues Found:
1. _________________________________
2. _________________________________
3. _________________________________

### Notes:
_________________________________
_________________________________
_________________________________

---

## 🚀 Next Steps After Testing

**If All Tests Pass:**
1. Document any UX improvements needed
2. Identify missing cards to build
3. Plan additional features
4. Consider user training

**If Tests Fail:**
1. Document exact error messages
2. Check browser console screenshots
3. Review Network tab for failed requests
4. Check PHP error logs on server
5. Report issues for fixing

---

**Battle Test Status**: ⬜ NOT STARTED | 🔄 IN PROGRESS | ✅ COMPLETE | ❌ FAILED

**Overall Result**: _________________
