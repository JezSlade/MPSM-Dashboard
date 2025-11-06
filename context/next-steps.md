# Project Next Steps / TODOs

Updated: 2025-11-06 13:50 UTC

## Data Refresh & Coverage

- Allow a full `cms/api/refresh-cache-enhanced.php?force=1` run to complete (without `skipDrilldown`) and confirm the Database Monitor card reports ≥95 % drill-down coverage; capture the before/after metrics in `BACKGROUND_REFRESH_SYSTEM.md`.
- Watch `cms/logs/cache-refresh-YYYY-MM-DD.log` for sustained rate-limit messages; if retries still hit the cap, consider increasing the base back-off above 0.75 s or adding a staggered queue.
- Surface the cached timestamp inside the device modal once the cache is steady so analysts know how fresh each drill-down snapshot is.

## Payload Debugger & Callbacks

- Clean up `test-payloads.ps1` (remove non-ASCII quotes) so the eight-case harness runs without parse errors; rerun and verify the debugger reflects the expected mix (2 success / 6 error).
- Monitor `mpsm_panel_callback_debug` for live MPS Monitor traffic to validate `unique_source`, `forwarded_for`, and `completed_at` data; record any production IP ranges for future allow-listing.
- After the next vendor callback, grab a debugger screenshot highlighting the “Completed” column for support documentation (`PAYLOAD_DEBUGGER_GUIDE.md`).

## Admin & Monitoring UX

- Add a light-weight alert on the Admin Database Monitor card whenever drill-down coverage drops below 90 % so on-call staff can launch a warm-up immediately.
- Document the new sample tables (device cache, drill-down cache, panel messages, payload debugger) in the runbook so new engineers know how to interpret them.
- Keep an eye on `panel-message-monitor.php` iframe behaviour; if browsers reintroduce frame restrictions, move the debugger into an in-page tab instead of using an `<iframe>`.
