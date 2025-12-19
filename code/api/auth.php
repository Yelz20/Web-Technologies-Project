<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

$auth = new Auth();
$response = ['success' => false, 'message' => ''];

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Handle different request methods
switch ($method) {
    case 'POST':
        // Handle login/register based on action
        $data = json_decode(file_get_contents('php://input'), true);
        $action = $_GET['action'] ?? '';
        
        if ($action === 'login') {
            // Handle login
            if (!isset($data['email']) || !isset($data['password'])) {
                http_response_code(400);
                $response['message'] = 'Email and password are required';
                break;
            }
            
            $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
            $password = $data['password'];
            $remember = $data['remember'] ?? false;
            
            $result = $auth->login($email, $password, $remember);
            
            if ($result['success']) {
                $response = [
                    'success' => true,
                    'message' => 'Login successful',
                    'user' => [
                        'id' => $result['user']['id'],
                        'email' => $result['user']['email'],
                        'display_name' => $result['user']['display_name'],
                        'role' => $result['user']['role']
                    ]
                ];
            } else {
                http_response_code(401);
                $response['message'] = $result['message'] ?? 'Invalid email or password';
            }
        } 
        elseif ($action === 'register') {
            // Handle registration
            $required = ['email', 'password', 'display_name'];
            $missing = array_diff($required, array_keys($data));
            
            if (!empty($missing)) {
                http_response_code(400);
                $response['message'] = 'Missing required fields: ' . implode(', ', $missing);
                break;
            }
            
            $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
            $password = $data['password'];
            $displayName = trim($data['display_name']);
            
            $result = $auth->register($email, $displayName, $password);
            
            if ($result['success']) {
                $response = [
                    'success' => true,
                    'message' => 'Registration successful',
                    'userId' => $result['userId']
                ];
            } else {
                http_response_code(400);
                $response['message'] = $result['message'] ?? 'Registration failed';
            }
        } 
        elseif ($action === 'logout') {
            // Handle logout
            $auth->logout();
            $response = [
                'success' => true,
                'message' => 'Logout successful'
            ];
        } 
        else {
            http_response_code(400);
            $response['message'] = 'Invalid action';
        }
        break;
        
    case 'GET':
        // Handle session check
        if ($auth->isLoggedIn()) {
            $user = $auth->getCurrentUser();
            $response = [
                'success' => true,
                'authenticated' => true,
                'user' => [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'display_name' => $user['display_name'],
                    'role' => $user['role']
                ]
            ];
        } else {
            $response = [
                'success' => true,
                'authenticated' => false
            ];
        }
        break;
        
    default:
        http_response_code(405);
        $response['message'] = 'Method not allowed';
        break;
}

echo json_encode($response);
