<?php
define('ROOT_DIR', __DIR__);
define('SETUP_COMPLETE_FILE', ROOT_DIR . '/.setup_complete');
define('CONFIG_FILE', ROOT_DIR . '/config.php');
define('DB_FILE', ROOT_DIR . '/db/cms.db');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_GET['reset']) && $_GET['reset'] === '1') {
    if (file_exists(SETUP_COMPLETE_FILE)) {
        unlink(SETUP_COMPLETE_FILE);
    }
    session_destroy();
    header('Location: setup.php');
    exit;
}

if (file_exists(SETUP_COMPLETE_FILE)) {
    file_put_contents(SETUP_COMPLETE_FILE, 'done');
    session_destroy();
    header('Location: index.php');
    exit;
}

$current_step = $_SESSION['setup_step'] ?? 1;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        switch ($current_step) {
            case 1:
            default:
                $_SESSION['setup_step'] = 2;
                break;

            case 2:
                if (!file_exists(dirname(DB_FILE))) {
                    mkdir(dirname(DB_FILE), 0755, true);
                }

                $db = new PDO('sqlite:' . DB_FILE);
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $db->exec("CREATE TABLE IF NOT EXISTS settings (
                    key TEXT PRIMARY KEY NOT NULL,
                    value TEXT
                );");

                // Create widgets table (Patch)
                $db->exec("CREATE TABLE IF NOT EXISTS widgets (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL,
                    type TEXT NOT NULL,
                    config TEXT,
                    is_active INTEGER NOT NULL DEFAULT 1,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );");

                file_put_contents(SETUP_COMPLETE_FILE, 'done');
                session_destroy();
                header('Location: index.php');
                exit;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
        $_SESSION['setup_error'] = true;
    }

    header('Location: setup.php');
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Setup</title>
</head>
<body>
    <h1>Setup Step <?= htmlspecialchars($current_step); ?></h1>
    <form method="post">
        <button type="submit">Continue</button>
    </form>
</body>
</html>
