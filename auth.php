<?php
// auth.php
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function logout() {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}
?>