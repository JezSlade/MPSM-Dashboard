<?php
header('Content-Type: text/html; charset=utf-8');

// Sample list data
$items = [
    ['title' => 'Complete project proposal', 'status' => 'pending', 'priority' => 'high'],
    ['title' => 'Review code changes', 'status' => 'completed', 'priority' => 'medium'],
    ['title' => 'Update documentation', 'status' => 'in-progress', 'priority' => 'low'],
    ['title' => 'Client meeting preparation', 'status' => 'pending', 'priority' => 'high'],
    ['title' => 'Database optimization', 'status' => 'in-progress', 'priority' => 'medium'],
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
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            min-height: 280px;
        }
        .list-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            padding: 15px;
            height: 250px;
            overflow-y: auto;
        }
        .list-title {
            text-align: center;
            margin-bottom: 15px;
            color: #333;
            font-weight: 600;
        }
        .list-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            margin-bottom: 8px;
            background: rgba(0, 0, 0, 0.02);
            border-radius: 8px;
            border-left: 4px solid #ddd;
        }
        .list-item.high { border-left-color: #ff4757; }
        .list-item.medium { border-left-color: #ffa502; }
        .list-item.low { border-left-color: #2ed573; }
        
        .item-title {
            font-size: 0.9rem;
            color: #333;
            flex: 1;
        }
        .item-status {
            font-size: 0.8rem;
            padding: 3px 8px;
            border-radius: 12px;
            font-weight: 500;
        }
        .status-pending { background: #ffeaa7; color: #d63031; }
        .status-completed { background: #d1f2eb; color: #00b894; }
        .status-in-progress { background: #ddd6fe; color: #6c5ce7; }
    </style>
</head>
<body>
    <div class="list-container">
        <h3 class="list-title">Task List</h3>
        <?php foreach ($items as $item): ?>
            <div class="list-item <?php echo $item['priority']; ?>">
                <span class="item-title"><?php echo htmlspecialchars($item['title']); ?></span>
                <span class="item-status status-<?php echo $item['status']; ?>">
                    <?php echo ucfirst(str_replace('-', ' ', $item['status'])); ?>
                </span>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
