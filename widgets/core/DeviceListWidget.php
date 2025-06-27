<?php
class DeviceListWidget {
    private static $allowedSortFields = [
        'serialNumber' => 'Serial',
        'model' => 'Model',
        'status' => 'Status',
        'lastSeen' => 'Last Seen'
    ];

    public static function render(array $config): string {
        $sortField = array_key_exists($_GET['sort'] ?? '', self::$allowedSortFields) 
            ? $_GET['sort'] 
            : 'serialNumber';
        
        $sortDirection = strtolower($_GET['dir'] ?? 'asc') === 'desc' 
            ? 'desc' 
            : 'asc';

        $devices = (new ApiClient())->getDevices();
        
        usort($devices, function($a, $b) use ($sortField, $sortDirection) {
            $valA = $a[$sortField] ?? '';
            $valB = $b[$sortField] ?? '';
            
            if ($sortDirection === 'asc') {
                return $valA <=> $valB;
            }
            return $valB <=> $valA;
        });

        $rows = '';
        foreach (array_slice($devices, 0, $config['maxItems'] ?? 15) as $device) {
            $rows .= self::renderDeviceRow($device);
        }

        $title = htmlspecialchars($config['title'] ?? 'Devices', ENT_QUOTES);
        $widgetId = htmlspecialchars($config['id'] ?? '', ENT_QUOTES);

        return <<<HTML
<div class="device-list-widget" data-id="{$widgetId}">
    <div class="widget-header">
        <h3>{$title}</h3>
        <button class="edit-title-btn" title="Edit title">✏️</button>
        <div class="widget-actions">
            <select class="sort-selector">
                {self::renderSortOptions($sortField, $sortDirection)}
            </select>
        </div>
    </div>
    <table>
        <thead>
            <tr>
                {self::renderTableHeaders($sortField, $sortDirection)}
            </tr>
        </thead>
        <tbody>{$rows}</tbody>
    </table>
</div>
<script>
    (function() {
        const widget = document.querySelector('.device-list-widget[data-id="{$widgetId}"]');
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

        widget.querySelectorAll('[data-sort]').forEach(header => {
            header.addEventListener('click', () => {
                const widget = header.closest('.device-list-widget');
                widget.dataset.loading = true;
                fetch(`/render_widget.php?type=DeviceListWidget&id={$widgetId}&sort=${header.dataset.sort}&dir=${header.dataset.dir}`)
                    .then(r => r.text())
                    .then(html => {
                        widget.outerHTML = html;
                    });
            });
        });
    })();
</script>
HTML;
    }

    private static function renderDeviceRow(array $device): string {
        $lastSeen = date('M j, Y g:i a', strtotime($device['lastSeen'] ?? 'now'));
        $statusClass = strtolower($device['status'] ?? 'unknown');
        $serial = htmlspecialchars($device['serialNumber'] ?? '', ENT_QUOTES);
        $model = htmlspecialchars($device['model'] ?? '', ENT_QUOTES);
        $status = htmlspecialchars($device['status'] ?? '', ENT_QUOTES);
        
        return <<<HTML
<tr>
    <td>{$serial}</td>
    <td>{$model}</td>
    <td><span class="status-badge {$statusClass}">{$status}</span></td>
    <td>{$lastSeen}</td>
</tr>
HTML;
    }

    private static function renderSortOptions(string $currentField, string $currentDir): string {
        $options = '';
        foreach (self::$allowedSortFields as $field => $label) {
            $selected = $field === $currentField ? ' selected' : '';
            $options .= "<option value=\"{$field}\"{$selected}>{$label}</option>";
        }
        return $options;
    }

    private static function renderTableHeaders(string $currentField, string $currentDir): string {
        $headers = '';
        foreach (self::$allowedSortFields as $field => $label) {
            $active = $field === $currentField ? ' class="active"' : '';
            $dir = $field === $currentField ? ($currentDir === 'asc' ? 'desc' : 'asc') : 'asc';
            $arrow = $field === $currentField 
                ? ($currentDir === 'asc' ? ' ↑' : ' ↓') 
                : '';
            
            $headers .= <<<HTML
<th data-sort="{$field}" data-dir="{$dir}"{$active}>
    {$label}{$arrow}
</th>
HTML;
        }
        return $headers;
    }
}