<?php
session_start();
define('BASE_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);
require_once BASE_PATH . 'db.php';
require_once BASE_PATH . 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['switch_role'])) {
    $role_id = filter_input(INPUT_POST, 'role_id', FILTER_VALIDATE_INT);
    if ($role_id && $role_id > 0) {
        $_SESSION['role_id'] = $role_id; // Temporarily switch role for testing
    }
    header("Location: " . $_SERVER['PHP_SELF'] . "?module=" . ($_GET['module'] ?? 'dashboard'));
    exit;
}

$modules = [
    'dashboard' => 'view_dashboard',
    'customers' => 'view_customers',
    'devices' => 'view_devices',
    'permissions' => 'manage_permissions',
    'reports' => 'view_reports',
    'devtools' => 'view_devtools', // New DevTools module
];

$module = $_GET['module'] ?? 'dashboard';
if (!array_key_exists($module, $modules) || !has_permission($modules[$module])) {
    $module = 'dashboard';
}

include BASE_PATH . "modules/$module.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MPSM - <?php echo ucfirst($module); ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="header">
        <h1>MPSM Control Panel 🎛️</h1>
        <form method="POST" style="display: inline;">
            <select name="role_id" onchange="this.form.submit()">
                <?php
                $result = $db->query("SELECT id, name FROM roles");
                while ($role = $result->fetch_assoc()) {
                    $selected = isset($_SESSION['role_id']) && $_SESSION['role_id'] == $role['id'] ? 'selected' : '';
                    echo "<option value='{$role['id']}' $selected>{$role['name']}</option>";
                }
                ?>
            </select>
            <input type="hidden" name="switch_role" value="1">
        </form>
        <a href="logout.php">Logout 👋</a>
    </div>

    <div class="main">
        <div class="sidebar">
            <ul>
                <?php foreach ($modules as $mod => $perm): ?>
                    <?php if (has_permission($perm)): ?>
                        <li><a href="index.php?module=<?php echo $mod; ?>" class="<?php echo $module === $mod ? 'active' : ''; ?>"><?php echo ucfirst($mod); ?> 📌</a></li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="content">
            <?php include BASE_PATH . "modules/$module.php"; ?>
        </div>
    </div>
</body>
</html>