-- 005_create_role_widgets_table.sql
CREATE TABLE IF NOT EXISTS role_widgets (
  role_name VARCHAR(50) NOT NULL,
  widget_id INT NOT NULL,
  PRIMARY KEY(role_name, widget_id),
  FOREIGN KEY(widget_id) REFERENCES widgets(id) ON DELETE CASCADE
);
