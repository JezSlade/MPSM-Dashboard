#!/usr/bin/env php
<?php
/**
 * CRON Router - Centralized Task Scheduler
 *
 * Single entry point for all scheduled tasks. Configure tasks below, then set up
 * ONE CRON job in cPanel that calls this script every minute:
 *
 * * * * * * /usr/bin/php /home/resolut7/public_html/cms/cron-router.php
 *
 * This router handles:
 * - Task scheduling based on intervals (every minute, hourly, daily, etc.)
 * - Execution tracking to prevent overlapping runs
 * - Logging of all task execution
 * - Easy enable/disable of individual tasks
 * - No need to modify cPanel for task changes
 */

// SHOW ALL ERRORS (development/debugging mode)
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

// Detect execution context - check if HTTP request (not CLI/CRON)
$isHTTP = !empty($_SERVER['REQUEST_METHOD']);

if ($isHTTP) {
    // Allow web access for testing/monitoring with secret parameter
    if (!isset($_GET['secret']) || $_GET['secret'] !== 'cron-router-access-2025') {
        http_response_code(403);
        die('Access denied. This script should be run via CRON.');
    }
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

// Configuration
$lockDir = __DIR__ . '/locks';
$logDir = __DIR__ . '/logs';
$logFile = $logDir . '/cron-router-' . date('Y-m-d') . '.log';

// Ensure directories exist
if (!is_dir($lockDir)) mkdir($lockDir, 0755, true);
if (!is_dir($logDir)) mkdir($logDir, 0755, true);

/**
 * Log message to daily log file
 */
function logMessage($message, $level = 'INFO') {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $line = "[{$timestamp}] [{$level}] {$message}\n";
    file_put_contents($logFile, $line, FILE_APPEND);

    // Also output to console if not HTTP request
    if (empty($_SERVER['REQUEST_METHOD'])) {
        echo $line;
    }
}

/**
 * Check if a task is currently running
 */
function isTaskRunning($taskName) {
    global $lockDir;
    $lockFile = $lockDir . '/' . $taskName . '.lock';

    if (!file_exists($lockFile)) {
        return false;
    }

    // Check if lock is stale (older than 1 hour)
    $lockAge = time() - filemtime($lockFile);
    if ($lockAge > 3600) {
        logMessage("Removing stale lock for {$taskName} (age: {$lockAge}s)", 'WARN');
        unlink($lockFile);
        return false;
    }

    return true;
}

/**
 * Create lock file for task
 */
function createTaskLock($taskName) {
    global $lockDir;
    $lockFile = $lockDir . '/' . $taskName . '.lock';
    file_put_contents($lockFile, getmypid() . "\n" . date('Y-m-d H:i:s'));
}

/**
 * Remove lock file for task
 */
function removeTaskLock($taskName) {
    global $lockDir;
    $lockFile = $lockDir . '/' . $taskName . '.lock';
    if (file_exists($lockFile)) {
        unlink($lockFile);
    }
}

/**
 * Check if task should run based on schedule
 *
 * @param string $interval One of: 'every_minute', 'every_5_minutes', 'every_15_minutes',
 *                         'hourly', 'every_6_hours', 'daily', 'weekly'
 * @return bool
 */
function shouldTaskRun($taskName, $interval) {
    $lastRunFile = __DIR__ . '/locks/' . $taskName . '.lastrun';
    $lastRun = file_exists($lastRunFile) ? (int)file_get_contents($lastRunFile) : 0;
    $now = time();

    switch ($interval) {
        case 'every_minute':
            return true; // Always run

        case 'every_5_minutes':
            return ($now - $lastRun) >= 300;

        case 'every_15_minutes':
            return ($now - $lastRun) >= 900;

        case 'hourly':
            return ($now - $lastRun) >= 3600;

        case 'every_6_hours':
            return ($now - $lastRun) >= 21600;

        case 'daily':
            // Run once per day at configured hour (default: midnight)
            $lastRunDate = date('Y-m-d', $lastRun);
            $todayDate = date('Y-m-d', $now);
            $currentHour = (int)date('H', $now);
            return ($lastRunDate !== $todayDate && $currentHour >= 0);

        case 'weekly':
            // Run once per week on configured day (default: Sunday at midnight)
            $lastRunWeek = date('Y-W', $lastRun);
            $thisWeek = date('Y-W', $now);
            $dayOfWeek = (int)date('w', $now);
            return ($lastRunWeek !== $thisWeek && $dayOfWeek === 0);

        default:
            return false;
    }
}

/**
 * Mark task as run
 */
function markTaskRun($taskName) {
    $lastRunFile = __DIR__ . '/locks/' . $taskName . '.lastrun';
    file_put_contents($lastRunFile, time());
}

/**
 * Execute a task
 */
function executeTask($task) {
    $taskName = $task['name'];
    $startTime = microtime(true);

    logMessage("Starting task: {$taskName}");

    try {
        // Create lock
        createTaskLock($taskName);

        // Execute task
        $result = call_user_func($task['callback']);

        $duration = round(microtime(true) - $startTime, 2);
        logMessage("Task completed: {$taskName} (duration: {$duration}s, result: " . json_encode($result) . ")");

        // Mark as run
        markTaskRun($taskName);

        return $result;

    } catch (Exception $e) {
        $duration = round(microtime(true) - $startTime, 2);
        logMessage("Task failed: {$taskName} (duration: {$duration}s, error: {$e->getMessage()})", 'ERROR');
        return false;

    } finally {
        // Always remove lock
        removeTaskLock($taskName);
    }
}

// ========================================================================
// TASK DEFINITIONS
// ========================================================================

$tasks = [

    // Chunked Cache Refresh - Process one chunk every minute (CLI execution, no timeout)
    [
        'name' => 'cache-refresh-chunked',
        'enabled' => true,
        'interval' => 'every_minute',
        'description' => 'Process one chunk of cache refresh via PHP CLI (no HTTP timeout)',
        'callback' => function() {
            // Execute chunked processor via PHP CLI (avoids HTTP timeout completely)
            $scriptPath = __DIR__ . '/api/refresh-cache-chunked.php';
            $command = "/usr/bin/php {$scriptPath} process 2>&1";

            $output = shell_exec($command);
            $data = json_decode($output, true);

            if (!$data || !isset($data['success'])) {
                return ['status' => 'error', 'message' => 'CLI execution failed', 'output' => substr($output, 0, 200)];
            }

            if (!$data['success']) {
                return ['status' => 'error', 'message' => $data['error'] ?? 'Unknown error'];
            }

            return [
                'status' => 'success',
                'chunk_duration' => $data['chunk_duration'] ?? null,
                'state' => $data['state']['status'] ?? 'unknown',
                'devices_cached' => $data['state']['devices_cached'] ?? 0,
                'drilldowns_cached' => $data['state']['drilldowns_cached'] ?? 0,
                'continue' => $data['continue'] ?? false
            ];
        }
    ],

    // Daily Full Cache Refresh - Start a new refresh cycle at midnight
    [
        'name' => 'cache-refresh-daily-init',
        'enabled' => false, // Disabled by default - enable if you want automatic daily refreshes
        'interval' => 'daily',
        'description' => 'Initialize a new full cache refresh cycle daily via PHP CLI',
        'callback' => function() {
            $scriptPath = __DIR__ . '/api/refresh-cache-chunked.php';
            $command = "/usr/bin/php {$scriptPath} start 2>&1";

            $output = shell_exec($command);
            $data = json_decode($output, true);

            return [
                'status' => ($data && $data['success']) ? 'success' : 'error',
                'message' => $data['message'] ?? 'CLI execution failed'
            ];
        }
    ],

    // Panel Message Processing (example - add if needed)
    [
        'name' => 'process-panel-messages',
        'enabled' => false,
        'interval' => 'every_15_minutes',
        'description' => 'Process queued panel messages',
        'callback' => function() {
            // Add logic here if you need to process panel messages in batches
            return ['status' => 'success', 'processed' => 0];
        }
    ],

    // Cache Cleanup - Remove old cache entries
    [
        'name' => 'cache-cleanup',
        'enabled' => false,
        'interval' => 'weekly',
        'description' => 'Clean up old cache entries and logs',
        'callback' => function() {
            // Example: Delete logs older than 30 days
            $logDir = __DIR__ . '/logs';
            $deleted = 0;

            if (is_dir($logDir)) {
                $files = glob($logDir . '/*.log');
                foreach ($files as $file) {
                    if (time() - filemtime($file) > 30 * 86400) {
                        unlink($file);
                        $deleted++;
                    }
                }
            }

            return ['status' => 'success', 'deleted_logs' => $deleted];
        }
    ],

];

// ========================================================================
// MAIN EXECUTION LOOP
// ========================================================================

logMessage("CRON Router started");

$tasksRun = 0;
$tasksSkipped = 0;
$tasksFailed = 0;

foreach ($tasks as $task) {
    // Skip disabled tasks
    if (!$task['enabled']) {
        continue;
    }

    $taskName = $task['name'];

    // Check if task should run based on schedule
    if (!shouldTaskRun($taskName, $task['interval'])) {
        $tasksSkipped++;
        continue;
    }

    // Check if task is already running
    if (isTaskRunning($taskName)) {
        logMessage("Task already running: {$taskName}", 'WARN');
        $tasksSkipped++;
        continue;
    }

    // Execute task
    $result = executeTask($task);

    if ($result === false) {
        $tasksFailed++;
    } else {
        $tasksRun++;
    }
}

logMessage("CRON Router finished (ran: {$tasksRun}, skipped: {$tasksSkipped}, failed: {$tasksFailed})");

// If running via web, return JSON response
if ($isHTTP) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'tasks_run' => $tasksRun,
        'tasks_skipped' => $tasksSkipped,
        'tasks_failed' => $tasksFailed,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
}

exit($tasksFailed > 0 ? 1 : 0);
