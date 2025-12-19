<?php
// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Use default session path to avoid permission issues with custom directories
    session_start();
}

// Base URL configuration - Robust calculation for subdirectories and ~user environments
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptName = $_SERVER['SCRIPT_NAME']; // e.g. /~yelsom.sanid/FootballReview/admin/matches.php
$scriptFilename = $_SERVER['SCRIPT_FILENAME']; // e.g. /home/yelsom.sanid/public_html/FootballReview/admin/matches.php
$projectRoot = realpath(__DIR__ . '/..'); // The physical path to the project root

// Find where the project root starts within the current script's path
$relativeRootPath = str_replace(realpath($projectRoot), '', realpath($scriptFilename));
// $relativeRootPath will be something like "/admin/matches.php" or "/index.php"

// By removing the relative path from the full URL path, we get the absolute URL path to the project root
$projectUrlPath = str_replace($relativeRootPath, '', $scriptName);

define('BASE_URL', $protocol . '://' . $host . rtrim($projectUrlPath, '/'));
define('SITE_NAME', 'Football Review');

// File upload settings
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', BASE_URL . '/uploads/');

// User roles
define('DB_HOST', 'localhost');
define('DB_NAME', 'webtech_2025A_yelsom_sanid');
define('DB_USER', 'yelsom.sanid');
define('DB_PASS', 'esj^NiaTCJ');

define('ROLE_FAN', 'fan');
define('ROLE_ADMIN', 'admin');

// Set timezone
date_default_timezone_set('UTC');

// Include database class
require_once __DIR__ . '/database.php';

// Initialize database connection
try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Create uploads directory if it doesn't exist
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

// Include required files
// db.php is removed to prevent conflicts
require_once __DIR__ . '/functions.php';
