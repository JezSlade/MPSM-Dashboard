<?php
/**
 * Queue Manager
 * Manages job queue operations (dispatch, process, status)
 *
 * Uses database table for persistence:
 * - Jobs survive server restarts
 * - Easy to monitor and debug
 * - No external dependencies (Redis, RabbitMQ)
 */

class QueueManager
{
    private PDO $pdo;
    private string $table = 'mpsm_jobs';

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->ensureTableExists();
    }

    /**
     * Dispatch job to queue
     *
     * @param Job $job Job instance
     * @param int $delay Delay in seconds before processing (default 0)
     * @return int Job ID
     */
    public function dispatch(Job $job, int $delay = 0): int
    {
        $runAt = date('Y-m-d H:i:s', time() + $delay);

        $stmt = $this->pdo->prepare("
            INSERT INTO {$this->table}
            (queue, job_class, payload, status, attempts, run_at, created_at)
            VALUES (?, ?, ?, 'pending', 0, ?, NOW())
        ");

        $stmt->execute([
            $job->getQueue(),
            get_class($job),
            json_encode($job->getPayload()),
            $runAt,
        ]);

        $jobId = (int) $this->pdo->lastInsertId();
        $job->setId($jobId);

        return $jobId;
    }

    /**
     * Get next pending job from queue
     *
     * @param string $queue Queue name (default: default)
     * @return Job|null Next job or null if queue is empty
     */
    public function getNextJob(string $queue = 'default'): ?Job
    {
        // Lock row for update to prevent race conditions
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM {$this->table}
            WHERE queue = ?
            AND status = 'pending'
            AND run_at <= NOW()
            ORDER BY id ASC
            LIMIT 1
            FOR UPDATE
        ");

        $this->pdo->beginTransaction();

        try {
            $stmt->execute([$queue]);
            $row = $stmt->fetch();

            if (!$row) {
                $this->pdo->rollBack();
                return null;
            }

            // Mark as processing
            $updateStmt = $this->pdo->prepare("
                UPDATE {$this->table}
                SET status = 'processing',
                    started_at = NOW()
                WHERE id = ?
            ");
            $updateStmt->execute([$row['id']]);

            $this->pdo->commit();

            // Reconstruct job
            return $this->reconstructJob($row);
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("QueueManager::getNextJob error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Mark job as completed
     */
    public function markCompleted(Job $job): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE {$this->table}
            SET status = 'completed',
                completed_at = NOW()
            WHERE id = ?
        ");

        $stmt->execute([$job->getId()]);
    }

    /**
     * Mark job as failed
     */
    public function markFailed(Job $job, string $reason): void
    {
        $shouldRetry = $job->shouldRetry();

        $stmt = $this->pdo->prepare("
            UPDATE {$this->table}
            SET status = ?,
                attempts = attempts + 1,
                failure_reason = ?,
                completed_at = NOW()
            WHERE id = ?
        ");

        $status = $shouldRetry ? 'pending' : 'failed';
        $stmt->execute([$status, $reason, $job->getId()]);
    }

    /**
     * Get job status
     */
    public function getStatus(int $jobId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM {$this->table}
            WHERE id = ?
        ");

        $stmt->execute([$jobId]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Get queue statistics
     */
    public function getStats(string $queue = 'default'): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                COUNT(*) as total_jobs,
                COALESCE(SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END), 0) as pending_jobs,
                COALESCE(SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END), 0) as processing_jobs,
                COALESCE(SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END), 0) as completed_jobs,
                COALESCE(SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END), 0) as failed_jobs,
                AVG(TIMESTAMPDIFF(SECOND, started_at, completed_at)) as avg_duration_seconds
            FROM {$this->table}
            WHERE queue = ?
        ");

        $stmt->execute([$queue]);
        $stats = $stmt->fetch() ?: [];
        if (!$stats) {
            return [];
        }

        $stats['total_jobs'] = (int)($stats['total_jobs'] ?? 0);
        $stats['pending_jobs'] = (int)($stats['pending_jobs'] ?? 0);
        $stats['processing_jobs'] = (int)($stats['processing_jobs'] ?? 0);
        $stats['completed_jobs'] = (int)($stats['completed_jobs'] ?? 0);
        $stats['failed_jobs'] = (int)($stats['failed_jobs'] ?? 0);
        $stats['avg_duration_seconds'] = isset($stats['avg_duration_seconds'])
            ? (float)$stats['avg_duration_seconds']
            : null;

        return $stats;
    }

    /**
     * Clean up old completed/failed jobs
     *
     * @param int $olderThanDays Remove jobs older than X days (default 7)
     * @return int Number of jobs removed
     */
    public function cleanup(int $olderThanDays = 7): int
    {
        $stmt = $this->pdo->prepare("
            DELETE FROM {$this->table}
            WHERE status IN ('completed', 'failed')
            AND completed_at < DATE_SUB(NOW(), INTERVAL ? DAY)
        ");

        $stmt->execute([$olderThanDays]);
        return $stmt->rowCount();
    }

    /**
     * Retry failed jobs
     *
     * @param string $queue Queue name
     * @return int Number of jobs retried
     */
    public function retryFailed(string $queue = 'default'): int
    {
        $stmt = $this->pdo->prepare("
            UPDATE {$this->table}
            SET status = 'pending',
                run_at = NOW(),
                failure_reason = NULL
            WHERE queue = ?
            AND status = 'failed'
            AND attempts < 3
        ");

        $stmt->execute([$queue]);
        return $stmt->rowCount();
    }

    /**
     * Reconstruct job from database row
     */
    private function reconstructJob(array $row): Job
    {
        $jobClass = $row['job_class'];
        $payload = json_decode($row['payload'], true) ?? [];

        // Create job instance from class name and payload
        $job = new $jobClass(...array_values($payload));
        $job->setId($row['id']);

        return $job;
    }

    /**
     * Ensure jobs table exists
     */
    private function ensureTableExists(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS {$this->table} (
                id INT AUTO_INCREMENT PRIMARY KEY,
                queue VARCHAR(50) NOT NULL DEFAULT 'default',
                job_class VARCHAR(255) NOT NULL,
                payload JSON,
                status ENUM('pending', 'processing', 'completed', 'failed') NOT NULL DEFAULT 'pending',
                attempts INT NOT NULL DEFAULT 0,
                failure_reason TEXT NULL,
                run_at DATETIME NOT NULL,
                created_at DATETIME NOT NULL,
                started_at DATETIME NULL,
                completed_at DATETIME NULL,
                INDEX idx_queue_status (queue, status),
                INDEX idx_run_at (run_at),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }
}
