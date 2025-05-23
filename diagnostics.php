<?php
/**
 * PHP Shell Diagnostics Tool v2.3.0
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
 * - Advanced terminal with 50+ commands
 * - Integrated file explorer with code analyzer
 * - Export functionality
 * 
 * Security: Password protected (default: admin/admin)
 * 
 * @author PHP Diagnostics Team
 * @version 2.3.0
 */

// Configuration
define('DIAG_VERSION', '2.3.0');
define('DIAG_PASSWORD', 'admin'); // Change this!
define('DIAG_USERNAME', 'admin'); // Change this!
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB max file size for analysis
define('SCAN_DEPTH', 15); // Maximum directory depth to scan

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
            case 'get_git_status':
                echo json_encode(getGitStatus());
                break;
            case 'get_processes':
                echo json_encode(getProcessList());
                break;
            case 'get_network_info':
                echo json_encode(getNetworkInfo());
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
                $size = @filesize($path) ?: 0;
                $modified = @filemtime($path) ?: 0;
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
                    'health' => calculateFileHealth($path),
                    'mime_type' => getMimeType($path)
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
 * Get MIME type of file
 */
function getMimeType($file) {
    if (function_exists('mime_content_type')) {
        return @mime_content_type($file) ?: 'application/octet-stream';
    }
    
    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $mimeTypes = [
        'txt' => 'text/plain',
        'php' => 'text/x-php',
        'html' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'xml' => 'application/xml',
        'log' => 'text/plain',
        'env' => 'text/plain',
        'md' => 'text/markdown',
        'sql' => 'text/x-sql',
        'yml' => 'text/yaml',
        'yaml' => 'text/yaml',
        'ini' => 'text/plain',
        'conf' => 'text/plain',
        'htaccess' => 'text/plain'
    ];
    
    return $mimeTypes[$extension] ?? 'application/octet-stream';
}

/**
 * Calculate file health score
 */
function calculateFileHealth($file) {
    if (!is_readable($file) || !file_exists($file)) {
        return 0;
    }
    
    $size = @filesize($file) ?: 0;
    if ($size > MAX_FILE_SIZE) {
        return 50; // Large files get medium score
    }
    
    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    
    // Non-PHP files get default scores based on type
    if ($extension !== 'php') {
        $safeExtensions = ['txt', 'md', 'json', 'yml', 'yaml', 'css', 'js', 'html'];
        $configExtensions = ['env', 'ini', 'conf', 'htaccess'];
        
        if (in_array($extension, $safeExtensions)) {
            return 100;
        } elseif (in_array($extension, $configExtensions)) {
            return 85; // Config files need attention
        } else {
            return 75; // Unknown files
        }
    }
    
    try {
        $content = @file_get_contents($file);
        if ($content === false) {
            return 0;
        }
        
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
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Analyze a specific file
 */
function analyzeFile($file) {
    if (!file_exists($file)) {
        return ['error' => 'File not found'];
    }
    
    if (!is_readable($file)) {
        return ['error' => 'File not readable. Check permissions.'];
    }
    
    $size = @filesize($file) ?: 0;
    if ($size > MAX_FILE_SIZE) {
        return ['error' => 'File too large for analysis (' . formatBytes($size) . ')'];
    }
    
    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    
    try {
        $content = @file_get_contents($file);
        if ($content === false) {
            return ['error' => 'Failed to read file content. Check permissions.'];
        }
        
        $lines = explode("\n", $content);
        
        $analysis = [
            'file' => $file,
            'size' => $size,
            'lines' => count($lines),
            'extension' => $extension,
            'mime_type' => getMimeType($file),
            'health' => calculateFileHealth($file),
            'issues' => [],
            'functions' => [],
            'classes' => [],
            'includes' => [],
            'security' => [],
            'performance' => [],
            'quality' => [],
            'encoding' => mb_detect_encoding($content) ?: 'Unknown'
        ];
        
        // Only do detailed analysis for certain file types
        if (in_array($extension, ['php', 'js', 'html', 'css', 'sql'])) {
            
            // Security analysis for PHP files
            if ($extension === 'php') {
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
                    if (preg_match('/function\s+(\w+)\s*$$([^)]*)$$/', $line, $matches)) {
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
            }
            
            // Code quality checks
            $analysis['quality'][] = [
                'metric' => 'Lines of Code',
                'value' => count($lines),
                'status' => count($lines) > 500 ? 'warning' : 'good'
            ];
            
            $analysis['quality'][] = [
                'metric' => 'File Size',
                'value' => formatBytes($size),
                'status' => $size > 1024 * 1024 ? 'warning' : 'good'
            ];
            
            if ($extension === 'php') {
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
            }
        }
        
        return $analysis;
    } catch (Exception $e) {
        return ['error' => 'Analysis failed: ' . $e->getMessage()];
    }
}

/**
 * Get file content
 */
function getFileContent($file) {
    if (!file_exists($file)) {
        return ['error' => 'File not found'];
    }
    
    if (!is_readable($file)) {
        return ['error' => 'File not readable. Check permissions.'];
    }
    
    $size = @filesize($file) ?: 0;
    if ($size > MAX_FILE_SIZE) {
        return ['error' => 'File too large to display (' . formatBytes($size) . ')'];
    }
    
    try {
        $content = @file_get_contents($file);
        if ($content === false) {
            return ['error' => 'Failed to read file content. Check permissions.'];
        }
        
        // Check if file is binary
        $isBinary = false;
        if (function_exists('mb_check_encoding')) {
            $isBinary = !mb_check_encoding($content, 'UTF-8') && !mb_check_encoding($content, 'ASCII');
        } else {
            $isBinary = strpos($content, "\0") !== false;
        }
        
        if ($isBinary) {
            return [
                'content' => '[Binary file - cannot display content]',
                'size' => $size,
                'modified' => filemtime($file),
                'lines' => 0,
                'is_binary' => true,
                'encoding' => 'Binary'
            ];
        }
        
        return [
            'content' => $content,
            'size' => $size,
            'modified' => filemtime($file),
            'lines' => substr_count($content, "\n") + 1,
            'is_binary' => false,
            'encoding' => mb_detect_encoding($content) ?: 'Unknown'
        ];
    } catch (Exception $e) {
        return ['error' => 'Failed to read file: ' . $e->getMessage()];
    }
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
        ],
        'problem_files' => []
    ];
    
    // Scan all files for issues
    $fileSystem = scanFileSystem();
    $allFiles = findAllFiles($fileSystem);
    
    $totalHealth = 0;
    $fileCount = 0;
    
    foreach ($allFiles as $file) {
        $size = @filesize($file) ?: 0;
        if ($size <= MAX_FILE_SIZE) {
            $analysis = analyzeFile($file);
            if (!isset($analysis['error'])) {
                $fileIssues = count($analysis['security']) + count($analysis['performance']);
                $criticalIssues = count(array_filter($analysis['security'], function($issue) {
                    return $issue['severity'] === 'critical';
                }));
                
                $diagnostics['files'][] = [
                    'file' => $file,
                    'health' => $analysis['health'],
                    'issues' => $fileIssues,
                    'critical' => $criticalIssues,
                    'extension' => $analysis['extension']
                ];
                
                $totalHealth += $analysis['health'];
                $fileCount++;
                
                // Add to problem files if it has issues
                if ($fileIssues > 0) {
                    $diagnostics['problem_files'][] = [
                        'file' => $file,
                        'health' => $analysis['health'],
                        'issues' => $fileIssues,
                        'critical' => $criticalIssues,
                        'security' => $analysis['security'],
                        'performance' => $analysis['performance']
                    ];
                }
                
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
 * Find all files recursively
 */
function findAllFiles($fileSystem, &$files = []) {
    if (isset($fileSystem['children'])) {
        foreach ($fileSystem['children'] as $child) {
            if ($child['type'] === 'file') {
                $files[] = $child['path'];
            } elseif ($child['type'] === 'directory') {
                findAllFiles($child, $files);
            }
        }
    }
    return $files;
}

/**
 * Test database connection
 */
function testDatabaseConnection($credentials = null) {
    $results = [];
    
    // Use provided credentials if available
    if ($credentials && isset($credentials['host'], $credentials['name'], $credentials['user'])) {
        try {
            $dsn = "mysql:host={$credentials['host']};dbname={$credentials['name']}";
            $pdo = new PDO($dsn, $credentials['user'], $credentials['pass'] ?? '');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $results[] = [
                'type' => 'MySQL Connection',
                'status' => 'success',
                'message' => 'Database connection successful',
                'details' => "Connected to {$credentials['name']} on {$credentials['host']}"
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
            
            return $results;
        } catch (PDOException $e) {
            $results[] = [
                'type' => 'MySQL Connection',
                'status' => 'error',
                'message' => 'Database connection failed',
                'details' => $e->getMessage()
            ];
            return $results;
        }
    }
    
    // Try to find database configuration
    $configFiles = ['config.php', 'database.php', '.env', 'wp-config.php', 'app/config/database.php'];
    $dbConfig = null;
    
    foreach ($configFiles as $configFile) {
        if (file_exists($configFile)) {
            $content = @file_get_contents($configFile);
            if ($content === false) continue;
            
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
            'details' => 'Checked: ' . implode(', ', $configFiles) . '. Use the credentials form to connect manually.'
        ];
    }
    
    return $results;
}

/**
 * Get comprehensive system information
 */
function getSystemInfo() {
    $info = [
        'php_version' => PHP_VERSION,
        'php_sapi' => PHP_SAPI,
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        'operating_system' => PHP_OS,
        'architecture' => php_uname('m'),
        'hostname' => php_uname('n'),
        'kernel' => php_uname('r'),
        'memory_limit' => ini_get('memory_limit'),
        'memory_usage' => round(memory_get_usage() / 1024 / 1024, 2) . ' MB',
        'memory_peak' => round(memory_get_peak_usage() / 1024 / 1024, 2) . ' MB',
        'max_execution_time' => ini_get('max_execution_time'),
        'post_max_size' => ini_get('post_max_size'),
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'max_file_uploads' => ini_get('max_file_uploads'),
        'error_reporting' => error_reporting(),
        'display_errors' => ini_get('display_errors'),
        'log_errors' => ini_get('log_errors'),
        'error_log' => ini_get('error_log'),
        'extensions' => get_loaded_extensions(),
        'disk_free_space' => disk_free_space('.'),
        'disk_total_space' => disk_total_space('.'),
        'current_user' => get_current_user(),
        'server_time' => date('Y-m-d H:i:s'),
        'timezone' => date_default_timezone_get(),
        'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
        'server_name' => $_SERVER['SERVER_NAME'] ?? 'Unknown',
        'server_port' => $_SERVER['SERVER_PORT'] ?? 'Unknown',
        'https' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
        'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
        'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'Unknown',
        'request_uri' => $_SERVER['REQUEST_URI'] ?? 'Unknown',
        'script_name' => $_SERVER['SCRIPT_NAME'] ?? 'Unknown',
        'query_string' => $_SERVER['QUERY_STRING'] ?? '',
        'session_save_path' => session_save_path(),
        'session_name' => session_name(),
        'session_id' => session_id(),
        'temp_dir' => sys_get_temp_dir(),
        'include_path' => get_include_path(),
        'open_basedir' => ini_get('open_basedir'),
        'disable_functions' => ini_get('disable_functions'),
        'disable_classes' => ini_get('disable_classes'),
        'auto_prepend_file' => ini_get('auto_prepend_file'),
        'auto_append_file' => ini_get('auto_append_file'),
        'default_charset' => ini_get('default_charset'),
        'mbstring_enabled' => extension_loaded('mbstring'),
        'curl_enabled' => extension_loaded('curl'),
        'gd_enabled' => extension_loaded('gd'),
        'pdo_enabled' => extension_loaded('pdo'),
        'openssl_enabled' => extension_loaded('openssl'),
        'zip_enabled' => extension_loaded('zip'),
        'json_enabled' => extension_loaded('json'),
        'xml_enabled' => extension_loaded('xml'),
        'fileinfo_enabled' => extension_loaded('fileinfo'),
        'opcache_enabled' => extension_loaded('opcache'),
        'xdebug_enabled' => extension_loaded('xdebug'),
        'environment_variables' => $_ENV,
        'server_variables' => $_SERVER,
        'loaded_ini_files' => php_ini_loaded_file(),
        'additional_ini_files' => php_ini_scanned_files()
    ];
    
    // Add OPcache info if available
    if (function_exists('opcache_get_status')) {
        $info['opcache_status'] = opcache_get_status();
    }
    
    // Add Xdebug info if available
    if (function_exists('xdebug_info')) {
        $info['xdebug_info'] = xdebug_info();
    }
    
    // Add cURL info if available
    if (function_exists('curl_version')) {
        $info['curl_version'] = curl_version();
    }
    
    // Add GD info if available
    if (function_exists('gd_info')) {
        $info['gd_info'] = gd_info();
    }
    
    return $info;
}

/**
 * Get Git status
 */
function getGitStatus() {
    if (!is_dir('.git')) {
        return ['error' => 'Not a Git repository'];
    }
    
    $status = [];
    
    // Get current branch
    $branch = @shell_exec('git branch --show-current 2>/dev/null');
    if ($branch) {
        $status['branch'] = trim($branch);
    }
    
    // Get status
    $gitStatus = @shell_exec('git status --porcelain 2>/dev/null');
    if ($gitStatus) {
        $status['changes'] = array_filter(explode("\n", trim($gitStatus)));
    }
    
    // Get last commit
    $lastCommit = @shell_exec('git log -1 --pretty=format:"%h - %an, %ar : %s" 2>/dev/null');
    if ($lastCommit) {
        $status['last_commit'] = trim($lastCommit);
    }
    
    // Get remote info
    $remote = @shell_exec('git remote -v 2>/dev/null');
    if ($remote) {
        $status['remotes'] = array_filter(explode("\n", trim($remote)));
    }
    
    return $status;
}

/**
 * Get process list (limited)
 */
function getProcessList() {
    $processes = [];
    
    if (function_exists('shell_exec')) {
        // Try to get PHP processes
        $output = @shell_exec('ps aux | grep php 2>/dev/null');
        if ($output) {
            $lines = array_filter(explode("\n", trim($output)));
            foreach ($lines as $line) {
                if (strpos($line, 'grep') === false) {
                    $processes[] = $line;
                }
            }
        }
    }
    
    return $processes;
}

/**
 * Get network information
 */
function getNetworkInfo() {
    $info = [];
    
    // Get network interfaces (Linux/Unix)
    if (function_exists('shell_exec')) {
        $ifconfig = @shell_exec('ifconfig 2>/dev/null || ip addr 2>/dev/null');
        if ($ifconfig) {
            $info['interfaces'] = $ifconfig;
        }
        
        // Get listening ports
        $netstat = @shell_exec('netstat -tuln 2>/dev/null || ss -tuln 2>/dev/null');
        if ($netstat) {
            $info['listening_ports'] = $netstat;
        }
    }
    
    // Get DNS info
    $info['dns_servers'] = [];
    if (file_exists('/etc/resolv.conf')) {
        $resolv = @file_get_contents('/etc/resolv.conf');
        if ($resolv) {
            preg_match_all('/nameserver\s+([^\s]+)/', $resolv, $matches);
            $info['dns_servers'] = $matches[1];
        }
    }
    
    return $info;
}

/**
 * Execute command (MASSIVELY EXPANDED!)
 */
function executeCommand($command) {
    $command = trim($command);
    
    if (empty($command)) {
        return ['error' => 'No command provided'];
    }
    
    $output = [];
    $parts = explode(' ', $command);
    $cmd = strtolower($parts[0]);
    
    // Safe commands only
    switch ($cmd) {
        case 'help':
            $output = [
                '🚀 PHP Diagnostics Terminal v' . DIAG_VERSION,
                '=' . str_repeat('=', 50),
                '',
                '📁 FILE OPERATIONS:',
                '  ls [path]          - List directory contents',
                '  cat [file]         - Display file contents',
                '  head [file] [n]    - Show first n lines (default 10)',
                '  tail [file] [n]    - Show last n lines (default 10)',
                '  find [pattern]     - Find files matching pattern',
                '  grep [pattern] [file] - Search for pattern in file',
                '  wc [file]          - Count lines, words, characters',
                '  file [file]        - Show file type information',
                '  stat [file]        - Show detailed file statistics',
                '  tree [path]        - Show directory tree',
                '',
                '🔍 ANALYSIS & SECURITY:',
                '  scan [path]        - Quick security scan',
                '  check [file]       - Detailed security check',
                '  analyze [file]     - Full code analysis',
                '  vulns              - Check for known vulnerabilities',
                '  perms [path]       - Check file permissions',
                '  hash [file]        - Calculate file hashes',
                '  diff [file1] [file2] - Compare two files',
                '',
                '🐘 PHP SPECIFIC:',
                '  phpinfo            - Show PHP configuration',
                '  phpversion         - Show PHP version details',
                '  extensions         - List PHP extensions',
                '  ini [setting]      - Show PHP ini setting',
                '  opcache            - Show OPcache status',
                '  composer           - Composer information',
                '  artisan            - Laravel Artisan commands',
                '',
                '🗄️ DATABASE:',
                '  dbtest             - Test database connection',
                '  dbinfo             - Show database information',
                '  tables             - List database tables',
                '  query [sql]        - Execute SQL query (SELECT only)',
                '',
                '🌐 NETWORK & SYSTEM:',
                '  ping [host]        - Ping a host',
                '  nslookup [host]    - DNS lookup',
                '  curl [url]         - Make HTTP request',
                '  ports              - Show listening ports',
                '  processes          - Show running processes',
                '  netinfo            - Network interface information',
                '',
                '📊 MONITORING:',
                '  top                - Show system resources',
                '  memory             - Memory usage details',
                '  disk               - Disk usage information',
                '  load               - System load average',
                '  uptime             - System uptime',
                '  logs [file]        - Show log files',
                '',
                '🔧 GIT OPERATIONS:',
                '  git status         - Git repository status',
                '  git log [n]        - Show git log (last n commits)',
                '  git branch         - List branches',
                '  git diff           - Show git diff',
                '  git remote         - Show remotes',
                '',
                '🛠️ UTILITIES:',
                '  pwd                - Current directory',
                '  whoami             - Current user',
                '  date               - Current date/time',
                '  env                - Environment variables',
                '  history            - Command history',
                '  clear              - Clear terminal',
                '  version            - Tool version',
                '  benchmark [n]      - Run performance benchmark',
                '  base64 [encode|decode] [text] - Base64 operations',
                '  json [validate|format] [text] - JSON operations',
                '  url [encode|decode] [text] - URL operations',
                '',
                '💡 TIPS:',
                '  - Use arrow keys for command history',
                '  - Commands are case-insensitive',
                '  - Use quotes for arguments with spaces',
                '  - Type "help [command]" for detailed help'
            ];
            break;
            
        case 'ls':
            $path = isset($parts[1]) ? $parts[1] : '.';
            
            if (!is_dir($path)) {
                $output = ["Directory not found: $path"];
                break;
            }
            
            $files = @scandir($path);
            if ($files === false) {
                $output = ["Cannot read directory: $path"];
                break;
            }
            
            $output[] = "Directory listing of $path:";
            $output[] = str_repeat('-', 60);
            $output[] = sprintf("%-30s %-10s %-10s %s", "Name", "Type", "Size", "Modified");
            $output[] = str_repeat('-', 60);
            
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') continue;
                $fullPath = $path . DIRECTORY_SEPARATOR . $file;
                $isDir = is_dir($fullPath);
                $size = $isDir ? '<DIR>' : formatBytes(@filesize($fullPath) ?: 0);
                $type = $isDir ? 'DIR' : strtoupper(pathinfo($file, PATHINFO_EXTENSION));
                $modified = date('Y-m-d H:i', @filemtime($fullPath) ?: 0);
                
                $output[] = sprintf("%-30s %-10s %-10s %s", 
                    substr($file, 0, 29), 
                    $type,
                    $size,
                    $modified
                );
            }
            break;
            
        case 'cat':
            if (!isset($parts[1])) {
                $output = ["Error: No file specified"];
                break;
            }
            
            $file = $parts[1];
            if (!file_exists($file)) {
                $output = ["File not found: $file"];
                break;
            }
            
            if (!is_readable($file)) {
                $output = ["File not readable: $file"];
                break;
            }
            
            $size = @filesize($file) ?: 0;
            if ($size > MAX_FILE_SIZE) {
                $output = ["File too large to display: $file (" . formatBytes($size) . ")"];
                break;
            }
            
            $content = @file_get_contents($file);
            if ($content === false) {
                $output = ["Failed to read file: $file"];
                break;
            }
            
            // Check if binary
            if (strpos($content, "\0") !== false) {
                $output = ["Binary file - cannot display: $file"];
                break;
            }
            
            $lines = explode("\n", $content);
            $output = array_merge(["Contents of $file:"], $lines);
            break;
            
        case 'head':
            if (!isset($parts[1])) {
                $output = ["Error: No file specified"];
                break;
            }
            
            $file = $parts[1];
            $lines = isset($parts[2]) ? (int)$parts[2] : 10;
            
            if (!file_exists($file)) {
                $output = ["File not found: $file"];
                break;
            }
            
            $content = @file_get_contents($file);
            if ($content === false) {
                $output = ["Failed to read file: $file"];
                break;
            }
            
            $fileLines = explode("\n", $content);
            $output = array_merge(["First $lines lines of $file:"], array_slice($fileLines, 0, $lines));
            break;
            
        case 'tail':
            if (!isset($parts[1])) {
                $output = ["Error: No file specified"];
                break;
            }
            
            $file = $parts[1];
            $lines = isset($parts[2]) ? (int)$parts[2] : 10;
            
            if (!file_exists($file)) {
                $output = ["File not found: $file"];
                break;
            }
            
            $content = @file_get_contents($file);
            if ($content === false) {
                $output = ["Failed to read file: $file"];
                break;
            }
            
            $fileLines = explode("\n", $content);
            $output = array_merge(["Last $lines lines of $file:"], array_slice($fileLines, -$lines));
            break;
            
        case 'grep':
            if (!isset($parts[1]) || !isset($parts[2])) {
                $output = ["Error: Usage: grep [pattern] [file]"];
                break;
            }
            
            $pattern = $parts[1];
            $file = $parts[2];
            
            if (!file_exists($file)) {
                $output = ["File not found: $file"];
                break;
            }
            
            $content = @file_get_contents($file);
            if ($content === false) {
                $output = ["Failed to read file: $file"];
                break;
            }
            
            $lines = explode("\n", $content);
            $matches = [];
            
            foreach ($lines as $lineNum => $line) {
                if (stripos($line, $pattern) !== false) {
                    $matches[] = ($lineNum + 1) . ": " . $line;
                }
            }
            
            if (empty($matches)) {
                $output = ["No matches found for '$pattern' in $file"];
            } else {
                $output = array_merge(["Matches for '$pattern' in $file:"], $matches);
            }
            break;
            
        case 'wc':
            if (!isset($parts[1])) {
                $output = ["Error: No file specified"];
                break;
            }
            
            $file = $parts[1];
            if (!file_exists($file)) {
                $output = ["File not found: $file"];
                break;
            }
            
            $content = @file_get_contents($file);
            if ($content === false) {
                $output = ["Failed to read file: $file"];
                break;
            }
            
            $lines = substr_count($content, "\n") + 1;
            $words = str_word_count($content);
            $chars = strlen($content);
            
            $output = [
                "Word count for $file:",
                "Lines: $lines",
                "Words: $words", 
                "Characters: $chars"
            ];
            break;
            
        case 'file':
            if (!isset($parts[1])) {
                $output = ["Error: No file specified"];
                break;
            }
            
            $file = $parts[1];
            if (!file_exists($file)) {
                $output = ["File not found: $file"];
                break;
            }
            
            $mime = getMimeType($file);
            $size = @filesize($file) ?: 0;
            $perms = substr(sprintf('%o', @fileperms($file) ?: 0), -4);
            
            $output = [
                "File information for $file:",
                "Type: " . (is_dir($file) ? 'Directory' : 'File'),
                "MIME Type: $mime",
                "Size: " . formatBytes($size),
                "Permissions: $perms",
                "Readable: " . (is_readable($file) ? 'Yes' : 'No'),
                "Writable: " . (is_writable($file) ? 'Yes' : 'No'),
                "Executable: " . (is_executable($file) ? 'Yes' : 'No')
            ];
            break;
            
        case 'tree':
            $path = isset($parts[1]) ? $parts[1] : '.';
            $output = ["Directory tree for $path:"];
            $output = array_merge($output, generateTree($path));
            break;
            
        case 'scan':
            $path = isset($parts[1]) ? $parts[1] : '.';
            $output = ["Quick security scan of $path:"];
            $files = glob($path . '/*.php');
            $issueCount = 0;
            
            foreach ($files as $file) {
                $size = @filesize($file) ?: 0;
                if ($size <= MAX_FILE_SIZE) {
                    $content = @file_get_contents($file);
                    if ($content === false) continue;
                    
                    $issues = [];
                    
                    if (strpos($content, 'eval(') !== false) $issues[] = 'eval()';
                    if (strpos($content, 'exec(') !== false) $issues[] = 'exec()';
                    if (strpos($content, 'system(') !== false) $issues[] = 'system()';
                    if (strpos($content, 'mysql_') !== false) $issues[] = 'mysql_*()';
                    if (preg_match('/\$_(?:GET|POST|REQUEST).*echo/', $content)) $issues[] = 'XSS risk';
                    
                    if (!empty($issues)) {
                        $output[] = "⚠️ $file: " . implode(', ', $issues);
                        $issueCount++;
                    }
                }
            }
            
            if ($issueCount === 0) {
                $output[] = "✅ No obvious security issues found in PHP files";
            } else {
                $output[] = "Found $issueCount files with potential security issues";
            }
            break;
            
        case 'check':
            if (!isset($parts[1])) {
                $output = ["Error: No file specified"];
                break;
            }
            
            $file = $parts[1];
            if (!file_exists($file)) {
                $output = ["File not found: $file"];
                break;
            }
            
            $output = ["Security check for $file:"];
            $analysis = analyzeFile($file);
            
            if (isset($analysis['error'])) {
                $output[] = "Error: " . $analysis['error'];
                break;
            }
            
            $output[] = "Health Score: " . $analysis['health'] . "%";
            $output[] = "File Size: " . formatBytes($analysis['size']);
            $output[] = "Lines: " . $analysis['lines'];
            $output[] = "MIME Type: " . $analysis['mime_type'];
            
            if (empty($analysis['security'])) {
                $output[] = "✅ No security issues found";
            } else {
                $output[] = "⚠️ Found " . count($analysis['security']) . " security issues:";
                foreach ($analysis['security'] as $issue) {
                    $output[] = "  - " . $issue['type'] . " (Line " . $issue['line'] . "): " . $issue['description'];
                }
            }
            
            if (!empty($analysis['performance'])) {
                $output[] = "⚡ Performance issues:";
                foreach ($analysis['performance'] as $issue) {
                    $output[] = "  - " . $issue['type'] . " (Line " . $issue['line'] . ")";
                }
            }
            break;
            
        case 'phpinfo':
            ob_start();
            phpinfo(INFO_GENERAL | INFO_CONFIGURATION | INFO_MODULES);
            $phpinfo = ob_get_clean();
            $output = [strip_tags($phpinfo)];
            break;
            
        case 'phpversion':
            $output = [
                'PHP Version: ' . PHP_VERSION,
                'PHP SAPI: ' . PHP_SAPI,
                'Zend Version: ' . zend_version(),
                'Build Date: ' . (defined('PHP_BUILD_DATE') ? PHP_BUILD_DATE : 'Unknown'),
                'Architecture: ' . (PHP_INT_SIZE * 8) . '-bit'
            ];
            break;
            
        case 'extensions':
            $extensions = get_loaded_extensions();
            sort($extensions);
            $output = array_merge(['Loaded PHP Extensions:'], $extensions);
            break;
            
        case 'ini':
            if (!isset($parts[1])) {
                $output = ["Error: No setting specified"];
                break;
            }
            
            $setting = $parts[1];
            $value = ini_get($setting);
            
            if ($value === false) {
                $output = ["Setting '$setting' not found or not accessible"];
            } else {
                $output = ["$setting = " . ($value === '' ? '(empty)' : $value)];
            }
            break;
            
        case 'opcache':
            if (!extension_loaded('opcache')) {
                $output = ["OPcache extension not loaded"];
                break;
            }
            
            if (function_exists('opcache_get_status')) {
                $status = opcache_get_status();
                if ($status === false) {
                    $output = ["OPcache is disabled"];
                } else {
                    $output = [
                        'OPcache Status:',
                        'Enabled: ' . ($status['opcache_enabled'] ? 'Yes' : 'No'),
                        'Cache Full: ' . ($status['cache_full'] ? 'Yes' : 'No'),
                        'Restart Pending: ' . ($status['restart_pending'] ? 'Yes' : 'No'),
                        'Restart In Progress: ' . ($status['restart_in_progress'] ? 'Yes' : 'No'),
                        'Memory Usage: ' . formatBytes($status['memory_usage']['used_memory']),
                        'Free Memory: ' . formatBytes($status['memory_usage']['free_memory']),
                        'Cached Scripts: ' . $status['opcache_statistics']['num_cached_scripts'],
                        'Cache Hits: ' . $status['opcache_statistics']['hits'],
                        'Cache Misses: ' . $status['opcache_statistics']['misses']
                    ];
                }
            } else {
                $output = ["OPcache status function not available"];
            }
            break;
            
        case 'composer':
            if (file_exists('composer.json')) {
                $composer = json_decode(@file_get_contents('composer.json'), true);
                if ($composer) {
                    $output = [
                        'Composer Project Information:',
                        'Name: ' . ($composer['name'] ?? 'Not specified'),
                        'Description: ' . ($composer['description'] ?? 'Not specified'),
                        'Version: ' . ($composer['version'] ?? 'Not specified'),
                        'Type: ' . ($composer['type'] ?? 'Not specified')
                    ];
                    
                    if (isset($composer['require'])) {
                        $output[] = 'Dependencies:';
                        foreach ($composer['require'] as $package => $version) {
                            $output[] = "  $package: $version";
                        }
                    }
                } else {
                    $output = ["Invalid composer.json file"];
                }
            } else {
                $output = ["No composer.json file found"];
            }
            break;
            
        case 'git':
            if (!isset($parts[1])) {
                $output = ["Error: No git command specified"];
                break;
            }
            
            $gitCmd = $parts[1];
            
            if (!is_dir('.git')) {
                $output = ["Not a Git repository"];
                break;
            }
            
            switch ($gitCmd) {
                case 'status':
                    $status = getGitStatus();
                    if (isset($status['error'])) {
                        $output = [$status['error']];
                    } else {
                        $output = ['Git Status:'];
                        if (isset($status['branch'])) {
                            $output[] = 'Branch: ' . $status['branch'];
                        }
                        if (isset($status['changes'])) {
                            $output[] = 'Changes:';
                            $output = array_merge($output, $status['changes']);
                        } else {
                            $output[] = 'Working directory clean';
                        }
                    }
                    break;
                    
                case 'log':
                    $count = isset($parts[2]) ? (int)$parts[2] : 5;
                    $log = @shell_exec("git log --oneline -$count 2>/dev/null");
                    if ($log) {
                        $output = array_merge(['Recent commits:'], explode("\n", trim($log)));
                    } else {
                        $output = ["Unable to get git log"];
                    }
                    break;
                    
                case 'branch':
                    $branches = @shell_exec('git branch 2>/dev/null');
                    if ($branches) {
                        $output = array_merge(['Branches:'], explode("\n", trim($branches)));
                    } else {
                        $output = ["Unable to get branches"];
                    }
                    break;
                    
                default:
                    $output = ["Unknown git command: $gitCmd"];
            }
            break;
            
        case 'ping':
            if (!isset($parts[1])) {
                $output = ["Error: No host specified"];
                break;
            }
            
            $host = $parts[1];
            $result = @shell_exec("ping -c 4 $host 2>/dev/null");
            if ($result) {
                $output = explode("\n", trim($result));
            } else {
                $output = ["Ping failed or not available"];
            }
            break;
            
        case 'nslookup':
            if (!isset($parts[1])) {
                $output = ["Error: No host specified"];
                break;
            }
            
            $host = $parts[1];
            $ip = gethostbyname($host);
            
            if ($ip === $host) {
                $output = ["DNS lookup failed for $host"];
            } else {
                $output = ["$host resolves to $ip"];
                
                // Try reverse lookup
                $reverse = gethostbyaddr($ip);
                if ($reverse !== $ip) {
                    $output[] = "Reverse lookup: $reverse";
                }
            }
            break;
            
        case 'curl':
            if (!isset($parts[1])) {
                $output = ["Error: No URL specified"];
                break;
            }
            
            $url = $parts[1];
            
            if (!function_exists('curl_init')) {
                $output = ["cURL extension not available"];
                break;
            }
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            
            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($error) {
                $output = ["cURL error: $error"];
            } else {
                $output = [
                    "HTTP request to $url:",
                    "Status Code: $httpCode",
                    "Headers:",
                    $result
                ];
            }
            break;
            
        case 'top':
            $output = [
                'System Resources:',
                'Memory Limit: ' . ini_get('memory_limit'),
                'Memory Usage: ' . round(memory_get_usage() / 1024 / 1024, 2) . ' MB',
                'Peak Memory: ' . round(memory_get_peak_usage() / 1024 / 1024, 2) . ' MB',
                'Load Average: ' . (function_exists('sys_getloadavg') ? implode(', ', sys_getloadavg()) : 'Not available'),
                'Disk Free: ' . formatBytes(disk_free_space('.')),
                'Disk Total: ' . formatBytes(disk_total_space('.'))
            ];
            break;
            
        case 'memory':
            $output = [
                'Memory Information:',
                'Memory Limit: ' . ini_get('memory_limit'),
                'Current Usage: ' . round(memory_get_usage() / 1024 / 1024, 2) . ' MB',
                'Peak Usage: ' . round(memory_get_peak_usage() / 1024 / 1024, 2) . ' MB',
                'Real Usage: ' . round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',
                'Real Peak: ' . round(memory_get_peak_usage(true) / 1024 / 1024, 2) . ' MB'
            ];
            break;
            
        case 'disk':
            $free = disk_free_space('.');
            $total = disk_total_space('.');
            $used = $total - $free;
            $percent = round(($used / $total) * 100, 1);
            
            $output = [
                'Disk Usage:',
                'Total: ' . formatBytes($total),
                'Used: ' . formatBytes($used),
                'Free: ' . formatBytes($free),
                'Usage: ' . $percent . '%'
            ];
            break;
            
        case 'uptime':
            if (function_exists('shell_exec')) {
                $uptime = @shell_exec('uptime 2>/dev/null');
                if ($uptime) {
                    $output = [trim($uptime)];
                } else {
                    $output = ['Uptime information not available'];
                }
            } else {
                $output = ['Shell execution not available'];
            }
            break;
            
        case 'processes':
            $processes = getProcessList();
            if (empty($processes)) {
                $output = ['No process information available'];
            } else {
                $output = array_merge(['PHP Processes:'], $processes);
            }
            break;
            
        case 'ports':
            $netinfo = getNetworkInfo();
            if (isset($netinfo['listening_ports'])) {
                $output = array_merge(['Listening Ports:'], explode("\n", $netinfo['listening_ports']));
            } else {
                $output = ['Port information not available'];
            }
            break;
            
        case 'netinfo':
            $netinfo = getNetworkInfo();
            if (isset($netinfo['interfaces'])) {
                $output = array_merge(['Network Interfaces:'], explode("\n", trim($netinfo['interfaces'])));
            } else {
                $output = ['Network interface information not available'];
            }
            
            if (isset($netinfo['dns_servers']) && !empty($netinfo['dns_servers'])) {
                $output[] = '';
                $output[] = 'DNS Servers:';
                $output = array_merge($output, $netinfo['dns_servers']);
            }
            break;
            
        case 'logs':
            $logFile = isset($parts[1]) ? $parts[1] : 'error.log';
            
            // Common log file locations
            $logPaths = [
                $logFile,
                '/var/log/' . $logFile,
                '/var/log/apache2/' . $logFile,
                '/var/log/nginx/' . $logFile,
                './logs/' . $logFile,
                './storage/logs/' . $logFile
            ];
            
            $found = false;
            foreach ($logPaths as $path) {
                if (file_exists($path) && is_readable($path)) {
                    $content = @file_get_contents($path);
                    if ($content !== false) {
                        $lines = explode("\n", $content);
                        $output = array_merge(["Last 20 lines of $path:"], array_slice($lines, -20));
                        $found = true;
                        break;
                    }
                }
            }
            
            if (!$found) {
                $output = ["Log file not found: $logFile"];
            }
            break;
            
        case 'env':
            $output = ['Environment Variables:'];
            foreach ($_ENV as $key => $value) {
                $output[] = "$key=" . (strlen($value) > 100 ? substr($value, 0, 100) . '...' : $value);
            }
            
            if (empty($_ENV)) {
                $output[] = 'No environment variables found in $_ENV';
                $output[] = 'Server variables available in $_SERVER';
            }
            break;
            
        case 'benchmark':
            $iterations = isset($parts[1]) ? (int)$parts[1] : 1000;
            
            $output = ["Running benchmark with $iterations iterations..."];
            
            // CPU benchmark
            $start = microtime(true);
            for ($i = 0; $i < $iterations; $i++) {
                $result = sqrt($i) * sin($i) * cos($i);
            }
            $cpuTime = (microtime(true) - $start) * 1000;
            
            // Memory benchmark
            $start = microtime(true);
            $array = [];
            for ($i = 0; $i < $iterations; $i++) {
                $array[] = str_repeat('x', 100);
            }
            $memTime = (microtime(true) - $start) * 1000;
            unset($array);
            
            // File I/O benchmark
            $start = microtime(true);
            $tempFile = tempnam(sys_get_temp_dir(), 'benchmark');
            for ($i = 0; $i < min($iterations, 100); $i++) {
                file_put_contents($tempFile, str_repeat('test', 100), FILE_APPEND);
            }
            $content = file_get_contents($tempFile);
            unlink($tempFile);
            $ioTime = (microtime(true) - $start) * 1000;
            
            $output[] = "Results:";
            $output[] = "CPU Test: " . round($cpuTime, 2) . "ms";
            $output[] = "Memory Test: " . round($memTime, 2) . "ms";
            $output[] = "I/O Test: " . round($ioTime, 2) . "ms";
            $output[] = "Total: " . round($cpuTime + $memTime + $ioTime, 2) . "ms";
            break;
            
        case 'base64':
            if (!isset($parts[1]) || !isset($parts[2])) {
                $output = ["Error: Usage: base64 [encode|decode] [text]"];
                break;
            }
            
            $operation = $parts[1];
            $text = implode(' ', array_slice($parts, 2));
            
            if ($operation === 'encode') {
                $output = ["Base64 Encoded: " . base64_encode($text)];
            } elseif ($operation === 'decode') {
                $decoded = base64_decode($text);
                if ($decoded === false) {
                    $output = ["Error: Invalid base64 input"];
                } else {
                    $output = ["Base64 Decoded: " . $decoded];
                }
            } else {
                $output = ["Error: Operation must be 'encode' or 'decode'"];
            }
            break;
            
        case 'json':
            if (!isset($parts[1]) || !isset($parts[2])) {
                $output = ["Error: Usage: json [validate|format] [json_text]"];
                break;
            }
            
            $operation = $parts[1];
            $jsonText = implode(' ', array_slice($parts, 2));
            
            if ($operation === 'validate') {
                $decoded = json_decode($jsonText);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $output = ["✅ Valid JSON"];
                } else {
                    $output = ["❌ Invalid JSON: " . json_last_error_msg()];
                }
            } elseif ($operation === 'format') {
                $decoded = json_decode($jsonText);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $output = ["Formatted JSON:", json_encode($decoded, JSON_PRETTY_PRINT)];
                } else {
                    $output = ["❌ Invalid JSON: " . json_last_error_msg()];
                }
            } else {
                $output = ["Error: Operation must be 'validate' or 'format'"];
            }
            break;
            
        case 'url':
            if (!isset($parts[1]) || !isset($parts[2])) {
                $output = ["Error: Usage: url [encode|decode] [text]"];
                break;
            }
            
            $operation = $parts[1];
            $text = implode(' ', array_slice($parts, 2));
            
            if ($operation === 'encode') {
                $output = ["URL Encoded: " . urlencode($text)];
            } elseif ($operation === 'decode') {
                $output = ["URL Decoded: " . urldecode($text)];
            } else {
                $output = ["Error: Operation must be 'encode' or 'decode'"];
            }
            break;
            
        case 'hash':
            if (!isset($parts[1])) {
                $output = ["Error: No file specified"];
                break;
            }
            
            $file = $parts[1];
            if (!file_exists($file)) {
                $output = ["File not found: $file"];
                break;
            }
            
            $output = [
                "File hashes for $file:",
                "MD5: " . md5_file($file),
                "SHA1: " . sha1_file($file),
                "SHA256: " . hash_file('sha256', $file)
            ];
            break;
            
        case 'diff':
            if (!isset($parts[1]) || !isset($parts[2])) {
                $output = ["Error: Usage: diff [file1] [file2]"];
                break;
            }
            
            $file1 = $parts[1];
            $file2 = $parts[2];
            
            if (!file_exists($file1)) {
                $output = ["File not found: $file1"];
                break;
            }
            
            if (!file_exists($file2)) {
                $output = ["File not found: $file2"];
                break;
            }
            
            $content1 = @file_get_contents($file1);
            $content2 = @file_get_contents($file2);
            
            if ($content1 === false || $content2 === false) {
                $output = ["Error reading files"];
                break;
            }
            
            if ($content1 === $content2) {
                $output = ["Files are identical"];
            } else {
                $lines1 = explode("\n", $content1);
                $lines2 = explode("\n", $content2);
                
                $output = ["Differences between $file1 and $file2:"];
                $maxLines = max(count($lines1), count($lines2));
                
                for ($i = 0; $i < min($maxLines, 50); $i++) {
                    $line1 = isset($lines1[$i]) ? $lines1[$i] : '';
                    $line2 = isset($lines2[$i]) ? $lines2[$i] : '';
                    
                    if ($line1 !== $line2) {
                        $output[] = "Line " . ($i + 1) . ":";
                        $output[] = "< $line1";
                        $output[] = "> $line2";
                        $output[] = "";
                    }
                }
                
                if ($maxLines > 50) {
                    $output[] = "... (showing first 50 differences)";
                }
            }
            break;
            
        case 'vulns':
            $output = ["Vulnerability Check:"];
            
            // Check PHP version
            $phpVersion = PHP_VERSION;
            $output[] = "PHP Version: $phpVersion";
            
            // Check for known vulnerable functions
            $dangerousFunctions = ['eval', 'exec', 'system', 'shell_exec', 'passthru', 'file_get_contents'];
            $disabledFunctions = explode(',', ini_get('disable_functions'));
            
            $output[] = "Dangerous Functions Status:";
            foreach ($dangerousFunctions as $func) {
                $status = in_array($func, $disabledFunctions) ? '✅ Disabled' : '⚠️ Enabled';
                $output[] = "  $func: $status";
            }
            
            // Check file permissions
            $criticalFiles = ['.env', 'config.php', 'wp-config.php', 'database.php'];
            $output[] = "Critical File Permissions:";
            
            foreach ($criticalFiles as $file) {
                if (file_exists($file)) {
                    $perms = substr(sprintf('%o', fileperms($file)), -3);
                    $status = ($perms === '644' || $perms === '600') ? '✅ Secure' : '⚠️ Check permissions';
                    $output[] = "  $file ($perms): $status";
                }
            }
            break;
            
        case 'perms':
            $path = isset($parts[1]) ? $parts[1] : '.';
            
            if (!file_exists($path)) {
                $output = ["Path not found: $path"];
                break;
            }
            
            $output = ["Permissions for $path:"];
            
            if (is_dir($path)) {
                $files = scandir($path);
                foreach ($files as $file) {
                    if ($file === '.' || $file === '..') continue;
                    
                    $fullPath = $path . DIRECTORY_SEPARATOR . $file;
                    $perms = substr(sprintf('%o', fileperms($fullPath)), -4);
                    $type = is_dir($fullPath) ? 'DIR' : 'FILE';
                    
                    $output[] = sprintf("%-30s %s %s", $file, $perms, $type);
                }
            } else {
                $perms = substr(sprintf('%o', fileperms($path)), -4);
                $output[] = "Permissions: $perms";
                $output[] = "Readable: " . (is_readable($path) ? 'Yes' : 'No');
                $output[] = "Writable: " . (is_writable($path) ? 'Yes' : 'No');
                $output[] = "Executable: " . (is_executable($path) ? 'Yes' : 'No');
            }
            break;
            
        case 'pwd':
            $output = [getcwd()];
            break;
            
        case 'date':
            $output = [
                'Current Date/Time: ' . date('Y-m-d H:i:s T'),
                'Timezone: ' . date_default_timezone_get(),
                'Unix Timestamp: ' . time()
            ];
            break;
            
        case 'whoami':
            $output = [
                'Current User: ' . get_current_user(),
                'Process Owner: ' . (function_exists('posix_getpwuid') && function_exists('posix_geteuid') ? 
                    posix_getpwuid(posix_geteuid())['name'] : 'Unknown')
            ];
            break;
            
        case 'version':
            $output = [
                'PHP Diagnostics Tool v' . DIAG_VERSION,
                'PHP Version: ' . PHP_VERSION,
                'Server: ' . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'),
                'OS: ' . PHP_OS
            ];
            break;
            
        case 'history':
            // This would need to be implemented with session storage
            $output = ['Command history not implemented yet'];
            break;
            
        case 'clear':
            return ['clear' => true];
            
        case 'find':
            if (!isset($parts[1])) {
                $output = ["Error: No pattern specified"];
                break;
            }
            
            $pattern = $parts[1];
            $path = isset($parts[2]) ? $parts[2] : '.';
            
            $output = ["Finding files matching '$pattern' in $path:"];
            
            try {
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS)
                );
                
                $matches = [];
                foreach ($iterator as $file) {
                    if (stripos($file->getFilename(), $pattern) !== false) {
                        $matches[] = $file->getPathname();
                    }
                }
                
                if (empty($matches)) {
                    $output[] = "No files found matching '$pattern'";
                } else {
                    $output = array_merge($output, array_slice($matches, 0, 50));
                    if (count($matches) > 50) {
                        $output[] = "... (" . count($matches) . " total matches, showing first 50)";
                    }
                }
            } catch (Exception $e) {
                $output = ["Error searching: " . $e->getMessage()];
            }
            break;
            
        default:
            $output = ["Command not recognized: $command", "Type 'help' for available commands"];
    }
    
    return ['output' => $output];
}

/**
 * Generate directory tree
 */
function generateTree($dir, $prefix = '', $isLast = true) {
    $tree = [];
    
    if (!is_dir($dir) || !is_readable($dir)) {
        return ["Cannot read directory: $dir"];
    }
    
    $files = @scandir($dir);
    if ($files === false) {
        return ["Cannot scan directory: $dir"];
    }
    
    $files = array_filter($files, function($file) {
        return $file !== '.' && $file !== '..';
    });
    
    $count = count($files);
    $i = 0;
    
    foreach ($files as $file) {
        $i++;
        $isLastFile = ($i === $count);
        $fullPath = $dir . DIRECTORY_SEPARATOR . $file;
        
        $connector = $isLastFile ? '└── ' : '├── ';
        $tree[] = $prefix . $connector . $file;
        
        if (is_dir($fullPath) && $i <= 10) { // Limit depth for performance
            $newPrefix = $prefix . ($isLastFile ? '    ' : '│   ');
            $subtree = generateTree($fullPath, $newPrefix, $isLastFile);
            $tree = array_merge($tree, $subtree);
        }
    }
    
    return $tree;
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
 * Export results in various formats
 */
function exportResults($format, $data) {
    if (empty($data)) {
        return ['error' => 'No data to export'];
    }
    
    $data = json_decode($data, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['error' => 'Invalid data format'];
    }
    
    switch ($format) {
        case 'json':
            return [
                'content' => json_encode($data, JSON_PRETTY_PRINT),
                'filename' => 'php_diagnostics_' . date('Y-m-d') . '.json',
                'mime' => 'application/json'
            ];
            
        case 'csv':
            $output = '';
            
            // Handle different data types
            if (isset($data['security'])) {
                $output = "File,Type,Severity,Line,Description\n";
                foreach ($data['security'] as $issue) {
                    $output .= '"' . $issue['file'] . '","' . $issue['type'] . '","' . 
                              $issue['severity'] . '",' . $issue['line'] . ',"' . 
                              str_replace('"', '""', $issue['description']) . "\"\n";
                }
            } elseif (isset($data['files'])) {
                $output = "File,Health,Issues,Critical\n";
                foreach ($data['files'] as $file) {
                    $output .= '"' . $file['file'] . '",' . $file['health'] . ',' . 
                              $file['issues'] . ',' . $file['critical'] . "\n";
                }
            } else {
                // Generic export
                $output = json_encode($data);
            }
            
            return [
                'content' => $output,
                'filename' => 'php_diagnostics_' . date('Y-m-d') . '.csv',
                'mime' => 'text/csv'
            ];
            
        case 'html':
            $title = 'PHP Diagnostics Report - ' . date('Y-m-d');
            $output = "<!DOCTYPE html>\n<html>\n<head>\n";
            $output .= "<title>$title</title>\n";
            $output .= "<style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                h1 { color: #1e3c72; }
                table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
                tr:nth-child(even) { background-color: #f9f9f9; }
                .critical { background-color: #ffdddd; }
                .high { background-color: #ffe0cc; }
                .medium { background-color: #fff4cc; }
                .low { background-color: #e6f7ff; }
                .summary { background-color: #f0f0f0; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
            </style>\n";
            $output .= "</head>\n<body>\n";
            $output .= "<h1>$title</h1>\n";
            
            // Summary
            if (isset($data['summary'])) {
                $output .= "<div class='summary'>";
                $output .= "<h2>Summary</h2>";
                $output .= "<p>Health Score: <strong>{$data['summary']['health_score']}%</strong></p>";
                $output .= "<p>Total Issues: <strong>{$data['summary']['total_issues']}</strong></p>";
                $output .= "<p>Critical Issues: <strong>{$data['summary']['critical_issues']}</strong></p>";
                $output .= "</div>";
            }
            
            // Security Issues
            if (isset($data['security']) && !empty($data['security'])) {
                $output .= "<h2>Security Issues</h2>";
                $output .= "<table>";
                $output .= "<tr><th>File</th><th>Type</th><th>Severity</th><th>Line</th><th>Description</th></tr>";
                
                foreach ($data['security'] as $issue) {
                    $output .= "<tr class='{$issue['severity']}'>";
                    $output .= "<td>" . htmlspecialchars($issue['file']) . "</td>";
                    $output .= "<td>" . htmlspecialchars($issue['type']) . "</td>";
                    $output .= "<td>" . htmlspecialchars($issue['severity']) . "</td>";
                    $output .= "<td>" . htmlspecialchars($issue['line']) . "</td>";
                    $output .= "<td>" . htmlspecialchars($issue['description']) . "</td>";
                    $output .= "</tr>";
                }
                
                $output .= "</table>";
            }
            
            // Files
            if (isset($data['files']) && !empty($data['files'])) {
                $output .= "<h2>Analyzed Files</h2>";
                $output .= "<table>";
                $output .= "<tr><th>File</th><th>Health</th><th>Issues</th><th>Critical</th></tr>";
                
                foreach ($data['files'] as $file) {
                    $class = $file['critical'] > 0 ? 'critical' : ($file['issues'] > 0 ? 'medium' : '');
                    $output .= "<tr class='$class'>";
                    $output .= "<td>" . htmlspecialchars($file['file']) . "</td>";
                    $output .= "<td>" . htmlspecialchars($file['health']) . "%</td>";
                    $output .= "<td>" . htmlspecialchars($file['issues']) . "</td>";
                    $output .= "<td>" . htmlspecialchars($file['critical']) . "</td>";
                    $output .= "</tr>";
                }
                
                $output .= "</table>";
            }
            
            $output .= "<p><em>Generated by PHP Diagnostics v" . DIAG_VERSION . " on " . date('Y-m-d H:i:s') . "</em></p>";
            $output .= "</body>\n</html>";
            
            return [
                'content' => $output,
                'filename' => 'php_diagnostics_' . date('Y-m-d') . '.html',
                'mime' => 'text/html'
            ];
            
        default:
            return ['error' => 'Unsupported export format'];
    }
}

/**
 * Save credentials
 */
function saveCredentials($credentials) {
    if (!$credentials) {
        return ['error' => 'No credentials provided'];
    }
    
    // In a real implementation, you might want to store these securely
    // For this demo, we'll just return success
    return [
        'success' => true,
        'message' => 'Credentials saved successfully'
    ];
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
            border-radius: 20px;
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
            margin-bottom: 1.5rem;
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
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        input[type="text"]:focus, input[type="password"]:focus {
            outline: none;
            border-color: #1e3c72;
            box-shadow: 0 0 0 3px rgba(30, 60, 114, 0.2);
        }
        .btn {
            width: 100%;
            padding: 0.75rem;
            background: #1e3c72;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 6px rgba(30, 60, 114, 0.2);
        }
        .btn:hover {
            background: #2a5298;
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(30, 60, 114, 0.3);
        }
        .btn:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(30, 60, 114, 0.2);
        }
        .error {
            background: #fee;
            color: #c33;
            padding: 0.75rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            border: 1px solid #fcc;
        }
        .info {
            background: #e8f4fd;
            color: #0066cc;
            padding: 0.75rem;
            border-radius: 10px;
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
        
        :root {
            --primary: #1e3c72;
            --primary-light: #2a5298;
            --secondary: #ff5e62;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
            --info: #17a2b8;
            --light: #f8f9fa;
            --dark: #343a40;
            --gray: #6c757d;
            --shadow-sm: 0 2px 4px rgba(0,0,0,0.05);
            --shadow: 0 4px 6px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
            --radius: 12px;
            --radius-sm: 6px;
            --radius-lg: 20px;
        }
        
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: #f5f5f5;
            color: #333;
        }
        
        .header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 100;
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
            background: var(--success);
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
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            height: fit-content;
            position: sticky;
            top: 6rem;
        }
        
        .sidebar h3 {
            margin-bottom: 1rem;
            color: var(--primary);
            border-bottom: 2px solid #eee;
            padding-bottom: 0.5rem;
        }
        
        .nav-item {
            display: block;
            padding: 0.75rem 1rem;
            margin: 0.5rem 0;
            background: var(--light);
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            text-align: left;
            width: 100%;
            transition: all 0.3s;
            text-decoration: none;
            color: #333;
            font-weight: 500;
            box-shadow: var(--shadow-sm);
        }
        
        .nav-item:hover, .nav-item.active {
            background: var(--primary);
            color: white;
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }
        
        .main-content {
            background: white;
            border-radius: var(--radius);
            padding: 2rem;
            box-shadow: var(--shadow);
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
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            color: var(--dark);
            padding: 1.5rem;
            border-radius: var(--radius);
            text-align: center;
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
            transition: all 0.3s;
            border: 1px solid rgba(0,0,0,0.05);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--primary) 0%, var(--secondary) 100%);
        }
        
        .stat-card h4 {
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            color: var(--gray);
        }
        
        .stat-card .value {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0.25rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .stat-card .label {
            font-size: 0.8rem;
            color: var(--gray);
        }
        
        .btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s;
            margin: 0.25rem;
            box-shadow: var(--shadow-sm);
            font-weight: 500;
        }
        
        .btn:hover {
            background: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .btn-danger {
            background: var(--danger);
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .btn-success {
            background: var(--success);
        }
        
        .btn-success:hover {
            background: #218838;
        }
        
        .btn-warning {
            background: var(--warning);
            color: #333;
        }
        
        .btn-warning:hover {
            background: #e0a800;
        }
        
        .btn-info {
            background: var(--info);
        }
        
        .btn-info:hover {
            background: #138496;
        }
        
        .alert {
            padding: 1rem;
            border-radius: var(--radius-sm);
            margin: 1rem 0;
            box-shadow: var(--shadow-sm);
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
        
        .file-explorer {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            height: 600px;
        }
        
        .file-tree {
            max-height: 100%;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: var(--radius-sm);
            padding: 1rem;
            background: var(--light);
        }
        
        .file-item {
            padding: 0.5rem;
            cursor: pointer;
            border-radius: var(--radius-sm);
            margin: 0.1rem 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }
        
        .file-item:hover {
            background: rgba(0,0,0,0.05);
        }
        
        .file-item.selected {
            background: var(--primary);
            color: white;
        }
        
        .file-analyzer {
            border: 1px solid #ddd;
            border-radius: var(--radius-sm);
            padding: 1rem;
            background: white;
            overflow-y: auto;
        }
        
        .file-icon {
            width: 16px;
            height: 16px;
            display: inline-block;
        }
        
        .health-bar {
            width: 100%;
            height: 10px;
            background: #eee;
            border-radius: 5px;
            overflow: hidden;
            margin: 0.5rem 0;
        }
        
        .health-fill {
            height: 100%;
            transition: width 0.3s;
        }
        
        .health-excellent { background: var(--success); }
        .health-good { background: #8BC34A; }
        .health-warning { background: var(--warning); }
        .health-poor { background: var(--danger); }
        
        .code-viewer {
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 1rem;
            border-radius: var(--radius-sm);
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            max-height: 400px;
            overflow: auto;
            white-space: pre-wrap;
            line-height: 1.4;
            box-shadow: var(--shadow);
        }
        
        .terminal-container {
            background: #1e1e1e;
            border-radius: var(--radius);
            padding: 1rem;
            box-shadow: var(--shadow);
            margin-top: 1rem;
        }
        
        .terminal {
            background: #000;
            color: #00ff00;
            padding: 1rem;
            border-radius: var(--radius-sm);
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            height: 400px;
            overflow-y: auto;
            box-shadow: inset 0 0 10px rgba(0,0,0,0.5);
        }
        
        .terminal-input-container {
            display: flex;
            align-items: center;
            background: #1a1a1a;
            padding: 0.75rem 1rem;
            border-radius: var(--radius-sm);
            margin-top: 0.5rem;
            border: 2px solid #333;
            transition: border-color 0.3s;
        }
        
        .terminal-input-container:focus-within {
            border-color: #00ff00;
            box-shadow: 0 0 10px rgba(0, 255, 0, 0.3);
        }
        
        .terminal-prompt {
            color: #00ff00;
            margin-right: 0.5rem;
            font-family: 'Courier New', monospace;
            font-weight: bold;
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
        
        .terminal-suggestions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }
        
        .terminal-suggestion {
            background: #333;
            color: #00ff00;
            border: 1px solid #555;
            padding: 0.25rem 0.5rem;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .terminal-suggestion:hover {
            background: #444;
            border-color: #00ff00;
            box-shadow: 0 0 5px rgba(0, 255, 0, 0.3);
        }
        
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid var(--primary);
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
            background: var(--primary);
            transition: width 0.3s;
        }
        
        .issue-item {
            background: var(--light);
            border-left: 4px solid var(--primary);
            padding: 1rem;
            margin: 0.5rem 0;
            border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s;
        }
        
        .issue-item:hover {
            transform: translateX(5px);
            box-shadow: var(--shadow);
        }
        
        .issue-critical {
            border-left-color: var(--danger);
            background: #fff5f5;
        }
        
        .issue-high {
            border-left-color: #fd7e14;
            background: #fff8f0;
        }
        
        .issue-medium {
            border-left-color: var(--warning);
            background: #fffbf0;
        }
        
        .issue-low {
            border-left-color: var(--info);
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
        
        .badge-critical { background: var(--danger); color: white; }
        .badge-high { background: #fd7e14; color: white; }
        .badge-medium { background: var(--warning); color: #333; }
        .badge-low { background: var(--info); color: white; }
        .badge-success { background: var(--success); color: white; }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: var(--radius);
            max-width: 600px;
            width: 100%;
            box-shadow: var(--shadow-lg);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--gray);
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: var(--radius-sm);
            font-size: 0.9rem;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(30, 60, 114, 0.2);
        }
        
        .card {
            background: white;
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            margin-bottom: 1rem;
            border-bottom: 1px solid #eee;
            padding-bottom: 1rem;
        }
        
        .card-title {
            font-size: 1.2rem;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }
        
        .export-options {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        
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
            
            .file-explorer {
                grid-template-columns: 1fr;
                height: auto;
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
            <button class="nav-item" onclick="showTab('security')">🛡️ Security Scan</button>
            <button class="nav-item" onclick="showTab('performance')">⚡ Performance</button>
            <button class="nav-item" onclick="showTab('database')">🗄️ Database</button>
            <button class="nav-item" onclick="showTab('terminal')">💻 Terminal</button>
            <button class="nav-item" onclick="showTab('system')">⚙️ System Info</button>
            <button class="nav-item" onclick="showTab('credentials')">🔑 Credentials</button>
            
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
                        <h4>Files Analyzed</h4>
                        <div class="value" id="total-files">--</div>
                        <div class="label">Total Files</div>
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
                
                <div class="export-options" id="dashboard-export" style="display: none;">
                    <button class="btn btn-info" onclick="exportResults('json')">Export as JSON</button>
                    <button class="btn btn-info" onclick="exportResults('csv')">Export as CSV</button>
                    <button class="btn btn-info" onclick="exportResults('html')">Export as HTML</button>
                </div>
            </div>

            <!-- File Explorer Tab -->
            <div id="files" class="tab-content">
                <h2>📁 File Explorer & Code Analyzer</h2>
                <button class="btn" onclick="loadFileSystem()">🔄 Refresh Files</button>
                
                <div class="file-explorer">
                    <div>
                        <h3>File Tree</h3>
                        <div id="file-tree" class="file-tree">
                            <div class="alert alert-info">Click "Refresh Files" to load the file system.</div>
                        </div>
                    </div>
                    
                    <div>
                        <h3>File Analysis</h3>
                        <div id="file-analyzer" class="file-analyzer">
                            <div class="alert alert-info">Select a file from the tree to analyze it.</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Tab -->
            <div id="security" class="tab-content">
                <h2>🛡️ Security Analysis</h2>
                <div id="security-content">
                    <div class="alert alert-info">Run a full scan to see security analysis results.</div>
                </div>
                
                <div class="export-options" id="security-export" style="display: none;">
                    <button class="btn btn-info" onclick="exportResults('json', 'security')">Export as JSON</button>
                    <button class="btn btn-info" onclick="exportResults('csv', 'security')">Export as CSV</button>
                    <button class="btn btn-info" onclick="exportResults('html', 'security')">Export as HTML</button>
                </div>
            </div>

            <!-- Performance Tab -->
            <div id="performance" class="tab-content">
                <h2>⚡ Performance Analysis</h2>
                <div id="performance-content">
                    <div class="alert alert-info">Run a full scan to see performance analysis results.</div>
                </div>
                
                <div class="export-options" id="performance-export" style="display: none;">
                    <button class="btn btn-info" onclick="exportResults('json', 'performance')">Export as JSON</button>
                    <button class="btn btn-info" onclick="exportResults('csv', 'performance')">Export as CSV</button>
                    <button class="btn btn-info" onclick="exportResults('html', 'performance')">Export as HTML</button>
                </div>
            </div>

            <!-- Database Tab -->
            <div id="database" class="tab-content">
                <h2>🗄️ Database Testing</h2>
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Database Connection</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Host</label>
                        <input type="text" id="db-host" class="form-control" placeholder="localhost">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Database Name</label>
                        <input type="text" id="db-name" class="form-control" placeholder="database_name">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Username</label>
                        <input type="text" id="db-user" class="form-control" placeholder="username">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" id="db-pass" class="form-control" placeholder="password">
                    </div>
                    <button class="btn" onclick="testDatabaseWithCredentials()">🔍 Test Connection</button>
                </div>
                
                <div id="database-content">
                    <div class="alert alert-info">Enter your database credentials and click "Test Connection" to check connectivity.</div>
                </div>
            </div>

            <!-- Terminal Tab -->
            <div id="terminal" class="tab-content">
                <h2>💻 Advanced Terminal</h2>
                <div class="terminal-container">
                    <div class="terminal" id="terminal-output">
                        <div style="color: #ff5e62; font-weight: bold; margin-bottom: 10px;">
                            ╔══════════════════════════════════════════════════════════════╗<br>
                            ║  🎯 PHP Diagnostics Terminal v<?= DIAG_VERSION ?> - Advanced Edition  ║<br>
                            ║  Type 'help' for 50+ available commands                     ║<br>
                            ╚══════════════════════════════════════════════════════════════╝<br>
                        </div>
                        <br>
                        $ <span id="terminal-cursor">_</span>
                    </div>
                    <div class="terminal-input-container">
                        <span class="terminal-prompt">$</span>
                        <input type="text" id="terminal-input" class="terminal-input" placeholder="Enter command (try 'help' for full list)..." onkeypress="handleTerminalInput(event)">
                    </div>
                    <div class="terminal-suggestions">
                        <button class="terminal-suggestion" onclick="insertCommand('help')">help</button>
                        <button class="terminal-suggestion" onclick="insertCommand('scan')">scan</button>
                        <button class="terminal-suggestion" onclick="insertCommand('ls')">ls</button>
                        <button class="terminal-suggestion" onclick="insertCommand('phpinfo')">phpinfo</button>
                        <button class="terminal-suggestion" onclick="insertCommand('git status')">git status</button>
                        <button class="terminal-suggestion" onclick="insertCommand('top')">top</button>
                        <button class="terminal-suggestion" onclick="insertCommand('find')">find</button>
                        <button class="terminal-suggestion" onclick="insertCommand('vulns')">vulns</button>
                        <button class="terminal-suggestion" onclick="insertCommand('benchmark')">benchmark</button>
                        <button class="terminal-suggestion" onclick="insertCommand('clear')">clear</button>
                    </div>
                </div>
            </div>

            <!-- System Info Tab -->
            <div id="system" class="tab-content">
                <h2>⚙️ Comprehensive System Information</h2>
                <button class="btn" onclick="loadSystemInfo()">🔄 Refresh System Info</button>
                <div id="system-content">
                    <div class="alert alert-info">Click "Refresh System Info" to load comprehensive system information.</div>
                </div>
            </div>
            
            <!-- Credentials Tab -->
            <div id="credentials" class="tab-content">
                <h2>🔑 Credentials Management</h2>
                
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Database Credentials</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Database Host</label>
                        <input type="text" id="cred-db-host" class="form-control" placeholder="localhost">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Database Name</label>
                        <input type="text" id="cred-db-name" class="form-control" placeholder="database_name">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Database Username</label>
                        <input type="text" id="cred-db-user" class="form-control" placeholder="username">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Database Password</label>
                        <input type="password" id="cred-db-pass" class="form-control" placeholder="password">
                    </div>
                    <button class="btn" onclick="saveDbCredentials()">💾 Save Database Credentials</button>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">API Credentials</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">API Endpoint</label>
                        <input type="text" id="api-endpoint" class="form-control" placeholder="https://api.example.com">
                    </div>
                    <div class="form-group">
                        <label class="form-label">API Key</label>
                        <input type="password" id="api-key" class="form-control" placeholder="Your API key">
                    </div>
                    <div class="form-group">
                        <label class="form-label">API Secret</label>
                        <input type="password" id="api-secret" class="form-control" placeholder="Your API secret">
                    </div>
                    <button class="btn" onclick="saveApiCredentials()">💾 Save API Credentials</button>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">SMTP Credentials</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">SMTP Server</label>
                        <input type="text" id="smtp-server" class="form-control" placeholder="smtp.example.com">
                    </div>
                    <div class="form-group">
                        <label class="form-label">SMTP Port</label>
                        <input type="number" id="smtp-port" class="form-control" placeholder="587">
                    </div>
                    <div class="form-group">
                        <label class="form-label">SMTP Username</label>
                        <input type="text" id="smtp-user" class="form-control" placeholder="username">
                    </div>
                    <div class="form-group">
                        <label class="form-label">SMTP Password</label>
                        <input type="password" id="smtp-pass" class="form-control" placeholder="password">
                    </div>
                    <button class="btn" onclick="saveSmtpCredentials()">💾 Save SMTP Credentials</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Export Modal -->
    <div id="export-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Export Results</h3>
                <button class="modal-close" onclick="closeExportModal()">&times;</button>
            </div>
            <div
