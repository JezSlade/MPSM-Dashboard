-- 003_create_debug_logs_table.sql
CREATE TABLE IF NOT EXISTS debug_logs (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  level      VARCHAR(10) NOT NULL,
  message    TEXT NOT NULL,
  context    JSON
);
