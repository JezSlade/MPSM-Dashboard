<?php
// PATCHED: session_vars.php v4.0 – TokenManager Integration

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../backend/core/TokenManager.php';
$token = TokenManager::getToken();

// Apply inline style to override drag-priority wrappers
echo "<div class='container p-4' style='user-select: text;'>";

// 🔐 MPS Token Card
echo "<div class='card shadow-xl mb-4 bg-dark text-light'>";
    echo "<div class='card-header bg-gradient text-info fw-bold'>"
        . "<i class='fa fa-key me-2'></i>MPS Token"
        . "</div>";
    echo "<div class='card-body'><pre class='text-success small' style='user-select: text;'>";
    echo htmlspecialchars($token);
    echo "</pre></div>";
echo "</div>";

ksort($_SESSION);
echo "<div class='accordion' id='sessionAccordion' style='user-select: text;'>";
$index = 0;
foreach ($_SESSION as $key => $value) {
    $collapseId = 'collapse' . $index++;
    echo "<div class='accordion-item bg-glassmorph mb-2 rounded-3xl shadow' style='user-select: text;'>";
        echo "<h2 class='accordion-header' id='heading$collapseId'>";
            echo "<button class='accordion-button collapsed bg-zinc-900 text-cyan-300 fw-semibold' type='button' data-bs-toggle='collapse' data-bs-target='#$collapseId' aria-expanded='false' aria-controls='$collapseId'>";
            echo "<i class='fa fa-database me-2'></i>$key";
            echo "</button>";
        echo "</h2>";
        echo "<div id='$collapseId' class='accordion-collapse collapse' aria-labelledby='heading$collapseId' data-bs-parent='#sessionAccordion'>";
            echo "<div class='accordion-body bg-zinc-800 text-warning small rounded-bottom-3xl' style='user-select: text;'>";
                echo "<pre class='overflow-x-auto' style='user-select: text;'>";
                print_r($value);
                echo "</pre>";
            echo "</div>";
        echo "</div>";
    echo "</div>";
}
echo "</div></div>";
?>