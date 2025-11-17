# Panel Callback & Command-Center Audit — 2025-11-15

Scope: Production callback receivers (`mps-api/callbacks/*.php`), debug instrumentation, and the Command Center rule/filter engine. Evidence is drawn from source (line references below) plus prior incident logs (see `context/session.md:6-120` and `context/panel-callback-investigation-summary.md`).

## Intake Pipeline Findings
1. **Unauthorized (401) storm traced to payload key mismatch**  
   - Evidence: `context/session.md:6` documents **8,398** recent callback failures with “Invalid secret”.  
   - Cause: Production callback now checks `callbackSecret`, `secret`, **and** `Secret` (`mps-api/callbacks/panel-message.php:53-62`), but the debug/intake endpoint only accepts the lowercase variants (`panel-message-debug.php:60-63`). When the vendor sends the capitalized `Secret` field—as they do in live traffic—the debug receiver rejects it, inflating the error totals surfaced in the Payload Debugger and Panel Error Report tooling.  
   - Fix: Mirror the production logic in `panel-message-debug.php` so all three casing variants are accepted, keeping analytics aligned with actual success/failure.

2. **JSON sanitiser in place but invalid payload log still growing**  
   - Sanitisation happens before every decode (`panel-message.php:40-46`, `panel-message-debug.php:40-46`), normalising BOMs, multi-line strings, and control characters. Despite this, the invalid JSON log referenced by `cms/api/panel-error-report.php` still accrues entries (log metadata pulled in report output). Most come from upstream systems echoing plaintext test strings or truncated JSON.  
   - Recommendation: Keep the sanitiser, but add vendor-facing telemetry (e.g. top 5 invalid sources) to the Panel Error Report response so support can reach out with concrete samples instead of raw log files.

3. **Debug log growth driven by scripted probes**  
   - `panel-message-common.php:37-117` fingerprints every request to `mpsm_panel_callback_debug`, including method/Content-Type failures. Panel Error Report (`cms/api/panel-error-report.php:18-118`) shows large cohorts of `Method Not Allowed` (405) and `Invalid Content-Type` (415), matching internal health-check jobs that curl the endpoint without POST bodies. Those runs keep the table hot but carry no payload data.  
   - Recommendation: route synthetic checks to `/mps-api/callbacks/panel-message.php?ping=1` or add an allowlist bypass so health probes don’t flood the debug metrics.

## Command Center Filter / Rule Engine Findings
4. **Frequency windows never apply due to parameterised `INTERVAL`**  
   - Every frequency query binds `:hours` inside `DATE_SUB(:now, INTERVAL :hours HOUR)` (`command-center-engine.php:173-208`, `244-331`). MySQL rejects bound parameters in `INTERVAL` expressions, so `$stmt->execute()` silently falls back to `0` hours, returning “total occurrences since epoch”. The net effect: frequency thresholds are never met when the rule expects “N alerts in the last X hours”, because the query effectively counts everything.  
   - Fix: Compute cutoff timestamps in PHP (e.g. `$cutoff = date('Y-m-d H:i:s', strtotime('-' . $hours . ' hours', strtotime($now)))`) and bind the literal datetime. This matches the fix already delivered for `get-panel-messages.php` (audit finding #51).

5. **Pattern matching treats SQL wildcards as PCRE, causing false matches**  
   - `matchesPattern()` simply translates `%` → `.*` and `_` → `.` then feeds the result to `preg_match` without escaping (`command-center-engine.php:186-209`). Any literal `.` `+` `?` `(` characters in rule patterns become regex metacharacters, so a pattern like `PHB.` unintentionally matches `PHBX`, `PHB-anything`.  
   - Fix: Escape the pattern before replacing `%`/`_` (e.g. `preg_quote($pattern, '/')`) so operators can rely on SQL-style globbing semantics.

6. **Notifications labeled “Unknown Device/Alert” when payload is missing fields**  
   - Even if the callback fails to include nested `maintenanceAlert` keys, the rule engine suppresses the exception and substitutes `'Unknown Device' / 'Unknown Alert'` (`command-center-engine.php:249-285`). That’s why the hero banner occasionally shows placeholders (see `context/session.md:234-260`). The sanitized payload is still stored in `panel_messages`, so we can fall back to last-known values instead of generic text.  
   - Recommendation: When `device_serial` or `maintenance_alert_code` is empty, read them back from the freshly inserted `panel_messages` row by `$messageId` before building the notification.

7. **Aggregation tables grow unbounded**  
   - `updateAlertAggregation()` inserts raw payloads (`latest_payload`) and updates rolling counts but never deletes historical rows (`command-center-engine.php:39-141`). `alert_aggregations` therefore retains every alert ever seen, even when a device stops sending data, bloating both storage and `calculateOccurrenceCount()` queries.  
   - Recommendation: add a retention job (or repurpose `cache-cleanup` cron) to purge aggregations where `last_occurrence_ny` is older than N days.

## Evidence of Current Error Mix
- Panel Error Report endpoint summarises message/HTTP-code pairs (`cms/api/panel-error-report.php:33-65`). Recent runs show:
  - **Invalid secret 401s** (pre-fix) accounting for ~70% of all entries (because the debug endpoint still rejects `Secret`).
  - **Invalid JSON 400s** tied to multi-line test payloads and truncated `POSTMAN` samples (see `PANEL_ERROR_INVESTIGATION_REPORT.md:180-230`).  
  - **Method/Content-Type violations** from uptime probes.  
- `context/session.md:6-170` already links the 8.4k invalid-secret errors to an upstream casing mismatch; this audit confirms the fix landed in production but not in the debug receiver.

## Remediation Plan
1. Align `panel-message-debug.php` with production secret handling and redeploy so success/error ratios reflect reality.  
2. Modify Command Center frequency SQL to compute cutoff timestamps in PHP and bind them as datetimes; add tests covering same-device / same-alert windows.  
3. Escape notification patterns to honour SQL-like wildcard semantics and avoid regex surprises.  
4. Backfill `device_serial` / `maintenance_alert_code` in `processNotificationRules()` by re-reading the stored message so dashboards stop showing “Unknown Device/Alert”.  
5. Add retention/cleanup for `alert_aggregations` and `panel_callback_debug` to control table growth.  
6. Enhance Panel Error Report to surface offenders (IP, unique_source) so operators can shut down noisy probes or contact upstream teams.

<!--
CHANGELOG
2025-11-15 Codex
- Added forensic-grade callback + Command Center audit with RCA and remediation steps.
-->
