<?php
/**
 * Queue Worker
 * Processes jobs from the queue
 *
 * Can be run via:
 * - CLI: php worker.php
 * - Cron: * * * * * php /path/to/worker.php
 * - Daemon: While loop with sleep
 */

class Worker
{
    private QueueManager $queue;
    private string $queueName;
    private bool $stopRequested = false;

    public function __construct(QueueManager $queue, string $queueName = 'default')
    {
        $this->queue = $queue;
        $this->queueName = $queueName;

        // Handle graceful shutdown
        if (function_exists('pcntl_signal')) {
            pcntl_signal(SIGTERM, [$this, 'stop']);
            pcntl_signal(SIGINT, [$this, 'stop']);
        }
    }

    /**
     * Process jobs continuously (daemon mode)
     *
     * @param int $sleep Sleep time between jobs in seconds (default 5)
     */
    public function run(int $sleep = 5): void
    {
        echo "Worker started for queue: {$this->queueName}\n";

        while (!$this->stopRequested) {
            $job = $this->queue->getNextJob($this->queueName);

            if ($job) {
                $this->processJob($job);
            } else {
                // No jobs available, sleep
                sleep($sleep);
            }

            // Check for signals (graceful shutdown)
            if (function_exists('pcntl_signal_dispatch')) {
                pcntl_signal_dispatch();
            }
        }

        echo "Worker stopped gracefully\n";
    }

    /**
     * Process single job (useful for cron)
     */
    public function runOnce(): bool
    {
        $job = $this->queue->getNextJob($this->queueName);

        if ($job) {
            return $this->processJob($job);
        }

        return false;
    }

    /**
     * Process a job
     */
    private function processJob(Job $job): bool
    {
        $jobName = $job->getName();
        echo "[" . date('Y-m-d H:i:s') . "] Processing: {$jobName}\n";

        try {
            $success = $job->handle();

            if ($success) {
                $this->queue->markCompleted($job);
                echo "[" . date('Y-m-d H:i:s') . "] Completed: {$jobName}\n";
                return true;
            } else {
                $this->handleFailure($job, "Job returned false");
                return false;
            }
        } catch (Exception $e) {
            $this->handleFailure($job, $e->getMessage());
            return false;
        }
    }

    /**
     * Handle job failure
     */
    private function handleFailure(Job $job, string $reason): void
    {
        $jobName = $job->getName();
        $job->incrementAttempts();

        echo "[" . date('Y-m-d H:i:s') . "] Failed: {$jobName} - {$reason}\n";

        if ($job->shouldRetry()) {
            echo "[" . date('Y-m-d H:i:s') . "] Will retry: {$jobName} (Attempt {$job->getAttempts()})\n";
        } else {
            echo "[" . date('Y-m-d H:i:s') . "] Max attempts reached: {$jobName}\n";
        }

        $this->queue->markFailed($job, $reason);
    }

    /**
     * Stop worker (graceful shutdown)
     */
    public function stop(): void
    {
        $this->stopRequested = true;
        echo "\nShutdown signal received, stopping after current job...\n";
    }

    /**
     * Get queue statistics
     */
    public function getStats(): array
    {
        return $this->queue->getStats($this->queueName);
    }
}
