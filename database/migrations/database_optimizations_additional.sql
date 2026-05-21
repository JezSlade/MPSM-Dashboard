-- ==================================================
-- MPSM Dashboard Additional Database Optimizations
-- ==================================================
-- Version: 2.1.1
-- Date: 2025-11-06
-- Purpose: Add missing indexes for payload debugger source filtering
-- Risk Level: LOW (indexes can be dropped without data loss)
-- Execution Time: ~10 seconds
-- ==================================================

USE resolut7_mpsm;

-- ==================================================
-- Panel Callback Debug Additional Indexes
-- ==================================================
-- Improves source filtering in payload debugger
-- ==================================================

SELECT 'Adding additional panel callback debug indexes...' AS status;

-- Separate index for timestamp (for ORDER BY DESC)
ALTER TABLE mpsm_panel_callback_debug
  ADD INDEX IF NOT EXISTS idx_timestamp_desc (timestamp DESC);

-- Index for status filtering alone
ALTER TABLE mpsm_panel_callback_debug
  ADD INDEX IF NOT EXISTS idx_status (status);

-- Index for new source filtering
ALTER TABLE mpsm_panel_callback_debug
  ADD INDEX IF NOT EXISTS idx_unique_source (unique_source);

-- Verify new indexes
SELECT 'Verification:' AS step;
SHOW INDEX FROM mpsm_panel_callback_debug WHERE Key_name IN ('idx_timestamp_desc', 'idx_status', 'idx_unique_source');

SELECT '✓ Additional indexes added successfully' AS result;
SELECT 'Total panel_callback_debug indexes: 4 (timestamp_status, timestamp_desc, status, unique_source)' AS summary;
