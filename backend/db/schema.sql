
CREATE TABLE IF NOT EXISTS curated_data (
    id TEXT PRIMARY KEY,
    source TEXT NOT NULL,
    payload TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_source ON curated_data (source);
CREATE INDEX IF NOT EXISTS idx_created_at ON curated_data (created_at);
