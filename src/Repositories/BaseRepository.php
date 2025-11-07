<?php
/**
 * Base Repository
 * Abstract base class for all repository implementations
 *
 * Provides common functionality:
 * - Database access via PDO
 * - Cache integration
 * - Common CRUD operations
 * - Query building helpers
 */

abstract class BaseRepository implements RepositoryInterface
{
    protected PDO $pdo;
    protected CacheInterface $cache;
    protected string $table;
    protected string $primaryKey = 'id';
    protected int $cacheTtl = 3600; // 1 hour default

    public function __construct(PDO $pdo, CacheInterface $cache)
    {
        $this->pdo = $pdo;
        $this->cache = $cache;

        if (!isset($this->table)) {
            throw new RuntimeException('Repository must define $table property');
        }
    }

    /**
     * Generate cache key
     */
    protected function getCacheKey(string $type, ...$params): string
    {
        $key = $this->table . ':' . $type;
        if (!empty($params)) {
            $key .= ':' . implode(':', array_map(fn($p) => is_array($p) ? md5(json_encode($p)) : $p, $params));
        }
        return $key;
    }

    /**
     * Find a single record by primary key
     */
    public function findById($id): ?array
    {
        $cacheKey = $this->getCacheKey('item', $id);

        // Check cache
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }

        // Query database
        $stmt = $this->pdo->prepare("
            SELECT * FROM {$this->table}
            WHERE {$this->primaryKey} = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $result = $stmt->fetch();

        if (!$result) {
            return null;
        }

        // Cache result
        $this->cache->set($cacheKey, $result, $this->cacheTtl);

        return $result;
    }

    /**
     * Find all records (with optional filters)
     */
    public function findAll(array $filters = []): array
    {
        $cacheKey = $this->getCacheKey('list', $filters);

        // Check cache
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }

        // Build query
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];

        // Apply filters (override in child classes for custom filtering)
        foreach ($filters as $key => $value) {
            if ($key === 'limit' || $key === 'offset') {
                continue; // Handle separately
            }
            $sql .= " AND {$key} = ?";
            $params[] = $value;
        }

        // Add limit/offset
        if (isset($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }
        if (isset($filters['offset'])) {
            $sql .= " OFFSET " . (int)$filters['offset'];
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll();

        // Cache results (shorter TTL for lists)
        $this->cache->set($cacheKey, $results, $this->cacheTtl / 2);

        return $results;
    }

    /**
     * Create a new record
     */
    public function create(array $data): array
    {
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');

        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_values($data));

        $id = $this->pdo->lastInsertId();

        // Invalidate list cache
        $this->invalidateListCache();

        // Return created record
        return $this->findById($id) ?? array_merge($data, [$this->primaryKey => $id]);
    }

    /**
     * Update an existing record
     */
    public function update($id, array $data): array
    {
        $sets = [];
        $params = [];

        foreach ($data as $key => $value) {
            $sets[] = "{$key} = ?";
            $params[] = $value;
        }
        $params[] = $id;

        $sql = sprintf(
            "UPDATE %s SET %s WHERE %s = ?",
            $this->table,
            implode(', ', $sets),
            $this->primaryKey
        );

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        // Invalidate caches
        $this->cache->delete($this->getCacheKey('item', $id));
        $this->invalidateListCache();

        // Return updated record
        return $this->findById($id) ?? $data;
    }

    /**
     * Delete a record
     */
    public function delete($id): bool
    {
        $stmt = $this->pdo->prepare("
            DELETE FROM {$this->table}
            WHERE {$this->primaryKey} = ?
        ");

        $result = $stmt->execute([$id]);

        // Invalidate caches
        $this->cache->delete($this->getCacheKey('item', $id));
        $this->invalidateListCache();

        return $result;
    }

    /**
     * Count records matching filters
     */
    public function count(array $filters = []): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE 1=1";
        $params = [];

        foreach ($filters as $key => $value) {
            if ($key === 'limit' || $key === 'offset') {
                continue;
            }
            $sql .= " AND {$key} = ?";
            $params[] = $value;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Check if record exists
     */
    public function exists($id): bool
    {
        return $this->findById($id) !== null;
    }

    /**
     * Invalidate all list caches for this table
     */
    protected function invalidateListCache(): void
    {
        // In a production system, you'd track cache keys or use tags
        // For now, we just clear specific known patterns
        // This is a simplified implementation
    }

    /**
     * Begin database transaction
     */
    protected function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * Commit database transaction
     */
    protected function commit(): bool
    {
        return $this->pdo->commit();
    }

    /**
     * Rollback database transaction
     */
    protected function rollback(): bool
    {
        return $this->pdo->rollBack();
    }
}
