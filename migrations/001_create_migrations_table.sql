-- 001_create_migrations_table.sql
CREATE TABLE IF NOT EXISTS migrations (
  version VARCHAR(50) PRIMARY KEY,
  applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
