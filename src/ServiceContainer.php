<?php
/**
 * Service Container
 * Simple dependency injection container for managing application services
 *
 * Usage:
 *   $container = ServiceContainer::getInstance();
 *   $container->register(PDO::class, function() { return new PDO(...); });
 *   $pdo = $container->get(PDO::class);
 *
 * Or use the helper:
 *   app(PDO::class)
 */

class ServiceContainer
{
    private static ?self $instance = null;
    private array $services = [];
    private array $singletons = [];
    private array $resolved = [];

    private function __construct()
    {
        // Private constructor for singleton
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Register a service in the container
     *
     * @param string $abstract The interface or class name
     * @param callable $factory Factory function that creates the service
     * @param bool $singleton Whether to cache the instance (default: true)
     */
    public function register(string $abstract, callable $factory, bool $singleton = true): void
    {
        $this->services[$abstract] = [
            'factory' => $factory,
            'singleton' => $singleton,
        ];
    }

    /**
     * Resolve a service from the container
     *
     * @param string $abstract The interface or class name
     * @return mixed The resolved service instance
     * @throws RuntimeException If service not found
     */
    public function get(string $abstract)
    {
        // Return cached singleton if exists
        if (isset($this->resolved[$abstract])) {
            return $this->resolved[$abstract];
        }

        // Check if service is registered
        if (!isset($this->services[$abstract])) {
            throw new RuntimeException("Service not registered: {$abstract}");
        }

        $service = $this->services[$abstract];
        $instance = ($service['factory'])($this);

        // Cache if singleton
        if ($service['singleton']) {
            $this->resolved[$abstract] = $instance;
        }

        return $instance;
    }

    /**
     * Check if a service is registered
     *
     * @param string $abstract The interface or class name
     * @return bool
     */
    public function has(string $abstract): bool
    {
        return isset($this->services[$abstract]);
    }

    /**
     * Remove a service from the container
     *
     * @param string $abstract The interface or class name
     */
    public function forget(string $abstract): void
    {
        unset($this->services[$abstract], $this->resolved[$abstract]);
    }

    /**
     * Clear all resolved singletons (useful for testing)
     */
    public function flush(): void
    {
        $this->resolved = [];
    }

    /**
     * Get all registered service names
     *
     * @return array
     */
    public function getRegistered(): array
    {
        return array_keys($this->services);
    }
}

/**
 * Global helper function for accessing the service container
 *
 * @param string|null $abstract Service to resolve, or null to get container
 * @return mixed
 */
function app(?string $abstract = null)
{
    $container = ServiceContainer::getInstance();

    if ($abstract === null) {
        return $container;
    }

    return $container->get($abstract);
}

/**
 * Global helper for accessing configuration
 *
 * @param string|null $key Configuration key (dot notation supported)
 * @param mixed $default Default value if key not found
 * @return mixed
 */
function config(?string $key = null, $default = null)
{
    static $config = null;

    if ($config === null) {
        $configFile = __DIR__ . '/../config/app.php';
        if (!file_exists($configFile)) {
            throw new RuntimeException("Configuration file not found: {$configFile}");
        }
        $config = require $configFile;
    }

    if ($key === null) {
        return $config;
    }

    // Support dot notation: 'database.host'
    $keys = explode('.', $key);
    $value = $config;

    foreach ($keys as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }
        $value = $value[$segment];
    }

    return $value;
}

/**
 * Global helper for checking feature flags
 *
 * @param string $feature Feature flag name
 * @return bool
 */
function feature(string $feature): bool
{
    return (bool) config("features.{$feature}", false);
}
