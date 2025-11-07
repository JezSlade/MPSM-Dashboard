#!/usr/bin/env php
<?php
/**
 * Queue Worker CLI
 * Process background jobs from the queue
 *
 * Usage:
 *   php worker.php                    # Process default queue once
 *   php worker.php --daemon           # Run continuously (daemon mode)
 *   php worker.php --queue=cache      # Process specific queue
 *   php worker.php --daemon --sleep=3 # Daemon with custom sleep time
 *   php worker.php --stats            # Show queue statistics
 *   php worker.php --cleanup          # Clean up old jobs
 *
 * Cron setup (run every minute):
 *   * * * * * cd /path/to/project && php worker.php >> /var/log/worker.log 2>&1
 */

require_once __DIR__ . '/bootstrap.php';

// Parse command line arguments
$options = getopt('', [
    'daemon',
    'queue:',
    'sleep:',
    'stats',
    'cleanup',
    'retry-failed',
]);

$queue = $options['queue'] ?? 'default';
$sleep = (int) ($options['sleep'] ?? 5);

// Get queue manager
$queueManager = new QueueManager(app(PDO::class));

// Show statistics
if (isset($options['stats'])) {
    $stats = $queueManager->getStats($queue);

    echo "Queue Statistics ({$queue}):\n";
    echo "  Total Jobs:      {$stats['total_jobs']}\n";
    echo "  Pending:         {$stats['pending_jobs']}\n";
    echo "  Processing:      {$stats['processing_jobs']}\n";
    echo "  Completed:       {$stats['completed_jobs']}\n";
    echo "  Failed:          {$stats['failed_jobs']}\n";
    echo "  Avg Duration:    " . round($stats['avg_duration_seconds'] ?? 0, 2) . "s\n";
    exit(0);
}

// Clean up old jobs
if (isset($options['cleanup'])) {
    echo "Cleaning up old completed/failed jobs...\n";
    $removed = $queueManager->cleanup(7);
    echo "Removed {$removed} old jobs\n";
    exit(0);
}

// Retry failed jobs
if (isset($options['retry-failed'])) {
    echo "Retrying failed jobs in queue: {$queue}...\n";
    $retried = $queueManager->retryFailed($queue);
    echo "Retried {$retried} failed jobs\n";
    exit(0);
}

// Create worker
$worker = new Worker($queueManager, $queue);

// Run worker
if (isset($options['daemon'])) {
    echo "Starting worker in daemon mode...\n";
    echo "Queue: {$queue}\n";
    echo "Sleep: {$sleep}s\n";
    echo "Press Ctrl+C to stop\n\n";

    $worker->run($sleep);
} else {
    // Run once (good for cron)
    $processed = $worker->runOnce();

    if ($processed) {
        echo "Processed 1 job from queue: {$queue}\n";
    } else {
        echo "No jobs available in queue: {$queue}\n";
    }
}
