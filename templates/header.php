<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(APP_NAME) ?> Dashboard</title>
    <link rel="stylesheet" href="/dashboard/style.css">
    <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
    <script>
        const APP_CONFIG = {
            baseUrl: '<?= APP_BASE_URL ?>',
            csrfToken: '<?= $_SESSION['csrf_token'] ?? '' ?>'
        };
    </script>

    <!-- GridStack CSS and fixed JS -->
    <link href="https://cdn.jsdelivr.net/npm/gridstack@8.2.1/dist/gridstack.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/gridstack@8.2.1/dist/gridstack-all.js"></script>

    <!-- Favicon fix -->
    <link rel="icon" href="/assets/favicon.ico" type="image/x-icon">
</head>
<body>
    <header>
        <h1><?= htmlspecialchars(APP_NAME) ?></h1>
        <nav>
            <a href="/dashboard/">Dashboard</a>
            <a href="/settings/">Settings</a>
        </nav>
    </header>
    <main>
