<?php
require_once __DIR__ . '/../core/TokenManager.php';

try {
    $token = TokenManager::getToken();
    echo "✅ Token acquired: " . $token . PHP_EOL;
} catch (Exception $e) {
    echo "❌ Token error: " . $e->getMessage() . PHP_EOL;
}
