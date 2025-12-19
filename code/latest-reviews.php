<?php
require_once 'includes/config.php';

// Pages
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Get total reviews count
$db = Database::getInstance()->getConnection();
$total_stmt = $db->query("SELECT COUNT(*) FROM reviews");
$total_reviews = $total_stmt->fetchColumn();
$total_pages = ceil($total_reviews / $per_page);

// Get recent reviews with pagination
$stmt = $db->prepare("SELECT r.*, 
    u.username, u.avatar as avatar_url,
    m.match_date,
    ht.name as home_team, at.name as away_team
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    JOIN matches m ON r.match_id = m.id
    JOIN teams ht ON m.home_team = ht.id
    JOIN teams at ON m.away_team = at.id
    ORDER BY r.created_at DESC
    LIMIT $per_page OFFSET $offset");
$stmt->execute();
$reviews = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Football Review - Latest Reviews</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="bg-gray-100">
<?php include 'includes/partials/header.php'; ?>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Latest Match Reviews</h1>
        <p class="mt-2 text-gray-600">See what fans are saying about recent matches</p>
    </div>

    <div class="space-y-6">
        <?php foreach ($reviews as $review): ?>
            <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-start">
                    <img src="<?= $review['avatar_url'] ?: 'assets/images/default-avatar.png' ?>" 
                         alt="<?= htmlspecialchars($review['username'] ?? 'User') ?>" 
                         class="h-12 w-12 rounded-full object-cover mr-4">
                    
                    <div class="flex-1">
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">
                                    <?= htmlspecialchars($review['home_team']) ?> vs <?= htmlspecialchars($review['away_team']) ?>
                                </h3>
                                <p class="text-sm text-gray-600">
                                    by <?= htmlspecialchars($review['username'] ?? 'User') ?> • 
                                    <?= time_elapsed_string($review['created_at']) ?> ago
                                </p>
                            </div>
                            
                            <div>
                                <?php if ($review['rating'] > 0): ?>
                                    <div class="flex items-center bg-green-100 px-3 py-1 rounded-full">
                                        <span class="text-sm font-semibold text-green-800">
                                            <?= $review['rating'] ?>/5
                                        </span>
                                        <i class="fas fa-star text-yellow-500 ml-1 text-sm"></i>
                                    </div>
                                <?php else: ?>
                                    <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">Comment</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <p class="text-gray-700 mb-3">
                            <?= htmlspecialchars($review['comment']) ?>
                        </p>
                        
                        <a href="match.php?id=<?= $review['match_id'] ?>" 
                           class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            View Match Details →
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (empty($reviews)): ?>
        <div class="bg-white rounded-lg shadow-md p-12 text-center">
            <i class="fas fa-comments text-6xl text-gray-300 mb-4"></i>
            <h2 class="text-2xl font-semibold text-gray-700 mb-2">No Reviews Yet</h2>
            <p class="text-gray-500">Be the first to review a match!</p>
            <a href="matches.php" class="inline-block mt-4 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Browse Matches
            </a>
        </div>
    <?php endif; ?>

    <!-- Page Layout -->
    <?php if ($total_pages > 1): ?>
        <div class="mt-8 flex justify-center">
            <nav class="inline-flex rounded-md shadow-sm -space-x-px">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>" 
                       class="relative inline-flex items-center px-4 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Previous
                    </a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?= $i ?>" 
                       class="relative inline-flex items-center px-4 py-2 border border-gray-300 <?= $i == $page ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' ?> text-sm font-medium">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page + 1 ?>" 
                       class="relative inline-flex items-center px-4 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Next
                    </a>
                <?php endif; ?>
            </nav>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/partials/footer.php'; ?>
</body>
</html>
