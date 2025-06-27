<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(APP_NAME) ?> Dashboard</title>
    <link rel="stylesheet" href="/dashboard/style.css">
    <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
    <script>
        // Global configuration
        const APP_CONFIG = {
            baseUrl: '<?= APP_BASE_URL ?>',
            csrfToken: '<?= $_SESSION['csrf_token'] ?? '' ?>'
        };
    </script>
</head>
<body>
    <header>
        <h1><?= htmlspecialchars(APP_NAME) ?></h1>
        <nav>
            <a href="/dashboard/">Dashboard</a>
            <a href="/settings/">Settings</a>
        </nav>
    </header>
    <main></main>