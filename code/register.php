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

$errors = [];
$formData = [
    'email' => '',
    'display_name' => ''
];

// Process registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (true) {
        // Get and sanitize form data
        $formData['email'] = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $formData['display_name'] = trim($_POST['display_name'] ?? '');
        $formData['role'] = $_POST['role'] ?? 'fan';
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $terms = isset($_POST['terms']);
        
        // Validate input
        if (empty($formData['email'])) {
            $errors[] = 'Email address is required';
        } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address';
        }
        
        if (empty($formData['display_name'])) {
            $errors[] = 'Display name is required';
        } elseif (strlen($formData['display_name']) < 3) {
            $errors[] = 'Display name must be at least 3 characters long';
        } elseif (!preg_match('/^[a-zA-Z0-9_\-\s]+$/', $formData['display_name'])) {
            $errors[] = 'Display name can only contain letters, numbers, spaces, hyphens, and underscores';
        }
        
        if (empty($password)) {
            $errors[] = 'Password is required';
        } elseif (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long';
        } elseif (!preg_match('/[A-Z]/', $password) || 
                 !preg_match('/[a-z]/', $password) || 
                 !preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter, one lowercase letter, and one number';
        } elseif ($password !== $confirm_password) {
            $errors[] = 'Passwords do not match';
        }
        
        if (!$terms) {
            $errors[] = 'You must accept the Terms of Service and Privacy Policy';
        }
        
        if (empty($errors)) {
            // Attempt to register the user
            $result = $auth->register($formData['email'], $formData['display_name'], $password, $formData['role']);
            
            if ($result['success']) {
                // Set success message
                $_SESSION['flash_message'] = [
                    'type' => 'success',
                    'message' => 'Registration successful! You can now log in.'
                ];
                
                // Redirect to login page
                header('Location: login.php');
                exit();
            } else {
                $errors[] = $result['message'] ?? 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Football Review - Register</title>
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
                Create your account
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                Join our community of football enthusiasts
            </p>
        </div>
        
        <?php if (!empty($errors)): ?>
            <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">There were problems with your submission</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <ul class="list-disc pl-5 space-y-1">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <form class="mt-8 space-y-6" action="register.php" method="POST" novalidate>
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token('register') ?>">
            
            <div class="space-y-4">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                    <div class="mt-1">
                        <input id="email" name="email" type="email" autocomplete="email" required
                               class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                               value="<?= htmlspecialchars($formData['email']) ?>"
                               placeholder="you@example.com">
                    </div>
                </div>

                <div>
                    <label for="display_name" class="block text-sm font-medium text-gray-700">Display Name</label>
                    <div class="mt-1">
                        <input id="display_name" name="display_name" type="text" autocomplete="username" required
                               class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                               value="<?= htmlspecialchars($formData['display_name']) ?>"
                               placeholder="Your username">
                    </div>
                    <p class="mt-1 text-xs text-gray-500">This will be visible to other users</p>
                </div>

                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700">I am a</label>
                    <div class="mt-1">
                        <select id="role" name="role" required
                                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="fan" <?= (isset($formData['role']) && $formData['role'] === 'fan') ? 'selected' : '' ?>>Fan (Customer)</option>
                            <option value="admin" <?= (isset($formData['role']) && $formData['role'] === 'admin') ? 'selected' : '' ?>>Administrator</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <div class="mt-1 relative">
                        <input id="password" name="password" type="password" autocomplete="new-password" required
                               class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                               placeholder="••••••••">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <button type="button" class="text-gray-400 hover:text-gray-500 focus:outline-none toggle-password">
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Must be at least 8 characters with uppercase, lowercase, and a number</p>
                </div>

                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                    <div class="mt-1 relative">
                        <input id="confirm_password" name="confirm_password" type="password" autocomplete="new-password" required
                               class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                               placeholder="••••••••">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <button type="button" class="text-gray-400 hover:text-gray-500 focus:outline-none toggle-password">
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input id="terms" name="terms" type="checkbox" required
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="terms" class="font-medium text-gray-700">I agree to the <a href="terms.php" target="_blank" class="text-blue-600 hover:text-blue-500">Terms of Service</a> and <a href="privacy.php" target="_blank" class="text-blue-600 hover:text-blue-500">Privacy Policy</a></label>
                    </div>
                </div>
            </div>

            <div>
                <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-blue-400 group-hover:text-blue-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                        </svg>
                    </span>
                    Create Account
                </button>
            </div>
            
            <div class="text-center text-sm text-gray-600">
                Already have an account? 
                <a href="login.php" class="font-medium text-blue-600 hover:text-blue-500">
                    Sign in
                </a>
            </div>
        </form>
        
    </div>
    </div>
</div>

<?php include 'includes/partials/footer.php'; ?>

</body>
</html>

<script>
// Toggle password visibility
document.querySelectorAll('.toggle-password').forEach(button => {
    button.addEventListener('click', function() {
        const input = this.closest('.relative').querySelector('input');
        const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
        input.setAttribute('type', type);
        
        // Toggle icon
        const icon = this.querySelector('svg');
        if (type === 'password') {
            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />';
        } else {
            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />';
        }
    });
});

// Password strength indicator
const passwordInput = document.getElementById('password');
const passwordStrength = document.createElement('div');
passwordStrength.className = 'mt-1 h-1 rounded-full overflow-hidden';
passwordInput.parentNode.insertBefore(passwordStrength, passwordInput.nextSibling);

passwordInput.addEventListener('input', function() {
    const password = this.value;
    let strength = 0;
    
    // Length check
    if (password.length >= 8) strength += 1;
    // Contains lowercase
    if (/[a-z]/.test(password)) strength += 1;
    // Contains uppercase
    if (/[A-Z]/.test(password)) strength += 1;
    // Contains number
    if (/[0-9]/.test(password)) strength += 1;
    // Contains special character
    if (/[^A-Za-z0-9]/.test(password)) strength += 1;
    
    // Update strength indicator
    const colors = ['bg-red-500', 'bg-yellow-500', 'bg-yellow-400', 'bg-blue-500', 'bg-green-500'];
    const width = (strength / 5) * 100;
    
    passwordStrength.innerHTML = `
        <div class="h-full ${colors[strength - 1] || 'bg-gray-200'}" style="width: ${width}%; transition: width 0.3s ease;"></div>
    `;
});
</script>
