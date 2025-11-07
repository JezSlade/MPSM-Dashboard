<?php
/**
 * CORS Middleware
 * Handles Cross-Origin Resource Sharing headers
 *
 * Allows frontend to communicate with API from different origins
 */

class CorsMiddleware
{
    /**
     * Execute middleware
     */
    public static function handle(): void
    {
        $allowedOrigins = config('api.cors.allowed_origins', ['*']);
        $allowedMethods = config('api.cors.allowed_methods', ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS']);
        $allowedHeaders = config('api.cors.allowed_headers', ['Content-Type', 'Authorization', 'X-Requested-With']);

        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        // Check if origin is allowed
        if (in_array('*', $allowedOrigins) || in_array($origin, $allowedOrigins)) {
            header('Access-Control-Allow-Origin: ' . ($origin ?: '*'));
        }

        header('Access-Control-Allow-Methods: ' . implode(', ', $allowedMethods));
        header('Access-Control-Allow-Headers: ' . implode(', ', $allowedHeaders));
        header('Access-Control-Max-Age: 86400'); // Cache preflight for 24 hours

        // Handle preflight OPTIONS request
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }
}
