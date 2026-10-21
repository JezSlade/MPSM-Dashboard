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

// Never store the personal MPS Monitor password in plaintext, and never let
// a blank password (field left untouched in the UI) erase a saved one.
if (array_key_exists('mpsPassword', $data)) {
    if (!empty($data['mpsPassword'])) {
        $data['mpsPasswordEnc'] = encryptSecret($data['mpsPassword']);
    }
    unset($data['mpsPassword']);
}

try {
    saveUserPreferences($userId, $data);
    jsonSuccess(['message' => 'Preferences saved']);
} catch (Exception $e) {
    jsonError($e->getMessage());
}
