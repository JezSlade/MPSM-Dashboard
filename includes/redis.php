<?php declare(strict_types=1);
// /includes/redis.php
if (!class_exists('RedisClient')) {
    class RedisClient {
        private Redis $client;
        public function __construct(array $config) {
            $host = $config['REDIS_HOST'] ?? '127.0.0.1';
            $port = $config['REDIS_PORT'] ?? 6379;
            \$this->client = new Redis();
            \$this->client->connect(\$host, \$port);
        }
        public function get(string \$key): ?string {
            return \$this->client->get(\$key) ?: null;
        }
        public function set(string \$key, string \$value, int \$ttl): bool {
            return \$this->client->setex(\$key, \$ttl, \$value);
        }
    }
}
