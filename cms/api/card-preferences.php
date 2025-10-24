<?php
/**
 * Card Preferences API
 * Handles saving and loading user card preferences (visibility, order)
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Configuration
define('PREFERENCES_FILE', __DIR__ . '/../data/card-preferences.json');
define('DEFAULT_USER_ID', 'default'); // In production, use actual user ID from session

/**
 * Get user ID (from session in production)
 */
function getUserId() {
    // TODO: Get from session/auth
    return DEFAULT_USER_ID;
}

/**
 * Load all preferences
 */
function loadPreferences() {
    if (!file_exists(PREFERENCES_FILE)) {
        return [];
    }

    $json = file_get_contents(PREFERENCES_FILE);
    $data = json_decode($json, true);

    return $data ?: [];
}

/**
 * Save all preferences
 */
function savePreferences($preferences) {
    $dir = dirname(PREFERENCES_FILE);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $json = json_encode($preferences, JSON_PRETTY_PRINT);
    return file_put_contents(PREFERENCES_FILE, $json) !== false;
}

/**
 * Get preferences for specific user
 */
function getUserPreferences($userId) {
    $allPreferences = loadPreferences();
    return $allPreferences[$userId] ?? null;
}

/**
 * Set preferences for specific user
 */
function setUserPreferences($userId, $preferences) {
    $allPreferences = loadPreferences();
    $allPreferences[$userId] = $preferences;
    return savePreferences($allPreferences);
}

/**
 * Get default preferences
 */
function getDefaultPreferences() {
    return [
        'cards' => [
            'customer-dashboard' => ['visible' => true, 'order' => 0],
            'printers' => ['visible' => true, 'order' => 1],
            'device-supplies' => ['visible' => true, 'order' => 2],
            'meter-reads' => ['visible' => true, 'order' => 3],
            'device-alerts' => ['visible' => true, 'order' => 4],
            'dealer-supplies' => ['visible' => false, 'order' => 10],
            'analytics-reports' => ['visible' => false, 'order' => 15],
            'explorer-data' => ['visible' => false, 'order' => 20],
            'api-clients' => ['visible' => false, 'order' => 25]
        ],
        'order' => [
            'customer-dashboard',
            'printers',
            'device-supplies',
            'meter-reads',
            'device-alerts',
            'dealer-supplies',
            'analytics-reports',
            'explorer-data',
            'api-clients'
        ]
    ];
}

// Handle request
try {
    $method = $_SERVER['REQUEST_METHOD'];
    $userId = getUserId();

    switch ($method) {
        case 'GET':
            // Get preferences for current user
            $preferences = getUserPreferences($userId);

            // If no preferences exist, return defaults
            if ($preferences === null) {
                $preferences = getDefaultPreferences();
            }

            echo json_encode([
                'success' => true,
                'preferences' => $preferences
            ]);
            break;

        case 'POST':
            // Save preferences for current user
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            if (!isset($data['preferences'])) {
                throw new Exception('Missing preferences data');
            }

            $success = setUserPreferences($userId, $data['preferences']);

            if (!$success) {
                throw new Exception('Failed to save preferences');
            }

            echo json_encode([
                'success' => true,
                'message' => 'Preferences saved successfully'
            ]);
            break;

        case 'DELETE':
            // Reset to defaults
            $success = setUserPreferences($userId, getDefaultPreferences());

            if (!$success) {
                throw new Exception('Failed to reset preferences');
            }

            echo json_encode([
                'success' => true,
                'message' => 'Preferences reset to defaults'
            ]);
            break;

        default:
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'error' => 'Method not allowed'
            ]);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
