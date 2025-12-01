-- Common Sense Alert Rules for MPSM Dashboard
-- These rules detect recurring device issues and trigger notifications
-- Created: 2025-11-28
-- Usage: Execute via phpMyAdmin or mysql CLI after verifying table structure

-- Rule 1: Repeated JAM Alerts (Same Device)
-- Detects when a single device has 3+ jam alerts within 24 hours
INSERT INTO mpsm_notification_rules (
    name, description, severity, enabled,
    alert_code_pattern, device_serial_pattern, customer_code_pattern,
    frequency_count, frequency_window_hours, frequency_type,
    show_dashboard, send_email, email_recipients,
    auto_dismiss_hours, notification_title, notification_message,
    created_by, created_at
) VALUES (
    'Repeated JAM Alerts',
    'Triggers when a device reports 3 or more JAM alerts within 24 hours, indicating potential mechanical issues requiring maintenance.',
    'high',
    1,
    'JAM%',  -- Matches JAM-001, JAM-002, etc.
    NULL,    -- Any device
    NULL,    -- Any customer
    3,       -- 3 occurrences
    24,      -- Within 24 hours
    'same_device',  -- Same device must trigger all 3
    1,       -- Show on dashboard
    0,       -- Email disabled (configure if needed)
    NULL,
    48,      -- Auto-dismiss after 48 hours
    'Recurring JAM Issue Detected',
    'Device experiencing repeated jam alerts. Inspect for mechanical blockages, paper fragments, or sensor malfunctions.',
    1,       -- System/admin user ID
    NOW()
);

-- Rule 2: Emergency Stop Alerts (Any Device, Same Customer)
-- Detects when any customer location has 2+ emergency stops within 12 hours
INSERT INTO mpsm_notification_rules (
    name, description, severity, enabled,
    alert_code_pattern, device_serial_pattern, customer_code_pattern,
    frequency_count, frequency_window_hours, frequency_type,
    show_dashboard, send_email, email_recipients,
    auto_dismiss_hours, notification_title, notification_message,
    created_by, created_at
) VALUES (
    'Emergency Stop Pattern',
    'Triggers when 2 or more emergency stop alerts occur at the same customer location within 12 hours, suggesting safety concerns or operator errors.',
    'critical',
    1,
    'E-%',    -- Matches E-001, E-002, etc. (emergency codes)
    NULL,
    NULL,
    2,
    12,
    'same_customer',  -- Any devices at same customer
    1,
    0,
    NULL,
    24,
    'Multiple Emergency Stops',
    'Multiple emergency stop events detected at this location. Review safety protocols and operator training. Verify emergency stop button function.',
    1,
    NOW()
);

-- Rule 3: Repeated Sensor Errors (Same Device)
-- Detects persistent sensor failures on a single device
INSERT INTO mpsm_notification_rules (
    name, description, severity, enabled,
    alert_code_pattern, device_serial_pattern, customer_code_pattern,
    frequency_count, frequency_window_hours, frequency_type,
    show_dashboard, send_email, email_recipients,
    auto_dismiss_hours, notification_title, notification_message,
    created_by, created_at
) VALUES (
    'Persistent Sensor Failures',
    'Triggers when a device reports 5+ sensor errors within 48 hours, indicating sensor hardware failure or wiring issues.',
    'high',
    1,
    'SENS%',  -- Matches SENSOR-%, SENS-%, etc.
    NULL,
    NULL,
    5,
    48,
    'same_device',
    1,
    0,
    NULL,
    72,
    'Sensor System Malfunction',
    'Device reporting persistent sensor errors. Check sensor connections, wiring integrity, and consider sensor replacement.',
    1,
    NOW()
);

-- Rule 4: Communication Loss Alerts (Same Alert Type)
-- Detects widespread communication issues affecting multiple devices
INSERT INTO mpsm_notification_rules (
    name, description, severity, enabled,
    alert_code_pattern, device_serial_pattern, customer_code_pattern,
    frequency_count, frequency_window_hours, frequency_type,
    show_dashboard, send_email, email_recipients,
    auto_dismiss_hours, notification_title, notification_message,
    created_by, created_at
) VALUES (
    'Widespread Communication Loss',
    'Triggers when 10+ devices report communication loss within 1 hour, suggesting network infrastructure issues rather than individual device failures.',
    'critical',
    1,
    'COMM%',  -- Matches COMM-LOSS, COMM-ERROR, etc.
    NULL,
    NULL,
    10,
    1,
    'same_alert',  -- Same alert code across multiple devices
    1,
    0,
    NULL,
    6,
    'Network Infrastructure Issue',
    'Multiple devices experiencing communication loss simultaneously. Check network infrastructure, router status, and ISP connection.',
    1,
    NOW()
);

-- Rule 5: Motor Overload Pattern (Same Device)
-- Detects recurring motor stress indicating mechanical binding or overload
INSERT INTO mpsm_notification_rules (
    name, description, severity, enabled,
    alert_code_pattern, device_serial_pattern, customer_code_pattern,
    frequency_count, frequency_window_hours, frequency_type,
    show_dashboard, send_email, email_recipients,
    auto_dismiss_hours, notification_title, notification_message,
    created_by, created_at
) VALUES (
    'Motor Overload Pattern',
    'Triggers when a device reports 4+ motor overload alerts within 24 hours, indicating mechanical binding, excessive load, or motor failure.',
    'high',
    1,
    'MOT%',   -- Matches MOTOR-%, MOT-%, etc.
    NULL,
    NULL,
    4,
    24,
    'same_device',
    1,
    0,
    NULL,
    48,
    'Motor Stress Detected',
    'Device experiencing repeated motor overload conditions. Inspect for mechanical binding, excessive resistance, belt tension, or motor bearing wear.',
    1,
    NOW()
);

/*
VERIFICATION QUERIES

-- View all created rules
SELECT id, name, severity, alert_code_pattern, frequency_count, frequency_window_hours, frequency_type, enabled
FROM mpsm_notification_rules
WHERE name IN (
    'Repeated JAM Alerts',
    'Emergency Stop Pattern',
    'Persistent Sensor Failures',
    'Widespread Communication Loss',
    'Motor Overload Pattern'
)
ORDER BY severity DESC, name;

-- Test pattern matching (replace with actual alert codes from your system)
SELECT alert_code, COUNT(*) as occurrences
FROM mpsm_panel_messages
WHERE alert_code LIKE 'JAM%'
  AND received_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY alert_code
ORDER BY occurrences DESC;

-- Check for existing notifications triggered by these rules
SELECT n.id, n.title, n.severity, r.name as rule_name, n.created_at
FROM mpsm_dashboard_notifications n
JOIN mpsm_notification_rules r ON n.rule_id = r.id
WHERE r.name IN (
    'Repeated JAM Alerts',
    'Emergency Stop Pattern',
    'Persistent Sensor Failures',
    'Widespread Communication Loss',
    'Motor Overload Pattern'
)
ORDER BY n.created_at DESC
LIMIT 20;
*/
