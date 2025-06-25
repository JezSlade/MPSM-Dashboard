<?php declare(strict_types=1);
// /includes/redis.php

// Ensure the Redis extension is available
if (!extension_loaded('redis')) {
    throw new \Exception('The PHP Redis extension is not installed or enabled.');
}

if (!class_exists('RedisClient')) {
    class RedisClient {
        /** @var \Redis */
        private \Redis $client;

        public function __construct(array $config) {
            $host = $config['REDIS_HOST'] ?? '127.0.0.1';
            $port = $config['REDIS_PORT'] ?? 6379;
            $this->client = new \Redis();          // fully-qualified Redis
            $this->client->connect($host, $port);
        }

        public function get(string $key): ?string {
            $val = $this->client->get($key);
            return $val === false ? null : $val;
        }

        public function set(string $key, string $value, int $ttl): bool {
            // setex returns true on success, false on failure
            return $this->client->setex($key, $ttl, $value);
        }

        /**
         * Proxy any other Redis methods
         */
        public function __call(string $method, array $args) {
            return call_user_func_array([$this->client, $method], $args);
        }
    }
}
