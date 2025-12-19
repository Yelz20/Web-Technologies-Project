<?php
require_once 'auth-check.php';

$db = Database::getInstance()->getConnection();
$user_id = $_SESSION['user_id'];

// Get admin user data
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Check if user exists (handle stale session after DB reset)
if (!$user) {
    header("Location: ../logout.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile - Football Review</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="bg-gray-100">
<?php include 'includes/header.php'; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Admin Profile</h1>
        <p class="mt-2 text-gray-600">View and manage your admin account</p>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
            <div>
                <h3 class="text-lg leading-6 font-medium text-gray-900">Admin Information</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">Personal details and account information</p>
            </div>
            <a href="edit-profile.php" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-edit mr-2"></i>Edit Profile
            </a>
        </div>
        <div class="border-t border-gray-200">
            <dl>
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Display Name</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"><?= htmlspecialchars($user['display_name'] ?? '') ?></dd>
                </div>
                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Username</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"><?= htmlspecialchars($user['username'] ?? $user['email'] ?? 'N/A') ?></dd>
                </div>
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Email address</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"><?= htmlspecialchars($user['email'] ?? '') ?></dd>
                </div>
                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Account type</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                            <i class="fas fa-shield-alt mr-1"></i>Administrator
                        </span>
                    </dd>
                </div>
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Member since</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"><?= date('F j, Y', strtotime($user['created_at'])) ?></dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
        <a href="index.php" class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-blue-100 rounded-full p-3">
                    <i class="fas fa-home text-2xl text-blue-600"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">Dashboard</h3>
                    <p class="text-sm text-gray-500">View statistics and overview</p>
                </div>
            </div>
        </a>

        <a href="messages.php" class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-purple-100 rounded-full p-3">
                    <i class="fas fa-comments text-2xl text-purple-600"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">Messages</h3>
                    <p class="text-sm text-gray-500">View fan messages</p>
                </div>
            </div>
        </a>

        <a href="../index.php" class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow" target="_blank">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-100 rounded-full p-3">
                    <i class="fas fa-eye text-2xl text-green-600"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">View Site</h3>
                    <p class="text-sm text-gray-500">See the fan homepage</p>
                </div>
            </div>
        </a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>
