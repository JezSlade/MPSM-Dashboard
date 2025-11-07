<?php
/**
 * Authentication Middleware
 * Ensures user is authenticated before accessing protected routes
 *
 * Checks for:
 * - Valid session
 * - Authenticated user
 */

class AuthMiddleware
{
    /**
     * Execute middleware
     */
    public static function handle(): void
    {
        // Start session if not started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Check if user is authenticated
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['authenticated'])) {
            self::unauthorized();
        }

        // Validate session hasn't expired
        $sessionLifetime = config('session.lifetime', 3600);
        $lastActivity = $_SESSION['last_activity'] ?? 0;

        if (time() - $lastActivity > $sessionLifetime) {
            session_destroy();
            self::unauthorized('Session expired');
        }

        // Update last activity timestamp
        $_SESSION['last_activity'] = time();
    }

    /**
     * Send unauthorized response
     */
    private static function unauthorized(string $message = 'Unauthorized'): void
    {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $message,
            'error_code' => 'UNAUTHORIZED',
        ], JSON_PRETTY_PRINT);
        exit;
    }
}
