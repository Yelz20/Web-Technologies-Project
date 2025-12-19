<?php
// profile.php
require_once 'includes/config.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit();
}

$db = Database::getInstance()->getConnection();
$user_id = $_SESSION['user_id'];

// Get user data
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Check if user exists (handle stale session after DB reset)
if (!$user) {
    // Determine path to logout.php relative to current script
    $logoutPath = 'logout.php';
    header("Location: $logoutPath");
    exit();
}

// Get user's reviews
$stmt = $db->prepare("
    SELECT r.*, 
           ht.name as home_team_name, 
           at.name as away_team_name,
           m.match_date
    FROM reviews r
    JOIN matches m ON r.match_id = m.id
    LEFT JOIN teams ht ON m.home_team = ht.id
    LEFT JOIN teams at ON m.away_team = at.id
    WHERE r.user_id = ?
    ORDER BY r.created_at DESC
");
$stmt->execute([$user_id]);
$reviews = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Football Review - Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="bg-gray-100">
<?php
include 'includes/partials/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">User Profile</h1>
                <p class="mt-1 text-sm text-gray-500">Manage your account and view your activity</p>
            </div>
            <div>
                <a href="edit-profile.php" 
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Edit Profile
                </a>
            </div>
        </div>
        <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-2">
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Display Name</dt>
                    <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($user['display_name']) ?></dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Email address</dt>
                    <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($user['email']) ?></dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Account type</dt>
                    <dd class="mt-1 text-sm text-gray-900 capitalize"><?= htmlspecialchars($user['role']) ?></dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Member since</dt>
                    <dd class="mt-1 text-sm text-gray-900"><?= date('F j, Y', strtotime($user['created_at'])) ?></dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- User's Reviews -->
    <div class="mt-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Your Match Reviews</h2>
        
        <?php if (empty($reviews)): ?>
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No reviews yet</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by reviewing a match you've watched.</p>
                    <div class="mt-6">
                        <a href="matches.php" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            Browse Matches
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <ul class="divide-y divide-gray-200">
                    <?php foreach ($reviews as $review): ?>
                        <li>
                            <a href="match.php?id=<?= $review['match_id'] ?>" class="block hover:bg-gray-50">
                                <div class="px-4 py-4 sm:px-6">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm font-medium text-blue-600 truncate">
                                            <?= htmlspecialchars($review['home_team_name'] ?? 'Home') ?> vs <?= htmlspecialchars($review['away_team_name'] ?? 'Away') ?>
                                        </p>
                                        <div class="ml-2 flex-shrink-0 flex">
                                            <p class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                <?= $review['rating'] ?>/5
                                            </p>
                                        </div>
                                    </div>
                                    <div class="mt-2 sm:flex sm:justify-between">
                                        <div class="sm:flex">
                                            <p class="flex items-center text-sm text-gray-500">
                                                <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                                                </svg>
                                                <?= date('F j, Y', strtotime($review['match_date'])) ?>
                                            </p>
                                        </div>
                                        <div class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0">
                                            <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                            </svg>
                                            <p>
                                                <?= date('F j, Y, g:i a', strtotime($review['created_at'])) ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/partials/footer.php'; ?>
<script src="assets/js/main.js"></script>
</body>
</html>
