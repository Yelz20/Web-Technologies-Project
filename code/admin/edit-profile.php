<?php
require_once 'auth-check.php';

$db = Database::getInstance()->getConnection();
$user_id = $_SESSION['user_id'];
$errors = [];
$success = false;

// Get user data
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $display_name = trim($_POST['display_name'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate input
    if (strlen($display_name) < 3) {
        $errors[] = 'Display name must be at least 3 characters long';
    }
    
    // If changing password
    if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
        if (!password_verify($current_password, $user['password_hash'])) {
            $errors[] = 'Current password is incorrect';
        }
        
        if (strlen($new_password) < 8) {
            $errors[] = 'New password must be at least 8 characters long';
        } elseif ($new_password !== $confirm_password) {
            $errors[] = 'New passwords do not match';
        }
    }
    
    if (empty($errors)) {
        try {
            $db->beginTransaction();
            
            // Update display name
            $stmt = $db->prepare("UPDATE users SET display_name = ? WHERE id = ?");
            $stmt->execute([$display_name, $user_id]);
            
            // Update password if provided
            if (!empty($new_password)) {
                $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                $stmt->execute([$password_hash, $user_id]);
            }
            
            $db->commit();
            $success = true;
            
            // Update session
            $_SESSION['display_name'] = $display_name;
            
            // Redirect to profile page with success message
            $_SESSION['success_message'] = 'Profile updated successfully';
            header('Location: profile.php');
            exit();
            
        } catch (Exception $e) {
            $db->rollBack();
            $errors[] = 'An error occurred while updating your profile';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="bg-gray-100">
<?php include 'includes/header.php'; ?>

<div class="max-w-3xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h1 class="text-2xl font-bold text-gray-900">Edit Admin Profile</h1>
        </div>
        
        <?php if ($success): ?>
            <div class="bg-green-50 border-l-4 border-green-400 p-4 mx-6 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700">Your profile has been updated successfully.</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="bg-red-50 border-l-4 border-red-400 p-4 mx-6 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">There were errors with your submission</h3>
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

        <form class="space-y-6 px-4 py-5 sm:p-6" action="edit-profile.php" method="POST">
            <div class="space-y-6">
                <div>
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Profile Information</h3>
                    <p class="mt-1 text-sm text-gray-500">Update your account's profile information.</p>
                </div>

                <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                    <div class="sm:col-span-4">
                        <label for="display_name" class="block text-sm font-medium text-gray-700">Display Name</label>
                        <div class="mt-1">
                            <input type="text" name="display_name" id="display_name" autocomplete="name" required
                                   class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                   value="<?= htmlspecialchars($user['display_name']) ?>">
                        </div>
                    </div>

                    <div class="sm:col-span-4">
                        <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                        <div class="mt-1">
                            <input id="email" name="email" type="email" autocomplete="email" disabled
                                   class="bg-gray-100 shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                   value="<?= htmlspecialchars($user['email']) ?>">
                            <p class="mt-2 text-sm text-gray-500">Contact support to change your email address.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-8 border-t border-gray-200">
                <div>
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Change Password</h3>
                    <p class="mt-1 text-sm text-gray-500">Leave blank to keep your current password.</p>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                    <div class="sm:col-span-4">
                        <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password</label>
                        <div class="mt-1">
                            <input id="current_password" name="current_password" type="password" autocomplete="current-password"
                                   class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>

                    <div class="sm:col-span-4">
                        <label for="new_password" class="block text-sm font-medium text-gray-700">New Password</label>
                        <div class="mt-1">
                            <input id="new_password" name="new_password" type="password" autocomplete="new-password"
                                   class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>

                    <div class="sm:col-span-4">
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                        <div class="mt-1">
                            <input id="confirm_password" name="confirm_password" type="password" autocomplete="new-password"
                                   class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-5">
                <div class="flex justify-end">
                    <a href="profile.php" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Cancel
                    </a>
                    <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Save
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>
