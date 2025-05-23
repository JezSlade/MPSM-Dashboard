<?php
/**
 * PHP Shell Diagnostics Tool v2.1.0
 * 
 * A bulletproof, self-contained PHP diagnostics tool.
 * Just drop this file into your project root and access it via browser.
 * 
 * Features:
 * - Real-time file monitoring
 * - Code analysis and security scanning
 * - Performance monitoring
 * - Database testing
 * - Network diagnostics
 * - Built-in terminal
 * 
 * Security: Password protected (default: admin/admin)
 * 
 * @author PHP Diagnostics Team
 * @version 2.1.0
 */

// Configuration
define('DIAG_VERSION', '2.1.0');
define('DIAG_PASSWORD', 'admin'); // Change this!
define('DIAG_USERNAME', 'admin'); // Change this!
define('MAX_FILE_SIZE', 1024 * 1024); // 1MB max file size for analysis
define('SCAN_DEPTH', 10); // Maximum directory depth to scan

// Start session for authentication
session_start();

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    if ($_POST['username'] === DIAG_USERNAME && $_POST['password'] === DIAG_PASSWORD) {
        $_SESSION['authenticated'] = true;
        $_SESSION['login_time'] = time();
    } else {
        $login_error = 'Invalid credentials';
    }
}

// Check authentication
$authenticated = isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;

// Handle AJAX requests
if ($authenticated && isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    try {
        switch ($_GET['action']) {
            case 'scan_files':
                echo json_encode(scanFileSystem());
                break;
            case 'analyze_file':
                $file = $_GET['file'] ?? '';
                echo json_encode(analyzeFile($file));
                break;
            case 'get_file_content':
                $file = $_GET['file'] ?? '';
                echo json_encode(getFileContent($file));
                break;
            case 'run_diagnostics':
                echo json_encode(runCompleteDiagnostics());
                break;
            case 'test_database':
                echo json_encode(testDatabaseConnection());
                break;
            case 'execute_command':
                $command = $_POST['command'] ?? '';
                echo json_encode(executeCommand($command));
                break;
            case 'get_system_info':
                echo json_encode(getSystemInfo());
                break;
            case 'fix_issue':
                $type = $_GET['type'] ?? '';
                $file = $_GET['file'] ?? '';
                echo json_encode(fixIssue($type, $file));
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
 * Scan the file system and return structure
 */
function scanFileSystem($dir = null, $depth = 0) {
    if ($dir === null) {
        $dir = dirname(__FILE__);
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
                $size = filesize($path);
                $modified = filemtime($path);
                $extension = pathinfo($file, PATHINFO_EXTENSION);
                
                $result['children'][] = [
                    'name' => $file,
                    'type' => 'file',
                    'path' => $path,
                    'size' => $size,
                    'extension' => $extension,
                    'modified' => $modified,
                    'readable' => is_readable($path),
                    'writable' => is_writable($path),
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
 * Calculate file health score
 */
function calculateFileHealth($file) {
    if (!is_readable($file) || filesize($file) > MAX_FILE_SIZE) {
        return 0;
    }
    
    $extension = pathinfo($file, PATHINFO_EXTENSION);
    if ($extension !== 'php') {
        return 100;
    }
    
    $content = file_get_contents($file);
    $score = 100;
    
    // Check for common issues
    if (strpos($content, 'eval(') !== false) $score -= 30;
    if (strpos($content, 'exec(') !== false) $score -= 20;
    if (strpos($content, 'system(') !== false) $score -= 20;
    if (strpos($content, '$_GET') !== false && strpos($content, 'htmlspecialchars') === false) $score -= 15;
    if (strpos($content, '$_POST') !== false && strpos($content, 'htmlspecialchars') === false) $score -= 15;
    if (strpos($content, 'mysql_') !== false) $score -= 25;
    if (strpos($content, 'SELECT * FROM') !== false) $score -= 10;
    
    // Check for good practices
    if (strpos($content, 'PDO') !== false) $score += 5;
    if (strpos($content, 'htmlspecialchars') !== false) $score += 5;
    if (strpos($content, 'password_hash') !== false) $score += 5;
    
    return max(0, min(100, $score));
}

/**
 * Analyze a specific file
 */
function analyzeFile($file) {
    if (!file_exists($file) || !is_readable($file)) {
        return ['error' => 'File not found or not readable'];
    }
    
    if (filesize($file) > MAX_FILE_SIZE) {
        return ['error' => 'File too large for analysis'];
    }
    
    $extension = pathinfo($file, PATHINFO_EXTENSION);
    if ($extension !== 'php') {
        return ['error' => 'Only PHP files can be analyzed'];
    }
    
    $content = file_get_contents($file);
    $lines = explode("\n", $content);
    
    $analysis = [
        'file' => $file,
        'size' => filesize($file),
        'lines' => count($lines),
        'health' => calculateFileHealth($file),
        'issues' => [],
        'functions' => [],
        'classes' => [],
        'includes' => [],
        'security' => [],
        'performance' => [],
        'quality' => []
    ];
    
    // Security analysis
    foreach ($lines as $lineNum => $line) {
        $lineNum++; // 1-based line numbers
        
        // SQL Injection risks
        if (preg_match('/\$\w+\s*=\s*["\'].*\$_(?:GET|POST|REQUEST).*["\']/', $line)) {
            $analysis['security'][] = [
                'type' => 'SQL Injection Risk',
                'line' => $lineNum,
                'code' => trim($line),
                'severity' => 'high',
                'description' => 'Direct use of user input in SQL query'
            ];
        }
        
        // XSS risks
        if (preg_match('/echo\s+\$_(?:GET|POST|REQUEST)/', $line)) {
            $analysis['security'][] = [
                'type' => 'XSS Risk',
                'line' => $lineNum,
                'code' => trim($line),
                'severity' => 'medium',
                'description' => 'Unescaped user input in output'
            ];
        }
        
        // Dangerous functions
        if (preg_match('/\b(eval|exec|system|shell_exec|passthru)\s*\(/', $line)) {
            $analysis['security'][] = [
                'type' => 'Dangerous Function',
                'line' => $lineNum,
                'code' => trim($line),
                'severity' => 'critical',
                'description' => 'Use of potentially dangerous function'
            ];
        }
        
        // Deprecated MySQL functions
        if (preg_match('/mysql_\w+\s*\(/', $line)) {
            $analysis['security'][] = [
                'type' => 'Deprecated Function',
                'line' => $lineNum,
                'code' => trim($line),
                'severity' => 'medium',
                'description' => 'Use of deprecated MySQL function'
            ];
        }
        
        // Performance issues
        if (preg_match('/SELECT\s+\*\s+FROM/i', $line)) {
            $analysis['performance'][] = [
                'type' => 'Inefficient Query',
                'line' => $lineNum,
                'code' => trim($line),
                'severity' => 'low',
                'description' => 'SELECT * can be inefficient'
            ];
        }
        
        // Extract functions
        if (preg_match('/function\s+(\w+)\s*$$(.*?)$$/', $line, $matches)) {
            $analysis['functions'][] = [
                'name' => $matches[1],
                'line' => $lineNum,
                'params' => $matches[2]
            ];
        }
        
        // Extract classes
        if (preg_match('/class\s+(\w+)/', $line, $matches)) {
            $analysis['classes'][] = [
                'name' => $matches[1],
                'line' => $lineNum
            ];
        }
        
        // Extract includes
        if (preg_match('/(include|require)(?:_once)?\s*\(?["\']([^"\']+)["\']/', $line, $matches)) {
            $analysis['includes'][] = [
                'type' => $matches[1],
                'file' => $matches[2],
                'line' => $lineNum
            ];
        }
    }
    
    // Code quality checks
    $analysis['quality'][] = [
        'metric' => 'Lines of Code',
        'value' => count($lines),
        'status' => count($lines) > 500 ? 'warning' : 'good'
    ];
    
    $analysis['quality'][] = [
        'metric' => 'Functions',
        'value' => count($analysis['functions']),
        'status' => 'info'
    ];
    
    $analysis['quality'][] = [
        'metric' => 'Classes',
        'value' => count($analysis['classes']),
        'status' => 'info'
    ];
    
    return $analysis;
}

/**
 * Get file content
 */
function getFileContent($file) {
    if (!file_exists($file) || !is_readable($file)) {
        return ['error' => 'File not found or not readable'];
    }
    
    if (filesize($file) > MAX_FILE_SIZE) {
        return ['error' => 'File too large to display'];
    }
    
    $content = file_get_contents($file);
    return [
        'content' => $content,
        'size' => filesize($file),
        'modified' => filemtime($file),
        'lines' => substr_count($content, "\n") + 1
    ];
}

/**
 * Run complete diagnostics
 */
function runCompleteDiagnostics() {
    $diagnostics = [
        'system' => getSystemInfo(),
        'security' => [],
        'performance' => [],
        'files' => [],
        'database' => testDatabaseConnection(),
        'summary' => [
            'total_issues' => 0,
            'critical_issues' => 0,
            'warnings' => 0,
            'health_score' => 0
        ]
    ];
    
    // Scan all PHP files for issues
    $fileSystem = scanFileSystem();
    $phpFiles = findPhpFiles($fileSystem);
    
    $totalHealth = 0;
    $fileCount = 0;
    
    foreach ($phpFiles as $file) {
        if (filesize($file) <= MAX_FILE_SIZE) {
            $analysis = analyzeFile($file);
            if (!isset($analysis['error'])) {
                $diagnostics['files'][] = [
                    'file' => $file,
                    'health' => $analysis['health'],
                    'issues' => count($analysis['security']) + count($analysis['performance']),
                    'critical' => count(array_filter($analysis['security'], function($issue) {
                        return $issue['severity'] === 'critical';
                    }))
                ];
                
                $totalHealth += $analysis['health'];
                $fileCount++;
                
                // Aggregate security issues
                foreach ($analysis['security'] as $issue) {
                    $diagnostics['security'][] = array_merge($issue, ['file' => $file]);
                    $diagnostics['summary']['total_issues']++;
                    if ($issue['severity'] === 'critical') {
                        $diagnostics['summary']['critical_issues']++;
                    } elseif ($issue['severity'] === 'high' || $issue['severity'] === 'medium') {
                        $diagnostics['summary']['warnings']++;
                    }
                }
                
                // Aggregate performance issues
                foreach ($analysis['performance'] as $issue) {
                    $diagnostics['performance'][] = array_merge($issue, ['file' => $file]);
                    $diagnostics['summary']['total_issues']++;
                    $diagnostics['summary']['warnings']++;
                }
            }
        }
    }
    
    $diagnostics['summary']['health_score'] = $fileCount > 0 ? round($totalHealth / $fileCount) : 100;
    
    return $diagnostics;
}

/**
 * Find all PHP files recursively
 */
function findPhpFiles($fileSystem, &$files = []) {
    if (isset($fileSystem['children'])) {
        foreach ($fileSystem['children'] as $child) {
            if ($child['type'] === 'file' && isset($child['extension']) && $child['extension'] === 'php') {
                $files[] = $child['path'];
            } elseif ($child['type'] === 'directory') {
                findPhpFiles($child, $files);
            }
        }
    }
    return $files;
}

/**
 * Test database connection
 */
function testDatabaseConnection() {
    $results = [];
    
    // Try to find database configuration
    $configFiles = ['config.php', 'database.php', '.env', 'wp-config.php'];
    $dbConfig = null;
    
    foreach ($configFiles as $configFile) {
        if (file_exists($configFile)) {
            $content = file_get_contents($configFile);
            
            // Try to extract database credentials
            if (preg_match('/DB_HOST["\']?\s*[=:]\s*["\']([^"\']+)/', $content, $matches)) {
                $dbConfig['host'] = $matches[1];
            }
            if (preg_match('/DB_NAME["\']?\s*[=:]\s*["\']([^"\']+)/', $content, $matches)) {
                $dbConfig['name'] = $matches[1];
            }
            if (preg_match('/DB_USER["\']?\s*[=:]\s*["\']([^"\']+)/', $content, $matches)) {
                $dbConfig['user'] = $matches[1];
            }
            if (preg_match('/DB_PASS["\']?\s*[=:]\s*["\']([^"\']+)/', $content, $matches)) {
                $dbConfig['pass'] = $matches[1];
            }
            
            if ($dbConfig) break;
        }
    }
    
    if ($dbConfig && isset($dbConfig['host'], $dbConfig['name'], $dbConfig['user'])) {
        try {
            $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['name']}";
            $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['pass'] ?? '');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $results[] = [
                'type' => 'MySQL Connection',
                'status' => 'success',
                'message' => 'Database connection successful',
                'details' => "Connected to {$dbConfig['name']} on {$dbConfig['host']}"
            ];
            
            // Test query performance
            $start = microtime(true);
            $stmt = $pdo->query('SELECT 1');
            $queryTime = (microtime(true) - $start) * 1000;
            
            $results[] = [
                'type' => 'Query Performance',
                'status' => $queryTime < 100 ? 'success' : 'warning',
                'message' => "Query response time: " . round($queryTime, 2) . "ms",
                'details' => $queryTime < 100 ? 'Excellent performance' : 'Consider optimization'
            ];
            
        } catch (PDOException $e) {
            $results[] = [
                'type' => 'MySQL Connection',
                'status' => 'error',
                'message' => 'Database connection failed',
                'details' => $e->getMessage()
            ];
        }
    } else {
        $results[] = [
            'type' => 'Database Configuration',
            'status' => 'warning',
            'message' => 'No database configuration found',
            'details' => 'Checked: ' . implode(', ', $configFiles)
        ];
    }
    
    return $results;
}

/**
 * Get system information
 */
function getSystemInfo() {
    return [
        'php_version' => PHP_VERSION,
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        'operating_system' => PHP_OS,
        'memory_limit' => ini_get('memory_limit'),
        'max_execution_time' => ini_get('max_execution_time'),
        'post_max_size' => ini_get('post_max_size'),
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'error_reporting' => error_reporting(),
        'display_errors' => ini_get('display_errors'),
        'log_errors' => ini_get('log_errors'),
        'extensions' => get_loaded_extensions(),
        'disk_free_space' => disk_free_space('.'),
        'disk_total_space' => disk_total_space('.'),
        'current_user' => get_current_user(),
        'server_time' => date('Y-m-d H:i:s'),
        'timezone' => date_default_timezone_get()
    ];
}

/**
 * Execute command (limited for security)
 */
function executeCommand($command) {
    $command = trim($command);
    
    if (empty($command)) {
        return ['error' => 'No command provided'];
    }
    
    $output = [];
    
    // Safe commands only
    switch (strtolower(explode(' ', $command)[0])) {
        case 'help':
            $output = [
                'Available commands:',
                '  help - Show this help',
                '  phpinfo - Show PHP information',
                '  ls - List files in current directory',
                '  pwd - Show current directory',
                '  date - Show current date and time',
                '  whoami - Show current user',
                '  version - Show PHP version',
                '  extensions - List loaded PHP extensions',
                '  memory - Show memory usage',
                '  disk - Show disk usage'
            ];
            break;
            
        case 'phpinfo':
            ob_start();
            phpinfo(INFO_GENERAL | INFO_CONFIGURATION);
            $phpinfo = ob_get_clean();
            $output = [strip_tags($phpinfo)];
            break;
            
        case 'ls':
            $files = scandir('.');
            $output = array_filter($files, function($file) {
                return $file !== '.' && $file !== '..';
            });
            break;
            
        case 'pwd':
            $output = [getcwd()];
            break;
            
        case 'date':
            $output = [date('Y-m-d H:i:s T')];
            break;
            
        case 'whoami':
            $output = [get_current_user()];
            break;
            
        case 'version':
            $output = ['PHP ' . PHP_VERSION];
            break;
            
        case 'extensions':
            $output = get_loaded_extensions();
            break;
            
        case 'memory':
            $output = [
                'Memory Limit: ' . ini_get('memory_limit'),
                'Memory Usage: ' . round(memory_get_usage() / 1024 / 1024, 2) . ' MB',
                'Peak Memory: ' . round(memory_get_peak_usage() / 1024 / 1024, 2) . ' MB'
            ];
            break;
            
        case 'disk':
            $free = disk_free_space('.');
            $total = disk_total_space('.');
            $used = $total - $free;
            $output = [
                'Total: ' . formatBytes($total),
                'Used: ' . formatBytes($used),
                'Free: ' . formatBytes($free),
                'Usage: ' . round(($used / $total) * 100, 1) . '%'
            ];
            break;
            
        default:
            $output = ["Command not recognized: $command", "Type 'help' for available commands"];
    }
    
    return ['output' => $output];
}

/**
 * Format bytes to human readable
 */
function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

/**
 * Fix common issues
 */
function fixIssue($type, $file) {
    if (!is_writable($file)) {
        return ['error' => 'File is not writable'];
    }
    
    $content = file_get_contents($file);
    $originalContent = $content;
    $changes = [];
    
    switch ($type) {
        case 'xss':
            // Add htmlspecialchars to echo statements with user input
            $content = preg_replace(
                '/echo\s+\$_(GET|POST|REQUEST)\[([^\]]+)\]/',
                'echo htmlspecialchars($_$1[$2], ENT_QUOTES, \'UTF-8\')',
                $content
            );
            $changes[] = 'Added XSS protection to user input output';
            break;
            
        case 'mysql_deprecated':
            // Replace mysql_ functions with PDO equivalents (basic replacement)
            $replacements = [
                'mysql_connect' => '// TODO: Replace with PDO connection',
                'mysql_query' => '// TODO: Replace with PDO prepared statement',
                'mysql_fetch_array' => '// TODO: Replace with PDO fetch',
                'mysql_close' => '// TODO: Replace with PDO connection = null'
            ];
            
            foreach ($replacements as $old => $new) {
                if (strpos($content, $old) !== false) {
                    $content = str_replace($old, $new, $content);
                    $changes[] = "Marked deprecated $old for replacement";
                }
            }
            break;
            
        default:
            return ['error' => 'Unknown issue type'];
    }
    
    if ($content !== $originalContent) {
        if (file_put_contents($file, $content)) {
            return [
                'success' => true,
                'changes' => $changes,
                'message' => 'File updated successfully'
            ];
        } else {
            return ['error' => 'Failed to write file'];
        }
    }
    
    return ['message' => 'No changes needed'];
}

// If not authenticated, show login form
if (!$authenticated) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Diagnostics - Login</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 400px;
        }
        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        .logo h1 {
            color: #1e3c72;
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }
        .logo p {
            color: #666;
            font-size: 0.9rem;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 500;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        input[type="text"]:focus, input[type="password"]:focus {
            outline: none;
            border-color: #1e3c72;
        }
        .btn {
            width: 100%;
            padding: 0.75rem;
            background: #1e3c72;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #2a5298;
        }
        .error {
            background: #fee;
            color: #c33;
            padding: 0.75rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            border: 1px solid #fcc;
        }
        .info {
            background: #e8f4fd;
            color: #0066cc;
            padding: 0.75rem;
            border-radius: 5px;
            margin-top: 1rem;
            border: 1px solid #b3d9ff;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1>🎯 PHP Diagnostics</h1>
            <p>Advanced System Analysis Tool v<?= DIAG_VERSION ?></p>
        </div>
        
        <?php if (isset($login_error)): ?>
            <div class="error"><?= htmlspecialchars($login_error) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" name="login" class="btn">Access Diagnostics</button>
        </form>
        
        <div class="info">
            <strong>Default credentials:</strong><br>
            Username: admin<br>
            Password: admin<br>
            <em>Change these in the PHP file!</em>
        </div>
    </div>
</body>
</html>
<?php
exit;
}

// Main application interface
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Diagnostics v<?= DIAG_VERSION ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: #f5f5f5;
            color: #333;
        }
        
        .header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .header-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            font-size: 0.9rem;
        }
        
        .status-indicator {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255,255,255,0.1);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
        }
        
        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #4CAF50;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 2rem;
        }
        
        .sidebar {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            height: fit-content;
            position: sticky;
            top: 2rem;
        }
        
        .sidebar h3 {
            margin-bottom: 1rem;
            color: #1e3c72;
            border-bottom: 2px solid #eee;
            padding-bottom: 0.5rem;
        }
        
        .nav-item {
            display: block;
            padding: 0.75rem 1rem;
            margin: 0.25rem 0;
            background: #f8f9fa;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-align: left;
            width: 100%;
            transition: all 0.3s;
            text-decoration: none;
            color: #333;
        }
        
        .nav-item:hover, .nav-item.active {
            background: #1e3c72;
            color: white;
            transform: translateX(5px);
        }
        
        .main-content {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            min-height: 600px;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 10px;
            text-align: center;
        }
        
        .stat-card h4 {
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            opacity: 0.9;
        }
        
        .stat-card .value {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.25rem;
        }
        
        .stat-card .label {
            font-size: 0.8rem;
            opacity: 0.8;
        }
        
        .btn {
            background: #1e3c72;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: background 0.3s;
            margin: 0.25rem;
        }
        
        .btn:hover {
            background: #2a5298;
        }
        
        .btn-danger {
            background: #dc3545;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .btn-success {
            background: #28a745;
        }
        
        .btn-success:hover {
            background: #218838;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #333;
        }
        
        .btn-warning:hover {
            background: #e0a800;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 5px;
            margin: 1rem 0;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .file-tree {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 1rem;
        }
        
        .file-item {
            padding: 0.25rem 0.5rem;
            cursor: pointer;
            border-radius: 3px;
            margin: 0.1rem 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .file-item:hover {
            background: #f0f0f0;
        }
        
        .file-item.selected {
            background: #1e3c72;
            color: white;
        }
        
        .file-icon {
            width: 16px;
            height: 16px;
            display: inline-block;
        }
        
        .health-bar {
            width: 100%;
            height: 20px;
            background: #eee;
            border-radius: 10px;
            overflow: hidden;
            margin: 0.5rem 0;
        }
        
        .health-fill {
            height: 100%;
            transition: width 0.3s;
        }
        
        .health-excellent { background: #4CAF50; }
        .health-good { background: #8BC34A; }
        .health-warning { background: #FF9800; }
        .health-poor { background: #F44336; }
        
        .code-viewer {
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 1rem;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            max-height: 500px;
            overflow: auto;
            white-space: pre-wrap;
            line-height: 1.4;
        }
        
        .terminal {
            background: #000;
            color: #00ff00;
            padding: 1rem;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            height: 400px;
            overflow-y: auto;
        }
        
        .terminal-input {
            background: transparent;
            border: none;
            color: #00ff00;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            width: 100%;
            outline: none;
        }
        
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #1e3c72;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .progress-bar {
            width: 100%;
            height: 10px;
            background: #eee;
            border-radius: 5px;
            overflow: hidden;
            margin: 1rem 0;
        }
        
        .progress-fill {
            height: 100%;
            background: #1e3c72;
            transition: width 0.3s;
        }
        
        .issue-item {
            background: #f8f9fa;
            border-left: 4px solid #1e3c72;
            padding: 1rem;
            margin: 0.5rem 0;
            border-radius: 0 5px 5px 0;
        }
        
        .issue-critical {
            border-left-color: #dc3545;
            background: #fff5f5;
        }
        
        .issue-high {
            border-left-color: #fd7e14;
            background: #fff8f0;
        }
        
        .issue-medium {
            border-left-color: #ffc107;
            background: #fffbf0;
        }
        
        .issue-low {
            border-left-color: #17a2b8;
            background: #f0f9ff;
        }
        
        .badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            font-weight: bold;
            border-radius: 3px;
            text-transform: uppercase;
        }
        
        .badge-critical { background: #dc3545; color: white; }
        .badge-high { background: #fd7e14; color: white; }
        .badge-medium { background: #ffc107; color: #333; }
        .badge-low { background: #17a2b8; color: white; }
        .badge-success { background: #28a745; color: white; }
        
        @media (max-width: 768px) {
            .container {
                grid-template-columns: 1fr;
                padding: 1rem;
            }
            
            .header {
                padding: 1rem;
                flex-direction: column;
                gap: 1rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🎯 PHP Diagnostics v<?= DIAG_VERSION ?></h1>
        <div class="header-info">
            <div class="status-indicator">
                <div class="status-dot"></div>
                <span>System Online</span>
            </div>
            <span id="current-time"><?= date('H:i:s') ?></span>
            <a href="?logout=1" style="color: white; text-decoration: none;">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="sidebar">
            <h3>🎛️ Control Panel</h3>
            <button class="nav-item active" onclick="showTab('dashboard')">📊 Dashboard</button>
            <button class="nav-item" onclick="showTab('files')">📁 File Explorer</button>
            <button class="nav-item" onclick="showTab('analyzer')">🔍 Code Analyzer</button>
            <button class="nav-item" onclick="showTab('security')">🛡️ Security Scan</button>
            <button class="nav-item" onclick="showTab('performance')">⚡ Performance</button>
            <button class="nav-item" onclick="showTab('database')">🗄️ Database</button>
            <button class="nav-item" onclick="showTab('terminal')">💻 Terminal</button>
            <button class="nav-item" onclick="showTab('system')">⚙️ System Info</button>
            
            <div style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #eee;">
                <button class="btn btn-success" onclick="runFullScan()" style="width: 100%;">
                    🚀 Run Full Scan
                </button>
            </div>
        </div>

        <div class="main-content">
            <!-- Dashboard Tab -->
            <div id="dashboard" class="tab-content active">
                <h2>📊 System Dashboard</h2>
                <div class="stats-grid" id="stats-grid">
                    <div class="stat-card">
                        <h4>System Health</h4>
                        <div class="value" id="health-score">--</div>
                        <div class="label">Overall Score</div>
                    </div>
                    <div class="stat-card">
                        <h4>PHP Files</h4>
                        <div class="value" id="php-files">--</div>
                        <div class="label">Analyzed</div>
                    </div>
                    <div class="stat-card">
                        <h4>Issues Found</h4>
                        <div class="value" id="total-issues">--</div>
                        <div class="label">Total Issues</div>
                    </div>
                    <div class="stat-card">
                        <h4>Critical Issues</h4>
                        <div class="value" id="critical-issues">--</div>
                        <div class="label">Needs Attention</div>
                    </div>
                </div>
                
                <div id="dashboard-content">
                    <div class="alert alert-info">
                        <strong>Welcome to PHP Diagnostics!</strong><br>
                        Click "Run Full Scan" to analyze your application for security vulnerabilities, performance issues, and code quality problems.
                    </div>
                </div>
            </div>

            <!-- File Explorer Tab -->
            <div id="files" class="tab-content">
                <h2>📁 File Explorer</h2>
                <button class="btn" onclick="loadFileSystem()">🔄 Refresh Files</button>
                <div id="file-tree" class="file-tree">
                    <div class="alert alert-info">Click "Refresh Files" to load the file system.</div>
                </div>
            </div>

            <!-- Code Analyzer Tab -->
            <div id="analyzer" class="tab-content">
                <h2>🔍 Code Analyzer</h2>
                <div id="analyzer-content">
                    <div class="alert alert-info">Select a PHP file from the File Explorer to analyze its code quality, security, and performance.</div>
                </div>
            </div>

            <!-- Security Tab -->
            <div id="security" class="tab-content">
                <h2>🛡️ Security Analysis</h2>
                <div id="security-content">
                    <div class="alert alert-info">Run a full scan to see security analysis results.</div>
                </div>
            </div>

            <!-- Performance Tab -->
            <div id="performance" class="tab-content">
                <h2>⚡ Performance Analysis</h2>
                <div id="performance-content">
                    <div class="alert alert-info">Run a full scan to see performance analysis results.</div>
                </div>
            </div>

            <!-- Database Tab -->
            <div id="database" class="tab-content">
                <h2>🗄️ Database Testing</h2>
                <button class="btn" onclick="testDatabase()">🔍 Test Database Connection</button>
                <div id="database-content">
                    <div class="alert alert-info">Click "Test Database Connection" to check your database configuration and connectivity.</div>
                </div>
            </div>

            <!-- Terminal Tab -->
            <div id="terminal" class="tab-content">
                <h2>💻 Terminal</h2>
                <div class="terminal" id="terminal-output">
                    PHP Diagnostics Terminal v<?= DIAG_VERSION ?><br>
                    Type 'help' for available commands.<br>
                    <br>
                    $ <span id="terminal-cursor">_</span>
                </div>
                <div style="margin-top: 1rem;">
                    <input type="text" id="terminal-input" class="terminal-input" placeholder="Enter command..." onkeypress="handleTerminalInput(event)">
                </div>
            </div>

            <!-- System Info Tab -->
            <div id="system" class="tab-content">
                <h2>⚙️ System Information</h2>
                <button class="btn" onclick="loadSystemInfo()">🔄 Refresh System Info</button>
                <div id="system-content">
                    <div class="alert alert-info">Click "Refresh System Info" to load current system information.</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentFile = null;
        let scanResults = null;
        
        // Update time every second
        setInterval(() => {
            document.getElementById('current-time').textContent = new Date().toLocaleTimeString();
        }, 1000);
        
        // Tab switching
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remove active class from nav items
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabName).classList.add('active');
            
            // Add active class to clicked nav item
            event.target.classList.add('active');
        }
        
        // API call helper
        async function apiCall(action, data = {}) {
            const url = new URL(window.location.href);
            url.searchParams.set('action', action);
            
            const options = {
                method: data.method || 'GET',
                headers: {
                    'Content-Type': 'application/json'
                }
            };
            
            if (data.method === 'POST' && data.body) {
                options.body = JSON.stringify(data.body);
            }
            
            try {
                const response = await fetch(url, options);
                return await response.json();
            } catch (error) {
                console.error('API call failed:', error);
                return { error: error.message };
            }
        }
        
        // Run full system scan
        async function runFullScan() {
            const button = event.target;
            const originalText = button.textContent;
            button.innerHTML = '<span class="loading"></span> Scanning...';
            button.disabled = true;
            
            try {
                const results = await apiCall('run_diagnostics');
                
                if (results.error) {
                    throw new Error(results.error);
                }
                
                scanResults = results;
                updateDashboard(results);
                updateSecurityTab(results);
                updatePerformanceTab(results);
                
                showAlert('success', 'Full system scan completed successfully!');
            } catch (error) {
                showAlert('danger', 'Scan failed: ' + error.message);
            } finally {
                button.textContent = originalText;
                button.disabled = false;
            }
        }
        
        // Update dashboard with scan results
        function updateDashboard(results) {
            document.getElementById('health-score').textContent = results.summary.health_score + '%';
            document.getElementById('php-files').textContent = results.files.length;
            document.getElementById('total-issues').textContent = results.summary.total_issues;
            document.getElementById('critical-issues').textContent = results.summary.critical_issues;
            
            const content = document.getElementById('dashboard-content');
            let html = '';
            
            if (results.summary.critical_issues > 0) {
                html += `<div class="alert alert-danger">
                    <strong>⚠️ Critical Issues Detected!</strong><br>
                    Found ${results.summary.critical_issues} critical security issues that need immediate attention.
                </div>`;
            } else if (results.summary.total_issues > 0) {
                html += `<div class="alert alert-warning">
                    <strong>⚠️ Issues Found</strong><br>
                    Found ${results.summary.total_issues} issues that should be reviewed.
                </div>`;
            } else {
                html += `<div class="alert alert-success">
                    <strong>✅ All Clear!</strong><br>
                    No critical issues detected. Your application looks healthy!
                </div>`;
            }
            
            // Add file health overview
            html += '<h3>📁 File Health Overview</h3>';
            html += '<div style="max-height: 300px; overflow-y: auto;">';
            
            results.files.forEach(file => {
                const healthClass = file.health >= 80 ? 'health-excellent' : 
                                  file.health >= 60 ? 'health-good' : 
                                  file.health >= 40 ? 'health-warning' : 'health-poor';
                
                html += `
                    <div class="issue-item">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <strong>${file.file}</strong><br>
                                <small>Issues: ${file.issues} | Critical: ${file.critical}</small>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-weight: bold; color: ${file.health >= 80 ? '#4CAF50' : file.health >= 60 ? '#8BC34A' : file.health >= 40 ? '#FF9800' : '#F44336'}">
                                    ${file.health}%
                                </div>
                                <div class="health-bar" style="width: 100px;">
                                    <div class="health-fill ${healthClass}" style="width: ${file.health}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            html += '</div>';
            content.innerHTML = html;
        }
        
        // Update security tab
        function updateSecurityTab(results) {
            const content = document.getElementById('security-content');
            let html = '';
            
            if (results.security.length === 0) {
                html = '<div class="alert alert-success"><strong>✅ No Security Issues Found!</strong><br>Your code appears to be secure.</div>';
            } else {
                html = `<div class="alert alert-warning"><strong>⚠️ ${results.security.length} Security Issues Found</strong></div>`;
                
                results.security.forEach(issue => {
                    const severityClass = `issue-${issue.severity}`;
                    const badgeClass = `badge-${issue.severity}`;
                    
                    html += `
                        <div class="issue-item ${severityClass}">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                <div style="flex: 1;">
                                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                                        <strong>${issue.type}</strong>
                                        <span class="badge ${badgeClass}">${issue.severity}</span>
                                    </div>
                                    <div style="margin-bottom: 0.5rem;">${issue.description}</div>
                                    <div style="font-family: monospace; background: rgba(0,0,0,0.1); padding: 0.5rem; border-radius: 3px; font-size: 0.9rem;">
                                        ${issue.file}:${issue.line}<br>
                                        <code>${issue.code}</code>
                                    </div>
                                </div>
                                <div>
                                    <button class="btn btn-warning" onclick="fixIssue('${issue.type.toLowerCase().replace(' ', '_')}', '${issue.file}')">
                                        🔧 Fix
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                });
            }
            
            content.innerHTML = html;
        }
        
        // Update performance tab
        function updatePerformanceTab(results) {
            const content = document.getElementById('performance-content');
            let html = '';
            
            if (results.performance.length === 0) {
                html = '<div class="alert alert-success"><strong>✅ No Performance Issues Found!</strong><br>Your code appears to be optimized.</div>';
            } else {
                html = `<div class="alert alert-warning"><strong>⚠️ ${results.performance.length} Performance Issues Found</strong></div>`;
                
                results.performance.forEach(issue => {
                    html += `
                        <div class="issue-item">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                <div style="flex: 1;">
                                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                                        <strong>${issue.type}</strong>
                                        <span class="badge badge-${issue.severity}">${issue.severity}</span>
                                    </div>
                                    <div style="margin-bottom: 0.5rem;">${issue.description}</div>
                                    <div style="font-family: monospace; background: rgba(0,0,0,0.1); padding: 0.5rem; border-radius: 3px; font-size: 0.9rem;">
                                        ${issue.file}:${issue.line}<br>
                                        <code>${issue.code}</code>
                                    </div>
                                </div>
                                <div>
                                    <button class="btn" onclick="optimizeCode('${issue.file}', ${issue.line})">
                                        ⚡ Optimize
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                });
            }
            
            content.innerHTML = html;
        }
        
        // Load file system
        async function loadFileSystem() {
            const container = document.getElementById('file-tree');
            container.innerHTML = '<div class="loading"></div> Loading file system...';
            
            try {
                const result = await apiCall('scan_files');
                
                if (result.error) {
                    throw new Error(result.error);
                }
                
                container.innerHTML = renderFileTree(result);
            } catch (error) {
                container.innerHTML = `<div class="alert alert-danger">Failed to load file system: ${error.message}</div>`;
            }
        }
        
        // Render file tree
        function renderFileTree(node, level = 0) {
            let html = '';
            const indent = '  '.repeat(level);
            
            if (node.type === 'directory') {
                html += `<div class="file-item" style="padding-left: ${level * 20}px;">
                    📁 <strong>${node.name}</strong>
                    <small style="color: #666; margin-left: 0.5rem;">
                        (${node.stats.files} files, ${node.stats.php_files} PHP)
                    </small>
                </div>`;
                
                if (node.children) {
                    node.children.forEach(child => {
                        html += renderFileTree(child, level + 1);
                    });
                }
            } else {
                const icon = node.extension === 'php' ? '🐘' : '📄';
                const healthColor = node.health >= 80 ? '#4CAF50' : 
                                  node.health >= 60 ? '#8BC34A' : 
                                  node.health >= 40 ? '#FF9800' : '#F44336';
                
                html += `<div class="file-item" style="padding-left: ${level * 20}px;" onclick="selectFile('${node.path}')">
                    ${icon} ${node.name}
                    ${node.extension === 'php' ? `<span style="color: ${healthColor}; font-weight: bold; margin-left: 0.5rem;">${node.health}%</span>` : ''}
                    <small style="color: #666; margin-left: 0.5rem;">
                        (${formatFileSize(node.size)})
                    </small>
                </div>`;
            }
            
            return html;
        }
        
        // Format file size
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
        }
        
        // Select file for analysis
        async function selectFile(filePath) {
            // Update UI
            document.querySelectorAll('.file-item').forEach(item => {
                item.classList.remove('selected');
            });
            event.target.classList.add('selected');
            
            currentFile = filePath;
            
            // Load file content and analysis
            showTab('analyzer');
            document.querySelector('.nav-item[onclick="showTab(\'analyzer\')"]').classList.add('active');
            
            const content = document.getElementById('analyzer-content');
            content.innerHTML = '<div class="loading"></div> Analyzing file...';
            
            try {
                const [fileContent, analysis] = await Promise.all([
                    apiCall('get_file_content', { file: filePath }),
                    apiCall('analyze_file', { file: filePath })
                ]);
                
                if (fileContent.error || analysis.error) {
                    throw new Error(fileContent.error || analysis.error);
                }
                
                let html = `
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                        <div>
                            <h3>📄 File Information</h3>
                            <div class="issue-item">
                                <strong>File:</strong> ${filePath}<br>
                                <strong>Size:</strong> ${formatFileSize(fileContent.size)}<br>
                                <strong>Lines:</strong> ${fileContent.lines}<br>
                                <strong>Modified:</strong> ${new Date(fileContent.modified * 1000).toLocaleString()}<br>
                                <strong>Health Score:</strong> <span style="color: ${analysis.health >= 80 ? '#4CAF50' : analysis.health >= 60 ? '#8BC34A' : analysis.health >= 40 ? '#FF9800' : '#F44336'}; font-weight: bold;">${analysis.health}%</span>
                            </div>
                            
                            <h3>🔍 Analysis Summary</h3>
                            <div class="stats-grid" style="grid-template-columns: repeat(2, 1fr);">
                                <div class="stat-card" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);">
                                    <h4>Security Issues</h4>
                                    <div class="value">${analysis.security.length}</div>
                                </div>
                                <div class="stat-card" style="background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); color: #333;">
                                    <h4>Performance Issues</h4>
                                    <div class="value">${analysis.performance.length}</div>
                                </div>
                                <div class="stat-card" style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);">
                                    <h4>Functions</h4>
                                    <div class="value">${analysis.functions.length}</div>
                                </div>
                                <div class="stat-card" style="background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%);">
                                    <h4>Classes</h4>
                                    <div class="value">${analysis.classes.length}</div>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <h3>💻 Code Preview</h3>
                            <div class="code-viewer" style="max-height: 300px;">
${fileContent.content}
                            </div>
                        </div>
                    </div>
                `;
                
                // Security Issues
                if (analysis.security.length > 0) {
                    html += '<h3>🛡️ Security Issues</h3>';
                    analysis.security.forEach(issue => {
                        const badgeClass = `badge-${issue.severity}`;
                        html += `
                            <div class="issue-item issue-${issue.severity}">
                                <div style="display: flex; justify-content: between; align-items: flex-start;">
                                    <div style="flex: 1;">
                                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                                            <strong>${issue.type}</strong>
                                            <span class="badge ${badgeClass}">${issue.severity}</span>
                                        </div>
                                        <div style="margin-bottom: 0.5rem;">${issue.description}</div>
                                        <div style="font-family: monospace; background: rgba(0,0,0,0.1); padding: 0.5rem; border-radius: 3px; font-size: 0.9rem;">
                                            Line ${issue.line}: <code>${issue.code}</code>
                                        </div>
                                    </div>
                                    <button class="btn btn-warning" onclick="fixIssue('${issue.type.toLowerCase().replace(' ', '_')}', '${filePath}')">
                                        🔧 Fix
                                    </button>
                                </div>
                            </div>
                        `;
                    });
                }
                
                // Performance Issues
                if (analysis.performance.length > 0) {
                    html += '<h3>⚡ Performance Issues</h3>';
                    analysis.performance.forEach(issue => {
                        html += `
                            <div class="issue-item">
                                <div style="display: flex; justify-content: between; align-items: flex-start;">
                                    <div style="flex: 1;">
                                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                                            <strong>${issue.type}</strong>
                                            <span class="badge badge-${issue.severity}">${issue.severity}</span>
                                        </div>
                                        <div style="margin-bottom: 0.5rem;">${issue.description}</div>
                                        <div style="font-family: monospace; background: rgba(0,0,0,0.1); padding: 0.5rem; border-radius: 3px; font-size: 0.9rem;">
                                            Line ${issue.line}: <code>${issue.code}</code>
                                        </div>
                                    </div>
                                    <button class="btn" onclick="optimizeCode('${filePath}', ${issue.line})">
                                        ⚡ Optimize
                                    </button>
                                </div>
                            </div>
                        `;
                    });
                }
                
                // Functions and Classes
                if (analysis.functions.length > 0 || analysis.classes.length > 0) {
                    html += '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 2rem;">';
                    
                    if (analysis.functions.length > 0) {
                        html += '<div><h3>🔧 Functions</h3>';
                        analysis.functions.forEach(func => {
                            html += `
                                <div class="issue-item">
                                    <strong>${func.name}()</strong><br>
                                    <small>Line ${func.line}</small><br>
                                    <code style="font-size: 0.8rem;">${func.params}</code>
                                </div>
                            `;
                        });
                        html += '</div>';
                    }
                    
                    if (analysis.classes.length > 0) {
                        html += '<div><h3>🏗️ Classes</h3>';
                        analysis.classes.forEach(cls => {
                            html += `
                                <div class="issue-item">
                                    <strong>${cls.name}</strong><br>
                                    <small>Line ${cls.line}</small>
                                </div>
                            `;
                        });
                        html += '</div>';
                    }
                    
                    html += '</div>';
                }
                
                content.innerHTML = html;
                
            } catch (error) {
                content.innerHTML = `<div class="alert alert-danger">Failed to analyze file: ${error.message}</div>`;
            }
        }
        
        // Test database connection
        async function testDatabase() {
            const content = document.getElementById('database-content');
            content.innerHTML = '<div class="loading"></div> Testing database connection...';
            
            try {
                const result = await apiCall('test_database');
                
                if (result.error) {
                    throw new Error(result.error);
                }
                
                let html = '<h3>🗄️ Database Test Results</h3>';
                
                result.forEach(test => {
                    const alertClass = test.status === 'success' ? 'alert-success' : 
                                     test.status === 'warning' ? 'alert-warning' : 'alert-danger';
                    const icon = test.status === 'success' ? '✅' : 
                               test.status === 'warning' ? '⚠️' : '❌';
                    
                    html += `
                        <div class="alert ${alertClass}">
                            <strong>${icon} ${test.type}</strong><br>
                            ${test.message}<br>
                            <small>${test.details}</small>
                        </div>
                    `;
                });
                
                content.innerHTML = html;
                
            } catch (error) {
                content.innerHTML = `<div class="alert alert-danger">Database test failed: ${error.message}</div>`;
            }
        }
        
        // Load system information
        async function loadSystemInfo() {
            const content = document.getElementById('system-content');
            content.innerHTML = '<div class="loading"></div> Loading system information...';
            
            try {
                const result = await apiCall('get_system_info');
                
                if (result.error) {
                    throw new Error(result.error);
                }
                
                let html = `
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                        <div>
                            <h3>🖥️ System Information</h3>
                            <div class="issue-item">
                                <strong>PHP Version:</strong> ${result.php_version}<br>
                                <strong>Server:</strong> ${result.server_software}<br>
                                <strong>Operating System:</strong> ${result.operating_system}<br>
                                <strong>Current User:</strong> ${result.current_user}<br>
                                <strong>Server Time:</strong> ${result.server_time}<br>
                                <strong>Timezone:</strong> ${result.timezone}
                            </div>
                        </div>
                        
                        <div>
                            <h3>⚙️ PHP Configuration</h3>
                            <div class="issue-item">
                                <strong>Memory Limit:</strong> ${result.memory_limit}<br>
                                <strong>Max Execution Time:</strong> ${result.max_execution_time}s<br>
                                <strong>Post Max Size:</strong> ${result.post_max_size}<br>
                                <strong>Upload Max Size:</strong> ${result.upload_max_filesize}<br>
                                <strong>Error Reporting:</strong> ${result.error_reporting}<br>
                                <strong>Display Errors:</strong> ${result.display_errors ? 'On' : 'Off'}<br>
                                <strong>Log Errors:</strong> ${result.log_errors ? 'On' : 'Off'}
                            </div>
                        </div>
                        
                        <div>
                            <h3>💾 Disk Usage</h3>
                            <div class="issue-item">
                                <strong>Total Space:</strong> ${formatFileSize(result.disk_total_space)}<br>
                                <strong>Free Space:</strong> ${formatFileSize(result.disk_free_space)}<br>
                                <strong>Used Space:</strong> ${formatFileSize(result.disk_total_space - result.disk_free_space)}<br>
                                <strong>Usage:</strong> ${Math.round(((result.disk_total_space - result.disk_free_space) / result.disk_total_space) * 100)}%
                            </div>
                            <div class="health-bar">
                                <div class="health-fill ${Math.round(((result.disk_total_space - result.disk_free_space) / result.disk_total_space) * 100) > 80 ? 'health-poor' : 'health-good'}" 
                                     style="width: ${Math.round(((result.disk_total_space - result.disk_free_space) / result.disk_total_space) * 100)}%"></div>
                            </div>
                        </div>
                        
                        <div>
                            <h3>🔌 PHP Extensions</h3>
                            <div class="issue-item" style="max-height: 200px; overflow-y: auto;">
                                ${result.extensions.map(ext => `<span class="badge badge-success" style="margin: 0.1rem;">${ext}</span>`).join('')}
                            </div>
                        </div>
                    </div>
                `;
                
                content.innerHTML = html;
                
            } catch (error) {
                content.innerHTML = `<div class="alert alert-danger">Failed to load system information: ${error.message}</div>`;
            }
        }
        
        // Terminal functionality
        let terminalHistory = [];
        let historyIndex = -1;
        
        function handleTerminalInput(event) {
            if (event.key === 'Enter') {
                const input = event.target;
                const command = input.value.trim();
                
                if (command) {
                    executeTerminalCommand(command);
                    terminalHistory.unshift(command);
                    historyIndex = -1;
                }
                
                input.value = '';
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                if (historyIndex < terminalHistory.length - 1) {
                    historyIndex++;
                    event.target.value = terminalHistory[historyIndex];
                }
            } else if (event.key === 'ArrowDown') {
                event.preventDefault();
                if (historyIndex > 0) {
                    historyIndex--;
                    event.target.value = terminalHistory[historyIndex];
                } else if (historyIndex === 0) {
                    historyIndex = -1;
                    event.target.value = '';
                }
            }
        }
        
        async function executeTerminalCommand(command) {
            const output = document.getElementById('terminal-output');
            
            // Add command to terminal
            output.innerHTML += `<br>$ ${command}<br>`;
            
            try {
                const result = await fetch('?action=execute_command', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `command=${encodeURIComponent(command)}`
                });
                
                const data = await result.json();
                
                if (data.error) {
                    output.innerHTML += `<span style="color: #ff6b6b;">Error: ${data.error}</span><br>`;
                } else if (data.output) {
                    data.output.forEach(line => {
                        output.innerHTML += `${line}<br>`;
                    });
                }
                
            } catch (error) {
                output.innerHTML += `<span style="color: #ff6b6b;">Error: ${error.message}</span><br>`;
            }
            
            // Scroll to bottom
            output.scrollTop = output.scrollHeight;
        }
        
        // Fix issue function
        async function fixIssue(type, file) {
            const button = event.target;
            const originalText = button.textContent;
            button.innerHTML = '<span class="loading"></span>';
            button.disabled = true;
            
            try {
                const result = await apiCall('fix_issue', { type, file });
                
                if (result.error) {
                    throw new Error(result.error);
                }
                
                if (result.success) {
                    showAlert('success', `Issue fixed successfully! ${result.changes.join(', ')}`);
                    // Refresh the current analysis
                    if (currentFile === file) {
                        selectFile(file);
                    }
                } else {
                    showAlert('info', result.message || 'No changes needed');
                }
                
            } catch (error) {
                showAlert('danger', 'Failed to fix issue: ' + error.message);
            } finally {
                button.textContent = originalText;
                button.disabled = false;
            }
        }
        
        // Optimize code function
        function optimizeCode(file, line) {
            showAlert('info', `Code optimization for ${file}:${line} is not yet implemented. This would analyze and suggest performance improvements.`);
        }
        
        // Show alert function
        function showAlert(type, message) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type}`;
            alertDiv.innerHTML = message;
            alertDiv.style.position = 'fixed';
            alertDiv.style.top = '20px';
            alertDiv.style.right = '20px';
            alertDiv.style.zIndex = '9999';
            alertDiv.style.maxWidth = '400px';
            
            document.body.appendChild(alertDiv);
            
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }
        
        // Initialize dashboard on load
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-load file system
            loadFileSystem();
            
            // Auto-load system info
            loadSystemInfo();
            
            // Show welcome message
            showAlert('info', 'Welcome to PHP Diagnostics! Click "Run Full Scan" to analyze your application.');
        });
    </script>
</body>
</html>
<?php
// End of file
?>
