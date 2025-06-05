<?php
session_start();
define('BASE_PATH', __DIR__ . '/');
require_once BASE_PATH . 'auth.php';

$modules = [
    'dashboard' => ['label' => 'Dashboard', 'icon' => 'home'],
    'customers' => ['label' => 'Customers', 'icon' => 'users'],
    'devices' => ['label' => 'Devices', 'icon' => 'device-mobile'],
    'permissions' => ['label' => 'Permissions', 'icon' => 'lock-closed'],
    'devtools' => ['label' => 'DevTools', 'icon' => 'wrench']
];

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>MPSM Control Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">MPSM Control Panel</h1>
        <div class="grid grid-cols-3 gap-4">
            <?php foreach ($modules as $key => $module): ?>
                <?php if (has_permission('view_' . $key) || $key === 'dashboard'): ?>
                    <a href="?module=<?php echo $key; ?>" class="bg-white p-4 rounded shadow hover:bg-gray-50">
                        <span><?php echo $module['label']; ?></span>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php
        $module = $_GET['module'] ?? 'dashboard';
        if (array_key_exists($module, $modules)) {
            $file = BASE_PATH . 'modules/' . $module . '.php';
            if (file_exists($file)) {
                include_once $file;
            } else {
                echo "<p class='text-red-500 p-4'>Module not found.</p>";
            }
        }
        ?>
    </div>
</body>
</html>