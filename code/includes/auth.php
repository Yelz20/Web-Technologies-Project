<?php
require_once __DIR__ . '/config.php';


class Auth {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Register a new user
     * 
     * @param string $email User's email
     * @param string $displayName User's display name
     * @param string $password User's password
     * @return array Registration result with success status and message/user ID
     */
    public function register($email, $displayName, $password, $role = 'fan') {
        // Validate input
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email format'];
        }
        
        if (strlen($password) < 8) {
            return ['success' => false, 'message' => 'Password must be at least 8 characters long'];
        }

        // Check password strength
        if (!preg_match('/[A-Z]/', $password) || 
            !preg_match('/[a-z]/', $password) || 
            !preg_match('/[0-9]/', $password)) {
            return ['success' => false, 'message' => 'Password must contain at least one uppercase letter, one lowercase letter, and one number'];
        }

        // Check if email already exists
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'Email already registered'];
        }

        // Hash password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        // Start transaction
        $this->db->beginTransaction();
        
        try {
            // Insert new user
            $stmt = $this->db->prepare("
                INSERT INTO users (email, display_name, password_hash, role, created_at, updated_at) 
                VALUES (?, ?, ?, ?, NOW(), NOW())
            ");
            $stmt->execute([$email, $displayName, $passwordHash, $role]);
            $userId = $this->db->lastInsertId();
            
            // Create user profile
            $this->createUserProfile($userId);
            
            // Commit transaction
            $this->db->commit();
            
            return ['success' => true, 'userId' => $userId];
            
        } catch (PDOException $e) {
            // Rollback on error
            $this->db->rollBack();
            error_log('Registration error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Registration failed. Please try again.'];
        }
    }

    /**
     * Create a user profile
     * 
     * @param int $userId The user ID
     * @return bool True if successful, false otherwise
     */
    private function createUserProfile($userId) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO user_profiles (user_id, created_at, updated_at)
                VALUES (?, NOW(), NOW())
            ");
            return $stmt->execute([$userId]);
        } catch (PDOException $e) {
            error_log('Error creating user profile: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Log in a user with email and password
     * 
     * @param string $email User's email
     * @param string $password User's password
     * @param bool $remember Whether to remember the user
     * @return array Login result with success status and message/user data
     */
    public function login($email, $password, $remember = false) {
        // Validate input
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email format'];
        }
        
        // Get user by email
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Verify user exists and password is correct
        if (!$user || !password_verify($password, $user['password_hash'])) {
            // Log failed login attempt
            $this->logFailedLoginAttempt($email);
            return ['success' => false, 'message' => 'Invalid email or password'];
        }
        
        // Check if account is locked
        if ($this->isAccountLocked($user['id'])) {
            return ['success' => false, 'message' => 'Account is temporarily locked. Please try again later.'];
        }
        
        // Reset failed login attempts on successful login
        $this->resetFailedLoginAttempts($user['id']);
        
        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);
        
        // Set session variables
        $_SESSION = [];
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['display_name'] = $user['display_name'];
        $_SESSION['last_activity'] = time();
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
        
        // Set remember me cookie if requested
        if ($remember) {
            $this->setRememberMeCookie($user['id']);
        }
        
        // Update last login time
        $this->updateLastLogin($user['id']);
        
        return ['success' => true, 'user' => $user];
    }
    
    /**
     * Log a failed login attempt
     * 
     * @param string $email The email that failed to log in
     */
    private function logFailedLoginAttempt($email) {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET login_attempts = login_attempts + 1, 
                last_failed_login = NOW(),
                updated_at = NOW()
            WHERE email = ?
        ");
        $stmt->execute([$email]);
        
        // Log the failed attempt for security monitoring
        error_log("Failed login attempt for email: " . $email . " from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    }
    
    /**
     * Check if an account is locked due to too many failed login attempts
     * 
     * @param int $userId The user ID to check
     * @return bool True if account is locked, false otherwise
     */
    private function isAccountLocked($userId) {
        $stmt = $this->db->prepare("
            SELECT login_attempts, last_failed_login 
            FROM users 
            WHERE id = ?
        ");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // If more than 5 failed attempts in the last 30 minutes, lock the account
        if ($user && $user['login_attempts'] >= 5) {
            $lastAttempt = strtotime($user['last_failed_login']);
            $lockoutTime = 30 * 60; // 30 minutes in seconds
            
            // If lockout period hasn't expired yet
            if (time() - $lastAttempt < $lockoutTime) {
                return true;
            } else {
                // Reset the counter if the lockout period has expired
                $this->resetFailedLoginAttempts($userId);
            }
        }
        
        return false;
    }
    
    /**
     * Reset failed login attempts for a user
     * 
     * @param int $userId The user ID to reset attempts for
     */
    private function resetFailedLoginAttempts($userId) {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET login_attempts = 0, 
                last_failed_login = NULL,
                updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$userId]);
    }
    
    /**
     * Set a remember me cookie for persistent login
     * 
     * @param int $userId The user ID to remember
     */
    private function setRememberMeCookie($userId) {
        // Generate a random token
        $token = bin2hex(random_bytes(32));
        $selector = bin2hex(random_bytes(8));
        $hashedToken = password_hash($token, PASSWORD_DEFAULT);
        
        // Set expiration to 30 days from now
        $expires = time() + (30 * 24 * 60 * 60);
        $expiresDb = date('Y-m-d H:i:s', $expires);
        
        // Delete any existing tokens for this user
        $this->db->prepare("DELETE FROM user_tokens WHERE user_id = ?")->execute([$userId]);
        
        // Store token in database
        $stmt = $this->db->prepare("
            INSERT INTO user_tokens (user_id, selector, token_hash, expires_at, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$userId, $selector, $hashedToken, $expiresDb]);
        
        // Set cookie (httpOnly for security)
        $cookieOptions = [
            'expires' => $expires,
            'path' => '/',
            'domain' => '',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Strict'
        ];
        
        setcookie('remember_me', $selector . ':' . $token, $cookieOptions);
    }
    
    /**
     * Update the last login time for a user
     * 
     * @param int $userId The user ID to update
     */
    private function updateLastLogin($userId) {
        $stmt = $this->db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$userId]);
    }

    /**
     * Log in a user from remember me cookie
     * 
     * @return bool True if login was successful, false otherwise
     */
    private function loginFromRememberMeCookie() {
        if (!isset($_COOKIE['remember_me'])) {
            return false;
        }
        
        $parts = explode(':', $_COOKIE['remember_me']);
        if (count($parts) !== 2) {
            setcookie('remember_me', '', time() - 3600, '/');
            return false;
        }
        
        list($selector, $token) = $parts;
        
        // Get token from database
        $stmt = $this->db->prepare("
            SELECT t.*, u.* 
            FROM user_tokens t
            JOIN users u ON t.user_id = u.id
            WHERE t.selector = ? AND t.expires_at > NOW()
        ");
        $stmt->execute([$selector]);
        $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($tokenData && password_verify($token, $tokenData['token_hash'])) {
            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);
            
            // Set session variables
            $_SESSION = [];
            $_SESSION['user_id'] = $tokenData['user_id'];
            $_SESSION['user_email'] = $tokenData['email'];
            $_SESSION['user_role'] = $tokenData['role'];
            $_SESSION['display_name'] = $tokenData['display_name'];
            $_SESSION['last_activity'] = time();
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
            $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
            
            // Update last login
            $this->updateLastLogin($tokenData['user_id']);
            
            // Generate new token for next time (refresh token)
            $this->setRememberMeCookie($tokenData['user_id']);
            
            return true;
        }
        
        // Invalid token, clear the cookie
        setcookie('remember_me', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
        
        // Delete the invalid token from database
        if (isset($selector)) {
            $this->db->prepare("DELETE FROM user_tokens WHERE selector = ?")->execute([$selector]);
        }
        
        return false;
    }

    /**
     * Check if a user is logged in
     * 
     * @return bool True if user is logged in, false otherwise
     */
    public function isLoggedIn() {
        // Check session
        if (isset($_SESSION['user_id'])) {
            // Validate session to prevent session fixation
            if ($_SESSION['user_agent'] !== ($_SERVER['HTTP_USER_AGENT'] ?? '') || 
                $_SESSION['ip_address'] !== ($_SERVER['REMOTE_ADDR'] ?? '')) {
                $this->logout();
                return false;
            }
            
            // Check for session timeout (30 minutes)
            if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
                $this->logout();
                return false;
            }
            
            // Update last activity time
            $_SESSION['last_activity'] = time();
            return true;
        }
        
        // Check remember me cookie
        if (isset($_COOKIE['remember_me'])) {
            return $this->loginFromRememberMeCookie();
        }
        
        return false;
    }

    /**
     * Require the user to be logged in
     * 
     * @param string $redirect URL to redirect to if not logged in
     * @param bool $return Whether to return false instead of redirecting
     * @return bool True if user is logged in, false otherwise
     */
    public function requireLogin($redirect = 'login.php', $return = false) {
        if (!$this->isLoggedIn()) {
            if ($return) {
                return false;
            }
            
            // Store the current URL for redirecting back after login
            $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
            
            // Set a flash message
            $_SESSION['flash_message'] = [
                'type' => 'warning',
                'message' => 'Please log in to access this page.'
            ];
            
            header("Location: $redirect");
            exit();
        }
        return true;
    }

    /**
     * Require the user to have a specific role
     * 
     * @param string|array $roles Role or array of roles to check
     * @param string $redirect URL to redirect to if user doesn't have the role
     * @param bool $return Whether to return false instead of redirecting
     * @return bool True if user has the role, false otherwise
     */
    public function requireRole($roles, $redirect = '403.php', $return = false) {
        if (!$this->requireLogin($redirect, $return)) {
            return false;
        }
        
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        
        if (!in_array($_SESSION['user_role'], $roles)) {
            if ($return) {
                return false;
            }
            
            // Set a flash message
            $_SESSION['flash_message'] = [
                'type' => 'error',
                'message' => 'You do not have permission to access this page.'
            ];
            
            header("Location: $redirect");
            exit();
        }
        
        return true;
    }

    /**
     * Require the user to be an admin
     * 
     * @param string $redirect URL to redirect to if user is not an admin
     * @param bool $return Whether to return false instead of redirecting
     * @return bool True if user is an admin, false otherwise
     */
    public function requireAdmin($redirect = '403.php', $return = false) {
        return $this->requireRole('admin', $redirect, $return);
    }

    /**
     * Get the current user
     * 
     * @return array|null User data or null if not logged in
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        $stmt = $this->db->prepare("SELECT id, email, display_name, role, created_at FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    }

    /**
     * Log out the current user
     */
    public function logout() {
        // Unset all session variables
        $_SESSION = array();
        
        // Delete the session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destroy the session
        session_destroy();
    }

}
