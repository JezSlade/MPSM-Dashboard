<?php
header('Content-Type: text/html; charset=utf-8');

// Sample KPI data
$kpis = [
    ['label' => 'Total Users', 'value' => '12,543', 'change' => '+12%', 'positive' => true],
    ['label' => 'Revenue', 'value' => '$45,231', 'change' => '+8%', 'positive' => true],
    ['label' => 'Bounce Rate', 'value' => '23.4%', 'change' => '-5%', 'positive' => true],
];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            min-height: 280px;
        }
        .kpi-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
        }
        .kpi-item {
            background: rgba(255, 255, 255, 0.1);
            padding: 15px;
            border-radius: 12px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .kpi-label {
            font-size: 0.9rem;
            opacity: 0.8;
            margin-bottom: 5px;
        }
        .kpi-value {
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .kpi-change {
            font-size: 0.8rem;
            padding: 3px 8px;
            border-radius: 6px;
            display: inline-block;
        }
        .kpi-change.positive {
            background: rgba(46, 213, 115, 0.3);
            color: #2ed573;
        }
        .kpi-change.negative {
            background: rgba(255, 71, 87, 0.3);
            color: #ff4757;
        }
    </style>
</head>
<body>
    <div class="kpi-grid">
        <?php foreach ($kpis as $kpi): ?>
            <div class="kpi-item">
                <div class="kpi-label"><?php echo htmlspecialchars($kpi['label']); ?></div>
                <div class="kpi-value"><?php echo htmlspecialchars($kpi['value']); ?></div>
                <span class="kpi-change <?php echo $kpi['positive'] ? 'positive' : 'negative'; ?>">
                    <?php echo htmlspecialchars($kpi['change']); ?>
                </span>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
