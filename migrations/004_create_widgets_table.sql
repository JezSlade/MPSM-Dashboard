-- 004_create_widgets_table.sql
CREATE TABLE IF NOT EXISTS widgets (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(50) UNIQUE NOT NULL,
  display_name  VARCHAR(100) NOT NULL,
  description   TEXT,
  category      VARCHAR(50),
  endpoint      VARCHAR(255),
  params        JSON,
  method        VARCHAR(20) NOT NULL,
  permission    VARCHAR(50) NOT NULL,
  help_link     VARCHAR(255),
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
