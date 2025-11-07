<?php
/**
 * Job Base Class
 * Abstract base for all background jobs
 *
 * Jobs are tasks that run in the background:
 * - Cache refresh
 * - Data synchronization
 * - Email sending
 * - Report generation
 */

abstract class Job
{
    protected int $id;
    protected string $queue = 'default';
    protected int $attempts = 0;
    protected int $maxAttempts = 3;
    protected ?string $failureReason = null;

    /**
     * Execute the job
     * Must be implemented by child classes
     *
     * @return bool Success status
     */
    abstract public function handle(): bool;

    /**
     * Get job name (used for logging)
     */
    public function getName(): string
    {
        return static::class;
    }

    /**
     * Get job payload (serializable data)
     */
    public function getPayload(): array
    {
        return [];
    }

    /**
     * Set job ID (assigned when queued)
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * Get job ID
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get queue name
     */
    public function getQueue(): string
    {
        return $this->queue;
    }

    /**
     * Set queue name
     */
    public function setQueue(string $queue): void
    {
        $this->queue = $queue;
    }

    /**
     * Increment attempts
     */
    public function incrementAttempts(): void
    {
        $this->attempts++;
    }

    /**
     * Get attempts
     */
    public function getAttempts(): int
    {
        return $this->attempts;
    }

    /**
     * Check if should retry
     */
    public function shouldRetry(): bool
    {
        return $this->attempts < $this->maxAttempts;
    }

    /**
     * Mark job as failed
     */
    public function markFailed(string $reason): void
    {
        $this->failureReason = $reason;
    }

    /**
     * Get failure reason
     */
    public function getFailureReason(): ?string
    {
        return $this->failureReason;
    }

    /**
     * Called before job execution
     */
    protected function before(): void
    {
        // Override in child classes if needed
    }

    /**
     * Called after successful execution
     */
    protected function after(): void
    {
        // Override in child classes if needed
    }

    /**
     * Called when job fails
     */
    protected function failed(Exception $e): void
    {
        error_log("Job {$this->getName()} failed: " . $e->getMessage());
    }
}
