<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'config.php';
require_once 'db.php';
require_once 'functions.php';

$modules = [
    'dashboard' => 'view_dashboard',
    'customers' => 'view_customers',
    'devices' => 'view_devices',
    'permissions' => 'manage_permissions',
];
$module = $_GET['module'] ?? 'dashboard';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MPSM Modular App</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="header">
        <h1>MPSM Modular App</h1>
        <div>
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="logout.php">Logout</a>
        </div>
    </div>
    <div class="main">
        <div class="sidebar">
            <ul>
                <?php foreach ($modules as $mod => $perm): ?>
                    <?php if (has_permission($perm)): ?>
                        <li><a href="index.php?module=<?php echo $mod; ?>" <?php echo $module === $mod ? 'class="active"' : ''; ?>><?php echo ucfirst($mod); ?></a></li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="content">
            <?php
            if (array_key_exists($module, $modules)) {
                if (has_permission($modules[$module])) {
                    include "modules/$module.php";
                } else {
                    echo "<p class='error'>Access denied.</p>";
                }
            } else {
                echo "<p class='error'>Module not found.</p>";
            }
            ?>
        </div>
    </div>
</body>
</html>
