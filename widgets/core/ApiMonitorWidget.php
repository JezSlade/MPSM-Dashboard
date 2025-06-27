<?php
class ApiMonitorWidget {
    const REFRESH_INTERVAL = 60;

    public static function render(array $config): string {
        $apiClient = new ApiClient();
        $lastChecked = date('Y-m-d H:i:s');
        $status = self::checkApiStatus($apiClient);
        $title = htmlspecialchars(isset($config['title']) ? $config['title'] : 'API Monitor', ENT_QUOTES);
        $widgetId = htmlspecialchars(isset($config['id']) ? $config['id'] : '', ENT_QUOTES);
        $refreshInterval = isset($config['refreshInterval']) ? (int)$config['refreshInterval'] : self::REFRESH_INTERVAL;

        return <<<HTML
<div class="api-monitor" data-refresh="{$refreshInterval}" data-id="{$widgetId}">
    <div class="widget-header">
        <h3>{$title}</h3>
        <span class="last-checked">{$lastChecked}</span>
    </div>
    <div class="status-grid">
        {$status}
    </div>
</div>
<script>
    (function() {
        const widget = document.querySelector('.api-monitor[data-id="{$widgetId}"]');
        const refreshInterval = {$refreshInterval} * 1000;
        
        setInterval(() => {
            fetch('/render_widget.php?type=ApiMonitorWidget&id={$widgetId}')
                .then(r => r.text())
                .then(html => {
                    widget.innerHTML = html;
                });
        }, refreshInterval);
    })();
</script>
HTML;
    }

    private static function checkApiStatus(ApiClient $client): string {
        $endpoints = [
            'Device List' => 'Device/GetDevices',
            'Auth' => 'oauth/token',
            'Ping' => 'system/ping'
        ];

        $output = '';
        foreach ($endpoints as $name => $endpoint) {
            try {
                $start = microtime(true);
                $response = $client->makeRequest('GET', $endpoint);
                $latency = round((microtime(true) - $start) * 1000, 2);
                $status = isset($response['status']) ? $response['status'] : 200;
                $output .= <<<HTML
<div class="endpoint">
    <span>{$name}</span>
    <div>
        <span class="latency">{$latency}ms</span>
        <span class="status-badge status-{$status}">{$status}</span>
    </div>
</div>
HTML;
            } catch (Exception $e) {
                $output .= <<<HTML
<div class="endpoint">
    <span>{$name}</span>
    <span class="status-badge status-error">Error</span>
</div>
HTML;
            }
        }
        return $output;
    }
}