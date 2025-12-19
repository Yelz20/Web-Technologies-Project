<?php
// Admin authentication check
require_once dirname(__DIR__) . '/includes/config.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: ../login.php');
    exit();
}

// Check if user is admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}
?>
