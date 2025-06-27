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

// Handle reset
if (isset($_GET['reset']) && $_GET['reset'] === '1') {
    if (file_exists(SETUP_COMPLETE_FILE)) {
        unlink(SETUP_COMPLETE_FILE);
    }
    session_destroy();
    header('Location: setup.php');
    exit;
}

// If setup already complete, redirect to index
if (file_exists(SETUP_COMPLETE_FILE)) {
    file_put_contents(SETUP_COMPLETE_FILE, 'done');  // Update timestamp
    session_destroy();
    header('Location: index.php');
    exit;
}

// Determine current step
$current_step = $_SESSION['setup_step'] ?? 1;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        switch ($current_step) {
            case 1:
                if (version_compare(PHP_VERSION, '7.4.0', '<')) {
                    throw new Exception('PHP 7.4 or higher is required.');
                }
                $_SESSION['setup_step'] = 2;
                break;
            case 2:
                if (!file_exists(dirname(DB_FILE))) {
                    mkdir(dirname(DB_FILE), 0755, true);
                }
                $db = new PDO('sqlite:' . DB_FILE);
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $db->exec("CREATE TABLE IF NOT EXISTS settings (
                    key TEXT PRIMARY KEY,
                    value TEXT
                )");
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Setup</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f0f0;
            padding: 2rem;
        }
        .container {
            max-width: 700px;
            background: #fff;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
            margin: auto;
        }
        h1, h2 {
            color: #333;
        }
        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin: 1rem 0;
        }
        .alert-danger {
            background: #ffdddd;
            color: #990000;
        }
        .alert-success {
            background: #ddffdd;
            color: #006600;
        }
        .button {
            display: inline-block;
            padding: 0.6rem 1.2rem;
            background: #007BFF;
            color: #fff;
            border: none;
            border-radius: 0.5rem;
            text-decoration: none;
        }
        .button:hover {
            background: #0056b3;
        }
        form {
            margin-top: 2rem;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>System Setup</h1>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="step" value="<?= $current_step ?>">

        <?php if ($current_step === 1): ?>
            <h2>Step 1: System Requirements</h2>
            <p>This setup will check that your server meets all the required extensions and PHP version.</p>
            <button type="submit" class="button">Continue</button>
        <?php elseif ($current_step === 2): ?>
            <h2>Step 2: Initialize Database</h2>
            <p>This step will create the necessary database and schema.</p>
            <button type="submit" class="button">Finish Setup</button>
        <?php endif; ?>
    </form>

    <p><a href="setup.php?reset=1" class="button">Restart Setup</a></p>
</div>
</body>
</html>
