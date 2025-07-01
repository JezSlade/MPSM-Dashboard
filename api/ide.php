<?php
// api/ide.php

// PHP Debugging Lines - START
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// PHP Debugging Lines - END

// Include configuration and classes
require_once __DIR__ . '/../config.php'; // Adjust path as needed
require_once __DIR__ . '/../src/php/FileManager.php';

header('Content-Type: application/json');
$response = ['status' => 'error', 'message' => 'Invalid request.'];

$fileManager = new FileManager(APP_ROOT);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ajax_action = $_POST['ajax_action'] ?? '';

    switch ($ajax_action) {
        case 'ide_list_files':
            $current_dir = $_POST['path'] ?? '.';
            $files = $fileManager->listFiles($current_dir);
            if ($files !== false) {
                $response = ['status' => 'success', 'files' => $files, 'current_path' => ($current_dir === '.' ? '' : $current_dir)];
            } else {
                $response['message'] = "Failed to list files or invalid path.";
            }
            break;

        case 'ide_read_file':
            $file_path = $_POST['path'] ?? '';
            $content = $fileManager->readFile($file_path);
            if ($content !== false) {
                $response = ['status' => 'success', 'content' => $content];
            } else {
                $response['message'] = "Failed to read file or invalid path.";
            }
            break;

        case 'ide_save_file':
            $file_path = $_POST['path'] ?? '';
            $content = $_POST['content'] ?? '';
            if ($fileManager->saveFile($file_path, $content)) {
                $response = ['status' => 'success', 'message' => 'File saved successfully.'];
            } else {
                $response['message'] = "Failed to save file. Check permissions or path.";
            }
            break;

        default:
            $response['message'] = 'Unknown IDE AJAX action.';
            break;
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
exit;
?>
