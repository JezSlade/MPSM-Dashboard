<?php
/**
 * Save User Preferences API
 */

require '../config.php';
require '../functions.php';

requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method not allowed', 405);
}

$userId = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

if (empty($data)) {
    jsonError('No preferences provided', 400);
}

try {
    saveUserPreferences($userId, $data);
    jsonSuccess(['message' => 'Preferences saved']);
} catch (Exception $e) {
    jsonError($e->getMessage());
}
