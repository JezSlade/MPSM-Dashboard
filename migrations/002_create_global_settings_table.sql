-- 002_create_global_settings_table.sql
CREATE TABLE IF NOT EXISTS global_settings (
  `key`   VARCHAR(100) PRIMARY KEY,
  `value` TEXT NOT NULL
);

INSERT IGNORE INTO global_settings (`key`,`value`) VALUES
  ('api_token_url',                 'https://api.abassetmanagement.com/api3/token'),
  ('api_base_url',                  'https://api.abassetmanagement.com/api3/'),
  ('table_page_size',               '20'),
  ('table_export_formats',          'csv,json'),
  ('widget_refresh_interval',       '30'),
  ('debug_widget_row_limit',        '200'),
  ('debug_log_retention_limit',     '1000'),
  ('role_hierarchy_order',          '["Developer","Admin","Dealer","Service","Sales"]'),
  ('notification_email_from',       'no-reply@domain.com'),
  ('session_timeout_minutes',       '60');
