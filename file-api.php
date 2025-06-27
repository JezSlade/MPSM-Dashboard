<?php
// file-api.php — Simple file manager API

// === CONFIG ===
$TOKEN = 'gizmo'; // Change to your desired token
$ROOT_DIR = realpath(__DIR__); // Limits access to root folder only

// === AUTH ===
if (!isset($_GET['token']) || $_GET['token'] !== $TOKEN) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// === HEADERS ===
header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];
$path = $_GET['path'] ?? '';
$fullPath = realpath($ROOT_DIR . '/' . $path);

// === SECURITY CHECK: prevent access above root ===
if ($fullPath && strpos($fullPath, $ROOT_DIR) !== 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid path']);
    exit;
}

switch ($method) {
    // === GET: List directory or download file ===
    case 'GET':
        if (!$path) {
            $items = array_values(array_diff(scandir($ROOT_DIR), ['.', '..']));
            echo json_encode(['result' => json_encode($items)]);
        } elseif (is_dir($fullPath)) {
            $items = array_values(array_diff(scandir($fullPath), ['.', '..']));
            echo json_encode(['result' => json_encode($items)]);
        } elseif (is_file($fullPath)) {
            header('Content-Type: text/plain');
            readfile($fullPath);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Not found']);
        }
        break;

    // === POST: Upload file or create folder ===
    case 'POST':
        if (isset($_FILES['file'])) {
            $filename = basename($_FILES['file']['name']);
            move_uploaded_file($_FILES['file']['tmp_name'], $ROOT_DIR . '/' . $filename);
            echo json_encode(['status' => 'uploaded', 'file' => $filename]);
        } elseif (isset($_GET['mkdir'])) {
            $folder = basename($_GET['mkdir']);
            if (!is_dir($ROOT_DIR . '/' . $folder)) {
                mkdir($ROOT_DIR . '/' . $folder);
                echo json_encode(['status' => 'folder created', 'folder' => $folder]);
            } else {
                echo json_encode(['status' => 'folder already exists', 'folder' => $folder]);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Missing file or folder name']);
        }
        break;

    // === PUT: Edit/replace file contents ===
    case 'PUT':
        parse_str($_SERVER['QUERY_STRING'], $params);
        $putPath = $params['path'] ?? null;
        if (!$putPath) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing path']);
            exit;
        }
        $targetFile = $ROOT_DIR . '/' . basename($putPath);
        $content = file_get_contents('php://input');
        if (file_put_contents($targetFile, $content) !== false) {
            echo json_encode(['status' => 'file updated', 'file' => basename($putPath)]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update file']);
        }
        break;

    // === DELETE: Delete file or folder ===
    case 'DELETE':
        if (!$path) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing path']);
            exit;
        }
        if (is_file($fullPath)) {
            unlink($fullPath);
            echo json_encode(['status' => 'file deleted', 'file' => basename($path)]);
        } elseif (is_dir($fullPath)) {
            rmdir($fullPath);
            echo json_encode(['status' => 'folder deleted', 'folder' => basename($path)]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Not found']);
        }
        break;

    // === Unsupported method ===
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
