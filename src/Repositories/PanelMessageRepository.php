<?php
/**
 * Panel Message Repository
 * Handles panel message data access
 *
 * Panel messages are webhook callbacks from MPS Monitor
 * containing device alerts and maintenance codes
 */

class PanelMessageRepository extends BaseRepository
{
    protected string $table = 'mpsm_panel_messages';
    protected string $primaryKey = 'id';
    protected int $cacheTtl = 600; // 10 minutes

    /**
     * Find messages by device serial
     */
    public function findByDevice(string $serial, int $limit = 100): array
    {
        $cacheKey = $this->getCacheKey('device', $serial, $limit);

        // Check cache
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }

        $stmt = $this->pdo->prepare("
            SELECT *
            FROM {$this->table}
            WHERE device_serial = ?
            ORDER BY received_at DESC
            LIMIT ?
        ");
        $stmt->execute([$serial, $limit]);
        $results = $stmt->fetchAll();

        // Parse JSON payloads
        $results = array_map(function($row) {
            if (isset($row['payload'])) {
                $row['payload'] = json_decode($row['payload'], true);
            }
            return $row;
        }, $results);

        // Cache results
        $this->cache->set($cacheKey, $results, $this->cacheTtl);

        return $results;
    }

    /**
     * Find recent messages within time window
     */
    public function findRecent(int $hours = 24, int $limit = 100, ?string $deviceSerial = null): array
    {
        $cacheKey = $this->getCacheKey('recent', $hours, $limit, $deviceSerial);

        // Check cache
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }

        $sql = "
            SELECT *
            FROM {$this->table}
            WHERE received_at >= DATE_SUB(NOW(), INTERVAL ? HOUR)
        ";
        $params = [$hours];

        // Optional device filter
        if ($deviceSerial !== null) {
            $sql .= " AND device_serial = ?";
            $params[] = $deviceSerial;
        }

        $sql .= " ORDER BY received_at DESC LIMIT ?";
        $params[] = $limit;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll();

        // Parse JSON payloads
        $results = array_map(function($row) {
            if (isset($row['payload'])) {
                $row['payload'] = json_decode($row['payload'], true);
            }
            return $row;
        }, $results);

        // Cache results (shorter TTL for recent data)
        $this->cache->set($cacheKey, $results, 60); // 1 minute

        return $results;
    }

    /**
     * Store a panel message from webhook
     */
    public function store(array $messageData): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO {$this->table}
            (received_at, customer_code, customer_description, device_serial,
             maintenance_alert_code, maintenance_alert_id, panel_configuration,
             source_ip, payload, processed)
            VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, ?, 0)
        ");

        $stmt->execute([
            $messageData['customer_code'] ?? null,
            $messageData['customer_description'] ?? null,
            $messageData['device_serial'] ?? null,
            $messageData['maintenance_alert_code'] ?? null,
            $messageData['maintenance_alert_id'] ?? null,
            $messageData['panel_configuration'] ?? null,
            $messageData['source_ip'] ?? $_SERVER['REMOTE_ADDR'] ?? null,
            json_encode($messageData['payload'] ?? $messageData),
        ]);

        $id = $this->pdo->lastInsertId();

        // Invalidate caches
        $this->invalidateListCache();
        if (!empty($messageData['device_serial'])) {
            $this->cache->delete($this->getCacheKey('device', $messageData['device_serial']));
        }

        return $id;
    }

    /**
     * Get message statistics
     */
    public function getStatistics(int $hours = 24): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                COUNT(*) as total_messages,
                COUNT(DISTINCT device_serial) as unique_devices,
                COUNT(DISTINCT customer_code) as unique_customers,
                MIN(received_at) as oldest_message,
                MAX(received_at) as newest_message
            FROM {$this->table}
            WHERE received_at >= DATE_SUB(NOW(), INTERVAL ? HOUR)
        ");
        $stmt->execute([$hours]);

        return $stmt->fetch() ?: [
            'total_messages' => 0,
            'unique_devices' => 0,
            'unique_customers' => 0,
            'oldest_message' => null,
            'newest_message' => null,
        ];
    }

    /**
     * Count distinct devices with panel messages
     */
    public function countDevicesWithMessages(): int
    {
        $stmt = $this->pdo->query("
            SELECT COUNT(DISTINCT device_serial)
            FROM {$this->table}
            WHERE device_serial IS NOT NULL
        ");

        return (int) $stmt->fetchColumn();
    }

    /**
     * Mark messages as processed
     */
    public function markProcessed(array $ids): bool
    {
        if (empty($ids)) {
            return true;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->pdo->prepare("
            UPDATE {$this->table}
            SET processed = 1
            WHERE id IN ({$placeholders})
        ");

        return $stmt->execute($ids);
    }
}
