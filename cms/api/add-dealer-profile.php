<?php
/**
 * Add Dealer Profile API
 * MPS Monitor gives each dealer its own complete login (username/password +
 * OAuth client ID/secret) - there is no single account that sees multiple
 * dealers. This verifies a new dealer's credentials live, then saves the
 * resolved dealer code alongside them for the dealer switcher to use.
 */
require '../config.php';
require '../functions.php';

requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method not allowed', 405);
}

$userId = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true) ?: [];

$label = trim($data['label'] ?? '');
$username = trim($data['username'] ?? '');
$password = (string)($data['password'] ?? '');
$clientId = trim($data['clientId'] ?? '');
$clientSecret = (string)($data['clientSecret'] ?? '');

if (!$label || !$username || !$password || !$clientId || !$clientSecret) {
    jsonError('Label, username, password, client ID, and client secret are all required', 400);
}

try {
    $payload = json_encode([
        'action' => 'Dealer/GetDealers',
        'params' => ['PageNumber' => 1, 'PageRows' => 5, 'SortColumn' => 'Description', 'SortOrder' => 'Asc'],
        'auth' => [
            'username' => $username,
            'password' => $password,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ]
    ]);

    $context = stream_context_create(['http' => [
        'method' => 'POST', 'header' => 'Content-Type: application/json',
        'content' => $payload, 'timeout' => 20, 'ignore_errors' => true
    ]]);
    $response = @file_get_contents('https://mpsm.resolutionsbydesign.us/mps-api/query', false, $context);
    $result = json_decode($response, true);

    if (!$result || !($result['success'] ?? false) || empty($result['data'][0]['Code'])) {
        jsonError('Could not verify these credentials against MPS Monitor: ' . ($result['error'] ?? 'no dealer returned'));
    }

    $dealer = $result['data'][0];

    $prefs = getUserPreferences($userId);
    $profiles = $prefs['dealerProfiles'] ?? [];
    $profiles = array_values(array_filter($profiles, fn($p) => ($p['dealerCode'] ?? null) !== $dealer['Code']));
    $profiles[] = [
        'label' => $label,
        'dealerCode' => $dealer['Code'],
        'dealerId' => $dealer['Id'] ?? '',
        'dealerDescription' => $dealer['Description'] ?? $label,
        'mpsUsername' => $username,
        'mpsPasswordEnc' => encryptSecret($password),
        'mpsClientId' => $clientId,
        'mpsClientSecretEnc' => encryptSecret($clientSecret),
    ];

    saveUserPreferences($userId, ['dealerProfiles' => $profiles]);

    jsonSuccess([
        'dealerCode' => $dealer['Code'],
        'dealerId' => $dealer['Id'] ?? '',
        'dealerDescription' => $dealer['Description'] ?? $label,
    ]);
} catch (Exception $e) {
    jsonError('Failed to add dealer: ' . $e->getMessage());
}
