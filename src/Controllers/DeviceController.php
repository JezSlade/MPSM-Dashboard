<?php
/**
 * Device Controller
 * Handles device-related API endpoints
 *
 * Routes:
 * - GET    /api/v1/devices           - List devices with filtering
 * - GET    /api/v1/devices/:serial   - Get device details
 * - GET    /api/v1/devices/:serial/drilldown - Get device drilldown
 * - POST   /api/v1/devices/search    - Search devices
 * - GET    /api/v1/devices/stats     - Get device statistics
 */

class DeviceController extends BaseController
{
    private DeviceRepository $deviceRepo;

    public function __construct()
    {
        parent::__construct();
        $this->deviceRepo = app(DeviceRepository::class);
    }

    /**
     * List devices with optional filtering
     *
     * Query params:
     * - dealer_id: Filter by dealer
     * - customer_code: Filter by customer
     * - is_uninstalled: Filter uninstalled devices
     * - search: Search query
     * - limit: Results limit (default 100)
     * - offset: Results offset (default 0)
     */
    public function index(): void
    {
        try {
            $filters = [
                'dealer_id' => $this->query('dealer_id'),
                'customer_code' => $this->query('customer_code'),
                'is_uninstalled' => $this->query('is_uninstalled'),
                'search' => $this->query('search'),
                'limit' => (int) $this->query('limit', 100),
                'offset' => (int) $this->query('offset', 0),
            ];

            // Remove null filters
            $filters = array_filter($filters, fn($v) => $v !== null);

            $devices = $this->deviceRepo->findAll($filters);

            $this->success([
                'devices' => $devices,
                'count' => count($devices),
                'filters' => $filters,
            ]);
        } catch (Exception $e) {
            error_log("DeviceController::index error: " . $e->getMessage());
            $this->error('Failed to fetch devices', 500);
        }
    }

    /**
     * Get device by serial number
     *
     * @param string $serial Device serial number
     */
    public function show(string $serial): void
    {
        try {
            $device = $this->deviceRepo->findBySerial($serial);

            if (!$device) {
                $this->notFound("Device '{$serial}' not found");
            }

            $this->success(['device' => $device]);
        } catch (Exception $e) {
            error_log("DeviceController::show error: " . $e->getMessage());
            $this->error('Failed to fetch device', 500);
        }
    }

    /**
     * Get device drilldown data
     *
     * @param string $serial Device serial number
     */
    public function drilldown(string $serial): void
    {
        try {
            $drilldown = $this->deviceRepo->getDrilldown($serial);

            if (!$drilldown) {
                $this->notFound("Drilldown for device '{$serial}' not found");
            }

            $this->success(['drilldown' => $drilldown]);
        } catch (Exception $e) {
            error_log("DeviceController::drilldown error: " . $e->getMessage());
            $this->error('Failed to fetch device drilldown', 500);
        }
    }

    /**
     * Search devices
     *
     * POST body:
     * - query: Search query (required)
     * - limit: Results limit (default 10)
     */
    public function search(): void
    {
        try {
            $this->validate([
                'query' => ['required' => true, 'type' => 'string', 'min' => 2],
                'limit' => ['type' => 'int', 'min' => 1, 'max' => 100],
            ]);

            $query = $this->input('query');
            $limit = (int) $this->input('limit', 10);

            $results = $this->deviceRepo->search($query, $limit);

            $this->success([
                'results' => $results,
                'count' => count($results),
                'query' => $query,
            ]);
        } catch (ValidationException $e) {
            $this->validationError($e->getMessage());
        } catch (Exception $e) {
            error_log("DeviceController::search error: " . $e->getMessage());
            $this->error('Search failed', 500);
        }
    }

    /**
     * Get device statistics
     */
    public function stats(): void
    {
        try {
            $stats = [
                'total_devices' => count($this->deviceRepo->findAll()),
                'drilldown_coverage' => $this->deviceRepo->getDrilldownCoverage(),
                'missing_drilldowns' => count($this->deviceRepo->getMissingDrilldowns()),
            ];

            $this->success($stats);
        } catch (Exception $e) {
            error_log("DeviceController::stats error: " . $e->getMessage());
            $this->error('Failed to fetch statistics', 500);
        }
    }

    /**
     * Cache device data (admin only)
     *
     * POST body:
     * - device_data: Device data array (required)
     * - ttl: Cache TTL in seconds (default 3600)
     */
    public function cache(): void
    {
        try {
            $this->validate([
                'device_data' => ['required' => true, 'type' => 'array'],
                'ttl' => ['type' => 'int', 'min' => 60, 'max' => 86400],
            ]);

            $deviceData = $this->input('device_data');
            $ttl = (int) $this->input('ttl', 3600);

            $success = $this->deviceRepo->cacheDevice($deviceData, $ttl);

            if ($success) {
                $this->success([], 'Device cached successfully');
            } else {
                $this->error('Failed to cache device', 500);
            }
        } catch (ValidationException $e) {
            $this->validationError($e->getMessage());
        } catch (Exception $e) {
            error_log("DeviceController::cache error: " . $e->getMessage());
            $this->error('Cache operation failed', 500);
        }
    }
}
