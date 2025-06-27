<!DOCTYPE html>
<html>
<head>
    <title>Error</title>
    <style>
        body { 
            font-family: sans-serif; 
            background: #1a1a2e;
            color: #e0f7fa;
            padding: 2rem;
        }
        .error-container {
            background: rgba(30, 30, 45, 0.8);
            border: 1px solid #ff3860;
            border-radius: 8px;
            padding: 2rem;
            max-width: 800px;
            margin: 0 auto;
        }
        pre {
            white-space: pre-wrap;
            background: rgba(0,0,0,0.3);
            padding: 1rem;
            border-radius: 4px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>System Error</h1>
        <p><?= htmlspecialchars($e->getMessage()) ?></p>
        
        <?php if (DEBUG_MODE): ?>
        <h3>Debug Information:</h3>
        <pre>Error in <?= htmlspecialchars($e->getFile()) ?> on line <?= $e->getLine() ?>

<?= htmlspecialchars($e->getTraceAsString()) ?></pre>
        <?php endif; ?>
    </div>
</body>
</html>