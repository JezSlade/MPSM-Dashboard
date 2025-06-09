<?php
class Database {
    private static PDO $pdo;

    public static function connect(array $env) {
        try {
            $dsn = "mysql:host={$env['DB_HOST']};dbname={$env['DB_NAME']};charset=utf8mb4";
            self::$pdo = new PDO($dsn, $env['DB_USER'], $env['DB_PASS'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
        } catch (PDOException $e) {
            error_log("DB Connection failed: ".$e->getMessage());
            die("Database connection error");
        }
    }
    public static function get(): PDO {
        return self::$pdo;
    }

    public static function initialize() {
        $sql = """
        CREATE TABLE IF NOT EXISTS roles (
          id INT AUTO_INCREMENT PRIMARY KEY,
          name VARCHAR(50) UNIQUE NOT NULL
        );
        CREATE TABLE IF NOT EXISTS users (
          id INT AUTO_INCREMENT PRIMARY KEY,
          username VARCHAR(50) UNIQUE NOT NULL,
          password_hash VARCHAR(255) NOT NULL,
          role_id INT NOT NULL,
          FOREIGN KEY(role_id) REFERENCES roles(id)
        );
        CREATE TABLE IF NOT EXISTS settings (
          `key` VARCHAR(50) PRIMARY KEY,
          `value` TEXT NOT NULL
        );
        CREATE TABLE IF NOT EXISTS modules (
          id INT AUTO_INCREMENT PRIMARY KEY,
          name VARCHAR(100) NOT NULL,
          class VARCHAR(100) NOT NULL
        );
        CREATE TABLE IF NOT EXISTS module_instances (
          id INT AUTO_INCREMENT PRIMARY KEY,
          module_id INT NOT NULL,
          position VARCHAR(50) NOT NULL,
          config JSON NULL,
          is_active TINYINT(1) NOT NULL DEFAULT 1,
          FOREIGN KEY(module_id) REFERENCES modules(id)
        );
        CREATE TABLE IF NOT EXISTS content (
          id INT AUTO_INCREMENT PRIMARY KEY,
          slug VARCHAR(100) UNIQUE NOT NULL,
          title VARCHAR(200) NOT NULL,
          body TEXT NOT NULL,
          created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          updated_at DATETIME NOT NULL ON UPDATE CURRENT_TIMESTAMP
        );
        """;
        self::$pdo->exec($sql);

        // Ensure theme setting exists
        if (!self::getSetting('theme')) {
            self::setSetting('theme','light');
        }
    }

    public static function seedRoles(array $names) {
        $stmt = self::$pdo->prepare("INSERT IGNORE INTO roles (name) VALUES (:n)");
        foreach ($names as $n) {
            $stmt->execute([':n'=>$n]);
        }
    }

    public static function seedUsers(array $users) {
        $get = self::$pdo->prepare("SELECT id FROM roles WHERE name=:r");
        $ins = self::$pdo->prepare("
          INSERT IGNORE INTO users (username,password_hash,role_id)
          VALUES (:u,:p,:rid)
        ");
        foreach ($users as $u) {
            $get->execute([':r'=>$u['role']]);
            $rid = $get->fetchColumn();
            if (!$rid) continue;
            $ins->execute([
                ':u'=>$u['username'],
                ':p'=>password_hash($u['password'], PASSWORD_DEFAULT),
                ':rid'=>$rid
            ]);
        }
    }

    public static function getSetting(string $key): ?string {
        $stmt = self::$pdo->prepare("SELECT `value` FROM settings WHERE `key`=:k");
        $stmt->execute([':k'=>$key]);
        return $stmt->fetchColumn() ?: null;
    }

    public static function setSetting(string $key, string $value): void {
        $stmt = self::$pdo->prepare("
          INSERT INTO settings (`key`,`value`)
          VALUES (:k,:v)
          ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)
        ");
        $stmt->execute([':k'=>$key,':v'=>$value]);
    }
}
