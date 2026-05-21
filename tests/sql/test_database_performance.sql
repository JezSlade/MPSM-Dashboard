-- ==================================================
-- MPSM Dashboard Database Performance Test Script
-- ==================================================
-- Version: 2.1.0
-- Date: 2025-11-06
-- Purpose: Test query performance before/after index optimization
-- Usage: Run before and after database/migrations/database_optimizations.sql
-- ==================================================

USE resolut7_mpsm;

SELECT '========================================' AS separator;
SELECT 'MPSM Database Performance Tests' AS title;
SELECT NOW() AS test_time;
SELECT '========================================' AS separator;

-- ==================================================
-- TEST 1: Panel Message Query (Date Range)
-- ==================================================

SELECT 'TEST 1: Panel Messages - Last 24 Hours' AS test_name;

-- Show query plan
EXPLAIN SELECT
    id, received_at, customer_code, device_serial,
    maintenance_alert_code
FROM mpsm_panel_messages
WHERE received_at >= NOW() - INTERVAL 24 HOUR
ORDER BY received_at DESC
LIMIT 100;

-- Execute query and measure
SELECT
    COUNT(*) AS message_count,
    'Panel messages in last 24h' AS description
FROM mpsm_panel_messages
WHERE received_at >= NOW() - INTERVAL 24 HOUR;

-- ==================================================
-- TEST 2: Panel Message Query (Device Serial)
-- ==================================================

SELECT 'TEST 2: Panel Messages - By Device Serial' AS test_name;

-- Get a sample serial number first
SET @sample_serial = (
    SELECT device_serial
    FROM mpsm_panel_messages
    WHERE device_serial IS NOT NULL
    LIMIT 1
);

-- Show query plan
EXPLAIN SELECT
    id, received_at, maintenance_alert_code
FROM mpsm_panel_messages
WHERE device_serial = @sample_serial
ORDER BY received_at DESC
LIMIT 100;

-- Execute query
SELECT
    COUNT(*) AS message_count,
    CONCAT('Messages for device: ', @sample_serial) AS description
FROM mpsm_panel_messages
WHERE device_serial = @sample_serial;

-- ==================================================
-- TEST 3: Visitor Log Query (Username Filter)
-- ==================================================

SELECT 'TEST 3: Visitor Log - By Username' AS test_name;

-- Show query plan
EXPLAIN SELECT
    id, username, ip_address, page_url, visited_at
FROM mpsm_visitor_log
WHERE username = 'admin'
ORDER BY visited_at DESC
LIMIT 100;

-- Execute query
SELECT
    COUNT(*) AS visit_count,
    'Admin user visits' AS description
FROM mpsm_visitor_log
WHERE username = 'admin';

-- ==================================================
-- TEST 4: Visitor Log Query (Date Range)
-- ==================================================

SELECT 'TEST 4: Visitor Log - Last 7 Days' AS test_name;

-- Show query plan
EXPLAIN SELECT
    id, username, ip_address, visited_at
FROM mpsm_visitor_log
WHERE visited_at >= NOW() - INTERVAL 7 DAY
ORDER BY visited_at DESC
LIMIT 100;

-- Execute query
SELECT
    COUNT(*) AS visit_count,
    'Visits in last 7 days' AS description
FROM mpsm_visitor_log
WHERE visited_at >= NOW() - INTERVAL 7 DAY;

-- ==================================================
-- TEST 5: Cache Device Query (Customer + Status)
-- ==================================================

SELECT 'TEST 5: Cache Devices - Customer Active Devices' AS test_name;

-- Get a sample customer code
SET @sample_customer = (
    SELECT customer_code
    FROM mpsm_cache_devices
    WHERE customer_code IS NOT NULL
    LIMIT 1
);

-- Show query plan
EXPLAIN SELECT
    serial_number, customer_code, is_uninstalled
FROM mpsm_cache_devices
WHERE customer_code = @sample_customer
  AND is_uninstalled = 0;

-- Execute query
SELECT
    COUNT(*) AS device_count,
    CONCAT('Active devices for customer: ', @sample_customer) AS description
FROM mpsm_cache_devices
WHERE customer_code = @sample_customer
  AND is_uninstalled = 0;

-- ==================================================
-- TEST 6: Cache Age Query
-- ==================================================

SELECT 'TEST 6: Cache Devices - Cache Age Check' AS test_name;

-- Show query plan
EXPLAIN SELECT
    MAX(cached_at) AS latest_cache,
    MIN(cached_at) AS oldest_cache,
    COUNT(*) AS total_cached
FROM mpsm_cache_devices;

-- Execute query
SELECT
    MAX(cached_at) AS latest_cache,
    MIN(cached_at) AS oldest_cache,
    COUNT(*) AS total_cached,
    TIMESTAMPDIFF(SECOND, MAX(cached_at), NOW()) AS age_seconds
FROM mpsm_cache_devices;

-- ==================================================
-- TEST 7: Panel Callback Debug Query (Status Filter)
-- ==================================================

SELECT 'TEST 7: Panel Callback Debug - Recent Errors' AS test_name;

-- Show query plan
EXPLAIN SELECT
    id, timestamp, ip_address, status, message
FROM mpsm_panel_callback_debug
WHERE timestamp >= NOW() - INTERVAL 1 HOUR
  AND status = 'ERROR'
ORDER BY timestamp DESC
LIMIT 100;

-- Execute query
SELECT
    COUNT(*) AS error_count,
    'Errors in last hour' AS description
FROM mpsm_panel_callback_debug
WHERE timestamp >= NOW() - INTERVAL 1 HOUR
  AND status = 'ERROR';

-- ==================================================
-- SUMMARY: Index Status
-- ==================================================

SELECT '========================================' AS separator;
SELECT 'Index Status Summary' AS title;
SELECT '========================================' AS separator;

SELECT
    table_name,
    index_name,
    column_name,
    CASE
        WHEN non_unique = 0 THEN 'UNIQUE'
        ELSE 'NON-UNIQUE'
    END AS index_type
FROM information_schema.statistics
WHERE table_schema = 'resolut7_mpsm'
  AND table_name IN (
    'mpsm_panel_messages',
    'mpsm_visitor_log',
    'mpsm_cache_devices',
    'mpsm_cache_device_drilldown',
    'mpsm_panel_callback_debug'
  )
  AND index_name LIKE 'idx_%'
ORDER BY table_name, index_name;

-- ==================================================
-- EXPECTED IMPROVEMENTS (After Indexes Added):
-- ==================================================
-- TEST 1: Panel Messages (24h) - 500ms → 50ms (10x faster)
-- TEST 2: Panel Messages (serial) - 200ms → 20ms (10x faster)
-- TEST 3: Visitor Log (username) - 2000ms → 200ms (10x faster)
-- TEST 4: Visitor Log (date) - 1500ms → 150ms (10x faster)
-- TEST 5: Cache Devices (customer) - 100ms → 20ms (5x faster)
-- TEST 6: Cache Age - 50ms → 10ms (5x faster)
-- TEST 7: Callback Debug - 800ms → 80ms (10x faster)
-- ==================================================

SELECT '========================================' AS separator;
SELECT 'Performance test complete!' AS status;
SELECT 'Compare EXPLAIN output: type should be "ref" or "range" (not "ALL")' AS note;
SELECT '========================================' AS separator;
