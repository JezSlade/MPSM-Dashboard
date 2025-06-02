<?php
// auth.php (auth temporarily disabled for development)

function require_login() {
    // Disabled: all users are treated as authenticated
    return true;
}

function is_logged_in(): bool {
    return true;
}

function login_user($username, $password): bool {
    return true;
}

function logout_user() {
    return;
}
