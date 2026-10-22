<?php
/**
 * Remove Dealer Profile API
 */
require '../config.php';
require '../functions.php';

requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method not allowed', 405);
}

$userId = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true) ?: [];
$dealerCode = trim($data['dealerCode'] ?? '');

if (!$dealerCode) {
    jsonError('dealerCode is required', 400);
}

try {
    $prefs = getUserPreferences($userId);
    $profiles = array_values(array_filter(
        $prefs['dealerProfiles'] ?? [],
        fn($p) => ($p['dealerCode'] ?? null) !== $dealerCode
    ));
    saveUserPreferences($userId, ['dealerProfiles' => $profiles]);
    jsonSuccess(['removed' => $dealerCode]);
} catch (Exception $e) {
    jsonError('Failed to remove dealer: ' . $e->getMessage());
}
