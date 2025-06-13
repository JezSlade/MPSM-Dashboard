<?php
// index.php — SPA Entry Point (No Frameworks, Modular, Proof-of-Concept)

// === Load Environment Variables ===
$env = [];
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (strpos(trim($line), '#') === 0 || !strpos($line, '=')) continue;
        list($key, $value) = explode('=', $line, 2);
        $env[trim($key)] = trim($value);
    }
}

// === Handle Card Rendering (Server-Side Option) ===
$customerId = $_GET['customer'] ?? null;
$cardHtml = '';

if ($customerId) {
    $cardFiles = glob(__DIR__ . '/cards/*.php');
    foreach ($cardFiles as $file) {
        include_once $file;

        // Each card must define renderCard($customerId, $env)
        if (function_exists('renderCard')) {
            $cardHtml .= renderCard($customerId, $env);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard POC</title>
    <link rel="stylesheet" href="public/style.css">
</head>
<body>
    <header>
        Customer:
        <select id="customerSelect">
            <option disabled selected>Loading...</option>
        </select>
    </header>

<main>
    <div id="tokenStatusBanner" class="status-banner">Checking token...</div>
    <div id="dashboard">
        <?= $cardHtml ?>
    </div>
</main>


    <script>
        // Make env variables available to JS
        window.__ENV__ = <?= json_encode($env, JSON_HEX_TAG) ?>;
    </script>
    <script src="public/app.js"></script>
</body>
</html>
