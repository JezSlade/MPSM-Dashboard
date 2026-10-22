<?php
/**
 * Get User Preferences API
 */

require '../config.php';
require '../functions.php';

requireAuth();

$userId = $_SESSION['user_id'];

try {
    $preferences = getUserPreferences($userId);
    // Never send saved dealer credentials (even encrypted) to the browser
    $preferences['dealerProfiles'] = array_map(function ($profile) {
        unset($profile['mpsPasswordEnc'], $profile['mpsClientSecretEnc']);
        return $profile;
    }, $preferences['dealerProfiles'] ?? []);
    jsonSuccess(['preferences' => $preferences]);
} catch (Exception $e) {
    jsonError($e->getMessage());
}
