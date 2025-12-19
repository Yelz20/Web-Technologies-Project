<?php
/**
 * Redirect to a specific URL
 * @param string $url URL to redirect to
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Sanitize input data
 * @param string $data Data to be sanitized
 * @return string Sanitized data
 */
function sanitize($data) {
    global $conn;
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * Check if user is logged in
 * @return bool True if user is logged in, false otherwise
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Get user data from session
 * @return array|null User data or null if not logged in
 */
function get_user_data() {
    if (is_logged_in() && isset($_SESSION['user_data'])) {
        return $_SESSION['user_data'];
    }
    return null;
}

/**
 * Display flash message
 * @param string $type Type of message (success, error, warning, info)
 * @param string $message Message to display
 */
function set_flash_message($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Show flash message if exists
 */
function show_flash_message() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        echo '<div class="alert alert-' . $flash['type'] . '">' . $flash['message'] . '</div>';
        unset($_SESSION['flash']);
    }
}

/**
 * Generate a random token
 * @param int $length Length of the token
 * @return string Generated token
 */
function generate_token($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Format date to a more readable format
 * @param string $date Date string
 * @param string $format Output format (default: F j, Y)
 * @return string Formatted date
 */
function format_date($date, $format = 'F j, Y') {
    $date = new DateTime($date);
    return $date->format($format);
}

/**
 * Get the current page URL
 * @return string Current URL
 */
function current_url() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    return $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * Check if the current page is active
 * @param string $page Page to check
 * @return string 'active' if current page matches, empty string otherwise
 */
function is_active($page) {
    $current_page = basename($_SERVER['PHP_SELF']);
    return ($current_page == $page) ? 'active' : '';
}
