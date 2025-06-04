<?php
namespace Models;
class Module {
    public $id; public $name;
    public static function all(): array {
        $pdo = require __DIR__ . '/../config/db.php';
        $stmt = $pdo->query('SELECT id,name FROM modules');
        return $stmt->fetchAll();
    }
}
?>