<?php
// Render each card in /cards/
foreach (scandir(__DIR__ . '/../cards/') as \$file) {
    if (preg_match('/Card\.php$/', \$file)) {
        include __DIR__ . '/../cards/' . \$file;
    }
}
?>