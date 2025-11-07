<?php
/**
 * Device Repository
 * Handles all device-related data access
 *
 * Responsibilities:
 * - Cache device list from Device/List API
 * - Cache device drill-down from Device/Get API
 * - Provide fast lookups by serial number
 * - Handle device filtering and search
 */

class DeviceRepository extends BaseRepository
{
    protected string $table = 'mpsm_cache_devices';
    protected string $primaryKey = 'serial_number';
    protected int $cacheTtl = 3600; // 1 hour

    /**
     * Find device by serial number
     */
    public function findBySerial(string $serial): ?array
    {
        return $this->findById($serial);
    }

    /**
     * Find all devices with advanced filtering
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

        // Dealer filter
        if (!empty($filters['dealer_id'])) {
            $sql .= " AND JSON_EXTRACT(device_data, '$.DealerCode') = ?";
            $params[] = $filters['dealer_id'];
        }

        // Customer filter
        if (!empty($filters['customer_code'])) {
            $sql .= " AND customer_code = ?";
            $params[] = $filters['customer_code'];
        }

        // Uninstalled filter
        if (isset($filters['is_uninstalled'])) {
            $sql .= " AND is_uninstalled = ?";
            $params[] = (int)$filters['is_uninstalled'];
        }

        // Search filter (serial, equipment ID, IP)
        if (!empty($filters['search'])) {
            $sql .= " AND (
                serial_number LIKE ? OR
                JSON_EXTRACT(device_data, '$.equipmentId') LIKE ? OR
                JSON_EXTRACT(device_data, '$.ipAddress') LIKE ?
            )";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Sorting
        $sortColumn = $filters['sort'] ?? 'cached_at';
        $sortOrder = strtoupper($filters['order'] ?? 'DESC');
        $sql .= " ORDER BY {$sortColumn} {$sortOrder}";

        // Pagination
        if (isset($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
            if (isset($filters['offset'])) {
                $sql .= " OFFSET " . (int)$filters['offset'];
            }
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll();

        // Parse JSON device_data
        $results = array_map(function($row) {
            if (isset($row['device_data'])) {
                $row['device_data'] = json_decode($row['device_data'], true);
            }
            return $row;
        }, $results);

        // Cache results (5 minutes for lists)
        $this->cache->set($cacheKey, $results, 300);

        return $results;
    }

    /**
     * Cache a device from API response
     */
    public function cacheDevice(array $deviceData, int $ttl = 3600): bool
    {
        $serial = $deviceData['serialNumber'] ?? $deviceData['SerialNumber'] ?? null;
        if (!$serial) {
            return false;
        }

        $customerCode = $deviceData['customerCode'] ?? $deviceData['CustomerCode'] ?? null;
        $isUninstalled = $deviceData['isUninstalled'] ?? $deviceData['IsUninstalled'] ?? 0;

        $stmt = $this->pdo->prepare("
            INSERT INTO {$this->table}
            (serial_number, device_data, customer_code, is_uninstalled, cached_at, expires_at)
            VALUES (?, ?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL ? SECOND))
            ON DUPLICATE KEY UPDATE
                device_data = VALUES(device_data),
                customer_code = VALUES(customer_code),
                is_uninstalled = VALUES(is_uninstalled),
                cached_at = NOW(),
                expires_at = VALUES(expires_at)
        ");

        $result = $stmt->execute([
            $serial,
            json_encode($deviceData),
            $customerCode,
            (int)$isUninstalled,
            $ttl
        ]);

        if ($result) {
            // Update cache
            $this->cache->set(
                $this->getCacheKey('item', $serial),
                [
                    'serial_number' => $serial,
                    'device_data' => $deviceData,
                    'customer_code' => $customerCode,
                    'is_uninstalled' => (int)$isUninstalled,
                ],
                $ttl
            );

            // Invalidate list caches
            $this->invalidateListCache();
        }

        return $result;
    }

    /**
     * Get device drill-down data
     */
    public function getDrilldown(string $serial): ?array
    {
        $cacheKey = "drilldown:item:{$serial}";

        // Check cache
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }

        // Query database
        $stmt = $this->pdo->prepare("
            SELECT drilldown_data, has_alerts, has_supplies, cached_at
            FROM mpsm_cache_device_drilldown
            WHERE serial_number = ?
            LIMIT 1
        ");
        $stmt->execute([$serial]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        $result = [
            'data' => json_decode($row['drilldown_data'], true),
            'has_alerts' => (bool)$row['has_alerts'],
            'has_supplies' => (bool)$row['has_supplies'],
            'cached_at' => $row['cached_at'],
        ];

        // Cache result
        $this->cache->set($cacheKey, $result, 3600);

        return $result;
    }

    /**
     * Cache device drill-down data
     */
    public function cacheDrilldown(string $serial, array $drilldownData): bool
    {
        $hasAlerts = !empty($drilldownData['supplyAlerts']) || !empty($drilldownData['SupplyAlerts']);
        $hasSupplies = !empty($drilldownData['supplies']) || !empty($drilldownData['Supplies']);

        $stmt = $this->pdo->prepare("
            INSERT INTO mpsm_cache_device_drilldown
            (serial_number, drilldown_data, has_alerts, has_supplies, cached_at, expires_at)
            VALUES (?, ?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 1 HOUR))
            ON DUPLICATE KEY UPDATE
                drilldown_data = VALUES(drilldown_data),
                has_alerts = VALUES(has_alerts),
                has_supplies = VALUES(has_supplies),
                cached_at = NOW(),
                expires_at = VALUES(expires_at)
        ");

        $result = $stmt->execute([
            $serial,
            json_encode($drilldownData),
            (int)$hasAlerts,
            (int)$hasSupplies
        ]);

        if ($result) {
            // Update cache
            $this->cache->set(
                "drilldown:item:{$serial}",
                [
                    'data' => $drilldownData,
                    'has_alerts' => $hasAlerts,
                    'has_supplies' => $hasSupplies,
                ],
                3600
            );
        }

        return $result;
    }

    /**
     * Get drill-down coverage statistics
     */
    public function getDrilldownCoverage(): array
    {
        $stmt = $this->pdo->query("
            SELECT
                COUNT(DISTINCT d.serial_number) as total_devices,
                COUNT(DISTINCT dd.serial_number) as drilldown_cached,
                ROUND(COUNT(DISTINCT dd.serial_number) * 100.0 / COUNT(DISTINCT d.serial_number), 1) as coverage_percent
            FROM {$this->table} d
            LEFT JOIN mpsm_cache_device_drilldown dd ON d.serial_number = dd.serial_number
        ");

        return $stmt->fetch() ?: [
            'total_devices' => 0,
            'drilldown_cached' => 0,
            'coverage_percent' => 0.0
        ];
    }

    /**
     * Get devices missing drill-down data
     */
    public function getMissingDrilldowns(int $limit = 20): array
    {
        $stmt = $this->pdo->prepare("
            SELECT d.serial_number, d.cached_at
            FROM {$this->table} d
            LEFT JOIN mpsm_cache_device_drilldown dd ON d.serial_number = dd.serial_number
            WHERE dd.serial_number IS NULL
            ORDER BY d.cached_at DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);

        return $stmt->fetchAll();
    }

    /**
     * Search devices by query string
     */
    public function search(string $query, int $limit = 10): array
    {
        $cacheKey = $this->getCacheKey('search', $query, $limit);

        // Check cache
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }

        $searchTerm = '%' . $query . '%';

        $stmt = $this->pdo->prepare("
            SELECT serial_number, device_data, customer_code
            FROM {$this->table}
            WHERE serial_number LIKE ?
               OR JSON_EXTRACT(device_data, '$.equipmentId') LIKE ?
               OR JSON_EXTRACT(device_data, '$.ipAddress') LIKE ?
               OR JSON_EXTRACT(device_data, '$.model') LIKE ?
               OR customer_code LIKE ?
            LIMIT ?
        ");

        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $limit]);
        $results = $stmt->fetchAll();

        // Parse JSON
        $results = array_map(function($row) {
            if (isset($row['device_data'])) {
                $row['device_data'] = json_decode($row['device_data'], true);
            }
            return $row;
        }, $results);

        // Cache results (1 minute)
        $this->cache->set($cacheKey, $results, 60);

        return $results;
    }
}
