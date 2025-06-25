<?php
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
?>
<?php require_once __DIR__ . '/includes/env.php'; ?>
<?php require_once __DIR__ . '/includes/header.php'; ?>
<?php render_view(__DIR__ . '/views/dashboard.php'); ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>