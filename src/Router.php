<?php
/**
 * Simple Router
 * Handles API routing with middleware support
 *
 * Features:
 * - RESTful routing (GET, POST, PUT, DELETE)
 * - Route parameters (/devices/:serial)
 * - Middleware pipeline
 * - 404 handling
 */

class Router
{
    private array $routes = [];
    private array $middleware = [];

    /**
     * Add GET route
     */
    public function get(string $path, callable $handler): self
    {
        return $this->addRoute('GET', $path, $handler);
    }

    /**
     * Add POST route
     */
    public function post(string $path, callable $handler): self
    {
        return $this->addRoute('POST', $path, $handler);
    }

    /**
     * Add PUT route
     */
    public function put(string $path, callable $handler): self
    {
        return $this->addRoute('PUT', $path, $handler);
    }

    /**
     * Add DELETE route
     */
    public function delete(string $path, callable $handler): self
    {
        return $this->addRoute('DELETE', $path, $handler);
    }

    /**
     * Add route for any method
     */
    private function addRoute(string $method, string $path, callable $handler): self
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
        ];

        return $this;
    }

    /**
     * Add global middleware
     */
    public function middleware(callable $middleware): self
    {
        $this->middleware[] = $middleware;
        return $this;
    }

    /**
     * Dispatch request to matching route
     */
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Remove script name from URI if present
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        if (strpos($uri, $scriptName) === 0) {
            $uri = substr($uri, strlen($scriptName));
        }

        // The app is usually mounted under /cms, while routes are registered
        // from the API root (/api/v1/*).
        $scriptDir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
        $apiPrefix = '/api/v1';
        if (
            $scriptDir !== ''
            && $scriptDir !== '.'
            && substr($scriptDir, -strlen($apiPrefix)) === $apiPrefix
            && strpos($uri, $scriptDir) === 0
        ) {
            $uri = $apiPrefix . substr($uri, strlen($scriptDir));
        }

        // Remove leading slash
        $uri = '/' . trim($uri, '/');

        // Find matching route
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $params = $this->matchRoute($route['path'], $uri);
            if ($params !== null) {
                // Execute middleware pipeline
                foreach ($this->middleware as $middleware) {
                    $middleware();
                }

                // Execute route handler with parameters
                call_user_func_array($route['handler'], $params);
                return;
            }
        }

        // No route matched - 404
        $this->notFound($uri);
    }

    /**
     * Match route pattern against URI
     *
     * @return array|null Route parameters or null if no match
     */
    private function matchRoute(string $pattern, string $uri): ?array
    {
        // Convert route pattern to regex
        // Example: /devices/:serial -> /devices/([^/]+)
        $regex = preg_replace('/:[a-zA-Z0-9_]+/', '([^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        if (!preg_match($regex, $uri, $matches)) {
            return null;
        }

        // Remove full match, keep only captured groups
        array_shift($matches);

        return $matches;
    }

    /**
     * Send 404 response
     */
    private function notFound(string $uri): void
    {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Route not found',
            'error_code' => 'NOT_FOUND',
            'uri' => $uri,
        ], JSON_PRETTY_PRINT);
        exit;
    }
}
