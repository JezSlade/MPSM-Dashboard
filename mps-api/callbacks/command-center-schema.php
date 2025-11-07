<?php
declare(strict_types=1);

/**
 * Command Center Database Schema
 *
 * Creates tables for notification rules, alert aggregation, and dashboard notifications
 */

if (!defined('MPS_ENGINE_ACCESS')) {
    exit('Access denied');
}

if (!function_exists('ensureCommandCenterTables')) {
    /**
     * Create all Command Center tables
     */
    function ensureCommandCenterTables(PDO $pdo): void
    {
        ensureNotificationRulesTable($pdo);
        ensureAlertAggregationsTable($pdo);
        ensureDashboardNotificationsTable($pdo);
        ensureRuleMatchHistoryTable($pdo);
    }
}

if (!function_exists('ensureNotificationRulesTable')) {
    /**
     * Notification Rules - User-defined alert patterns
     */
    function ensureNotificationRulesTable(PDO $pdo): void
    {
        static $ensured = false;
        if ($ensured) return;

        $table = DB_PREFIX . 'notification_rules';
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS {$table} (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL COMMENT 'User-friendly rule name',
                description TEXT NULL COMMENT 'Rule description',
                severity ENUM('info', 'warning', 'high', 'critical') DEFAULT 'warning',
                enabled TINYINT(1) DEFAULT 1,

                -- Pattern Matching
                alert_code_pattern VARCHAR(255) NULL COMMENT 'Alert code to match (supports wildcards)',
                device_serial_pattern VARCHAR(255) NULL COMMENT 'Device serial to match (supports wildcards)',
                customer_code_pattern VARCHAR(255) NULL COMMENT 'Customer code to match (supports wildcards)',

                -- Frequency Thresholds
                frequency_count INT NULL COMMENT 'Number of occurrences',
                frequency_window_hours INT NULL COMMENT 'Time window in hours',
                frequency_type ENUM('same_device', 'same_alert', 'same_customer', 'any') DEFAULT 'same_device',

                -- Actions
                show_dashboard TINYINT(1) DEFAULT 1 COMMENT 'Show in dashboard hero header',
                send_email TINYINT(1) DEFAULT 0,
                email_recipients TEXT NULL COMMENT 'Comma-separated email addresses',
                auto_dismiss_hours INT NULL COMMENT 'Auto-dismiss after X hours',

                -- Notification Template
                notification_title VARCHAR(255) NULL COMMENT 'Template: {severity} - {device} has {alert}',
                notification_message TEXT NULL COMMENT 'Template: {device} has triggered {alert} {count} times in {window}',

                -- Metadata
                created_by INT NULL COMMENT 'User ID who created rule',
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
                last_triggered_at DATETIME NULL,
                trigger_count INT DEFAULT 0,

                INDEX idx_enabled (enabled),
                INDEX idx_severity (severity),
                INDEX idx_alert_code (alert_code_pattern),
                INDEX idx_device_serial (device_serial_pattern)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $ensured = true;
    }
}

if (!function_exists('ensureAlertAggregationsTable')) {
    /**
     * Alert Aggregations - Track frequency of alerts for threshold matching
     */
    function ensureAlertAggregationsTable(PDO $pdo): void
    {
        static $ensured = false;
        if ($ensured) return;

        $table = DB_PREFIX . 'alert_aggregations';
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS {$table} (
                id INT AUTO_INCREMENT PRIMARY KEY,

                -- Aggregation Key
                device_serial VARCHAR(150) NOT NULL,
                alert_code VARCHAR(150) NOT NULL,
                customer_code VARCHAR(100) NULL,

                -- Frequency Tracking (NY Time)
                first_occurrence_ny DATETIME NOT NULL COMMENT 'First alert in NY time',
                last_occurrence_ny DATETIME NOT NULL COMMENT 'Most recent alert in NY time',
                occurrence_count INT DEFAULT 1,

                -- Time Windows
                count_1h INT DEFAULT 0 COMMENT 'Count in last 1 hour',
                count_24h INT DEFAULT 0 COMMENT 'Count in last 24 hours',
                count_7d INT DEFAULT 0 COMMENT 'Count in last 7 days',
                count_30d INT DEFAULT 0 COMMENT 'Count in last 30 days',

                -- Latest Message Reference
                latest_message_id INT NULL,
                latest_payload JSON NULL,

                -- Metadata
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

                UNIQUE KEY idx_aggregation_key (device_serial, alert_code, customer_code),
                INDEX idx_device_serial (device_serial),
                INDEX idx_alert_code (alert_code),
                INDEX idx_customer_code (customer_code),
                INDEX idx_last_occurrence (last_occurrence_ny),
                INDEX idx_occurrence_count (occurrence_count)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $ensured = true;
    }
}

if (!function_exists('ensureDashboardNotificationsTable')) {
    /**
     * Dashboard Notifications - Active notifications shown in hero header
     */
    function ensureDashboardNotificationsTable(PDO $pdo): void
    {
        static $ensured = false;
        if ($ensured) return;

        $table = DB_PREFIX . 'dashboard_notifications';
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS {$table} (
                id INT AUTO_INCREMENT PRIMARY KEY,

                -- Notification Content
                title VARCHAR(255) NOT NULL,
                message TEXT NOT NULL,
                severity ENUM('info', 'warning', 'high', 'critical') DEFAULT 'warning',

                -- Source
                rule_id INT NULL COMMENT 'Notification rule that triggered this',
                device_serial VARCHAR(150) NULL,
                alert_code VARCHAR(150) NULL,
                customer_code VARCHAR(100) NULL,

                -- Context Data
                trigger_count INT DEFAULT 1,
                time_window_hours INT NULL,
                related_message_ids TEXT NULL COMMENT 'Comma-separated message IDs',
                metadata JSON NULL COMMENT 'Additional context data',

                -- Status
                status ENUM('active', 'acknowledged', 'dismissed', 'expired') DEFAULT 'active',
                acknowledged_by INT NULL COMMENT 'User ID who acknowledged',
                acknowledged_at DATETIME NULL,
                dismissed_by INT NULL COMMENT 'User ID who dismissed',
                dismissed_at DATETIME NULL,

                -- Timing (NY Time)
                created_at_ny DATETIME NOT NULL COMMENT 'Created in NY time',
                expires_at_ny DATETIME NULL COMMENT 'Auto-expire time in NY time',

                -- Display
                icon VARCHAR(50) NULL COMMENT 'Icon class/name',
                color VARCHAR(50) NULL COMMENT 'Color theme',
                priority INT DEFAULT 0 COMMENT 'Higher = shown first',

                INDEX idx_status (status),
                INDEX idx_severity (severity),
                INDEX idx_rule_id (rule_id),
                INDEX idx_device_serial (device_serial),
                INDEX idx_created_at (created_at_ny),
                INDEX idx_priority (priority),
                INDEX idx_expires_at (expires_at_ny)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $ensured = true;
    }
}

if (!function_exists('ensureRuleMatchHistoryTable')) {
    /**
     * Rule Match History - Audit trail of rule triggers
     */
    function ensureRuleMatchHistoryTable(PDO $pdo): void
    {
        static $ensured = false;
        if ($ensured) return;

        $table = DB_PREFIX . 'rule_match_history';
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS {$table} (
                id INT AUTO_INCREMENT PRIMARY KEY,

                rule_id INT NOT NULL,
                panel_message_id INT NOT NULL,
                notification_id INT NULL COMMENT 'Notification created from this match',

                matched_at_ny DATETIME NOT NULL COMMENT 'Match time in NY time',

                -- Match Context
                device_serial VARCHAR(150) NULL,
                alert_code VARCHAR(150) NULL,
                customer_code VARCHAR(100) NULL,
                occurrence_count INT NULL COMMENT 'Count at time of match',

                -- Rule Snapshot
                rule_name VARCHAR(255) NULL,
                rule_severity VARCHAR(20) NULL,

                INDEX idx_rule_id (rule_id),
                INDEX idx_panel_message_id (panel_message_id),
                INDEX idx_notification_id (notification_id),
                INDEX idx_matched_at (matched_at_ny),
                INDEX idx_device_serial (device_serial)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $ensured = true;
    }
}
