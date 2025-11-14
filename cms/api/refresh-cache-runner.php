<?php
/**
 * Cache Refresh Runner
 *
 * Orchestrates the chunked refresh process by repeatedly calling
 * refresh-cache-chunked.php until completion.
 *
 * Works within web server timeout by processing one chunk per invocation.
 * Each chunk completes in <60 seconds, well within typical 2-3 minute limits.
 *
 * USAGE:
 * 1. Manual: Visit this URL in browser and refresh periodically
 * 2. Auto: Set up 1-minute CRON: * * * * * curl -s "https://...runner.php"
 * 3. CLI: php refresh-cache-runner.php
 */

set_time_limit(90);
require '../config.php';

$baseUrl = 'https://mpsm.resolutionsbydesign.us/cms/api/refresh-cache-chunked.php';

function callChunked($action) {
    global $baseUrl;
    $url = $baseUrl . '?action=' . $action;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 80); // Allow 80s for chunk processing
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        throw new Exception("HTTP {$httpCode} from chunked API");
    }

    return json_decode($response, true);
}

function formatDuration($seconds) {
    $mins = floor($seconds / 60);
    $secs = $seconds % 60;
    return sprintf("%dm %ds", $mins, $secs);
}

function respondHtml($status, $progress, $message) {
    header('Content-Type: text/html; charset=utf-8');
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Cache Refresh Progress</title>
        <meta http-equiv="refresh" content="2">
        <style>
            body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #d4d4d4; }
            .status { font-size: 24px; margin-bottom: 20px; }
            .status.running { color: #4ec9b0; }
            .status.complete { color: #4fc3f7; }
            .status.error { color: #f44336; }
            .progress { background: #2d2d2d; border: 1px solid #3e3e3e; padding: 15px; border-radius: 4px; }
            .progress div { margin: 5px 0; }
            .label { color: #858585; display: inline-block; width: 200px; }
            .value { color: #4ec9b0; font-weight: bold; }
            .bar-container { background: #2d2d2d; height: 30px; border: 1px solid #3e3e3e; margin: 10px 0; border-radius: 4px; overflow: hidden; }
            .bar { background: linear-gradient(90deg, #4ec9b0, #4fc3f7); height: 100%; transition: width 0.3s; }
            .message { background: #264f78; border-left: 4px solid #4ec9b0; padding: 10px; margin: 10px 0; }
            .error-box { background: #5a1d1d; border-left: 4px solid #f44336; padding: 10px; margin: 10px 0; }
        </style>
    </head>
    <body>
        <div class="status <?= strtolower($status) ?>"><?= htmlspecialchars($status) ?></div>
        <div class="progress">
            <?php foreach ($progress as $label => $value): ?>
                <div>
                    <span class="label"><?= htmlspecialchars($label) ?>:</span>
                    <span class="value"><?= htmlspecialchars($value) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    </body>
    </html>
    <?php
    exit;
}

try {
    // Check current status
    $status = callChunked('status');

    // If idle or completed, start new refresh
    if (!isset($status['state']) || $status['state']['status'] === 'completed') {
        $result = callChunked('start');
        respondHtml(
            'STARTING',
            ['State' => 'Initializing'],
            'Refresh started. Page will auto-refresh to show progress.'
        );
    }

    $state = $status['state'];

    // Process next chunk
    $result = callChunked('process');
    $newState = $result['state'];

    // Calculate progress
    $progress = [];

    if ($newState['status'] === 'fetching_devices') {
        $pct = $newState['total_pages'] > 0
            ? round(($newState['current_page'] / $newState['total_pages']) * 100)
            : 0;
        $progress['Phase'] = '1/3 - Fetching Devices';
        $progress['Page'] = "{$newState['current_page']} / {$newState['total_pages']} ({$pct}%)";
        $progress['Devices Cached'] = number_format($newState['devices_cached']);
    }
    elseif ($newState['status'] === 'fetching_drilldowns') {
        $total = count($newState['devices_to_fetch_drilldown']);
        $pct = $total > 0 ? round(($newState['drilldown_index'] / $total) * 100) : 0;
        $progress['Phase'] = '2/3 - Fetching Drill-Downs';
        $progress['Progress'] = "{$newState['drilldown_index']} / {$total} ({$pct}%)";
        $progress['Drilldowns Cached'] = number_format($newState['drilldowns_cached']);
    }
    elseif ($newState['status'] === 'ready_for_cutover') {
        $progress['Phase'] = '3/3 - Atomic Cutover';
        $progress['Status'] = 'Ready to swap tables';
    }
    elseif ($newState['status'] === 'completed') {
        $duration = strtotime($newState['completed_at']) - strtotime($newState['started_at']);
        $progress['Status'] = 'COMPLETED';
        $progress['Devices'] = number_format($newState['devices_cached']);
        $progress['Drill-Downs'] = number_format($newState['drilldowns_cached']);
        $progress['Duration'] = formatDuration($duration);
        $progress['Errors'] = count($newState['errors']);

        respondHtml(
            'COMPLETE',
            $progress,
            'Cache refresh completed successfully! Dashboard is now populated with latest data.'
        );
    }

    $progress['Started'] = $newState['started_at'];
    $progress['Last Activity'] = $newState['last_activity'];
    $progress['Errors'] = count($newState['errors']);

    $message = $result['continue']
        ? 'Processing... Page will auto-refresh every 2 seconds.'
        : 'Finalizing...';

    respondHtml('RUNNING', $progress, $message);

} catch (Exception $e) {
    respondHtml(
        'ERROR',
        ['Error' => $e->getMessage()],
        'An error occurred. Check logs for details.'
    );
}
