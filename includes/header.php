<?php
// --- DEBUG BLOCK (Always Keep at Top) ---
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/debug.log');
// ----------------------------------------

require_once __DIR__ . '/config.php';

// Check token status
$env = load_env();
$token_status = 'unknown';

if (!empty($env['CLIENT_ID']) && !empty($env['TOKEN_URL'])) {
    $postFields = http_build_query([
        'grant_type'    => 'password',
        'client_id'     => $env['CLIENT_ID'],
        'client_secret' => $env['CLIENT_SECRET'],
        'username'      => $env['USERNAME'],
        'password'      => $env['PASSWORD'],
        'scope'         => $env['SCOPE']
    ]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $env['TOKEN_URL']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded',
        'Accept: application/json'
    ]);

    $response = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $json = json_decode($response, true);
    $token_status = ($code === 200 && isset($json['access_token'])) ? 'good' : 'bad';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= APP_NAME ?></title>
  <link rel="stylesheet" href="<?= APP_BASE_URL ?>public/css/styles.css" />
</head>
<body class="dark-theme">
  <div id="wrapper">
    <header class="glass-header">
      <h1><?= APP_NAME ?></h1>
      <div style="display: flex; align-items: center; gap: 1rem;">
        <span class="token-indicator <?= $token_status === 'good' ? 'status-ok' : 'status-fail' ?>" title="API Token Status"></span>
        <button id="theme-toggle" aria-label="Toggle Theme">🌓</button>
      </div>
    </header>
