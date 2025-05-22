-- 007_create_auth_tables.sql
CREATE TABLE IF NOT EXISTS users (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  username       VARCHAR(50) UNIQUE NOT NULL,
  password_hash  VARCHAR(255) NOT NULL,
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS roles (
  id   INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) UNIQUE NOT NULL
);

CREATE TABLE IF NOT EXISTS permissions (
  id   INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) UNIQUE NOT NULL
);

CREATE TABLE IF NOT EXISTS role_permissions (
  role_id       INT NOT NULL,
  permission_id INT NOT NULL,
  PRIMARY KEY(role_id,permission_id),
  FOREIGN KEY(role_id)       REFERENCES roles(id)       ON DELETE CASCADE,
  FOREIGN KEY(permission_id) REFERENCES permissions(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS user_roles (
  user_id INT NOT NULL,
  role_id INT NOT NULL,
  PRIMARY KEY(user_id,role_id),
  FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY(role_id) REFERENCES roles(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS user_widget_settings (
  user_id INT      NOT NULL,
  widget  VARCHAR(50) NOT NULL,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY(user_id,widget),
  FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Seed the static roles
INSERT IGNORE INTO roles (name) VALUES
  ('Developer'),('Admin'),('Dealer'),('Service'),('Sales');
