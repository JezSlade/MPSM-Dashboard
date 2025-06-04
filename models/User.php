<?php
namespace Models;
class User {
    public $id;
    public $username;
    public $role_id;
    public static function findById(int $id): ?self {
        $pdo = require __DIR__ . '/../config/db.php';
        $stmt = $pdo->prepare('SELECT id,username,role_id FROM users WHERE id=?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) return null;
        $u = new self();
        $u->id = $row['id'];
        $u->username = $row['username'];
        $u->role_id = $row['role_id'];
        return $u;
    }
}
?>