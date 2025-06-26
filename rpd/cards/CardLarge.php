<?php
header('Content-Type: text/html; charset=utf-8');
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
        .large-content {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 20px;
            height: 250px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .content-title {
            font-size: 1.5rem;
            margin-bottom: 20px;
            text-align: center;
        }
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            height: calc(100% - 60px);
        }
        .content-item {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 15px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .item-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .item-label {
            font-size: 0.9rem;
            opacity: 0.8;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="large-content">
        <h3 class="content-title">System Overview</h3>
        <div class="content-grid">
            <div class="content-item">
                <div class="item-number">98.5%</div>
                <div class="item-label">Uptime</div>
            </div>
            <div class="content-item">
                <div class="item-number">1,234</div>
                <div class="item-label">Requests/min</div>
            </div>
            <div class="content-item">
                <div class="item-number">45GB</div>
                <div class="item-label">Storage Used</div>
            </div>
            <div class="content-item">
                <div class="item-number">12ms</div>
                <div class="item-label">Avg Response</div>
            </div>
        </div>
    </div>
</body>
</html>
