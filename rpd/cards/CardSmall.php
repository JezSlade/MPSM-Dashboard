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
            padding: 20px;
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            min-height: 280px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .small-card-content {
            text-align: center;
            background: rgba(255, 255, 255, 0.8);
            padding: 30px;
            border-radius: 15px;
            backdrop-filter: blur(10px);
        }
        .metric-value {
            font-size: 3rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        .metric-label {
            font-size: 1rem;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
    </style>
</head>
<body>
    <div class="small-card-content">
        <div class="metric-value">42</div>
        <div class="metric-label">Active Sessions</div>
    </div>
</body>
</html>
