<?php
/**
 * Calculates how much time has passed since a given date turning a timestamp into a friendly phrase like "2 hours ago".
 */
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    // Calculate weeks manually as $diff->w
    $weeks = floor($diff->days / 7);
    $diff->d -= $weeks * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    
    foreach ($string as $k => &$v) {
        if ($k === 'w') {
            $value = $weeks;
        } else {
            $value = $diff->$k;
        }

        if ($value) {
            $v = $value . ' ' . $v . ($value > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}

// Cleans up user input to prevent any unwanted code or malicious characters from reaching our database.
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Check if a logged-in user session active.
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Identifies if the current user has administrative privileges.
function is_admin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

// Redirect to a specific page
function redirect($url) {
    header("Location: " . BASE_URL . "/$url");
    exit();
}

/**
 * Generate CSRF token
 */
/**
 * Generate CSRF token
 */
function generate_csrf_token($action = 'default') {
    if (!isset($_SESSION['csrf_tokens'])) {
        $_SESSION['csrf_tokens'] = [];
    }
    if (empty($_SESSION['csrf_tokens'][$action])) {
        $_SESSION['csrf_tokens'][$action] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_tokens'][$action];
}

/**
 * Verify CSRF token
 */
function verify_csrf_token($action, $token) {
    if (!isset($_SESSION['csrf_tokens'][$action]) || $token !== $_SESSION['csrf_tokens'][$action]) {
        return false;
    }
    return true;
}

/**
 * Format match score
 */
function format_score($home_score, $away_score, $status = 'finished') {
    if ($status !== 'finished') {
        return 'VS';
    }
    return $home_score . ' - ' . $away_score;
}

// Looks for a user's chosen avatar and falls back to a colorful initial if they haven't uploaded one yet.
function get_avatar_url($user_avatar, $size = 100, $name_fallback = null) {
    if (!empty($user_avatar)) {
        return $user_avatar;
    }
    $name = $name_fallback ?? $_SESSION['username'] ?? 'User';
    // Default avatar from UI Faces with single initial
    return "https://ui-avatars.com/api/?name=" . urlencode($name) . "&size=$size&background=random&length=1";
}

// Handle file uploads, make sure the images are the right size, and type before saving them to the server.
function upload_file($file, $target_dir, $allowed_types = ['jpg', 'jpeg', 'png', 'gif']) {
    $errors = [];
    $file_name = basename($file['name']);
    $target_file = rtrim($target_dir, '/') . '/' . $file_name;
    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Generate unique filename
    $new_filename = uniqid() . '.' . $file_type;
    $target_file = rtrim($target_dir, '/') . '/' . $new_filename;
    
    // Check file type
    if (!in_array($file_type, $allowed_types)) {
        $errors[] = "Sorry, only " . implode(', ', $allowed_types) . " files are allowed.";
    }
    
    // Check file size (5MB max)
    if ($file['size'] > 5 * 1024 * 1024) {
        $errors[] = "Sorry, your file is too large. Maximum size is 5MB.";
    }
    
    // Check for errors
    if (empty($errors)) {
        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            return [
                'success' => true,
                'filename' => $new_filename,
                'path' => $target_file
            ];
        } else {
            $errors[] = "Sorry, there was an error uploading your file.";
        }
    }
    
    return [
        'success' => false,
        'errors' => $errors
    ];
}

// Generates the page numbers at the bottom of the lists.
function get_pagination($total_items, $items_per_page, $current_page, $base_url) {
    $total_pages = ceil($total_items / $items_per_page);
    $pagination = [
        'current_page' => $current_page,
        'total_pages' => $total_pages,
        'has_previous' => $current_page > 1,
        'has_next' => $current_page < $total_pages,
        'pages' => []
    ];
    
    // Always show first page
    $pagination['pages'][] = [
        'number' => 1,
        'is_current' => $current_page == 1,
        'url' => $base_url . '?page=1'
    ];
    
    // Calculate range of pages to show
    $start = max(2, $current_page - 2);
    $end = min($total_pages - 1, $current_page + 2);
    
    // Add ellipsis if needed
    if ($start > 2) {
        $pagination['pages'][] = ['ellipsis' => true];
    }
    
    // Add page numbers in range
    for ($i = $start; $i <= $end; $i++) {
        $pagination['pages'][] = [
            'number' => $i,
            'is_current' => $i == $current_page,
            'url' => $base_url . '?page=' . $i
        ];
    }
    
    // Add ellipsis if needed
    if ($end < $total_pages - 1) {
        $pagination['pages'][] = ['ellipsis' => true];
    }
    
    // Always show last page if there is more than one page
    if ($total_pages > 1) {
        $pagination['pages'][] = [
            'number' => $total_pages,
            'is_current' => $current_page == $total_pages,
            'url' => $base_url . '?page=' . $total_pages
        ];
    }
    
    return $pagination;
}
/**
 * Get the full URL for a team or competition logo
 * @param string $logo Logo filename or URL
 * @return string Full URL
 */
function get_logo_url($logo) {
    if (!$logo) return '';
    
    // If it's already a full URL (including http/https or starting with //), return it
    if (preg_match('/^https?:\/\//i', $logo) || strpos($logo, '//') === 0) {
        return $logo;
    }
    
    // If the path starts with assets/, it's already a relative path we can use
    if (strpos($logo, 'assets/') === 0) {
        return BASE_URL . '/' . $logo;
    }
    
    // If it's just a filename, we check if it's a league or a team (legacy support)
    $leagues = ['premier_league', 'laliga', 'bundesliga', 'serie_a', 'ligue_1'];
    $filename_no_ext = pathinfo($logo, PATHINFO_FILENAME);
    
    if (in_array($filename_no_ext, $leagues)) {
        return BASE_URL . '/assets/images/leagues/' . $logo;
    }
    
    return BASE_URL . '/assets/images/teams/' . $logo;
}
