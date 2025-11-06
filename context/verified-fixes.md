# Verified Fixes & Hard Lessons

Each item below is battle-tested in production and should not be undone without a replacement strategy. When investigating regressions, confirm these guardrails still exist.

## Authentication

- **Multi-source credential parsing**  
  File: `cms/api/login.php`  
  Fix: Accept JSON body, `$_POST`, or raw stream to work around hosting where `php://input` is empty (commit `2220dcd`). The code checks each source in order before calling `loginUser()`, logging an explicit error if credentials are missing.

- **Session cookie hardening**  
  File: `cms/config.php`  
  Fix: Secure SameSite Lax cookies with HTTPS detection and strict mode to satisfy Chrome/Firefox (`session_set_cookie_params([... 'samesite' => 'Lax' ...])`).

## Frontend Stability

- **CardManager.setContext guard**  
  File: `cms/assets/app.js`  
  Fix: Every call to `CardManager.setContext()` now wrapped in `if (typeof CardManager !== 'undefined' && typeof CardManager.setContext === 'function')`. Prevents “CardManager.setContext is not a function” when the dashboard rehydrates or when scripts load out of order (commit `a9e937b`).

- **Supply alert modal parity**  
  File: `cms/assets/app.js` (alert click handlers)  
  Fix: Alert handlers now pass `deviceId`, `serialNumber`, and `customerCode` to `openDeviceModal()`. Ensures deep dive resolves the correct device for supply alerts (documented in `AUDIT_REPORT.md`).

## Panel Messages

- **Callback storage & security**  
  Files: `mps-api/callbacks/panel-message.php`, `mps-api/callbacks/panel-message-debug.php`  
  Fix: Shared secret validation (`mpsm-panel-message-v1`), JSON-only enforcement, source IP logging, and automatic table creation. Debug variant logs all headers/body for diagnostics.

- **Panel history integration**  
  File: `cms/api/get-device-deep-dive.php`  
  Fix: Step 5 fetches up to 100 messages from `mpsm_panel_messages` and returns them under `panelHistory`. Device modal work can rely on this contract.

- **Payload debugger surface**  
  File: `cms/payload-debugger.php` and `cms/api/get-payload-debug-logs.php`  
  Fix: Real-time stats + logs for every callback attempt, enabling root-cause analysis of webhook issues (`PAYLOAD_DEBUGGER_GUIDE.md` captures the feature).

## Background Caching

- **Enhanced refresh script**  
  File: `cms/api/refresh-cache-enhanced.php`  
  Fix: Introduces locking, pagination for installed + deleted devices, drill-down caching, panel message counts, and logging. Pending action: ensure upstream API returns devices with the configured dealer code.

## Search & Pagination

- **Global search coverage**  
  Files: `cms/assets/app.js` (`fetchAllDevicesForSearch()`), `cms/api/get-devices.php` (support for `allCustomers=true`)  
  Fix: Fetches up to 10 000 devices across all customers with 1-minute cache TTL. Documented in ADR-0005.

## Export Reliability

- **Download pipeline validation**  
  Files: `cms/api/run-export.php`, `cms/assets/js/card-registry.js`  
  Fix: API returns base64 metadata; frontend decodes via Blob with multiple fallbacks. Verified in `AUDIT_REPORT.md` (“Export functionality is fully implemented and working correctly”).

Keep this list current. When closing a new issue, add the code paths here along with supporting doc references so future maintainers know which fixes are intentional.
