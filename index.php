<?php declare(strict_types=1);
// index.php — Front Controller

// 1) Global header (styles, scripts, theme toggle, debug, etc.)
require __DIR__ . '/includes/header.php';

// 2) Navigation bar (customer dropdown)
require __DIR__ . '/includes/navigation.php';

// 3) Preferences modal toggle & markup
require __DIR__ . '/components/preferences-modal.php';

// 4) Main dashboard view, which pulls in each card
require __DIR__ . '/views/dashboard.php';

// 5) Global footer (modals close, end body/html)
require __DIR__ . '/includes/footer.php';
