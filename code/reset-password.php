<?php
// reset-password.php
require_once 'includes/config.php';

$error = '';
$success = false;
$token = $_GET['token'] ?? '';

// Validate token
if (empty($token)) {
    $error = 'Invalid or expired reset link';
} else {
    $db = Database::getInstance()->getConnection();
    
    // Check if token is valid and not expired
    $stmt = $db->prepare("
        SELECT email 
        FROM password_resets 
        WHERE token = ? AND used = 0 AND expires_at > NOW()
    ");
    $stmt->execute([$token]);
    $reset = $stmt->fetch();
    
    if (!$reset) {
        $error = 'Invalid or expired reset link';
    } else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validate input
        if (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters long';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match';
        } else {
            try {
                $db->beginTransaction();
                
                // Update password
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
                $stmt->execute([$password_hash, $reset['email']]);
                
                // Mark token as used
                $stmt = $db->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
                $stmt->execute([$token]);
                
                $db->commit();
                $success = true;
                
            } catch (Exception $e) {
                $db->rollBack();
                $error = 'An error occurred while resetting your password';
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
    <title>Football Review - Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="bg-gray-100">
<?php
include 'includes/partials/header.php';
?>

<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Reset your password
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Enter your new password below.
            </p>
        </div>
        
        <?php if ($error): ?>
            <div class="bg-red-50 border-l-4 border-red-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700"><?= htmlspecialchars($error) ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="bg-green-50 border-l-4 border-green-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700">Your password has been reset successfully. You can now <a href="login.php" class="font-medium text-green-700 hover:text-green-600">log in</a> with your new password.</p>
                    </div>
                </div>
            </div>
        <?php elseif (empty($error)): ?>
            <form class="mt-8 space-y-6" action="reset-password.php?token=<?= htmlspecialchars($token) ?>" method="POST">
                <div class="rounded-md shadow-sm -space-y-px">
                    <div>
                        <label for="password" class="sr-only">New Password</label>
                        <input id="password" name="password" type="password" required
                               class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                               placeholder="New Password">
                    </div>
                    <div>
                        <label for="confirm_password" class="sr-only">Confirm New Password</label>
                        <input id="confirm_password" name="confirm_password" type="password" required
                               class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                               placeholder="Confirm New Password">
                    </div>
                </div>

                <div>
                    <button type="submit"
                            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Reset Password
                    </button>
                </div>
            </form>
        <?php endif; ?>
        
        <div class="text-center">
            <a href="login.php" class="font-medium text-blue-600 hover:text-blue-500">
                Back to login
            </a>
        </div>
    </div>
</div>

<?php include 'includes/partials/footer.php'; ?>
<script src="assets/js/main.js"></script>
</body>
</html>
