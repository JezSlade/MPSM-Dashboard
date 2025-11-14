-- Panel Callback Debug Error Analysis Queries
-- Database: resolut7_mpsm
-- Table: mpsm_panel_callback_debug

-- ===========================================
-- QUICK OVERVIEW
-- ===========================================

-- Overall statistics
SELECT
    COUNT(*) as total_entries,
    SUM(CASE WHEN status = 'ERROR' THEN 1 ELSE 0 END) as total_errors,
    SUM(CASE WHEN status = 'SUCCESS' THEN 1 ELSE 0 END) as total_success,
    SUM(CASE WHEN status = 'PROCESSING' THEN 1 ELSE 0 END) as total_processing,
    ROUND(SUM(CASE WHEN status = 'ERROR' THEN 1 ELSE 0 END) / COUNT(*) * 100, 2) as error_rate_percent,
    MIN(timestamp) as first_entry,
    MAX(timestamp) as last_entry
FROM mpsm_panel_callback_debug;

-- ===========================================
-- ERROR BREAKDOWN
-- ===========================================

-- Error types with counts
SELECT
    message,
    http_code,
    COUNT(*) as count,
    MIN(timestamp) as first_seen,
    MAX(timestamp) as last_seen,
    COUNT(DISTINCT unique_source) as unique_sources,
    COUNT(DISTINCT ip_address) as unique_ips
FROM mpsm_panel_callback_debug
WHERE status = 'ERROR'
GROUP BY message, http_code
ORDER BY count DESC;

-- ===========================================
-- RECENT ERRORS
-- ===========================================

-- Last 10 errors with details
SELECT
    id,
    timestamp,
    ip_address,
    unique_source,
    http_method,
    content_type,
    message,
    http_code,
    SUBSTRING(raw_body, 1, 200) as body_preview,
    LENGTH(raw_body) as body_length
FROM mpsm_panel_callback_debug
WHERE status = 'ERROR'
ORDER BY timestamp DESC
LIMIT 10;

-- ===========================================
-- JSON SPECIFIC ERRORS
-- ===========================================

-- JSON parsing errors
SELECT
    id,
    timestamp,
    message,
    SUBSTRING(raw_body, 1, 300) as body_preview,
    unique_source,
    ip_address
FROM mpsm_panel_callback_debug
WHERE
    status = 'ERROR'
    AND (
        message LIKE '%Invalid JSON%'
        OR message LIKE '%json%'
        OR message LIKE '%JSON%'
    )
ORDER BY timestamp DESC
LIMIT 20;

-- ===========================================
-- SOURCE ANALYSIS
-- ===========================================

-- Which sources are generating errors?
SELECT
    unique_source,
    ip_address,
    COUNT(*) as total_requests,
    SUM(CASE WHEN status = 'SUCCESS' THEN 1 ELSE 0 END) as success_count,
    SUM(CASE WHEN status = 'ERROR' THEN 1 ELSE 0 END) as error_count,
    ROUND(SUM(CASE WHEN status = 'ERROR' THEN 1 ELSE 0 END) / COUNT(*) * 100, 2) as error_rate_percent,
    MIN(timestamp) as first_seen,
    MAX(timestamp) as last_seen
FROM mpsm_panel_callback_debug
GROUP BY unique_source, ip_address
ORDER BY error_count DESC, total_requests DESC
LIMIT 20;

-- Error messages by source
SELECT
    unique_source,
    message,
    COUNT(*) as count
FROM mpsm_panel_callback_debug
WHERE status = 'ERROR'
GROUP BY unique_source, message
ORDER BY count DESC;

-- ===========================================
-- TEST DATA DETECTION
-- ===========================================

-- Find potential test data
SELECT
    id,
    timestamp,
    message,
    status,
    SUBSTRING(raw_body, 1, 150) as body_preview,
    unique_source,
    ip_address
FROM mpsm_panel_callback_debug
WHERE
    raw_body LIKE '%TEST%'
    OR raw_body LIKE '%test%'
    OR raw_body LIKE '%SUCCESS_SERIAL%'
    OR message LIKE '%test%'
    OR unique_source LIKE '%test%'
ORDER BY timestamp DESC
LIMIT 50;

-- ===========================================
-- TIME-BASED ANALYSIS
-- ===========================================

-- Errors by hour (last 24 hours)
SELECT
    DATE_FORMAT(timestamp, '%Y-%m-%d %H:00') as hour,
    COUNT(*) as total,
    SUM(CASE WHEN status = 'ERROR' THEN 1 ELSE 0 END) as errors,
    SUM(CASE WHEN status = 'SUCCESS' THEN 1 ELSE 0 END) as success
FROM mpsm_panel_callback_debug
WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
GROUP BY DATE_FORMAT(timestamp, '%Y-%m-%d %H:00')
ORDER BY hour DESC;

-- Errors by day (last 30 days)
SELECT
    DATE(timestamp) as day,
    COUNT(*) as total,
    SUM(CASE WHEN status = 'ERROR' THEN 1 ELSE 0 END) as errors,
    SUM(CASE WHEN status = 'SUCCESS' THEN 1 ELSE 0 END) as success
FROM mpsm_panel_callback_debug
WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY DATE(timestamp)
ORDER BY day DESC;

-- ===========================================
-- SAMPLE PAYLOADS
-- ===========================================

-- Get full payload for specific error ID
-- Replace {ID} with actual ID from previous queries
SELECT
    id,
    timestamp,
    message,
    http_code,
    raw_body,
    headers,
    unique_source
FROM mpsm_panel_callback_debug
WHERE id = {ID};

-- First 5 unique error messages with sample payloads
SELECT
    message,
    COUNT(*) as count,
    MAX(id) as sample_id,
    MAX(SUBSTRING(raw_body, 1, 200)) as sample_payload
FROM mpsm_panel_callback_debug
WHERE status = 'ERROR'
GROUP BY message
ORDER BY count DESC
LIMIT 5;

-- ===========================================
-- CLEANUP OPERATIONS
-- ===========================================

-- DANGER: Delete test data (review IDs first!)
-- Step 1: Get IDs to delete
SELECT GROUP_CONCAT(id) as ids_to_delete
FROM mpsm_panel_callback_debug
WHERE
    raw_body LIKE '%TEST%'
    OR raw_body LIKE '%SUCCESS_SERIAL%'
    OR raw_body LIKE '%test%'
LIMIT 1000;

-- Step 2: Delete (uncomment and replace IDs from step 1)
-- DELETE FROM mpsm_panel_callback_debug WHERE id IN (1,2,3,...);

-- Delete old successful entries (keep errors)
-- Keeps last 7 days of successful entries, keeps all errors
-- DELETE FROM mpsm_panel_callback_debug
-- WHERE status = 'SUCCESS'
-- AND timestamp < DATE_SUB(NOW(), INTERVAL 7 DAY);

-- ===========================================
-- SUCCESSFUL MESSAGES
-- ===========================================

-- Recent successful messages
SELECT
    id,
    timestamp,
    unique_source,
    message,
    http_code
FROM mpsm_panel_callback_debug
WHERE status = 'SUCCESS'
ORDER BY timestamp DESC
LIMIT 10;

-- Success rate by source
SELECT
    unique_source,
    COUNT(*) as total,
    SUM(CASE WHEN status = 'SUCCESS' THEN 1 ELSE 0 END) as success,
    ROUND(SUM(CASE WHEN status = 'SUCCESS' THEN 1 ELSE 0 END) / COUNT(*) * 100, 2) as success_rate_percent
FROM mpsm_panel_callback_debug
GROUP BY unique_source
HAVING COUNT(*) >= 5
ORDER BY success_rate_percent ASC;

-- ===========================================
-- VALIDATION ERRORS
-- ===========================================

-- Authentication errors
SELECT
    timestamp,
    unique_source,
    ip_address,
    SUBSTRING(raw_body, 1, 200) as body_preview
FROM mpsm_panel_callback_debug
WHERE message LIKE '%Unauthorized%'
   OR message LIKE '%secret%'
ORDER BY timestamp DESC
LIMIT 10;

-- Content-Type errors
SELECT
    timestamp,
    unique_source,
    content_type,
    http_method
FROM mpsm_panel_callback_debug
WHERE message LIKE '%Content-Type%'
ORDER BY timestamp DESC
LIMIT 10;

-- Method errors
SELECT
    timestamp,
    unique_source,
    http_method,
    ip_address
FROM mpsm_panel_callback_debug
WHERE message LIKE '%Method%'
ORDER BY timestamp DESC
LIMIT 10;

-- Empty payload errors
SELECT
    timestamp,
    unique_source,
    content_type,
    http_method,
    LENGTH(raw_body) as body_length
FROM mpsm_panel_callback_debug
WHERE message LIKE '%Empty%'
ORDER BY timestamp DESC
LIMIT 10;
