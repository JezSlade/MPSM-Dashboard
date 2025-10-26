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
    jsonSuccess(['preferences' => $preferences]);
} catch (Exception $e) {
    jsonError($e->getMessage());
}
