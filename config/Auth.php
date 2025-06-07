<?php
class Auth {
    private static ?array $user = null;

    public static function init(): void {
        if (!empty($_SESSION['user_id'])) {
            $stmt = Database::get()->prepare("
              SELECT u.id,u.username,r.name AS role
              FROM users u
              JOIN roles r ON u.role_id=r.id
              WHERE u.id=:id
            ");
            $stmt->execute([':id'=>$_SESSION['user_id']]);
            self::$user = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        }
    }

    public static function login(string $user, string $pass): bool {
        $stmt = Database::get()->prepare("
          SELECT u.id,u.password_hash,r.name AS role
          FROM users u
          JOIN roles r ON u.role_id=r.id
          WHERE u.username=:u
        ");
        $stmt->execute([':u'=>$user]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && password_verify($pass, $row['password_hash'])) {
            $_SESSION['user_id'] = $row['id'];
            return true;
        }
        return false;
    }

    public static function logout(): void {
        session_destroy();
        self::$user = null;
    }

    public static function user(): ?array {
        return self::$user;
    }

    public static function requireLogin(): void {
        if (!self::$user) {
            header('Location: ' . APP_BASE . '/?path=login');
            exit;
        }
    }

    public static function allow(array $roles): void {
        if (in_array('guest', $roles, true)) {
            return;
        }
        self::requireLogin();
        if (!in_array(self::$user['role'], $roles, true)) {
            die("Access denied");
        }
    }
}
