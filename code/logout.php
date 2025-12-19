<?php
session_start();
$_SESSION = array();

// Delete session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Delete remember me cookie to prevent auto-login
if (isset($_COOKIE['remember_me'])) {
    setcookie('remember_me', '', time() - 3600, '/');
}

session_destroy();

// Force redirect to login page
header('Location: login.php');
exit();
?>
