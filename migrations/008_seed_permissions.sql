-- 008_seed_permissions.sql
-- Seed default permissions and assign to roles

INSERT IGNORE INTO permissions (name) VALUES
  ('view_dashboard'),
  ('view_customers'),
  ('view_devices'),
  ('view_alerts'),
  ('view_device'),
  ('view_debug'),
  ('edit_profile'),
  ('view_help'),
  ('view_widgets'),
  ('manage_users'),
  ('manage_roles'),
  ('manage_widgets'),
  ('manage_role_widgets'),
  ('manage_widget_settings');

-- Give ALL permissions to Developer
INSERT IGNORE INTO role_permissions (role_id,permission_id)
  SELECT r.id, p.id
    FROM roles r
    CROSS JOIN permissions p
   WHERE r.name = 'Developer';

-- Give core view/manage perms to Admin
INSERT IGNORE INTO role_permissions (role_id,permission_id)
  SELECT r.id, p.id
    FROM roles r
    JOIN permissions p ON p.name IN (
      'view_dashboard','view_customers','view_devices','view_alerts',
      'view_device','view_debug','edit_profile','view_help',
      'view_widgets','manage_users','manage_roles','manage_widgets',
      'manage_role_widgets','manage_widget_settings'
    )
   WHERE r.name = 'Admin';
