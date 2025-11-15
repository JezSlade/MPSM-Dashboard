<?php
/**
 * Simple Git Deployment Script
 *
 * Pulls latest code from GitHub when triggered via HTTP.
 *
 * Usage: https://mpsm.resolutionsbydesign.us/deploy.php?secret=YOUR_SECRET
 *
 * IMPORTANT: Set DEPLOY_SECRET environment variable or define in config.php
 */

// SHOW ERRORS (development/debugging mode)
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

header('Content-Type: application/json');

// Load config for secret
if (file_exists(__DIR__ . '/cms/config.php')) {
    require_once __DIR__ . '/cms/config.php';
}

// Check for deployment secret
$deploySecret = defined('DEPLOY_SECRET') ? DEPLOY_SECRET : getenv('DEPLOY_SECRET');
$providedSecret = $_GET['secret'] ?? null;

if (!$deploySecret) {
    echo json_encode([
        'success' => false,
        'error' => 'Deployment secret not configured',
        'message' => 'Set DEPLOY_SECRET in config.php or as environment variable'
    ]);
    exit;
}

if ($providedSecret !== $deploySecret) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'error' => 'Invalid deployment secret'
    ]);
    exit;
}

// Execute git pull
$output = [];
$returnCode = 0;

// Change to script directory
chdir(__DIR__);

// Execute git pull
exec('git pull origin main 2>&1', $output, $returnCode);

$response = [
    'success' => $returnCode === 0,
    'timestamp' => date('Y-m-d H:i:s'),
    'output' => implode("\n", $output),
    'return_code' => $returnCode
];

if ($returnCode === 0) {
    $response['message'] = 'Deployment successful';
} else {
    $response['error'] = 'Git pull failed';
    http_response_code(500);
}

echo json_encode($response, JSON_PRETTY_PRINT);
