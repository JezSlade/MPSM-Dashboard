<?php
class Database {
    private $pdo;
    private $inTransaction = false;

    public function __construct() {
        $this->connect();
        $this->checkSchema();
    }

    private function connect(): void {
        try {
            $this->pdo = new PDO('sqlite:' . DB_FILE, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => false,
                PDO::ATTR_TIMEOUT => 30
            ]);
            $this->pdo->exec('PRAGMA foreign_keys = ON');
            $this->pdo->exec('PRAGMA journal_mode = WAL');
        } catch (PDOException $e) {
            throw new RuntimeException("Database connection failed: " . $e->getMessage());
        }
    }

    public function beginTransaction(): bool {
        if ($this->inTransaction) {
            throw new RuntimeException("Transaction already in progress");
        }
        $this->inTransaction = $this->pdo->beginTransaction();
        return $this->inTransaction;
    }

    public function commit(): bool {
        if (!$this->inTransaction) {
            throw new RuntimeException("No active transaction");
        }
        try {
            $result = $this->pdo->commit();
            $this->inTransaction = false;
            return $result;
        } catch (PDOException $e) {
            $this->inTransaction = true; // Preserve state
            throw new RuntimeException("Commit failed: " . $e->getMessage());
        }
    }

    public function rollBack(): bool {
        if (!$this->inTransaction) {
            throw new RuntimeException("No active transaction");
        }
        try {
            $result = $this->pdo->rollBack();
            $this->inTransaction = false;
            return $result;
        } catch (PDOException $e) {
            $this->inTransaction = false; // Force reset
            throw new RuntimeException("Rollback failed: " . $e->getMessage());
        }
    }

    public function query(string $sql, array $params = []): PDOStatement {
        try {
            $stmt = $this->pdo->prepare($sql);
            foreach ($params as $key => $value) {
                $type = is_int($value) ? PDO::PARAM_INT : 
                       (is_bool($value) ? PDO::PARAM_BOOL : 
                       (is_null($value) ? PDO::PARAM_NULL : PDO::PARAM_STR));
                $stmt->bindValue(is_int($key) ? $key+1 : ":{$key}", $value, $type);
            }
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            if ($this->inTransaction) {
                $this->rollBack();
            }
            throw new RuntimeException("Query failed: " . $e->getMessage());
        }
    }

    private function checkSchema(): void {
        $currentVersion = $this->getSetting('schema_version');
        if ($currentVersion !== DB_SCHEMA_VERSION) {
            $this->migrateSchema($currentVersion);
        }
    }

    private function migrateSchema(?string $currentVersion): void {
        $this->beginTransaction();
        try {
            // Migration logic would go here
            $this->setSetting('schema_version', DB_SCHEMA_VERSION);
            $this->commit();
        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    public function getSetting(string $key): ?string {
        $stmt = $this->query("SELECT value FROM settings WHERE key = ?", [$key]);
        return $stmt->fetchColumn() ?: null;
    }

    public function setSetting(string $key, string $value): bool {
        return $this->query(
            "INSERT OR REPLACE INTO settings (key, value) VALUES (?, ?)",
            [$key, $value]
        )->rowCount() > 0;
    }
}