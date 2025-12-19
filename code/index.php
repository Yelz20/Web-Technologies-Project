<?php
// Core configuration and utility functions.
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Admin dashboard.
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    header('Location: admin/index.php');
    exit();
}

$page = $_GET['page'] ?? 'home';
$allowed_pages = ['home', 'matches', 'leagues', 'teams', 'login', 'register', 'profile'];
$page = in_array($page, $allowed_pages) ? $page : '404';

// Upcoming matches on dashboard.
$db = Database::getInstance()->getConnection();
$stmt = $db->query("SELECT m.*, 
    ht.name as home_team_name, at.name as away_team_name,
    ht.logo as home_team_logo, at.logo as away_team_logo,
    c.name as league_name, m.id as match_id
    FROM matches m
    JOIN teams ht ON m.home_team = ht.id
    JOIN teams at ON m.away_team = at.id
    LEFT JOIN competitions c ON m.competition_id = c.id
    WHERE m.status = 'scheduled'
    ORDER BY m.match_date ASC
    LIMIT 5");
$featured_matches = $stmt->fetchAll();

// Latest reviews.
$stmt = $db->query("SELECT r.*, r.comment as content, 
    COALESCE(NULLIF(u.display_name, ''), u.username) as username, u.avatar as avatar_url, 
    ht.name as home_team, at.name as away_team,
    COALESCE(NULLIF(u.display_name, ''), u.username) as title,
    (SELECT COUNT(*) FROM reactions WHERE review_id = r.id AND type = 'like') as like_count,
    (SELECT COUNT(*) FROM review_replies WHERE review_id = r.id) as reply_count,
    m.id as match_id
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    JOIN matches m ON r.match_id = m.id
    JOIN teams ht ON m.home_team = ht.id
    JOIN teams at ON m.away_team = at.id
    ORDER BY r.created_at DESC
    LIMIT 5");
$recent_reviews = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Football Review - Home</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <?php include 'includes/partials/header.php'; ?>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <!-- Hero Section -->
        <section class="mb-12">
            <div class="bg-gradient-to-r from-blue-800 to-blue-600 text-white rounded-2xl p-8 shadow-lg">
                <h1 class="text-4xl font-bold mb-4">Football Match Reviews & Analysis</h1>
                <p class="text-xl mb-6">Share your thoughts, rate matches, and connect with football fans worldwide</p>
                <a href="matches.php" class="bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-bold py-3 px-6 rounded-lg transition duration-300 inline-block">
                    Explore Matches <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </section>

        <!-- Featured Matches -->
        <section class="mb-12">
            <h2 class="text-2xl font-bold mb-6 flex items-center">
                <i class="fas fa-star text-yellow-500 mr-2"></i> Upcoming Matches
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($featured_matches as $match): ?>
                    <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
                        <div class="p-4 bg-gray-50 border-b">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm text-gray-600"><?= htmlspecialchars($match['league_name'] ?? 'Friendly') ?></span>
                                <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">
                                    <?= date('M j, H:i', strtotime($match['match_date'])) ?>
                                </span>
                            </div>
                            <div class="flex items-center justify-between py-2">
                                <div class="flex-1 text-right pr-4">
                                    <div class="font-semibold"><?= htmlspecialchars($match['home_team_name']) ?></div>
                                    <?php if ($match['home_team_logo']): ?>
                                        <img src="<?= htmlspecialchars(get_logo_url($match['home_team_logo'])) ?>" alt="<?= htmlspecialchars($match['home_team_name']) ?>" class="h-12 mx-auto my-2">
                                    <?php endif; ?>
                                </div>
                                <div class="text-xl font-bold px-4">VS</div>
                                <div class="flex-1 text-left pl-4">
                                    <div class="font-semibold"><?= htmlspecialchars($match['away_team_name']) ?></div>
                                    <?php if ($match['away_team_logo']): ?>
                                        <img src="<?= htmlspecialchars(get_logo_url($match['away_team_logo'])) ?>" alt="<?= htmlspecialchars($match['away_team_name']) ?>" class="h-12 mx-auto my-2">
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="p-3 bg-gray-50 text-center">
                            <a href="match.php?id=<?= $match['match_id'] ?>" 
                               class="text-blue-600 hover:text-blue-800 font-medium text-sm">
                                View Details & Reviews <i class="fas fa-chevron-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Recent Reviews -->
        <section class="mb-12">
            <h2 class="text-2xl font-bold mb-6 flex items-center">
                <i class="fas fa-comment-alt text-blue-500 mr-2"></i> Recent Reviews
            </h2>
            <div class="space-y-6">
                <?php foreach ($recent_reviews as $review): ?>
                    <div class="bg-white rounded-xl shadow-md overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 mr-4">
                                    <img src="<?= get_avatar_url($review['avatar_url'] ?? '', 100, $review['username']) ?>" 
                                         alt="<?= htmlspecialchars($review['username'] ?? 'User') ?>" 
                                         class="h-12 w-12 rounded-full object-cover">
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h3 class="font-bold text-lg">
                                                <?= htmlspecialchars($review['title'] ?? '') ?>
                                            </h3>
                                            <p class="text-sm text-gray-600">
                                                By <?= htmlspecialchars($review['username'] ?? 'User') ?> • 
                                                <?= time_elapsed_string($review['created_at']) ?>
                                            </p>
                                        </div>
                                        <div class="text-yellow-400 text-lg">
                                            <?php if ($review['rating'] > 0): ?>
                                                <?= str_repeat('★', $review['rating']) ?><?= str_repeat('☆', 5 - $review['rating']) ?>
                                            <?php else: ?>
                                                <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">Comment</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <p class="mt-2 text-gray-700">
                                        <?= nl2br(htmlspecialchars(substr($review['content'], 0, 200))) ?>
                                        <?= strlen($review['content']) > 200 ? '...' : '' ?>
                                    </p>
                                    <div class="mt-3 flex items-center text-sm text-gray-500">
                                        <span class="mr-4">
                                            <i class="far fa-thumbs-up mr-1"></i> <?= $review['like_count'] ?>
                                        </span>
                                        <span class="mr-4">
                                            <i class="far fa-comment-alt mr-1"></i> <?= $review['reply_count'] ?>
                                        </span>
                                        <a href="match.php?id=<?= $review['match_id'] ?>#reviews" class="text-blue-600 hover:underline">Read full review</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-6 py-3 text-sm text-gray-500 border-t">
                            <i class="fas fa-futbol mr-1"></i>
                            <?= htmlspecialchars($review['home_team']) ?> vs <?= htmlspecialchars($review['away_team']) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="mt-6 text-center">
                <a href="latest-reviews.php" class="inline-block bg-white hover:bg-gray-100 text-gray-800 font-semibold py-2 px-6 border border-gray-300 rounded-lg shadow">
                    View All Reviews <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <?php include 'includes/partials/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</body>
</html>
