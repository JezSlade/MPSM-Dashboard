<?php
/**
 * Base Controller
 * Abstract base for all API controllers
 *
 * Provides common functionality:
 * - JSON response handling
 * - Input validation
 * - Error handling
 * - HTTP status codes
 */

abstract class BaseController
{
    protected array $input = [];
    protected array $query = [];
    protected array $headers = [];

    public function __construct()
    {
        $this->parseInput();
        $this->parseQuery();
        $this->parseHeaders();
    }

    /**
     * Parse request input (JSON or form data)
     */
    protected function parseInput(): void
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (strpos($contentType, 'application/json') !== false) {
            $rawInput = file_get_contents('php://input');
            $this->input = json_decode($rawInput, true) ?? [];
        } else {
            $this->input = $_POST;
        }
    }

    /**
     * Parse query parameters
     */
    protected function parseQuery(): void
    {
        $this->query = $_GET;
    }

    /**
     * Parse request headers
     */
    protected function parseHeaders(): void
    {
        $this->headers = getallheaders() ?: [];
    }

    /**
     * Get input value with optional default
     */
    protected function input(string $key, $default = null)
    {
        return $this->input[$key] ?? $default;
    }

    /**
     * Get query parameter with optional default
     */
    protected function query(string $key, $default = null)
    {
        return $this->query[$key] ?? $default;
    }

    /**
     * Get header value with optional default
     */
    protected function header(string $key, $default = null)
    {
        return $this->headers[$key] ?? $default;
    }

    /**
     * Validate required input fields
     *
     * @throws ValidationException
     */
    protected function validate(array $rules): void
    {
        foreach ($rules as $field => $rule) {
            $value = $this->input[$field] ?? null;

            // Required validation
            if (isset($rule['required']) && $rule['required'] && empty($value)) {
                throw new ValidationException("Field '{$field}' is required");
            }

            // Type validation
            if (!empty($value) && isset($rule['type'])) {
                $this->validateType($field, $value, $rule['type']);
            }

            // Min/max validation for strings
            if (!empty($value) && is_string($value)) {
                if (isset($rule['min']) && strlen($value) < $rule['min']) {
                    throw new ValidationException("Field '{$field}' must be at least {$rule['min']} characters");
                }
                if (isset($rule['max']) && strlen($value) > $rule['max']) {
                    throw new ValidationException("Field '{$field}' must not exceed {$rule['max']} characters");
                }
            }

            // Min/max validation for numbers
            if (!empty($value) && is_numeric($value)) {
                if (isset($rule['min']) && $value < $rule['min']) {
                    throw new ValidationException("Field '{$field}' must be at least {$rule['min']}");
                }
                if (isset($rule['max']) && $value > $rule['max']) {
                    throw new ValidationException("Field '{$field}' must not exceed {$rule['max']}");
                }
            }

            // Pattern validation
            if (!empty($value) && isset($rule['pattern'])) {
                if (!preg_match($rule['pattern'], $value)) {
                    throw new ValidationException("Field '{$field}' has invalid format");
                }
            }
        }
    }

    /**
     * Validate field type
     */
    private function validateType(string $field, $value, string $type): void
    {
        $valid = false;

        switch ($type) {
            case 'string':
                $valid = is_string($value);
                break;
            case 'int':
            case 'integer':
                $valid = is_int($value) || ctype_digit($value);
                break;
            case 'float':
            case 'double':
                $valid = is_float($value) || is_numeric($value);
                break;
            case 'bool':
            case 'boolean':
                $valid = is_bool($value) || in_array($value, ['true', 'false', '1', '0', 1, 0], true);
                break;
            case 'array':
                $valid = is_array($value);
                break;
            case 'email':
                $valid = filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
                break;
            case 'url':
                $valid = filter_var($value, FILTER_VALIDATE_URL) !== false;
                break;
        }

        if (!$valid) {
            throw new ValidationException("Field '{$field}' must be of type {$type}");
        }
    }

    /**
     * Send JSON response
     */
    protected function json($data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Send success response
     */
    protected function success($data = [], string $message = 'Success', int $statusCode = 200): void
    {
        $this->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    /**
     * Send error response
     */
    protected function error(string $message, int $statusCode = 500, ?string $errorCode = null, ?array $context = null): void
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errorCode) {
            $response['error_code'] = $errorCode;
        }

        if ($context) {
            $response['context'] = $context;
        }

        $this->json($response, $statusCode);
    }

    /**
     * Send validation error response
     */
    protected function validationError(string $message): void
    {
        $this->error($message, 400, 'VALIDATION_ERROR');
    }

    /**
     * Send not found response
     */
    protected function notFound(string $message = 'Resource not found'): void
    {
        $this->error($message, 404, 'NOT_FOUND');
    }

    /**
     * Send unauthorized response
     */
    protected function unauthorized(string $message = 'Unauthorized'): void
    {
        $this->error($message, 401, 'UNAUTHORIZED');
    }

    /**
     * Send forbidden response
     */
    protected function forbidden(string $message = 'Forbidden'): void
    {
        $this->error($message, 403, 'FORBIDDEN');
    }
}

/**
 * Validation Exception
 * Thrown when input validation fails
 */
class ValidationException extends Exception
{
}
