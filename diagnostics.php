<?php
/**
 * PHP Unit Testing for Reusable Components App v3.0.0
 *
 * Enhanced from PHP Diagnostics Tool v2.2.0 to focus on unit testing reusable components.
 * Tests components in their natural environment within the parent app.
 *
 * Features:
 * - Component detection and testing with @reusable tag
 * - Real-time file monitoring and multi-format preview
 * - Security, performance, and resource monitoring
 * - Built-in terminal with safety restrictions
 * - Exportable test results
 *
 * Security: Password protected (default: admin/admin)
 *
 * @author PHP Diagnostics Team
 * @version 3.0.0
 */

// Configuration
define('DIAG_VERSION', '3.0.0');
define('DIAG_PASSWORD', 'admin'); // Change this!
define('DIAG_USERNAME', 'admin'); // Change this!
define('MAX_FILE_SIZE', 1024 * 1024); // 1MB max file size
define('SCAN_DEPTH', 10); // Maximum directory depth
define('EXEC_TIME_LIMIT', 30); // 30s max per operation
define('BASE_DIR', realpath(dirname(__FILE__))); // Base directory for path validation

// Start session
session_start();

// Global settings
$settings = [
    'read_only' => isset($_SESSION['read_only']) ? $_SESSION['read_only'] : false,
    'theme' => isset($_SESSION['theme']) ? $_SESSION['theme'] : 'light',
    'use_auth' => true // Toggle for session-less operation
];

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login']) && $settings['use_auth']) {
    if ($_POST['username'] === DIAG_USERNAME && $_POST['password'] === DIAG_PASSWORD) {
        $_SESSION['authenticated'] = true;
        $_SESSION['login_time'] = time();
    } else {
        $login_error = 'Invalid credentials';
    }
}

// Check authentication
$authenticated = !$settings['use_auth'] || (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true);

// Toggle settings
if ($authenticated && isset($_GET['toggle'])) {
    switch ($_GET['toggle']) {
        case 'read_only':
            $_SESSION['read_only'] = !$_SESSION['read_only'];
            break;
        case 'theme':
            $_SESSION['theme'] = $_SESSION['theme'] === 'light' ? 'dark' : 'light';
            break;
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Handle AJAX requests
if ($authenticated && isset($_GET['action'])) {
    header('Content-Type: application/json');
    set_time_limit(EXEC_TIME_LIMIT);

    try {
        switch ($_GET['action']) {
            case 'scan_files':
                echo json_encode(scanFileSystem());
                break;
            case 'analyze_file':
                $file = validatePath($_GET['file'] ?? '');
                echo json_encode(analyzeFile($file));
                break;
            case 'get_file_content':
                $file = validatePath($_GET['file'] ?? '');
                echo json_encode(getFileContent($file));
                break;
            case 'run_diagnostics':
                echo json_encode(runCompleteDiagnostics());
                break;
            case 'test_database':
                $credentials = isset($_POST['credentials']) ? json_decode($_POST['credentials'], true) : null;
                echo json_encode(testDatabaseConnection($credentials));
                break;
            case 'execute_command':
                $command = $_POST['command'] ?? '';
                echo json_encode(executeCommand($command));
                break;
            case 'get_system_info':
                echo json_encode(getSystemInfo());
                break;
            case 'export_results':
                $format = $_GET['format'] ?? 'json';
                $data = $_POST['data'] ?? '';
                echo json_encode(exportResults($format, $data));
                break;
            case 'save_credentials':
                $credentials = isset($_POST['credentials']) ? json_decode($_POST['credentials'], true) : null;
                echo json_encode(saveCredentials($credentials));
                break;
            case 'scan_components':
                echo json_encode(scanForComponents());
                break;
            case 'test_component':
                $component = json_decode($_POST['component'] ?? '{}', true);
                echo json_encode(testComponent($component));
                break;
            default:
                echo json_encode(['error' => 'Invalid action']);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

/**
 * Validate file path to prevent directory traversal
 */
function validatePath($path) {
    $realPath = realpath($path);
    if ($realPath === false || strpos($realPath, BASE_DIR) !== 0) {
        throw new Exception('Invalid path: Access denied');
    }
    return $realPath;
}

/**
 * Log critical operations
 */
function logOperation($message) {
    $logFile = BASE_DIR . '/diagnostics.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

/**
 * Scan file system with enhanced filtering
 */
function scanFileSystem($dir = null, $depth = 0) {
    if ($dir === null) {
        $dir = BASE_DIR;
    }

    if ($depth > SCAN_DEPTH) {
        return ['error' => 'Maximum scan depth reached'];
    }

    if (!is_dir($dir) || !is_readable($dir)) {
        return ['error' => 'Directory not accessible'];
    }

    $result = [
        'name' => basename($dir),
        'type' => 'directory',
        'path' => $dir,
        'children' => [],
        'stats' => [
            'files' => 0,
            'php_files' => 0,
            'total_size' => 0,
            'last_modified' => 0
        ]
    ];

    try {
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || $file === basename(__FILE__)) {
                continue;
            }

            $path = $dir . DIRECTORY_SEPARATOR . $file;

            if (is_dir($path)) {
                $subdir = scanFileSystem($path, $depth + 1);
                if (!isset($subdir['error'])) {
                    $result['children'][] = $subdir;
                    $result['stats']['files'] += $subdir['stats']['files'];
                    $result['stats']['php_files'] += $subdir['stats']['php_files'];
                    $result['stats']['total_size'] += $subdir['stats']['total_size'];
                }
            } else {
                if (isBinary($path)) {
                    continue; // Skip binary files
                }

                $size = filesize($path);
                $modified = filemtime($path);
                $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                $perms = substr(sprintf('%o', fileperms($path)), -3);

                $result['children'][] = [
                    'name' => $file,
                    'type' => 'file',
                    'path' => $path,
                    'size' => $size,
                    'extension' => $extension,
                    'modified' => $modified,
                    'readable' => is_readable($path),
                    'writable' => is_writable($path),
                    'executable' => is_executable($path),
                    'perms' => $perms,
                    'health' => calculateFileHealth($path)
                ];

                $result['stats']['files']++;
                if ($extension === 'php') {
                    $result['stats']['php_files']++;
                }
                $result['stats']['total_size'] += $size;
                $result['stats']['last_modified'] = max($result['stats']['last_modified'], $modified);
            }
        }
    } catch (Exception $e) {
        return ['error' => 'Failed to scan directory: ' . $e->getMessage()];
    }

    return $result;
}

/**
 * Check if file is binary
 */
function isBinary($file) {
    $content = file_get_contents($file, false, null, 0, 1024);
    return $content === false || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', $content);
}

/**
 * Calculate file health score
 */
function calculateFileHealth($file) {
    // Existing implementation (unchanged for brevity)
    if (!is_readable($file) || filesize($file) > MAX_FILE_SIZE) {
        return 0;
    }
    $content = file_get_contents($file);
    $score = 100;
    if (strpos($content, 'eval(') !== false) $score -= 30;
    // ... (rest of the original logic)
    return max(0, min(100, $score));
}

/**
 * Analyze a specific file
 */
function analyzeFile($file) {
    // Existing implementation (unchanged for brevity)
    $result = ['file' => $file]; // Placeholder
    return $result;
}

/**
 * Get file content with multi-format support
 */
function getFileContent($file) {
    if (!file_exists($file) || !is_readable($file) || filesize($file) > MAX_FILE_SIZE) {
        return ['error' => 'File not accessible or too large'];
    }

    $content = file_get_contents($file);
    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $mime = [
        'php' => 'application/x-php',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'txt' => 'text/plain',
        'yaml' => 'text/yaml',
        'xml' => 'application/xml'
    ][$extension] ?? 'text/plain';

    return [
        'content' => $content,
        'size' => filesize($file),
        'modified' => filemtime($file),
        'lines' => substr_count($content, "\n") + 1,
        'mime' => $mime
    ];
}

/**
 * Scan for reusable components
 */
function scanForComponents() {
    $phpFiles = findPhpFiles(scanFileSystem());
    $components = [];

    foreach ($phpFiles as $file) {
        $content = file_get_contents($file);
        $tokens = token_get_all($content);
        $docComment = '';

        foreach ($tokens as $i => $token) {
            if (is_array($token)) {
                list($tokenId, $tokenValue) = $token;

                if ($tokenId === T_DOC_COMMENT) {
                    $docComment = $tokenValue;
                } elseif (in_array($tokenId, [T_FUNCTION, T_CLASS]) && strpos($docComment, '@reusable') !== false) {
                    $nameToken = $tokens[$i + 2];
                    if (is_array($nameToken) && $nameToken[0] === T_STRING) {
                        $name = $nameToken[1];
                        $line = $nameToken[2];

                        if ($tokenId === T_FUNCTION) {
                            $components[] = [
                                'type' => 'function',
                                'name' => $name,
                                'file' => $file,
                                'line' => $line
                            ];
                        } elseif ($tokenId === T_CLASS) {
                            $classComponents = extractClassMethods($file, $name, $content);
                            $components[] = [
                                'type' => 'class',
                                'name' => $name,
                                'file' => $file,
                                'line' => $line,
                                'methods' => $classComponents
                            ];
                        }
                    }
                    $docComment = '';
                }
            }
        }
    }

    return $components;
}

/**
 * Extract methods from a class
 */
function extractClassMethods($file, $className, $content) {
    $reflection = new ReflectionClass($className);
    $methods = [];
    foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
        $doc = $method->getDocComment();
        if ($doc && strpos($doc, '@reusable') !== false) {
            $methods[] = [
                'name' => $method->name,
                'file' => $file,
                'line' => $method->getStartLine()
            ];
        }
    }
    return $methods;
}

/**
 * Test a component
 */
function testComponent($component) {
    if (!$component || !isset($component['type'], $component['name'], $component['file'])) {
        return ['error' => 'Invalid component data'];
    }

    $file = validatePath($component['file']);
    $params = $component['params'] ?? [];
    $sandboxed = $component['sandboxed'] ?? false;

    try {
        require_once $file;
        $startTime = microtime(true);
        ob_start();

        if ($component['type'] === 'function') {
            $reflection = new ReflectionFunction($component['name']);
            $result = call_user_func_array($component['name'], validateParameters($reflection, $params));
        } elseif ($component['type'] === 'method' && isset($component['class'])) {
            $reflection = new ReflectionMethod($component['class'], $component['name']);
            $instance = new $component['class']();
            $result = $reflection->invokeArgs($instance, validateParameters($reflection, $params));
        } else {
            throw new Exception('Unsupported component type');
        }

        $output = ob_get_clean();
        $executionTime = (microtime(true) - $startTime) * 1000;
        $memoryUsage = memory_get_peak_usage() / 1024 / 1024;

        $testResult = [
            'status' => 'success',
            'result' => $result,
            'output' => $output,
            'execution_time' => round($executionTime, 2) . 'ms',
            'memory_usage' => round($memoryUsage, 2) . 'MB',
            'timestamp' => date('Y-m-d H:i:s')
        ];

        // Store in session history (last 10)
        if (!isset($_SESSION['test_history'])) {
            $_SESSION['test_history'] = [];
        }
        array_unshift($_SESSION['test_history'], $testResult);
        $_SESSION['test_history'] = array_slice($_SESSION['test_history'], 0, 10);

        logOperation("Tested component: {$component['name']} in {$file}");
        return $testResult;
    } catch (Exception $e) {
        ob_end_clean();
        return [
            'status' => 'error',
            'error' => $e->getMessage(),
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
}

/**
 * Validate parameters against reflection
 */
function validateParameters($reflection, $params) {
    $validated = [];
    foreach ($reflection->getParameters() as $param) {
        $name = $param->getName();
        $type = $param->hasType() ? $param->getType()->getName() : null;
        $value = $params[$name] ?? ($param->isDefaultValueAvailable() ? $param->getDefaultValue() : null);

        if (isset($value)) {
            switch ($type) {
                case 'int':
                    $validated[] = (int)$value;
                    break;
                case 'float':
                    $validated[] = (float)$value;
                    break;
                case 'bool':
                    $validated[] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                    break;
                case 'string':
                    $validated[] = (string)$value;
                    break;
                default:
                    $validated[] = $value;
            }
        } elseif (!$param->isOptional()) {
            throw new Exception("Missing required parameter: $name");
        }
    }
    return $validated;
}

/**
 * Run complete diagnostics
 */
function runCompleteDiagnostics() {
    // Existing implementation (unchanged for brevity)
    $diagnostics = ['system' => getSystemInfo()];
    return $diagnostics;
}

/**
 * Find all PHP files
 */
function findPhpFiles($fileSystem, &$files = []) {
    // Existing implementation (unchanged for brevity)
    return $files;
}

/**
 * Test database connection
 */
function testDatabaseConnection($credentials = null) {
    // Existing implementation (unchanged for brevity)
    return [];
}

/**
 * Get system information with resource monitoring
 */
function getSystemInfo() {
    $info = [
        'php_version' => PHP_VERSION,
        'memory_usage' => memory_get_usage() / 1024 / 1024,
        'disk_free_space' => disk_free_space('.') / 1024 / 1024,
    ];
    if ($info['memory_usage'] > 100) {
        $info['memory_alert'] = 'High memory usage detected';
    }
    if ($info['disk_free_space'] < 100) {
        $info['disk_alert'] = 'Low disk space detected';
    }
    return $info;
}

/**
 * Execute command with allow-list
 */
function executeCommand($command) {
    $allowedCommands = ['help', 'phpinfo', 'ls', 'cat', 'scan', 'find', 'check'];
    $cmdParts = explode(' ', trim($command));
    $cmd = strtolower($cmdParts[0]);

    if (!in_array($cmd, $allowedCommands)) {
        return ['error' => 'Command not allowed'];
    }
    // Existing implementation (unchanged for brevity)
    return ['output' => ["Command executed: $command"]];
}

/**
 * Format bytes
 */
function formatBytes($bytes, $precision = 2) {
    // Existing implementation (unchanged for brevity)
    return "$bytes B";
}

/**
 * Export results
 */
function exportResults($format, $data) {
    $data = json_decode($data, true);
    if (!$data) {
        return ['error' => 'Invalid data'];
    }

    switch ($format) {
        case 'json':
            return [
                'content' => json_encode($data, JSON_PRETTY_PRINT),
                'filename' => 'test_results_' . date('Y-m-d') . '.json',
                'mime' => 'application/json'
            ];
        // Add CSV, HTML as needed
        default:
            return ['error' => 'Unsupported format'];
    }
}

/**
 * Save credentials
 */
function saveCredentials($credentials) {
    // Existing implementation (unchanged for brevity)
    return ['success' => true];
}

// Login form (unchanged for brevity)
if (!$authenticated) {
    // ... (existing login HTML)
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Unit Testing v<?= DIAG_VERSION ?></title>
    <style>
        :root {
            --primary: #1e3c72;
            --light-bg: #f5f5f5;
            --dark-bg: #1e1e1e;
            --light-text: #333;
            --dark-text: #d4d4d4;
        }
        body {
            background: <?= $settings['theme'] === 'dark' ? 'var(--dark-bg)' : 'var(--light-bg)' ?>;
            color: <?= $settings['theme'] === 'dark' ? 'var(--dark-text)' : 'var(--light-text)' ?>;
            font-family: 'Segoe UI', sans-serif;
        }
        .container { display: grid; grid-template-columns: 300px 1fr; gap: 2rem; padding: 2rem; max-width: 1400px; margin: auto; }
        @media (max-width: 768px) { .container { grid-template-columns: 1fr; } }
        /* Existing styles with responsiveness */
    </style>
</head>
<body>
    <header class="header">
        <h1>🔬 PHP Unit Testing</h1>
        <div class="header-info">
            <span>Mode: <?= $settings['read_only'] ? 'Read-Only' : 'Full' ?></span>
            <a href="?toggle=read_only" class="btn">Toggle Read-Only</a>
            <a href="?toggle=theme" class="btn">Switch to <?= $settings['theme'] === 'light' ? 'Dark' : 'Light' ?> Mode</a>
            <a href="?logout" class="btn btn-danger">Logout</a>
        </div>
    </header>

    <div class="container">
        <aside class="sidebar">
            <h3>Navigation</h3>
            <button class="nav-item" onclick="showTab('dashboard')">🏠 Dashboard</button>
            <button class="nav-item" onclick="showTab('components')">🧪 Component Testing</button>
            <button class="nav-item" onclick="showTab('files')">📂 File Explorer</button>
            <button class="nav-item" onclick="showTab('terminal')">💻 Terminal</button>
        </aside>

        <main class="main-content">
            <div id="dashboard" class="tab-content active">...</div>
            <div id="components" class="tab-content">
                <h2>🧪 Component Testing</h2>
                <button class="btn" onclick="loadComponents()">🔄 Load Components</button>
                <div id="component-list"></div>
                <div id="test-form"></div>
                <div id="test-results"></div>
                <div id="test-history"></div>
            </div>
            <div id="files" class="tab-content">
                <h2>📂 File Explorer</h2>
                <input type="text" id="file-search" placeholder="Search files..." onkeyup="searchFiles()">
                <button class="btn" onclick="loadFileSystem()">🔄 Refresh Files</button>
                <div id="file-tree" class="file-tree"></div>
            </div>
            <div id="terminal" class="tab-content">...</div>
        </main>
    </div>

    <script>
        let components = [];
        async function loadComponents() {
            const result = await apiCall('scan_components');
            components = result;
            const list = document.getElementById('component-list');
            list.innerHTML = components.map(c => `
                <div class="file-item" onclick="showTestForm('${c.type}', '${c.name}', '${c.file}', ${c.line}, '${c.methods ? JSON.stringify(c.methods) : '[]'}')">
                    ${c.type === 'function' ? '🔧' : '🏗️'} ${c.name} (${c.file}:${c.line})
                </div>
            `).join('');
        }

        async function showTestForm(type, name, file, line, methods) {
            const form = document.getElementById('test-form');
            const reflection = await apiCall('analyze_file', { file });
            let params = [];
            if (type === 'function') {
                const func = reflection.functions.find(f => f.name === name);
                params = func.params.split(',').map(p => p.trim());
            }
            form.innerHTML = `
                <h3>Test: ${name}</h3>
                <p>File: ${file}:${line}</p>
                ${params.map((p, i) => `
                    <div class="form-group">
                        <label>${p}</label>
                        <input type="text" id="param-${i}" class="form-control">
                    </div>
                `).join('')}
                <label><input type="checkbox" id="sandboxed"> Sandboxed</label>
                <button class="btn" onclick="runTest('${type}', '${name}', '${file}')">Run Test</button>
            `;
        }

        async function runTest(type, name, file) {
            const params = {};
            document.querySelectorAll('#test-form input[type="text"]').forEach((input, i) => {
                params[`param${i}`] = input.value;
            });
            const sandboxed = document.getElementById('sandboxed').checked;
            const result = await apiCall('test_component', {
                method: 'POST',
                component: { type, name, file, params, sandboxed }
            });
            document.getElementById('test-results').innerHTML = `
                <div class="alert ${result.status === 'success' ? 'alert-success' : 'alert-danger'}">
                    ${result.status === 'success' ? '✅' : '❌'} ${result.result || result.error}
                    <br>Time: ${result.execution_time || ''} | Memory: ${result.memory_usage || ''}
                </div>
            `;
            updateTestHistory();
        }

        function updateTestHistory() {
            const history = <?= json_encode($_SESSION['test_history'] ?? []) ?>;
            document.getElementById('test-history').innerHTML = `
                <h3>Test History</h3>
                ${history.map(h => `
                    <div class="alert ${h.status === 'success' ? 'alert-success' : 'alert-danger'}">
                        ${h.status === 'success' ? '✅' : '❌'} ${h.result || h.error} @ ${h.timestamp}
                    </div>
                `).join('')}
            `;
        }

        function searchFiles() {
            const query = document.getElementById('file-search').value.toLowerCase();
            document.querySelectorAll('.file-item').forEach(item => {
                item.style.display = item.textContent.toLowerCase().includes(query) ? 'block' : 'none';
            });
        }

        // Existing JS functions (unchanged for brevity)
        async function apiCall(action, data = {}) { /* ... */ }
        function showTab(tabName) { /* ... */ }
        async function loadFileSystem() { /* ... */ }
    </script>
</body>
</html>
<?php
// Cleanup temporary files
array_map('unlink', glob(BASE_DIR . '/temp_*'));
?>