<?php
require_once 'auth-check.php';

$db = Database::getInstance()->getConnection();

// Get statistics with error handling
try {
    $total_matches = $db->query("SELECT COUNT(*) FROM matches")->fetchColumn();
    $total_teams = $db->query("SELECT COUNT(*) FROM teams")->fetchColumn();
    $total_leagues = $db->query("SELECT COUNT(*) FROM competitions")->fetchColumn();
    $total_messages = $db->query("SELECT COUNT(DISTINCT user_id) FROM support_messages WHERE is_admin = 0")->fetchColumn();
    $upcoming_matches = $db->query("SELECT COUNT(*) FROM matches WHERE match_date >= CURDATE()")->fetchColumn();
} catch (PDOException $e) {
    // Handle missing tables
    $total_matches = 0;
    $total_teams = 0;
    $total_leagues = 0;
    $total_messages = 0;
    $upcoming_matches = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Football Review</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="bg-gray-100">
<?php include 'includes/header.php'; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
        <p class="mt-2 text-gray-600">Welcome back, <?= htmlspecialchars($_SESSION['display_name'] ?? $_SESSION['username'] ?? 'Admin') ?></p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Total Matches</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1"><?= $total_matches ?></p>
                </div>
                <div class="bg-blue-100 rounded-full p-3">
                    <i class="fas fa-futbol text-2xl text-blue-600"></i>
                </div>
            </div>
            <p class="text-sm text-gray-600 mt-4">
                <span class="text-green-600 font-semibold"><?= $upcoming_matches ?></span> upcoming
            </p>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Total Teams</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1"><?= $total_teams ?></p>
                </div>
                <div class="bg-green-100 rounded-full p-3">
                    <i class="fas fa-shield-alt text-2xl text-green-600"></i>
                </div>
           </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Total Leagues</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1"><?= $total_leagues ?></p>
                </div>
                <div class="bg-yellow-100 rounded-full p-3">
                    <i class="fas fa-trophy text-2xl text-yellow-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Active Conversations</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1"><?= $total_messages ?></p>
                </div>
                <div class="bg-purple-100 rounded-full p-3">
                    <i class="fas fa-comments text-2xl text-purple-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Quick Actions</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="matches.php" class="flex items-center p-4 border-2 border-blue-200 rounded-lg hover:bg-blue-50 hover:border-blue-400 transition-colors">
                <i class="fas fa-plus-circle text-2xl text-blue-600 mr-3"></i>
                <span class="font-medium text-gray-900">Add New Match</span>
            </a>
            <a href="teams.php" class="flex items-center p-4 border-2 border-green-200 rounded-lg hover:bg-green-50 hover:border-green-400 transition-colors">
                <i class="fas fa-plus-circle text-2xl text-green-600 mr-3"></i>
                <span class="font-medium text-gray-900">Add New Team</span>
            </a>
            <a href="leagues.php" class="flex items-center p-4 border-2 border-yellow-200 rounded-lg hover:bg-yellow-50 hover:border-yellow-400 transition-colors">
                <i class="fas fa-plus-circle text-2xl text-yellow-600 mr-3"></i>
                <span class="font-medium text-gray-900">Add New League</span>
            </a>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Recent Matches</h2>
        <?php
        try {
            $stmt = $db->query("SELECT m.*, ht.name as home_team, at.name as away_team 
                FROM matches m 
                JOIN teams ht ON m.home_team = ht.id 
                JOIN teams at ON m.away_team = at.id 
                ORDER BY m.match_date DESC 
                LIMIT 5");
            $recent_matches = $stmt->fetchAll();
        } catch (PDOException $e) {
            $recent_matches = [];
        }
        ?>
        
        <?php if (empty($recent_matches)): ?>
            <p class="text-gray-500 text-center py-8">No matches found. <a href="matches.php?action=add" class="text-blue-600 hover:underline">Add your first match</a></p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Match</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($recent_matches as $match): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($match['home_team']) ?> vs <?= htmlspecialchars($match['away_team']) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= date('M j, Y', strtotime($match['match_date'])) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="matches.php?action=edit&id=<?= $match['id'] ?>" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                                    <a href="../match.php?id=<?= $match['id'] ?>" class="text-gray-600 hover:text-gray-900" target="_blank">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Footer -->
<?php include 'includes/footer.php'; ?>
</body>
</html>
