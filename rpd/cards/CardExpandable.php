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
            background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
            min-height: 280px;
        }
        .expandable-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            padding: 15px;
            height: 250px;
            overflow: hidden;
        }
        .expandable-title {
            text-align: center;
            margin-bottom: 15px;
            color: #333;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .expandable-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }
        .expandable-content.expanded {
            max-height: 200px;
        }
        .content-section {
            margin-bottom: 15px;
            padding: 10px;
            background: rgba(0, 0, 0, 0.05);
            border-radius: 8px;
        }
        .section-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        .section-text {
            color: #666;
            font-size: 0.9rem;
            line-height: 1.4;
        }
        .expand-icon {
            transition: transform 0.3s ease;
        }
        .expand-icon.rotated {
            transform: rotate(180deg);
        }
    </style>
</head>
<body>
    <div class="expandable-container">
        <h3 class="expandable-title" onclick="toggleExpand()">
            Expandable Content
            <span class="expand-icon" id="expandIcon">▼</span>
        </h3>
        <div class="expandable-content" id="expandableContent">
            <div class="content-section">
                <div class="section-title">Performance Metrics</div>
                <div class="section-text">
                    CPU usage is at 45%, memory consumption is 2.3GB, and disk I/O is operating normally.
                </div>
            </div>
            <div class="content-section">
                <div class="section-title">Recent Activity</div>
                <div class="section-text">
                    15 new users registered today, 234 orders processed, and 12 support tickets resolved.
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleExpand() {
            const content = document.getElementById('expandableContent');
            const icon = document.getElementById('expandIcon');
            
            content.classList.toggle('expanded');
            icon.classList.toggle('rotated');
        }
    </script>
</body>
</html>
