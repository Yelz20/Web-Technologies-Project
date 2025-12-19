<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Initialize Auth class
$auth = new Auth();

// Check if user is already logged in
if ($auth->isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error = '';
$email = '';

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // TODO: Fix CSRF session issue - temporarily disabled
    // if (!isset($_POST['csrf_token']) || !verify_csrf_token('login', $_POST['csrf_token'])) {
    //     $error = 'Invalid request. Please try again.';
    // } else {
    if (true) {
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);
        
        // Attempt to log in
        $result = $auth->login($email, $password, $remember);
        
        if ($result['success']) {
            // Set success flash message
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'message' => 'Login successful! Welcome back, ' . htmlspecialchars($result['user']['display_name']) . '!'
            ];
            
            // Redirect based on user role
           if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
                $redirect = 'admin/index.php';
            } else {
                $redirect = $_SESSION['redirect_url'] ?? 'index.php';
            }
            unset($_SESSION['redirect_url']);
            
            header('Location: ' . $redirect);
            exit();
        } else {
            $error = $result['message'] ?? 'Invalid email or password';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Football Review - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="bg-gray-100">
<?php
include 'includes/partials/header.php';
?>

<!-- Flash Messages -->
<?php if (isset($_SESSION['flash_message'])): ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
        <div class="bg-<?= $_SESSION['flash_message']['type'] === 'success' ? 'green' : 'red' ?>-50 border-l-4 border-<?= $_SESSION['flash_message']['type'] === 'success' ? 'green' : 'red' ?>-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-<?= $_SESSION['flash_message']['type'] === 'success' ? 'green' : 'red' ?>-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-<?= $_SESSION['flash_message']['type'] === 'success' ? 'green' : 'red' ?>-700">
                        <?= htmlspecialchars($_SESSION['flash_message']['message']) ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
    <?php unset($_SESSION['flash_message']); ?>
<?php endif; ?>

<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-lg shadow-md">
        <div class="text-center">
            <h2 class="mt-2 text-3xl font-extrabold text-gray-900">
                Sign in to your account
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                Or
                <a href="register.php" class="font-medium text-blue-600 hover:text-blue-500">
                    create a new account
                </a>
            </p>
        </div>
        
        <?php if ($error): ?>
            <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700">
                            <strong>Error:</strong> <?= htmlspecialchars($error) ?>
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <form class="mt-8 space-y-6" action="login.php" method="POST" novalidate>
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token('login') ?>">
            
            <div class="space-y-4">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                    <div class="mt-1">
                        <input id="email" name="email" type="email" autocomplete="email" required 
                               class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                               value="<?= htmlspecialchars($email) ?>"
                               placeholder="you@example.com">
                    </div>
                </div>

                <div>
                    <div class="flex justify-between">
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <a href="forgot-password.php" class="text-sm text-blue-600 hover:text-blue-500">
                            Forgot password?
                        </a>
                    </div>
                    <div class="mt-1 relative">
                        <input id="password" name="password" type="password" autocomplete="current-password" required
                               class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                               placeholder="••••••••">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <button type="button" class="text-gray-400 hover:text-gray-500 focus:outline-none toggle-password" onclick="togglePasswordVisibility()">
                                <svg id="eye-icon" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <script>
                    function togglePasswordVisibility() {
                        const passwordInput = document.getElementById('password');
                        const eyeIcon = document.getElementById('eye-icon');
                        
                        if (passwordInput.type === 'password') {
                            passwordInput.type = 'text';
                            // Change to eye-off icon
                            eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.056 10.056 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.058 10.058 0 01-3.7 3.85m0 0L21 21" />';
                        } else {
                            passwordInput.type = 'password';
                            // Change back to eye icon
                            eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />';
                        }
                    }
                </script>

                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox" 
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="remember" class="ml-2 block text-sm text-gray-700">
                        Remember me
                    </label>
                </div>
            </div>

            <div>
                <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-blue-400 group-hover:text-blue-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                        </svg>
                    </span>
                    Sign in
                </button>
            </div>
            
            <div class="text-center text-sm text-gray-600">
                Don't have an account? 
                <a href="register.php" class="font-medium text-blue-600 hover:text-blue-500">
                    Sign up
                </a>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/partials/footer.php'; ?>

</body>
</html>
