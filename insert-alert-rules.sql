-- Insert Common Sense Alert Rules
-- Execute this SQL to populate 5 standard alert rules for recurring device issues

-- Rule 1: Repeated JAM Alerts
INSERT INTO mpsm_notification_rules
    (name, description, severity, enabled, alert_code_pattern,
     device_serial_pattern, customer_code_pattern, frequency_count,
     frequency_window_hours, frequency_type, show_dashboard,
     send_email, email_recipients, auto_dismiss_hours,
     notification_title, notification_message, created_by, created_at)
SELECT * FROM (SELECT
    'Repeated JAM Alerts' AS name,
    'Triggers when a device reports 3 or more JAM alerts within 24 hours, indicating potential mechanical issues requiring maintenance.' AS description,
    'high' AS severity,
    1 AS enabled,
    'JAM%' AS alert_code_pattern,
    NULL AS device_serial_pattern,
    NULL AS customer_code_pattern,
    3 AS frequency_count,
    24 AS frequency_window_hours,
    'same_device' AS frequency_type,
    1 AS show_dashboard,
    0 AS send_email,
    NULL AS email_recipients,
    48 AS auto_dismiss_hours,
    'Recurring JAM Issue Detected' AS notification_title,
    'Device experiencing repeated jam alerts. Inspect for mechanical blockages, paper fragments, or sensor malfunctions.' AS notification_message,
    1 AS created_by,
    NOW() AS created_at
) AS tmp
WHERE NOT EXISTS (
    SELECT 1 FROM mpsm_notification_rules WHERE name = 'Repeated JAM Alerts'
);

-- Rule 2: Emergency Stop Pattern
INSERT INTO mpsm_notification_rules
    (name, description, severity, enabled, alert_code_pattern,
     device_serial_pattern, customer_code_pattern, frequency_count,
     frequency_window_hours, frequency_type, show_dashboard,
     send_email, email_recipients, auto_dismiss_hours,
     notification_title, notification_message, created_by, created_at)
SELECT * FROM (SELECT
    'Emergency Stop Pattern' AS name,
    'Triggers when 2 or more emergency stop alerts occur at the same customer location within 12 hours, suggesting safety concerns or operator errors.' AS description,
    'critical' AS severity,
    1 AS enabled,
    'E-%' AS alert_code_pattern,
    NULL AS device_serial_pattern,
    NULL AS customer_code_pattern,
    2 AS frequency_count,
    12 AS frequency_window_hours,
    'same_customer' AS frequency_type,
    1 AS show_dashboard,
    0 AS send_email,
    NULL AS email_recipients,
    24 AS auto_dismiss_hours,
    'Multiple Emergency Stops' AS notification_title,
    'Multiple emergency stop events detected at this location. Review safety protocols and operator training. Verify emergency stop button function.' AS notification_message,
    1 AS created_by,
    NOW() AS created_at
) AS tmp
WHERE NOT EXISTS (
    SELECT 1 FROM mpsm_notification_rules WHERE name = 'Emergency Stop Pattern'
);

-- Rule 3: Persistent Sensor Failures
INSERT INTO mpsm_notification_rules
    (name, description, severity, enabled, alert_code_pattern,
     device_serial_pattern, customer_code_pattern, frequency_count,
     frequency_window_hours, frequency_type, show_dashboard,
     send_email, email_recipients, auto_dismiss_hours,
     notification_title, notification_message, created_by, created_at)
SELECT * FROM (SELECT
    'Persistent Sensor Failures' AS name,
    'Triggers when a device reports 5+ sensor errors within 48 hours, indicating sensor hardware failure or wiring issues.' AS description,
    'high' AS severity,
    1 AS enabled,
    'SENS%' AS alert_code_pattern,
    NULL AS device_serial_pattern,
    NULL AS customer_code_pattern,
    5 AS frequency_count,
    48 AS frequency_window_hours,
    'same_device' AS frequency_type,
    1 AS show_dashboard,
    0 AS send_email,
    NULL AS email_recipients,
    72 AS auto_dismiss_hours,
    'Sensor System Malfunction' AS notification_title,
    'Device reporting persistent sensor errors. Check sensor connections, wiring integrity, and consider sensor replacement.' AS notification_message,
    1 AS created_by,
    NOW() AS created_at
) AS tmp
WHERE NOT EXISTS (
    SELECT 1 FROM mpsm_notification_rules WHERE name = 'Persistent Sensor Failures'
);

-- Rule 4: Widespread Communication Loss
INSERT INTO mpsm_notification_rules
    (name, description, severity, enabled, alert_code_pattern,
     device_serial_pattern, customer_code_pattern, frequency_count,
     frequency_window_hours, frequency_type, show_dashboard,
     send_email, email_recipients, auto_dismiss_hours,
     notification_title, notification_message, created_by, created_at)
SELECT * FROM (SELECT
    'Widespread Communication Loss' AS name,
    'Triggers when 10+ devices report communication loss within 1 hour, suggesting network infrastructure issues rather than individual device failures.' AS description,
    'critical' AS severity,
    1 AS enabled,
    'COMM%' AS alert_code_pattern,
    NULL AS device_serial_pattern,
    NULL AS customer_code_pattern,
    10 AS frequency_count,
    1 AS frequency_window_hours,
    'same_alert' AS frequency_type,
    1 AS show_dashboard,
    0 AS send_email,
    NULL AS email_recipients,
    6 AS auto_dismiss_hours,
    'Network Infrastructure Issue' AS notification_title,
    'Multiple devices experiencing communication loss simultaneously. Check network infrastructure, router status, and ISP connection.' AS notification_message,
    1 AS created_by,
    NOW() AS created_at
) AS tmp
WHERE NOT EXISTS (
    SELECT 1 FROM mpsm_notification_rules WHERE name = 'Widespread Communication Loss'
);

-- Rule 5: Motor Overload Pattern
INSERT INTO mpsm_notification_rules
    (name, description, severity, enabled, alert_code_pattern,
     device_serial_pattern, customer_code_pattern, frequency_count,
     frequency_window_hours, frequency_type, show_dashboard,
     send_email, email_recipients, auto_dismiss_hours,
     notification_title, notification_message, created_by, created_at)
SELECT * FROM (SELECT
    'Motor Overload Pattern' AS name,
    'Triggers when a device reports 4+ motor overload alerts within 24 hours, indicating mechanical binding, excessive load, or motor failure.' AS description,
    'high' AS severity,
    1 AS enabled,
    'MOT%' AS alert_code_pattern,
    NULL AS device_serial_pattern,
    NULL AS customer_code_pattern,
    4 AS frequency_count,
    24 AS frequency_window_hours,
    'same_device' AS frequency_type,
    1 AS show_dashboard,
    0 AS send_email,
    NULL AS email_recipients,
    48 AS auto_dismiss_hours,
    'Motor Stress Detected' AS notification_title,
    'Device experiencing repeated motor overload conditions. Inspect for mechanical binding, excessive resistance, belt tension, or motor bearing wear.' AS notification_message,
    1 AS created_by,
    NOW() AS created_at
) AS tmp
WHERE NOT EXISTS (
    SELECT 1 FROM mpsm_notification_rules WHERE name = 'Motor Overload Pattern'
);

-- Verify inserted rules
SELECT
    id,
    name,
    alert_code_pattern,
    CONCAT(frequency_count, ' in ', frequency_window_hours, 'h') AS threshold,
    frequency_type,
    severity,
    enabled
FROM mpsm_notification_rules
WHERE name IN (
    'Repeated JAM Alerts',
    'Emergency Stop Pattern',
    'Persistent Sensor Failures',
    'Widespread Communication Loss',
    'Motor Overload Pattern'
)
ORDER BY id;
