<?php
/**
 * Engine Interface
 * Contract for MPS API Engine (vendor API integration)
 *
 * Abstracts the vendor API connection, allowing us to:
 * - Mock the engine for testing
 * - Swap implementations without changing calling code
 * - Enforce consistent error handling
 */

interface EngineInterface
{
    /**
     * Execute an action against the MPS Monitor API
     *
     * @param string $action Action name (e.g., 'Device/List')
     * @param array $params Action parameters
     * @return array Response with 'success', 'data', 'meta' keys
     * @throws EngineException On vendor API failure
     */
    public function query(string $action, array $params = []): array;

    /**
     * Check if the vendor API is reachable
     *
     * @return bool Health status
     */
    public function healthCheck(): bool;

    /**
     * Get list of available actions
     *
     * @return array Action names
     */
    public function getAvailableActions(): array;

    /**
     * Get OAuth token status
     *
     * @return array Token info (valid, expires_at, etc.)
     */
    public function getTokenStatus(): array;
}

/**
 * Engine Exception
 * Thrown when vendor API operations fail
 */
class EngineException extends RuntimeException
{
    private ?string $errorCode;
    private ?array $context;

    public function __construct(
        string $message,
        ?string $errorCode = null,
        ?array $context = null,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->errorCode = $errorCode;
        $this->context = $context;
    }

    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    public function getContext(): ?array
    {
        return $this->context;
    }
}
