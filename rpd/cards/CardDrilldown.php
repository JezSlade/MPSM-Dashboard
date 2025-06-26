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
        .drilldown-container {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 15px;
            height: 250px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .drilldown-title {
            text-align: center;
            margin-bottom: 15px;
            font-weight: 600;
        }
        .breadcrumb {
            margin-bottom: 15px;
            font-size: 0.9rem;
            opacity: 0.8;
        }
        .breadcrumb a {
            color: white;
            text-decoration: none;
            cursor: pointer;
        }
        .breadcrumb a:hover {
            text-decoration: underline;
        }
        .drill-items {
            display: flex;
            flex-direction: column;
            gap: 8px;
            max-height: 180px;
            overflow-y: auto;
        }
        .drill-item {
            background: rgba(255, 255, 255, 0.1);
            padding: 12px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .drill-item:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(5px);
        }
        .drill-label {
            font-weight: 500;
        }
        .drill-value {
            font-size: 0.9rem;
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <div class="drilldown-container">
        <h3 class="drilldown-title">Sales Drilldown</h3>
        <div class="breadcrumb" id="breadcrumb">
            <a onclick="goToLevel(0)">Dashboard</a>
        </div>
        <div class="drill-items" id="drillItems">
            <!-- Items will be populated by JavaScript -->
        </div>
    </div>

    <script>
        const drilldownData = [
            // Level 0 - Regions
            [
                { label: 'North America', value: '$125,000', hasChildren: true },
                { label: 'Europe', value: '$98,000', hasChildren: true },
                { label: 'Asia Pacific', value: '$87,000', hasChildren: true },
                { label: 'South America', value: '$45,000', hasChildren: true }
            ],
            // Level 1 - Countries (example for North America)
            [
                { label: 'United States', value: '$95,000', hasChildren: true },
                { label: 'Canada', value: '$20,000', hasChildren: true },
                { label: 'Mexico', value: '$10,000', hasChildren: false }
            ],
            // Level 2 - States (example for United States)
            [
                { label: 'California', value: '$35,000', hasChildren: false },
                { label: 'New York', value: '$25,000', hasChildren: false },
                { label: 'Texas', value: '$20,000', hasChildren: false },
                { label: 'Florida', value: '$15,000', hasChildren: false }
            ]
        ];

        const breadcrumbLabels = ['Dashboard', 'North America', 'United States'];
        let currentLevel = 0;

        function renderLevel(level) {
            const container = document.getElementById('drillItems');
            const breadcrumb = document.getElementById('breadcrumb');
            
            // Update breadcrumb
            let breadcrumbHTML = '';
            for (let i = 0; i <= level; i++) {
                if (i > 0) breadcrumbHTML += ' > ';
                breadcrumbHTML += `<a onclick="goToLevel(${i})">${breadcrumbLabels[i]}</a>`;
            }
            breadcrumb.innerHTML = breadcrumbHTML;

            // Render items
            container.innerHTML = '';
            if (drilldownData[level]) {
                drilldownData[level].forEach((item, index) => {
                    const itemEl = document.createElement('div');
                    itemEl.className = 'drill-item';
                    itemEl.innerHTML = `
                        <span class="drill-label">${item.label}</span>
                        <span class="drill-value">${item.value}</span>
                    `;
                    if (item.hasChildren) {
                        itemEl.onclick = () => drillDown(level + 1, item.label);
                    }
                    container.appendChild(itemEl);
                });
            }
        }

        function drillDown(level, label) {
            if (level < drilldownData.length) {
                currentLevel = level;
                if (level < breadcrumbLabels.length) {
                    breadcrumbLabels[level] = label;
                }
                renderLevel(level);
            }
        }

        function goToLevel(level) {
            currentLevel = level;
            renderLevel(level);
        }

        // Initialize
        renderLevel(0);
    </script>
</body>
</html>
