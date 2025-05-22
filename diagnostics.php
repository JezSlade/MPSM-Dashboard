<?php
// diagnostics.php — Server‐side project snapshot & integrity report

// 1) Bootstrap your app so all constants, DB, and functions are loaded
require __DIR__ . '/core/bootstrap.php';
header('Content-Type: application/json');

// Helpers
function file_md5(string $path): string {
    return md5_file($path) ?: '';
}
function scan_dir(string $dir, array &$out, array $skip = ['vendor','node_modules','logs','.git']) {
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($it as $file) {
        $parts = explode(DIRECTORY_SEPARATOR, $file->getPath());
        if (array_intersect($parts, $skip)) continue;
        if ($file->isFile()) {
            $rel = substr($file->getPathname(), strlen(__DIR__)+1);
            $out[$rel] = file_md5($file->getPathname());
        }
    }
}

// 2) Gather data
$report = [];

// PHP environment
$report['php'] = [
    'version'    => phpversion(),
    'extensions' => get_loaded_extensions(),
    'ini'        => [
        'display_errors'         => ini_get('display_errors'),
        'display_startup_errors' => ini_get('display_startup_errors'),
        'error_reporting'        => ini_get('error_reporting'),
    ],
];

// ENV vars & config
$report['env'] = [
    'ENVIRONMENT' => defined('ENVIRONMENT') ? ENVIRONMENT : null,
    'DEBUG'       => defined('DEBUG') ? DEBUG : null,
];

// Database schema
try {
    $pdo = get_db();
    // Get list of tables
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $report['database'] = [
        'name'          => DB_NAME,
        'table_count'   => count($tables),
        'tables'        => $tables,
        'migrations'    => (int)$pdo->query("SELECT COUNT(*) FROM migrations")->fetchColumn(),
        'widgets_count' => (int)$pdo->query("SELECT COUNT(*) FROM widgets")->fetchColumn(),
        'users_count'   => (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    ];
} catch (Exception $e) {
    $report['database_error'] = $e->getMessage();
}

// Auth & ACL
try {
    // Roles
    $roles = get_db()->query("SELECT name FROM roles")->fetchAll(PDO::FETCH_COLUMN);
    // Permissions
    $perms = get_db()->query("SELECT name FROM permissions")->fetchAll(PDO::FETCH_COLUMN);
    $report['acl'] = [
        'roles'       => $roles,
        'permissions' => $perms,
    ];
} catch (Exception $e) {
    $report['acl_error'] = $e->getMessage();
}

// Widgets
try {
    $all = get_all_widgets();
    $usr = isset($_SESSION['user_id']) ? get_user_widgets() : [];
    $report['widgets'] = [
        'all_count'  => count($all),
        'user_count' => count($usr),
        'sample_all' => array_map(fn($w)=>[
            'name'=>$w['name'],'endpoint'=>$w['endpoint']
        ], array_slice($all,0,5)),
        'sample_user'=> array_map(fn($w)=>$w['name'], array_slice($usr,0,5)),
    ];
} catch (Exception $e) {
    $report['widgets_error'] = $e->getMessage();
}

// File inventory with MD5
$files = [];
scan_dir(__DIR__, $files);
$report['files'] = $files;

// Output
echo json_encode($report, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
