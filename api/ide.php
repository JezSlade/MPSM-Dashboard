<?php
// api/ide.php

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Define APP_ROOT if it's not already defined (e.g., if this file is accessed directly)
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

// Include necessary files
require_once APP_ROOT . '/config.php'; // For APP_ROOT and other configurations
require_once APP_ROOT . '/src/php/FileManager.php'; // For file operations

header('Content-Type: application/json');

$fileManager = new FileManager(APP_ROOT);

// Determine the action based on POST or GET request
$action = $_REQUEST['action'] ?? null; // Use $_REQUEST to handle both GET and POST

$response = ['status' => 'error', 'message' => 'Invalid action or request method.'];

try {
    switch ($action) {
        case 'list_files':
            $path = $_GET['path'] ?? ''; // Path to list, relative to APP_ROOT
            $files = $fileManager->listFiles($path);
            if ($files !== false) {
                $response = ['status' => 'success', 'data' => $files];
            } else {
                $response = ['status' => 'error', 'message' => 'Failed to list files or invalid path.'];
            }
            break;

        case 'read_file':
            $file_path = $_GET['file'] ?? ''; // File to read, relative to APP_ROOT
            $content = $fileManager->readFile($file_path);
            if ($content !== false) {
                $response = ['status' => 'success', 'data' => $content];
            } else {
                $response = ['status' => 'error', 'message' => 'Failed to read file or invalid file path.'];
            }
            break;

        case 'save_file':
            // Ensure this is a POST request for saving
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $response = ['status' => 'error', 'message' => 'Save operation requires POST method.'];
                break;
            }
            $file_path = $_POST['file'] ?? ''; // File to save, relative to APP_ROOT
            $content = $_POST['content'] ?? ''; // Content to save

            // Basic validation for file path to ensure it's not empty
            if (empty($file_path)) {
                $response = ['status' => 'error', 'message' => 'File path cannot be empty.'];
                break;
            }

            $success = $fileManager->saveFile($file_path, $content);
            if ($success) {
                $response = ['status' => 'success', 'message' => 'File saved successfully.'];
            } else {
                $response = ['status' => 'error', 'message' => 'Failed to save file. Check permissions and path.'];
            }
            break;

        default:
            $response = ['status' => 'error', 'message' => 'Unknown action: ' . htmlspecialchars($action)];
            break;
    }
} catch (Exception $e) {
    error_log("IDE API Error: " . $e->getMessage());
    $response = ['status' => 'error', 'message' => 'An internal server error occurred: ' . $e->getMessage()];
}

echo json_encode($response);
exit;
?>
