<?php
// widgets/debug_info.php

// Widget Name: Debug Info Stream
// Widget Icon: fas fa-bug
// Widget Width: 2.0
// Widget Height: 2.0

// Shared content for both views (time)
$time_content = '<h4 class="text-xl font-bold text-[var(--accent)] mb-2">Current Time:</h4>';
$time_content .= '<pre class="neomorphic-card p-3 mb-4 text-sm text-[var(--text-primary)]">' . htmlspecialchars(date('Y-m-d H:i:s')) . '</pre>';

// Session data content
$session_content = '<h4 class="text-xl font-bold text-[var(--accent)] mb-2">$_SESSION Data:</h4>';
$session_content .= '<pre class="neomorphic-card p-3 mb-4 text-sm text-[var(--text-primary)]">';
if (isset($_SESSION) && !empty($_SESSION)) {
    $display_session = $_SESSION;
    unset($display_session['PHPSESSID']);
    $session_content .= htmlspecialchars(print_r($display_session, true));
} else {
    $session_content .= 'Session is empty or not started.';
}
$session_content .= '</pre>';

// POST data content
$post_content = '<h4 class="text-xl font-bold text-[var(--accent)] mb-2">$_POST Data (Last Request):</h4>';
$post_content .= '<pre class="neomorphic-card p-3 mb-4 text-sm text-[var(--text-primary)]">';
if (isset($_POST) && !empty($_POST)) {
    $post_content .= htmlspecialchars(print_r($_POST, true));
} else {
    $post_content .= 'No POST data received in the last request.';
}
$post_content .= '</pre>';
?>
<div class="p-4 h-full flex flex-col overflow-y-auto">
    <div class="compact-content">
        <?= $time_content ?>
        <?= $session_content ?>
    </div>
    <div class="expanded-content">
        <?= $time_content ?>
        <?= $session_content ?>
        <?= $post_content ?>
    </div>
</div>
