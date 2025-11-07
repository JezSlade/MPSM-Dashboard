<?php
/**
 * Refresh Cache Job
 * Background job to refresh device cache from MPS API
 *
 * This replaces the synchronous refresh-cache-enhanced.php script
 * with an asynchronous background job that won't timeout.
 */

class RefreshCacheJob extends Job
{
    protected string $queue = 'cache';
    protected int $maxAttempts = 2;

    private ?string $specificSerial = null;
    private bool $fullRefresh = false;
    private bool $forceDrilldown = false;

    /**
     * Create job for specific device or full refresh
     */
    public function __construct(?string $serial = null, bool $fullRefresh = false, bool $forceDrilldown = false)
    {
        $this->specificSerial = $serial;
        $this->fullRefresh = $fullRefresh;
        $this->forceDrilldown = $forceDrilldown;
    }

    /**
     * Execute cache refresh
     */
    public function handle(): bool
    {
        $startTime = microtime(true);

        try {
            $deviceRepo = app(DeviceRepository::class);
            $engine = app(EngineInterface::class);

            if ($this->specificSerial) {
                // Refresh specific device
                return $this->refreshDevice($this->specificSerial, $deviceRepo, $engine);
            } else {
                // Refresh all devices
                return $this->refreshAllDevices($deviceRepo, $engine);
            }
        } catch (Exception $e) {
            error_log("RefreshCacheJob failed: " . $e->getMessage());
            $this->markFailed($e->getMessage());
            return false;
        } finally {
            $duration = round(microtime(true) - $startTime, 2);
            error_log("RefreshCacheJob completed in {$duration}s");
        }
    }

    /**
     * Refresh single device
     */
    private function refreshDevice(string $serial, DeviceRepository $deviceRepo, EngineInterface $engine): bool
    {
        // Fetch device from API
        $response = $engine->query('Device/Get', ['serial' => $serial]);

        if (!$response['success'] || empty($response['data'])) {
            error_log("Failed to fetch device {$serial} from API");
            return false;
        }

        $device = $response['data'];

        // Cache device
        $deviceRepo->cacheDevice($device, 3600);

        // Fetch and cache drilldown if needed
        if ($this->forceDrilldown || !$deviceRepo->getDrilldown($serial)) {
            $drilldownResponse = $engine->query('Device/GetDeepDive', ['serial' => $serial]);

            if ($drilldownResponse['success'] && !empty($drilldownResponse['data'])) {
                $deviceRepo->cacheDrilldown($serial, $drilldownResponse['data']);
            }
        }

        return true;
    }

    /**
     * Refresh all devices
     */
    private function refreshAllDevices(DeviceRepository $deviceRepo, EngineInterface $engine): bool
    {
        $batchSize = 50;
        $offset = 0;
        $totalProcessed = 0;

        while (true) {
            // Fetch batch from API
            $response = $engine->query('Device/List', [
                'limit' => $batchSize,
                'offset' => $offset,
            ]);

            if (!$response['success'] || empty($response['data'])) {
                break;
            }

            $devices = $response['data'];

            // Cache devices in batch
            foreach ($devices as $device) {
                $deviceRepo->cacheDevice($device, 3600);
                $totalProcessed++;
            }

            // Check if we got fewer devices than batch size (last page)
            if (count($devices) < $batchSize) {
                break;
            }

            $offset += $batchSize;

            // Prevent memory issues on very large datasets
            if ($totalProcessed % 500 === 0) {
                gc_collect_cycles();
            }
        }

        error_log("RefreshCacheJob: Processed {$totalProcessed} devices");

        // Optionally refresh drilldowns for devices missing them
        if ($this->fullRefresh) {
            $this->refreshMissingDrilldowns($deviceRepo, $engine);
        }

        return true;
    }

    /**
     * Refresh drilldowns for devices that don't have them
     */
    private function refreshMissingDrilldowns(DeviceRepository $deviceRepo, EngineInterface $engine): void
    {
        $missingSerials = $deviceRepo->getMissingDrilldowns(100);

        error_log("RefreshCacheJob: Refreshing " . count($missingSerials) . " missing drilldowns");

        foreach ($missingSerials as $serial) {
            try {
                $response = $engine->query('Device/GetDeepDive', ['serial' => $serial]);

                if ($response['success'] && !empty($response['data'])) {
                    $deviceRepo->cacheDrilldown($serial, $response['data']);
                }

                // Rate limiting - avoid hammering API
                usleep(100000); // 100ms delay
            } catch (Exception $e) {
                error_log("Failed to refresh drilldown for {$serial}: " . $e->getMessage());
            }
        }
    }

    /**
     * Get job payload for serialization
     */
    public function getPayload(): array
    {
        return [
            'serial' => $this->specificSerial,
            'full_refresh' => $this->fullRefresh,
            'force_drilldown' => $this->forceDrilldown,
        ];
    }

    /**
     * Get job name
     */
    public function getName(): string
    {
        if ($this->specificSerial) {
            return "RefreshCache:{$this->specificSerial}";
        }
        return $this->fullRefresh ? "RefreshCache:Full" : "RefreshCache:Incremental";
    }
}
