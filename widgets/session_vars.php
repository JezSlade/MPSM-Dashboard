<?php
// PATCHED: widgets/session_vars.php (v2.1) - Automatic token refresh logic integrated
// Backup created at /backup/widgets/session_vars.php.bak

// Widget Name: Session Variables
// Widget Icon: fas fa-bug
// Widget Width: 2.0
// Widget Height: 2.0

$_widget_config = [
    'name' => 'Session Variables',
    'icon' => 'bug',
    'width' => 2,
    'height' => 2
];

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$excluded_prefixes = ['dashboard_', 'widget_', 'ui_', 'layout_', 'grid_', 'settings'];
$excluded_keys = ['active_widgets', 'widget_order', 'dashboard_config'];

function is_runtime_variable($key, $excluded_keys, $excluded_prefixes) {
    if (in_array($key, $excluded_keys, true)) {
        return false;
    }
    foreach ($excluded_prefixes as $prefix) {
        if (stripos($key, $prefix) === 0) {
            return false;
        }
    }
    return true;
}

$runtime_session = [];
foreach ($_SESSION as $key => $value) {
    if (is_runtime_variable($key, $excluded_keys, $excluded_prefixes)) {
        $runtime_session[$key] = $value;
    }
}

// Token specific processing with auto-refresh
$token = $_SESSION['mps_token'] ?? null;
$timestamp = $_SESSION['mps_token_timestamp'] ?? null;
$token_info = '';

if ($token && $timestamp) {
    $expiresIn = (int)$token['expires_in'];
    $elapsed = time() - $timestamp;
    $remaining = max($expiresIn - $elapsed, 0);

    // Auto-refresh if less than 60 seconds remaining and refresh_token exists
    if ($remaining < 60 && !empty($token['refresh_token'])) {
        $refreshUrl = "/mps_monitor/api/get_token.php?refresh=true";
        $refreshResponse = @file_get_contents($refreshUrl);
        if ($refreshResponse) {
            $decoded = json_decode($refreshResponse, true);
            if (isset($decoded['data']['access_token'])) {
                $_SESSION['mps_token'] = $decoded['data'];
                $_SESSION['mps_token_timestamp'] = time();
                $token = $_SESSION['mps_token'];
                $timestamp = $_SESSION['mps_token_timestamp'];
                $expiresIn = (int)$token['expires_in'];
                $remaining = $expiresIn;
            }
        }
    }

    $token_info = "<strong>Access Token:</strong> " . htmlspecialchars($token['access_token']) . "<br>"
        . "<strong>Type:</strong> " . htmlspecialchars($token['token_type']) . "<br>"
        . "<strong>Expires In:</strong> {$expiresIn} sec<br>"
        . "<strong>Remaining:</strong> {$remaining} sec<br>"
        . "<strong>Refresh Token:</strong> " . htmlspecialchars($token['refresh_token']) . "<br>"
        . "<strong>Fetched At:</strong> " . htmlspecialchars($token['fetched_at']) . "<br>";
}
?>

<div class="widget-body">
    <h3 class="widget-section-title">Runtime Session Variables</h3>
    <?php if (!empty($runtime_session)): ?>
        <pre><?= htmlspecialchars(print_r($runtime_session, true)) ?></pre>
    <?php else: ?>
        <p><em>No runtime session variables are set.</em></p>
    <?php endif; ?>

    <h3 class="widget-section-title">Cookies</h3>
    <?php if (!empty($_COOKIE)): ?>
        <pre><?= htmlspecialchars(print_r($_COOKIE, true)) ?></pre>
    <?php else: ?>
        <p><em>No cookies set.</em></p>
    <?php endif; ?>

    <h3 class="widget-section-title">Tokens</h3>
    <?php if ($token_info): ?>
        <?= $token_info ?>
    <?php else: ?>
        <p><em>No tokens found in session or cookies.</em></p>
    <?php endif; ?>
</div>
