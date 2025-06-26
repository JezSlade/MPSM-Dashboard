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
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 280px;
        }
        .sample-content {
            text-align: center;
            padding: 20px;
        }
        .sample-title {
            color: #333;
            font-size: 1.5rem;
            margin-bottom: 15px;
        }
        .sample-text {
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        .sample-button {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: transform 0.2s ease;
        }
        .sample-button:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="sample-content">
        <h2 class="sample-title">Sample PHP Card</h2>
        <p class="sample-text">
            This is a sample PHP card that demonstrates how the hybrid dashboard works. 
            Each card is an independent PHP file rendered in an iframe.
        </p>
        <button class="sample-button" onclick="alert('Hello from PHP Card!')">
            Click Me!
        </button>
        <p style="margin-top: 20px; font-size: 0.9rem; color: #888;">
            Current time: <?php echo date('Y-m-d H:i:s'); ?>
        </p>
    </div>
</body>
</html>
