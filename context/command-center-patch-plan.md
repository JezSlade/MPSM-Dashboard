# Command Center Patch Plan
**Date**: 2025-11-28
**Status**: Pending Approval

## Root Cause Analysis

### Issue 1: Edit Rule Save Error (CRITICAL)
**Symptom**: HTML error toast appears when saving edited rule, breaks Command Center dashboard

**Root Cause**:
- HTML form has NO `enabled` field/checkbox ([command-center.php:264-378](cms/command-center.php#L264-L378))
- JavaScript `handleRuleSubmit` does NOT send `enabled` in request body ([command-center.js:685-738](cms/assets/command-center.js#L685-L738))
- PHP `updateRule` REQUIRES `enabled` field ([command-center.php:462](cms/api/command-center.php#L462): `:enabled => $data['enabled']`)
- When `enabled` is missing, PHP attempts to bind `null` to non-nullable column, causing database error
- Error response likely contains HTML (error page) instead of JSON, causing "HTML error toast"

**Primary Driver**: Missing `enabled` field in edit workflow

### Issue 2: Alert Labels Tab Lacks CRUD
**Symptom**: Alert Labels tab is read-only, "Manage in full" link opens separate page with unclear UI

**Root Cause**:
- Current implementation only displays alert labels in read-only table ([command-center.js:1186-1216](cms/assets/command-center.js#L1186-L1216))
- No create/edit/delete buttons or modals in Command Center
- Full CRUD exists in separate [alert-definitions.php](cms/alert-definitions.php) (798 lines)
- User cannot manage labels without leaving Command Center

**Primary Driver**: Incomplete feature implementation, separate management UI not integrated

### Issue 3: Tools Tab Iframe Issues
**Symptom**: Tools tab is broken, iframes cause problems

**Root Cause**:
- Tools tab uses TWO iframes to embed device-lifecycle.php and payload-debugger.php ([command-center.php:215-228](cms/command-center.php#L215-L228))
- Iframes create nested scrolling, styling conflicts, and slow loading
- User described as "broken" - likely styling issues or iframe sandbox restrictions
- No error handling if iframe fails to load

**Primary Driver**: Iframe-based architecture creates UX and technical issues

## Patch Plan

### Fix 1: Edit Rule Save Error
**Files to Modify**:
- [cms/assets/command-center.js](cms/assets/command-center.js) (function handleRuleSubmit, lines 685-738)

**Changes**:
1. Add `enabled` field extraction from form data
2. Default `enabled` to 1 (true) for backward compatibility
3. Include `enabled` in request body sent to API

**Test Plan**:
1. Create new rule - verify saves successfully
2. Edit existing rule - verify saves without error
3. Toggle between enabled/disabled states if UI control exists
4. Verify no HTML error toast appears
5. Verify dashboard does not break after save

**Regression Shields**:
- Ensure create rule still works (no enabled field in create form either)
- Verify existing rules retain enabled status
- Check all rule fields save correctly (patterns, frequency, etc.)

**Rollback**: Revert [command-center.js](cms/assets/command-center.js) to previous commit

### Fix 2: Alert Labels CRUD Integration
**Files to Modify**:
- [cms/command-center.php](cms/command-center.php) (definitions tab section, lines 188-202)
- [cms/assets/command-center.js](cms/assets/command-center.js) (add definition CRUD functions)
- [cms/api/command-center.php](cms/api/command-center.php) (add create_definition, update_definition, delete_definition actions)

**Changes**:
1. Add "Create Label" button to Alert Labels tab header
2. Add Edit/Delete action buttons to each table row
3. Create definition modal (similar to rule modal)
4. Implement `createDefinition()`, `editDefinition()`, `deleteDefinition()` functions in JS
5. Add backend handlers in API for create/update/delete operations
6. Remove "Manage in full" link (functionality now integrated)

**Test Plan**:
1. Create new alert label - verify saves and appears in table
2. Edit existing label - verify updates correctly
3. Delete label - verify removes from table with confirmation
4. Verify pattern suggestions still work in rule creation
5. Test with empty table (no labels yet)

**Regression Shields**:
- Ensure notification rules still use alert labels for pattern suggestions
- Verify panel stream still displays alert labels correctly
- Check get_alert_definitions API still works for other pages

**Rollback**: Revert [command-center.php](cms/command-center.php), [command-center.js](cms/assets/command-center.js), [command-center.php](cms/api/command-center.php) to previous commit

### Fix 3: Tools Tab Iframe Removal
**Files to Modify**:
- [cms/command-center.php](cms/command-center.php) (tools tab section, lines 205-230)
- [cms/assets/command-center.js](cms/assets/command-center.js) (add tools tab functions)

**Changes**:
1. Remove iframes from Tools tab
2. Split into two separate sections: "Device Lifecycle" and "Payload Debugger"
3. Inline Device Lifecycle functionality:
   - Add search input for device serial
   - Add timeline display for device events
   - Use existing API or create new endpoint
4. Inline Payload Debugger functionality:
   - Add form to submit test payload
   - Add response display area
   - Use existing API or create new endpoint
5. Load data via AJAX instead of iframes

**Test Plan**:
1. Navigate to Tools tab - verify no iframes, no broken layout
2. Test Device Lifecycle search - verify returns device history
3. Test Payload Debugger submission - verify shows response
4. Verify styling matches Command Center theme
5. Test tab switching (no iframe reload delays)

**Regression Shields**:
- Ensure device-lifecycle.php and payload-debugger.php still work standalone
- Verify no broken links elsewhere referencing these tools
- Check performance (no slower than iframe loading)

**Rollback**: Revert [command-center.php](cms/command-center.php), [command-center.js](cms/assets/command-center.js) to previous commit

## Scope Check

**Alignment with Architecture**:
- All changes confined to Command Center components
- No changes to database schema required (enabled column already exists)
- Uses existing API patterns (JSON request/response)
- Follows existing modal/form patterns

**Guardrails Compliance**:
- Changes are minimal and cohesive
- No new files created (only editing existing)
- No placeholders or stubs
- Full file outputs required
- Changelogs appended to modified files

**Token Discipline**:
- 3 files to modify for Fix 1 (small change)
- 3 files to modify for Fix 2 (medium change)
- 2 files to modify for Fix 3 (medium change)
- Total: 5 unique files (some overlap)
- Within single-artifact delivery threshold

## Deployment Plan

1. Implement all three fixes
2. Test locally if possible, otherwise test live
3. Commit changes with descriptive message
4. User executes `git push origin main` to trigger GitHub Actions FTP deploy
5. Monitor deployment: https://github.com/JezSlade/MPSM-Dashboard/actions
6. Verify live site after 2-5 minutes
7. Run live tests per test plans above
8. Document results in `context/test-log.md`
9. User confirms all three issues resolved

## Estimated Complexity

- **Fix 1**: Low (10-15 lines changed)
- **Fix 2**: High (150-200 lines added, existing code from alert-definitions.php can be adapted)
- **Fix 3**: Medium (100-150 lines changed, depends on API availability)

**Total LOC Changed**: ~300-400 lines across 5 files

## Questions for User

None - plan is clear and unambiguous. Ready to proceed.
