<?php
class DataChartWidget {
    private static $allowedChartTypes = ['line', 'bar', 'pie', 'doughnut'];
    private static $allowedLegendPositions = ['top', 'bottom', 'left', 'right', 'hidden'];

    public static function render(array $config): string {
        $chartType = in_array(isset($config['chartType']) ? $config['chartType'] : '', self::$allowedChartTypes) 
            ? $config['chartType'] 
            : 'bar';
            
        $legendPosition = in_array(isset($config['legendPosition']) ? $config['legendPosition'] : '', self::$allowedLegendPositions)
            ? $config['legendPosition']
            : 'top';

        $data = self::fetchChartData($config);
        $chartId = 'chart-' . bin2hex(random_bytes(4));
        $title = htmlspecialchars(isset($config['title']) ? $config['title'] : 'Data Chart', ENT_QUOTES);
        $widgetId = htmlspecialchars(isset($config['id']) ? $config['id'] : '', ENT_QUOTES);
        $height = isset($config['height']) ? (int)$config['height'] : 300;
        $dataLabel = isset($config['dataLabel']) ? $config['dataLabel'] : 'Values';

        // Prepare JSON data strings
        $labelsJson = json_encode($data['labels'], JSON_HEX_TAG | JSON_HEX_APOS);
        $valuesJson = json_encode($data['values'], JSON_NUMERIC_CHECK);
        $dataLabelJson = json_encode($dataLabel, JSON_HEX_TAG);

        return <<<HTML
<div class="chart-widget" data-id="{$widgetId}">
    <div class="widget-header">
        <h3>{$title}</h3>
        <button class="edit-title-btn" title="Edit title">✏️</button>
        <select class="chart-type-selector">
            {self::renderChartTypeOptions($chartType)}
        </select>
    </div>
    <div class="chart-container">
        <canvas id="{$chartId}" height="{$height}"></canvas>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    (function() {
        const widget = document.querySelector('.chart-widget[data-id="{$widgetId}"]');
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

        const ctx = document.getElementById('{$chartId}').getContext('2d');
        const chart = new Chart(ctx, {
            type: '{$chartType}',
            data: {
                labels: $labelsJson,
                datasets: [{
                    label: $dataLabelJson,
                    data: $valuesJson,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { 
                        position: '{$legendPosition}',
                        labels: {
                            color: '#e0f7fa'
                        }
                    }
                },
                scales: {
                    y: {
                        ticks: {
                            color: '#e0f7fa'
                        },
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        }
                    },
                    x: {
                        ticks: {
                            color: '#e0f7fa'
                        },
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        }
                    }
                }
            }
        });

        widget.querySelector('.chart-type-selector').addEventListener('change', (e) => {
            chart.config.type = e.target.value;
            chart.update();
        });
    })();
</script>
HTML;
    }

    private static function renderChartTypeOptions(string $currentType): string {
        $options = '';
        foreach (self::$allowedChartTypes as $type) {
            $selected = $type === $currentType ? ' selected' : '';
            $label = ucfirst($type);
            $options .= "<option value=\"{$type}\"{$selected}>{$label}</option>";
        }
        return $options;
    }

    private static function fetchChartData(array $config): array {
        try {
            $endpoint = isset($config['dataEndpoint']) ? $config['dataEndpoint'] : '';
            $labelField = isset($config['labelField']) ? $config['labelField'] : 'label';
            $valueField = isset($config['valueField']) ? $config['valueField'] : 'value';
            
            $apiData = (new ApiClient())->get($endpoint);
            
            return [
                'labels' => array_column($apiData, $labelField),
                'values' => array_map('floatval', array_column($apiData, $valueField))
            ];
        } catch (Exception $e) {
            ErrorHandler::log("Chart data fetch failed: " . $e->getMessage());
            return [
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May'],
                'values' => [10, 20, 30, 40, 50]
            ];
        }
    }
}