<?php
/**
 * Get Dealers API
 * MPS Monitor has no "list my dealers" call that spans multiple logins -
 * each dealer is its own separate credential set. This just returns the
 * default dealer plus whatever dealer profiles the user has added, for the
 * dealer switcher dropdown.
 */
require '../config.php';
require '../functions.php';

requireAuth();

$prefs = getUserPreferences($_SESSION['user_id']);
$profiles = $prefs['dealerProfiles'] ?? [];

$dealers = [[
    'Code' => DEFAULT_DEALER_CODE,
    'Id' => DEFAULT_DEALER_ID,
    'Description' => 'Default dealership',
]];

foreach ($profiles as $profile) {
    $dealers[] = [
        'Code' => $profile['dealerCode'],
        'Id' => $profile['dealerId'] ?? '',
        'Description' => $profile['label'] ?? $profile['dealerDescription'] ?? $profile['dealerCode'],
    ];
}

jsonSuccess([
    'dealers' => $dealers,
    'total' => count($dealers)
]);
