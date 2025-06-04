<?php
namespace Models;
class Role {
    public $id; public $name;
    public static function findById(int $id): ?self {
        $pdo = require __DIR__ . '/../config/db.php';
        $stmt = $pdo->prepare('SELECT id,name FROM roles WHERE id=?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) return null;
        $r = new self();
        $r->id = $row['id']; $r->name = $row['name'];
        return $r;
    }
}
?>