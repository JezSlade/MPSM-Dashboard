-- 006_seed_initial_widgets.sql

INSERT IGNORE INTO widgets 
  (name, display_name, description, category, endpoint, params, method, permission, help_link)
VALUES
  ('DashboardOverview','Dashboard','Snapshot of all enabled widgets','Core',NULL,NULL,'dashboard','view_dashboard',NULL),
  ('CustomerCount','Total Customers','Shows how many customers you have','Dealer','get_customers.php',NULL,'count','view_customers',NULL),
  ('DeviceCount','Total Devices','Shows how many devices for the selected customer','Dealer','get_devices.php','{\"customerId\":\"{{selectedCustomer}}\"}','count','view_devices',NULL),
  ('RecentAlerts','Recent Alerts','Lists the latest device alerts','Service','get_devices.php','{\"customerId\":\"{{selectedCustomer}}\",\"limit\":5}','list','view_alerts',NULL),
  ('TonerLevels','Toner Levels','Displays toner levels for a device','Service','get_device_details.php','{\"deviceId\":\"{{selectedDevice}}\"}','table','view_device',NULL),
  ('DebugConsole','Debug Console','Live debug log viewer','Developer','get_debug_logs.php',NULL,'table','view_debug',NULL),
  ('UserProfile','My Profile','Edit your credentials and contact info','Core','get_profile.php',NULL,'custom','edit_profile',NULL),
  ('HelpCenter','Help Center','Comprehensive guide and release notes','Core','get_help_center.php',NULL,'custom','view_help',NULL),
  ('HelpSearch','Search Help','Search your help topics','Core','help_search.php',NULL,'custom','view_help',NULL),
  ('HelpFeedback','Submit Feedback','Send feedback on docs','Core','post_feedback.php',NULL,'custom','view_help',NULL),
  ('GuidedTour','Take a Tour','Interactive tour of the UI','Core',NULL,NULL,'custom','view_help',NULL),
  ('ReleaseNotes','Release Notes','What’s new each version','Core','get_release_notes.php',NULL,'custom','view_help',NULL),
  ('Changelog','Changelog','Detailed change log','Core','get_changelog.php',NULL,'custom','view_help',NULL);

-- assign defaults to roles
INSERT IGNORE INTO role_widgets (role_name,widget_id)
SELECT 'Developer', id FROM widgets;
INSERT IGNORE INTO role_widgets (role_name,widget_id)
SELECT 'Admin', id FROM widgets WHERE category IN ('Core','Developer','Dealer','Service','Sales');
INSERT IGNORE INTO role_widgets (role_name,widget_id)
SELECT 'Dealer', id FROM widgets WHERE category='Dealer';
INSERT IGNORE INTO role_widgets (role_name,widget_id)
SELECT 'Service', id FROM widgets WHERE category='Service';
INSERT IGNORE INTO role_widgets (role_name,widget_id)
SELECT 'Sales', id FROM widgets WHERE category='Sales';
