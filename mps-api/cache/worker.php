<?php
/**
 * Cache Worker
 *
 * CLI utility that warms configured cache entries on a fixed cadence.
 * Usage:
 *   php cache/worker.php            # single pass over configured actions
 *   php cache/worker.php --watch    # continuous loop (sleep between passes)
 */

define('MPS_ENGINE_ACCESS', true);

require_once __DIR__ . '/../engine.php';
require_once __DIR__ . '/ActionCache.php';

$configFile = __DIR__ . '/config.php';
if (!is_file($configFile)) {
    fwrite(STDERR, "[cache-worker] Missing configuration file: {$configFile}\n");
    exit(1);
}

$config = require $configFile;
ActionCache::init($config);

$watchMode = in_array('--watch', $argv, true);
$sleepInterval = getenv('MPS_CACHE_WORKER_INTERVAL') ? (int) getenv('MPS_CACHE_WORKER_INTERVAL') : 300;
if ($sleepInterval < 30) {
    $sleepInterval = 30;
}

$engine = MPSMonitorEngine::getInstance();
$warmSets = ActionCache::getWarmParameterSets();

if (empty($warmSets)) {
    fwrite(STDOUT, "[cache-worker] No warm parameter sets defined. Nothing to do.\n");
    exit(0);
}

do {
    $cycleStart = microtime(true);
    fwrite(STDOUT, sprintf("[cache-worker] Starting warm cycle at %s\n", date('c')));

    foreach ($warmSets as $action => $paramSets) {
        if (empty($paramSets)) {
            $paramSets = [[]];
        }

        foreach ($paramSets as $params) {
            if (!is_array($params)) {
                $params = [];
            }

            try {
                $result = $engine->dispatchAction($action, $params);
                $status = ($result['success'] ?? false) ? 'ok' : 'fail';
                fwrite(
                    STDOUT,
                    sprintf(
                        "[cache-worker] %-30s %-4s (params: %s)\n",
                        $action,
                        $status,
                        json_encode($params, JSON_UNESCAPED_SLASHES)
                    )
                );
            } catch (Throwable $e) {
                fwrite(
                    STDERR,
                    sprintf(
                        "[cache-worker] ERROR warming %s (%s)\n",
                        $action,
                        $e->getMessage()
                    )
                );
            }
        }
    }

    $cycleDuration = round((microtime(true) - $cycleStart) * 1000);
    fwrite(STDOUT, sprintf("[cache-worker] Cycle complete in %dms\n", $cycleDuration));

    if ($watchMode) {
        sleep($sleepInterval);
    }
} while ($watchMode);

exit(0);

