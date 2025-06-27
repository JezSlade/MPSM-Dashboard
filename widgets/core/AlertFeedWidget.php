<?php
class AlertFeedWidget {
    const MAX_ALERTS = 50;
    const DEFAULT_ALERT_LEVEL = 'warning';

    public static function render(array $config): string {
        $alertLevel = self::validateAlertLevel(isset($config['alertLevel']) ? $config['alertLevel'] : '');
        $initialAlerts = self::getRecentAlerts($alertLevel);
        $alertsHtml = implode('', array_map([self::class, 'renderAlert'], $initialAlerts));
        $wsUrl = self::getWebSocketUrl();
        $title = htmlspecialchars(isset($config['title']) ? $config['title'] : 'Alert Feed', ENT_QUOTES);
        $widgetId = htmlspecialchars(isset($config['id']) ? $config['id'] : '', ENT_QUOTES);
        $maxAlerts = isset($config['maxAlerts']) ? (int)$config['maxAlerts'] : self::MAX_ALERTS;

        return <<<HTML
<div class="alert-feed-widget" data-id="{$widgetId}">
    <div class="widget-header">
        <h3>{$title}</h3>
        <button class="edit-title-btn" title="Edit title">✏️</button>
        <div class="alert-filters">
            <select class="alert-level-filter">
                {self::renderAlertLevelOptions($alertLevel)}
            </select>
        </div>
    </div>
    <div class="alert-list">{$alertsHtml}</div>
</div>
<script>
    (function() {
        const widget = document.querySelector('.alert-feed-widget[data-id="{$widgetId}"]');
        const titleElement = widget.querySelector('h3');
        const editBtn = widget.querySelector('.edit-title-btn');
        
        editBtn.addEventListener('click', () => {
            const newTitle = prompt('Edit widget title:', titleElement.textContent);
            if (newTitle !== null && newTitle.trim() !== '') {
                fetch('/update_widget_title.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        id: '{$widgetId}',
                        title: newTitle.trim()
                    })
                }).then(r => r.json()).then(data => {
                    if (data.success) titleElement.textContent = newTitle.trim();
                });
            }
        });

        const socket = new WebSocket('{$wsUrl}');
        const alertList = widget.querySelector('.alert-list');
        const maxAlerts = {$maxAlerts};

        socket.onmessage = (event) => {
            try {
                const alert = JSON.parse(event.data);
                const currentLevel = widget.querySelector('.alert-level-filter').value;
                
                if (currentLevel !== 'all' && alert.level !== currentLevel) return;
                
                const alertElement = document.createElement('div');
                alertElement.className = 'alert-item ' + alert.level;
                alertElement.innerHTML = `
                    <div class="alert-time">${new Date(alert.timestamp).toLocaleTimeString()}</div>
                    <div class="alert-message">${escapeHtml(alert.message)}</div>
                `;
                
                alertList.prepend(alertElement);
                if (alertList.children.length > maxAlerts) {
                    alertList.lastChild.remove();
                }
            } catch (e) {
                console.error('Invalid alert message:', e);
            }
        };

        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }
    })();
</script>
HTML;
    }

    private static function validateAlertLevel(string $level): string {
        $allowedLevels = ['all', 'critical', 'warning', 'info'];
        return in_array(strtolower($level), $allowedLevels) 
            ? strtolower($level)
            : self::DEFAULT_ALERT_LEVEL;
    }

    private static function renderAlertLevelOptions(string $currentLevel): string {
        $levels = [
            'all' => 'All Alerts',
            'critical' => 'Critical Only',
            'warning' => 'Warnings+',
            'info' => 'Info+'
        ];
        
        $options = '';
        foreach ($levels as $value => $label) {
            $selected = $value === $currentLevel ? ' selected' : '';
            $options .= "<option value=\"{$value}\"{$selected}>{$label}</option>";
        }
        return $options;
    }

    private static function getWebSocketUrl(): string {
        $isHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
        return ($isHttps ? 'wss://' : 'ws://') . $host . '/alerts';
    }

    private static function getRecentAlerts(string $level): array {
        $query = "SELECT * FROM alerts";
        $params = [];
        
        if ($level !== 'all') {
            $query .= " WHERE level = ?";
            $params[] = $level;
        }
        
        $query .= " ORDER BY timestamp DESC LIMIT ?";
        $params[] = self::MAX_ALERTS;
        
        return (new Database())->query($query, $params)->fetchAll();
    }

    private static function renderAlert(array $alert): string {
        $time = date('H:i:s', strtotime($alert['timestamp']));
        $message = htmlspecialchars($alert['message'], ENT_QUOTES);
        $level = htmlspecialchars($alert['level'], ENT_QUOTES);
        
        return <<<HTML
<div class="alert-item {$level}">
    <div class="alert-time">{$time}</div>
    <div class="alert-message">{$message}</div>
</div>
HTML;
    }
}