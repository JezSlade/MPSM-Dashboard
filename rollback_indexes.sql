-- ==================================================
-- MPSM Dashboard Database Rollback Script
-- ==================================================
-- Version: 2.1.0
-- Date: 2025-11-06
-- Purpose: Remove all indexes added by database_optimizations.sql
-- Risk Level: LOW (only drops indexes, no data loss)
-- Execution Time: ~10 seconds
-- ==================================================

USE resolut7_mpsm;

SELECT 'Starting database rollback...' AS status;

-- ==================================================
-- SECTION 1: Remove Panel Message Indexes
-- ==================================================

SELECT 'Removing panel message indexes...' AS status;

ALTER TABLE mpsm_panel_messages
  DROP INDEX IF EXISTS idx_device_serial;

ALTER TABLE mpsm_panel_messages
  DROP INDEX IF EXISTS idx_received_at;

ALTER TABLE mpsm_panel_messages
  DROP INDEX IF EXISTS idx_customer_code;

-- ==================================================
-- SECTION 2: Remove Visitor Log Indexes
-- ==================================================

SELECT 'Removing visitor log indexes...' AS status;

ALTER TABLE mpsm_visitor_log
  DROP INDEX IF EXISTS idx_username;

ALTER TABLE mpsm_visitor_log
  DROP INDEX IF EXISTS idx_ip_address;

ALTER TABLE mpsm_visitor_log
  DROP INDEX IF EXISTS idx_visited_at;

-- ==================================================
-- SECTION 3: Remove Cache Device Indexes
-- ==================================================

SELECT 'Removing cache device indexes...' AS status;

ALTER TABLE mpsm_cache_devices
  DROP INDEX IF EXISTS idx_customer_active;

ALTER TABLE mpsm_cache_devices
  DROP INDEX IF EXISTS idx_cached_at;

-- ==================================================
-- SECTION 4: Remove Cache Drilldown Indexes
-- ==================================================

SELECT 'Removing cache drilldown indexes...' AS status;

ALTER TABLE mpsm_cache_device_drilldown
  DROP INDEX IF EXISTS idx_cached_at;

-- ==================================================
-- SECTION 5: Remove Panel Callback Debug Indexes
-- ==================================================

SELECT 'Removing panel callback debug indexes...' AS status;

ALTER TABLE mpsm_panel_callback_debug
  DROP INDEX IF EXISTS idx_timestamp_status;

-- ==================================================
-- SECTION 6: Verification
-- ==================================================

SELECT 'Rollback complete!' AS status;
SELECT NOW() AS completed_at;

-- Verify all custom indexes are removed
SELECT
    table_name,
    index_name,
    'WARNING: Index still exists' AS note
FROM information_schema.statistics
WHERE table_schema = 'resolut7_mpsm'
  AND table_name IN (
    'mpsm_panel_messages',
    'mpsm_visitor_log',
    'mpsm_cache_devices',
    'mpsm_cache_device_drilldown',
    'mpsm_panel_callback_debug'
  )
  AND index_name LIKE 'idx_%';

-- If no rows returned, rollback was successful
SELECT '✓ All custom indexes removed' AS result;
SELECT '✓ Database restored to pre-optimization state' AS confirmation;
