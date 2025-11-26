<?php
require 'config.php';
require 'functions.php';
requireAuth();
$userId = $_SESSION['user_id'] ?? null;
$preferences = $userId ? getUserPreferences($userId) : [];
$customer = $_GET['customerCode'] ?? ($preferences['customerCode'] ?? (defined('DEFAULT_CUSTOMER_CODE') ? DEFAULT_CUSTOMER_CODE : ''));
$qs = $customer ? ('?tab=panel&customerCode=' . urlencode($customer)) : ('?tab=panel');
header('Location: command-center.php' . $qs, true, 302);
exit;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Message Monitor (Moved)</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <main class="monitor-container">
        <div class="card">
            <div class="card-header">
                <div>
                    <h2>Page Moved</h2>
                    <p class="text-muted">This page has moved to the unified Command Center.</p>
                </div>
                <a class="btn btn-primary" href="command-center.php?tab=panel"><i class="fas fa-shield-alt"></i> Go to Command Center</a>
            </div>
            <div class="card-body">
                <p>You will be redirected automatically.</p>
            </div>
        </div>
    </main>
    <script>setTimeout(function(){ window.location.replace('command-center.php?tab=panel'); }, 1000);</script>
</body>
</html>
<?php
/*
CHANGELOG
2025-11-26 Codex
- Retired legacy Panel Message Monitor: redirect to unified Command Center (Panel tab) with fallback message.
*/
?>