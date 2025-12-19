<?php
require_once 'includes/config.php';

// If someone somehow lands here without a match ID, send them back to the full list.
if (!isset($_GET['id'])) {
    header('Location: matches.php');
    exit();
}

$match_id = (int)$_GET['id'];
$db = Database::getInstance()->getConnection();

// First, pull the essential details about the match, the teams, and the venue.
$stmt = $db->prepare("
    SELECT m.*, 
           ht.name as home_team_name, 
           at.name as away_team_name,
           ht.logo as home_team_logo,
           at.logo as away_team_logo,
           v.name as venue_name,
           v.city as venue_city,
           v.country as venue_country,
           c.name as competition_name,
           c.logo as competition_logo
    FROM matches m
    LEFT JOIN teams ht ON m.home_team = ht.id
    LEFT JOIN teams at ON m.away_team = at.id
    LEFT JOIN venues v ON m.venue_id = v.id
    LEFT JOIN competitions c ON m.competition_id = c.id
    WHERE m.id = ?
");

$stmt->execute([$match_id]);
$match = $stmt->fetch();

if (!$match) {
    header('Location: matches.php');
    exit();
}

// Gather the chronological events of the match—goals, cards, and subs—to build the timeline.
$stmt = $db->prepare("
    SELECT me.*, p.name as player_name, p.number as player_number, t.name as team_name, t.logo as team_logo
    FROM match_events me
    LEFT JOIN players p ON me.player_id = p.id
    LEFT JOIN teams t ON p.team_id = t.id
    WHERE me.match_id = ?
    ORDER BY me.minute, me.event_type
");
$stmt->execute([$match_id]);
$events = $stmt->fetchAll();

// Group events by type for display
$grouped_events = [
    'goal' => [],
    'yellow_card' => [],
    'red_card' => [],
    'substitution' => [],
    'penalty' => [],
    'own_goal' => [],
    'var_decision' => []
];

foreach ($events as $event) {
    if (isset($grouped_events[$event['event_type']])) {
        $grouped_events[$event['event_type']][] = $event;
    }
}

// Need the match-day statistics like possession and shots to give a full picture of the game.
$stmt = $db->prepare("SELECT * FROM match_stats WHERE match_id = ?");
$stmt->execute([$match_id]);
$db_stats = $stmt->fetchAll();

$stats = [
    'home' => [
        'possession' => 50,
        'shots' => 0,
        'shots_on_target' => 0,
        'corners' => 0,
        'fouls' => 0,
        'yellow_cards' => 0,
        'red_cards' => 0,
    ],
    'away' => [
        'possession' => 50,
        'shots' => 0,
        'shots_on_target' => 0,
        'corners' => 0,
        'fouls' => 0,
        'yellow_cards' => 0,
        'red_cards' => 0,
    ]
];

foreach ($db_stats as $stat) {
    $team = ($stat['team_id'] == $match['home_team']) ? 'home' : 'away';
    $stats[$team]['possession'] = $stat['possession'];
    $stats[$team]['shots'] = $stat['shots'];
    $stats[$team]['shots_on_target'] = $stat['shots_on_target'];
    $stats[$team]['corners'] = $stat['corners'];
    $stats[$team]['fouls'] = $stat['fouls'];
}


foreach ($events as $event) {
    if ($event['event_type'] === 'yellow_card' || $event['event_type'] === 'red_card') {
        $team = ($event['team_id'] == $match['home_team']) ? 'home' : 'away';
        if ($event['event_type'] === 'yellow_card') $stats[$team]['yellow_cards']++;
        if ($event['event_type'] === 'red_card') $stats[$team]['red_cards']++;
    }
}

// Get reviews with user info and reactions
$reviews = [];
$user_review = null;
$user_review_id = null;
$user_has_reviewed = false;

if (is_logged_in()) {
    // Check if user has already reviewed this match
    $stmt = $db->prepare("SELECT id FROM reviews WHERE user_id = ? AND match_id = ?");
    $stmt->execute([$_SESSION['user_id'], $match_id]);
    $user_review = $stmt->fetch();
    $user_has_reviewed = (bool)$user_review;
    $user_review_id = $user_review ? $user_review['id'] : null;
}

// Switch gears to the social side, fetching fan reviews and their reactions.
$stmt = $db->prepare("
    SELECT r.*, 
           COALESCE(NULLIF(u.display_name, ''), u.username) as user_name,
           u.avatar as user_avatar,
           u.role as user_role,
           (SELECT COUNT(*) FROM reactions WHERE review_id = r.id AND type = 'like') as like_count,
           (SELECT COUNT(*) FROM reactions WHERE review_id = r.id AND type = 'dislike') as dislike_count,
           (SELECT COUNT(*) FROM review_replies WHERE review_id = r.id) as reply_count,
           (SELECT type FROM reactions WHERE review_id = r.id AND user_id = ?) as user_reaction
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    WHERE r.match_id = ?
    ORDER BY r.created_at DESC
");

$stmt->execute([is_logged_in() ? $_SESSION['user_id'] : 0, $match_id]);
$reviews = $stmt->fetchAll();

// Page logic
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 5; // Show 5 reviews per page
$total_reviews = count($reviews);
$total_pages = max(1, ceil($total_reviews / $per_page)); // Ensure at least 1 page
$offset = ($page - 1) * $per_page;

// Ensure page is valid
if ($page < 1) $page = 1;
if ($page > $total_pages) $page = $total_pages;

// Slice reviews for current page
$reviews = array_slice($reviews, $offset, $per_page);

// Handling new reviews or updates to existing ones from our users.
if (isset($_POST['submit_review'])) {
        // Calculate match status again to be safe
        $match_timestamp_check = strtotime($match['match_date'] . ' ' . ($match['match_time'] ?? '00:00:00'));
        $is_upcoming_check = $match_timestamp_check > time();

        if ($is_upcoming_check) {
            $rating = 0; // Force 0 for upcoming matches (comment only)
        } else {
            $rating = (int)$_POST['rating'];
        }
        
        $comment = trim($_POST['comment']);
        $errors = [];
        
        // Validate rating based on match status
        if ($is_upcoming_check) {
            if ($rating !== 0) {
                 // Should ideally not happen if UI is correct, but good for security
                 $rating = 0;
            }
        } else {
            if ($rating < 1 || $rating > 5) {
                $errors[] = 'Please provide a rating between 1 and 5';
            }
        }
        
        if (strlen($comment) < 10) {
            $errors[] = 'Review comment must be at least 10 characters long';
        }
        
        if (empty($errors)) {
            try {
                $db->beginTransaction();
                
                if ($user_has_reviewed) {
                    // Update existing review
                    $stmt = $db->prepare("
                        UPDATE reviews 
                        SET rating = ?, comment = ?, updated_at = NOW() 
                        WHERE id = ? AND user_id = ?
                    ");
                    $stmt->execute([$rating, $comment, $user_review_id, $_SESSION['user_id']]);
                    $success_message = 'Your review has been updated successfully!';
                } else {
                    // Create new review
                    $stmt = $db->prepare("
                        INSERT INTO reviews (user_id, match_id, rating, comment, created_at, updated_at)
                        VALUES (?, ?, ?, ?, NOW(), NOW())
                    ");
                    $stmt->execute([$_SESSION['user_id'], $match_id, $rating, $comment]);
                    $user_review_id = $db->lastInsertId();
                    $user_has_reviewed = true;
                    $success_message = 'Thank you for your review!';
                }
                
                $db->commit();
                
                // Refresh reviews
                $stmt = $db->prepare("
                    SELECT r.*, 
                           COALESCE(NULLIF(u.display_name, ''), u.username) as user_name,
                           u.avatar as user_avatar,
                           u.role as user_role,
                           (SELECT COUNT(*) FROM reactions WHERE review_id = r.id AND type = 'like') as like_count,
                           (SELECT COUNT(*) FROM reactions WHERE review_id = r.id AND type = 'dislike') as dislike_count,
                           (SELECT COUNT(*) FROM review_replies WHERE review_id = r.id) as reply_count,
                           (SELECT type FROM reactions WHERE review_id = r.id AND user_id = ?) as user_reaction
                    FROM reviews r
                    JOIN users u ON r.user_id = u.id
                    WHERE r.match_id = ?
                    ORDER BY r.created_at DESC
                ");
                $stmt->execute([$_SESSION['user_id'], $match_id]);
                $reviews = $stmt->fetchAll();
                
            } catch (Exception $e) {
                $db->rollBack();
                $errors[] = 'An error occurred while saving your review: ' . $e->getMessage();
            }
        }
    }
    
    // Handle reaction toggling
    if (isset($_POST['react'])) {
        $review_id = (int)$_POST['review_id'];
        $reaction_type = $_POST['reaction_type'];
        
        // Verify the review exists and is not the user's own review
        $stmt = $db->prepare("SELECT id FROM reviews WHERE id = ? AND user_id != ?");
        $stmt->execute([$review_id, $_SESSION['user_id']]);
        
        if ($stmt->fetch()) {
            try {
                $db->beginTransaction();
                
                // Check if user already reacted
                $stmt = $db->prepare("SELECT id, type FROM reactions WHERE review_id = ? AND user_id = ?");
                $stmt->execute([$review_id, $_SESSION['user_id']]);
                $existing_reaction = $stmt->fetch();
                
                if ($existing_reaction) {
                    if ($existing_reaction['type'] === $reaction_type) {
                        // Remove reaction if clicking the same type
                        $stmt = $db->prepare("DELETE FROM reactions WHERE id = ?");
                        $stmt->execute([$existing_reaction['id']]);
                    } else {
                        // Update reaction if clicking different type
                        $stmt = $db->prepare("UPDATE reactions SET type = ? WHERE id = ?");
                        $stmt->execute([$reaction_type, $existing_reaction['id']]);
                    }
                } else {
                    // Add new reaction
                    $stmt = $db->prepare("INSERT INTO reactions (review_id, user_id, type, created_at) VALUES (?, ?, ?, NOW())");
                    $stmt->execute([$review_id, $_SESSION['user_id'], $reaction_type]);
                }
                
                $db->commit();
                
                // Refresh the page to show updated reactions
                header("Location: match.php?id=$match_id");
                exit();
                
            } catch (Exception $e) {
                $db->rollBack();
                $errors[] = 'Failed to update reaction. Please try again.';
            }
        }
    }

// Calculate match status for review logic
$match_timestamp = strtotime($match['match_date'] . ' ' . ($match['match_time'] ?? '00:00:00'));
$is_upcoming = $match_timestamp > time();

// Fetch replies for the reviews
if (!empty($reviews)) {
    $review_ids = array_column($reviews, 'id');
    $placeholders = implode(',', array_fill(0, count($review_ids), '?'));
    
    $stmt = $db->prepare("SELECT r.review_id, r.id, r.content, r.created_at, r.user_id,
        COALESCE(NULLIF(u.display_name, ''), u.username) as user_name, 
        u.avatar as user_avatar 
        FROM review_replies r 
        JOIN users u ON r.user_id = u.id 
        WHERE r.review_id IN ($placeholders) 
        ORDER BY r.created_at ASC");
    $stmt->execute($review_ids);
    $all_replies = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);
    
    foreach ($reviews as &$review) {
        $review['replies'] = isset($all_replies[$review['id']]) ? $all_replies[$review['id']] : [];
    }
    unset($review); // Break reference
}

// Calculate average rating (excluding 0 ratings which are comments only)
$average_rating = 0;
$rated_reviews = array_filter($reviews, function($r) {
    return $r['rating'] > 0;
});

if (count($rated_reviews) > 0) {
    $total_rating = array_sum(array_column($rated_reviews, 'rating'));
    $average_rating = round($total_rating / count($rated_reviews), 1);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Football Review - Match Details</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="bg-gray-100">
<?php
include 'includes/partials/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Match Header -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8">
        <div class="px-4 py-5 sm:px-6">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="flex-1 text-center md:text-left mb-4 md:mb-0">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        <?= htmlspecialchars($match['competition_name']) ?>
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        <?= date('l, F j, Y', strtotime($match['match_date'])) ?> at <?= date('g:i A', strtotime($match['match_time'])) ?>
                    </p>
                        <?php if (!empty($match['venue_name'])): ?>
                            <?= htmlspecialchars($match['venue_name']) ?><?= !empty($match['venue_city']) ? ', ' . htmlspecialchars($match['venue_city']) : '' ?>
                        <?php endif; ?>
                </div>
                <div class="flex items-center space-x-4">
                    <?php if (is_logged_in() && is_admin()): ?>
                        <a href="admin/match-edit.php?id=<?= $match_id ?>" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Edit Match
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Match Score -->
        <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
            <div class="flex items-center justify-between">
                <!-- Home Team -->
                <div class="flex-1 flex flex-col items-center">
                    <div class="flex-shrink-0 h-16 w-16 mb-2">
                        <?php if (!empty($match['home_team_logo'])): ?>
                            <img class="h-16 w-16" src="<?= htmlspecialchars(get_logo_url($match['home_team_logo'])) ?>" alt="<?= htmlspecialchars($match['home_team_name']) ?> logo">
                        <?php else: ?>
                            <div class="h-16 w-16 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 text-xl font-bold">
                                <?= substr($match['home_team_name'], 0, 2) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 text-center">
                        <?= htmlspecialchars($match['home_team_name']) ?>
                    </h3>
                    <p class="text-3xl font-bold text-gray-900 mt-2">
                        <?= $match['home_team_score'] !== null ? $match['home_team_score'] : '-' ?>
                    </p>
                </div>
                
                <!-- Match Status -->
                <div class="flex flex-col items-center mx-4">
                    <span class="text-sm font-medium text-gray-500">
                        <?php
                        $match_time = strtotime($match['match_date'] . ' ' . $match['match_time']);
                        $now = time();
                        
                        if ($match_time > $now) {
                            echo 'Upcoming';
                        } elseif ($match['status'] === 'FT') {
                            echo 'Full Time';
                        } elseif ($match['status'] === 'HT') {
                            echo 'Half Time';
                        } elseif ($match['status'] === 'ET' || $match['status'] === 'PEN') {
                            echo $match['status'];
                        } else {
                            $minutes = floor(($now - $match_time) / 60);
                            if ($minutes > 0 && $minutes <= 120) {
                                echo $minutes . "'";
                            } else {
                                echo 'Live';
                            }
                        }
                        ?>
                    </span>
                    
                    <?php if ($match['home_team_penalties'] !== null && $match['away_team_penalties'] !== null): ?>
                        <div class="text-xs text-gray-500 mt-1">
                            (<?= $match['home_team_penalties'] ?> - <?= $match['away_team_penalties'] ?> PEN)
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Away Team -->
                <div class="flex-1 flex flex-col items-center">
                    <div class="flex-shrink-0 h-16 w-16 mb-2">
                        <?php if (!empty($match['away_team_logo'])): ?>
                            <img class="h-16 w-16" src="<?= htmlspecialchars(get_logo_url($match['away_team_logo'])) ?>" alt="<?= htmlspecialchars($match['away_team_name']) ?> logo">
                        <?php else: ?>
                            <div class="h-16 w-16 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 text-xl font-bold">
                                <?= substr($match['away_team_name'], 0, 2) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 text-center">
                        <?= htmlspecialchars($match['away_team_name']) ?>
                    </h3>
                    <p class="text-3xl font-bold text-gray-900 mt-2">
                        <?= $match['away_team_score'] !== null ? $match['away_team_score'] : '-' ?>
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Match Tabs -->
        <div class="border-t border-gray-200">
            <nav class="flex -mb-px" aria-label="Match tabs">
                <button id="summary-tab" class="w-1/3 py-4 px-1 text-center border-b-2 font-medium text-sm border-blue-500 text-blue-600">
                    Summary
                </button>
                <button id="stats-tab" class="w-1/3 py-4 px-1 text-center border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    Stats
                </button>
                <button id="reviews-tab" class="w-1/3 py-4 px-1 text-center border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    Reviews (<?= count($reviews) ?>)
                </button>
            </nav>
        </div>
    </div>
    
    <!-- Summary Tab Content -->
    <div id="summary-content" class="tab-content">
        <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Match Events</h3>
            </div>
            <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
                <dl class="sm:divide-y sm:divide-gray-200">
                    <?php if (!empty($grouped_events['goal'])): ?>
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Goals</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <ul class="space-y-2">
                                    <?php foreach ($grouped_events['goal'] as $event): ?>
                                        <li class="flex items-center">
                                            <span class="w-12 text-sm text-gray-500"><?= $event['minute'] ?>'</span>
                                            <div class="flex items-center">
                                                <?php if (!empty($event['team_logo'])): ?>
                                                    <img class="h-4 w-4 mr-2" src="<?= htmlspecialchars($event['team_logo']) ?>" alt="">
                                                <?php endif; ?>
                                                <span class="font-medium"><?= htmlspecialchars($event['player_name']) ?></span>
                                                <?php if (!empty($event['assist_id'])): ?>
                                                    <?php 
                                                    $stmt = $db->prepare("SELECT name FROM players WHERE id = ?");
                                                    $stmt->execute([$event['assist_id']]);
                                                    $assist = $stmt->fetch();
                                                    ?>
                                                    <span class="text-gray-500 ml-1">(assist: <?= htmlspecialchars($assist['name']) ?>)</span>
                                                <?php endif; ?>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </dd>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($grouped_events['yellow_card'])): ?>
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Yellow Cards</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <ul class="space-y-2">
                                    <?php foreach ($grouped_events['yellow_card'] as $event): ?>
                                        <li class="flex items-center">
                                            <span class="w-12 text-sm text-gray-500"><?= $event['minute'] ?>'</span>
                                            <div class="flex items-center">
                                                <?php if (!empty($event['team_logo'])): ?>
                                                    <img class="h-4 w-4 mr-2" src="<?= htmlspecialchars($event['team_logo']) ?>" alt="">
                                                <?php endif; ?>
                                                <span class="font-medium"><?= htmlspecialchars($event['player_name']) ?></span>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </dd>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($grouped_events['red_card'])): ?>
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Red Cards</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <ul class="space-y-2">
                                    <?php foreach ($grouped_events['red_card'] as $event): ?>
                                        <li class="flex items-center">
                                            <span class="w-12 text-sm text-gray-500"><?= $event['minute'] ?>'</span>
                                            <div class="flex items-center">
                                                <?php if (!empty($event['team_logo'])): ?>
                                                    <img class="h-4 w-4 mr-2" src="<?= htmlspecialchars($event['team_logo']) ?>" alt="">
                                                <?php endif; ?>
                                                <span class="font-medium"><?= htmlspecialchars($event['player_name']) ?></span>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </dd>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($grouped_events['substitution'])): ?>
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Substitutions</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <ul class="space-y-2">
                                    <?php foreach ($grouped_events['substitution'] as $event): ?>
                                        <?php 
                                        $stmt = $db->prepare("SELECT name FROM players WHERE id = ?");
                                        $stmt->execute([$event['related_player_id']]);
                                        $sub_out = $stmt->fetch();
                                        ?>
                                        <li class="flex items-center">
                                            <span class="w-12 text-sm text-gray-500"><?= $event['minute'] ?>'</span>
                                            <div class="flex items-center">
                                                <?php if (!empty($event['team_logo'])): ?>
                                                    <img class="h-4 w-4 mr-2" src="<?= htmlspecialchars($event['team_logo']) ?>" alt="">
                                                <?php endif; ?>
                                                <span class="font-medium"><?= htmlspecialchars($event['player_name']) ?></span>
                                                <span class="text-gray-500 ml-1">for <?= htmlspecialchars($sub_out['name'] ?? 'Unknown') ?></span>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </dd>
                        </div>
                    <?php endif; ?>
                </dl>
            </div>
        </div>
        
        <?php if (!empty($match['highlights_url'])): ?>
            <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Match Highlights</h3>
                </div>
                <div class="px-4 py-5 sm:p-6">
                    <div class="aspect-w-16 aspect-h-9">
                        <iframe class="w-full h-96" src="<?= htmlspecialchars($match['highlights_url']) ?>" frameborder="0" allowfullscreen></iframe>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($match['match_report'])): ?>
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Match Report</h3>
                </div>
                <div class="px-4 py-5 sm:p-6 border-t border-gray-200">
                    <div class="prose max-w-none">
                        <?= nl2br(htmlspecialchars($match['match_report'])) ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Stats Tab Content -->
    <div id="stats-content" class="tab-content hidden">
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Match Statistics</h3>
            </div>
            <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
                <dl class="sm:divide-y sm:divide-gray-200">
                    <!-- Possession -->
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Possession</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <div class="flex items-center">
                                <div class="w-1/3 text-right pr-2"><?= $stats['home']['possession'] ?>%</div>
                                <div class="w-1/3">
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <div class="bg-blue-600 h-2.5 rounded-full" style="width: <?= $stats['home']['possession'] ?>%"></div>
                                    </div>
                                </div>
                                <div class="w-1/3 pl-2"><?= $stats['away']['possession'] ?>%</div>
                            </div>
                        </dd>
                    </div>
                    
                    <!-- Shots -->
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Shots</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <div class="flex items-center">
                                <div class="w-1/3 text-right pr-2"><?= $stats['home']['shots'] ?></div>
                                <div class="w-1/3">
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <?php 
                                        $total_shots = $stats['home']['shots'] + $stats['away']['shots'];
                                        $home_shots_pct = $total_shots > 0 ? ($stats['home']['shots'] / $total_shots) * 100 : 50;
                                        ?>
                                        <div class="bg-blue-600 h-2.5 rounded-full" style="width: <?= $home_shots_pct ?>%"></div>
                                    </div>
                                </div>
                                <div class="w-1/3 pl-2"><?= $stats['away']['shots'] ?></div>
                            </div>
                        </dd>
                    </div>
                    
                    <!-- Shots on Target -->
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Shots on Target</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <div class="flex items-center">
                                <div class="w-1/3 text-right pr-2"><?= $stats['home']['shots_on_target'] ?></div>
                                <div class="w-1/3">
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <?php 
                                        $total_sot = $stats['home']['shots_on_target'] + $stats['away']['shots_on_target'];
                                        $home_sot_pct = $total_sot > 0 ? ($stats['home']['shots_on_target'] / $total_sot) * 100 : 50;
                                        ?>
                                        <div class="bg-blue-600 h-2.5 rounded-full" style="width: <?= $home_sot_pct ?>%"></div>
                                    </div>
                                </div>
                                <div class="w-1/3 pl-2"><?= $stats['away']['shots_on_target'] ?></div>
                            </div>
                        </dd>
                    </div>
                    
                    <!-- Corners -->
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Corners</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <div class="flex items-center">
                                <div class="w-1/3 text-right pr-2"><?= $stats['home']['corners'] ?></div>
                                <div class="w-1/3">
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <?php 
                                        $total_corners = $stats['home']['corners'] + $stats['away']['corners'];
                                        $home_corners_pct = $total_corners > 0 ? ($stats['home']['corners'] / $total_corners) * 100 : 50;
                                        ?>
                                        <div class="bg-blue-600 h-2.5 rounded-full" style="width: <?= $home_corners_pct ?>%"></div>
                                    </div>
                                </div>
                                <div class="w-1/3 pl-2"><?= $stats['away']['corners'] ?></div>
                            </div>
                        </dd>
                    </div>
                    
                    <!-- Fouls -->
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Fouls</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <div class="flex items-center">
                                <div class="w-1/3 text-right pr-2"><?= $stats['home']['fouls'] ?></div>
                                <div class="w-1/3">
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <?php 
                                        $total_fouls = $stats['home']['fouls'] + $stats['away']['fouls'];
                                        $home_fouls_pct = $total_fouls > 0 ? ($stats['home']['fouls'] / $total_fouls) * 100 : 50;
                                        ?>
                                        <div class="bg-blue-600 h-2.5 rounded-full" style="width: <?= $home_fouls_pct ?>%"></div>
                                    </div>
                                </div>
                                <div class="w-1/3 pl-2"><?= $stats['away']['fouls'] ?></div>
                            </div>
                        </dd>
                    </div>
                    
                    <!-- Yellow Cards -->
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Yellow Cards</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <div class="flex items-center">
                                <div class="w-1/3 text-right pr-2"><?= $stats['home']['yellow_cards'] ?></div>
                                <div class="w-1/3">
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <?php 
                                        $total_yellows = $stats['home']['yellow_cards'] + $stats['away']['yellow_cards'];
                                        $home_yellows_pct = $total_yellows > 0 ? ($stats['home']['yellow_cards'] / $total_yellows) * 100 : 50;
                                        ?>
                                        <div class="bg-yellow-400 h-2.5 rounded-full" style="width: <?= $home_yellows_pct ?>%"></div>
                                    </div>
                                </div>
                                <div class="w-1/3 pl-2"><?= $stats['away']['yellow_cards'] ?></div>
                            </div>
                        </dd>
                    </div>
                    
                    <!-- Red Cards -->
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Red Cards</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <div class="flex items-center">
                                <div class="w-1/3 text-right pr-2"><?= $stats['home']['red_cards'] ?></div>
                                <div class="w-1/3">
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <?php 
                                        $total_reds = $stats['home']['red_cards'] + $stats['away']['red_cards'];
                                        $home_reds_pct = $total_reds > 0 ? ($stats['home']['red_cards'] / $total_reds) * 100 : 50;
                                        ?>
                                        <div class="bg-red-600 h-2.5 rounded-full" style="width: <?= $home_reds_pct > 0 ? $home_reds_pct : '0.1' ?>%"></div>
                                    </div>
                                </div>
                                <div class="w-1/3 pl-2"><?= $stats['away']['red_cards'] ?></div>
                            </div>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
    
    <!-- Reviews Tab Content -->
    <div id="reviews-content" class="tab-content hidden">
        <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8">
            <div class="px-4 py-5 sm:px-6">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Fan Reviews
                        <?php if (count($reviews) > 0): ?>
                            <span class="text-sm font-normal text-gray-500">(Average: <?= $average_rating ?>/5 from <?= count($reviews) ?> reviews)</span>
                        <?php endif; ?>
                    </h3>
                    <div class="flex items-center
                    <?php if (!is_logged_in()): ?> 
                        tooltip" data-tooltip="Sign in to write a review">
                        <button disabled class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-gray-400 cursor-not-allowed">
                            Write a Review
                        </button>
                    <?php else: ?>
                        ">
                        <button id="write-review-btn" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <?= $user_has_reviewed ? 'Edit Your Review' : 'Write a Review' ?>
                        </button>
                    <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Review Form (initially hidden) -->
            <div id="review-form-container" class="border-t border-gray-200 px-4 py-5 sm:px-6 <?= isset($_POST['submit_review']) || (is_logged_in() && !$user_has_reviewed) ? '' : 'hidden' ?>">
                <h4 class="text-md font-medium text-gray-900 mb-4">
                    <?= $user_has_reviewed ? 'Edit Your Review' : 'Write a Review' ?>
                </h4>
                
                <?php if (!empty($errors)): ?>
                    <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-4">
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
                
                <?php if (isset($success_message)): ?>
                    <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-green-700"><?= $success_message ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="match.php?id=<?= $match_id ?>#reviews">
                    <?php if ($is_upcoming): ?>
                        <div class="mb-4 bg-blue-50 p-4 rounded-md">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3 flex-1 md:flex md:justify-between">
                                    <p class="text-sm text-blue-700">
                                        This match is upcoming. You can post comments/predictions, but rating is disabled until kickoff.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="rating" value="0">
                    <?php else: ?>
                        <div class="mb-4">
                            <label for="rating" class="block text-sm font-medium text-gray-700 mb-1">Your Rating</label>
                            <div class="flex items-center">
                                <div class="rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>" class="hidden" 
                                               <?= (isset($_POST['rating']) && $_POST['rating'] == $i) || ($user_has_reviewed && $reviews[array_search($user_review_id, array_column($reviews, 'id'))]['rating'] == $i) ? 'checked' : '' ?> 
                                               <?= $i == 5 ? 'required' : '' ?>>
                                        <label for="star<?= $i ?>">
                                            <svg class="w-6 h-6 cursor-pointer" fill="<?= (isset($_POST['rating']) && $_POST['rating'] >= $i) || ($user_has_reviewed && $reviews[array_search($user_review_id, array_column($reviews, 'id'))]['rating'] >= $i) ? 'currentColor' : 'none' ?>" 
                                                 stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                            </svg>
                                        </label>
                                    <?php endfor; ?>
                                </div>
                                <span class="ml-2 text-sm text-gray-500">
                                    <?= isset($_POST['rating']) ? $_POST['rating'] . '/5' : ($user_has_reviewed && isset($reviews[array_search($user_review_id, array_column($reviews, 'id'))]) ? $reviews[array_search($user_review_id, array_column($reviews, 'id'))]['rating'] . '/5' : 'Rate this match') ?>
                                </span>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mb-4">
                        <label for="comment" class="block text-sm font-medium text-gray-700 mb-1">Your Review</label>
                        <textarea id="comment" name="comment" rows="4" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Share your thoughts about the match..." required><?= isset($_POST['comment']) ? htmlspecialchars($_POST['comment']) : ($user_has_reviewed ? htmlspecialchars($reviews[array_search($user_review_id, array_column($reviews, 'id'))]['comment']) : '') ?></textarea>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="button" id="cancel-review" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 mr-3">
                            Cancel
                        </button>
                        <button type="submit" name="submit_review" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <?= $user_has_reviewed ? 'Update Review' : 'Submit Review' ?>
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Reviews List -->
            <div class="border-t border-gray-200">
                <?php if (empty($reviews)): ?>
                    <div class="px-4 py-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No reviews yet</h3>
                        <p class="mt-1 text-sm text-gray-500">Be the first to review this match!</p>
                        <?php if (!is_logged_in()): ?>
                            <div class="mt-6">
                                <a href="login.php?redirect=match.php%3Fid%3D<?= $match_id ?>%23reviews" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Sign in to review
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <ul class="divide-y divide-gray-200">
                        <?php foreach ($reviews as $review): ?>
                            <li class="py-6 px-4 sm:px-6">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <img class="h-10 w-10 rounded-full" src="<?= get_avatar_url($review['user_avatar'] ?? '', 40, $review['user_name']) ?>" alt="<?= htmlspecialchars($review['user_name']) ?>'s avatar">
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">
                                                    <?= htmlspecialchars($review['user_name']) ?>
                                                    <?php if ($review['user_role'] === 'admin'): ?>
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 ml-1">Admin</span>
                                                    <?php endif; ?>
                                                </p>
                                                <div class="flex items-center mt-1">
                                                    <?php if ($review['rating'] > 0): ?>
                                                        <div class="flex items-center">
                                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                                <svg class="h-4 w-4 <?= $i <= $review['rating'] ? 'text-yellow-400' : 'text-gray-300' ?>" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                                 </svg>
                                                             <?php endfor; ?>
                                                             <span class="ml-2 text-sm text-gray-500"><?= $review['rating'] ?>/5</span>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                            Comment based
                                                        </span>
                                                    <?php endif; ?>
                                                    <span class="mx-1 text-gray-300">•</span>
                                                    <span class="text-xs text-gray-500">
                                                        <?= time_elapsed_string($review['created_at']) ?>
                                                        <?php if ($review['created_at'] != $review['updated_at']): ?>
                                                            (edited)
                                                        <?php endif; ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <?php if (is_logged_in() && ($_SESSION['user_id'] == $review['user_id'] || is_admin())): ?>
                                                <div class="relative inline-block text-left" x-data="{ open: false }">
                                                    <div>
                                                        <button @click="open = !open" type="button" class="flex items-center text-gray-400 hover:text-gray-600 focus:outline-none" id="options-menu-<?= $review['id'] ?>" aria-expanded="true" aria-haspopup="true">
                                                            <span class="sr-only">Open options</span>
                                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                                <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                    
                                                    <div x-show="open" @click.away="open = false" class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-10" role="menu" aria-orientation="vertical" aria-labelledby="options-menu-<?= $review['id'] ?>" style="display: none;">
                                                        <div class="py-1" role="none">
                                                            <?php if ($_SESSION['user_id'] == $review['user_id']): ?>
                                                                <a href="#" class="text-gray-700 block px-4 py-2 text-sm hover:bg-gray-100" role="menuitem" onclick="event.preventDefault(); document.getElementById('edit-review-<?= $review['id'] ?>').submit();">
                                                                    Edit review
                                                                </a>
                                                                <form id="edit-review-<?= $review['id'] ?>" action="match.php?id=<?= $match_id ?>#reviews" method="POST" style="display: none;">
                                                                    <input type="hidden" name="edit_review" value="1">
                                                                    <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                                                                </form>
                                                            <?php endif; ?>
                                                            <a href="#" class="text-red-600 block px-4 py-2 text-sm hover:bg-gray-100" role="menuitem" onclick="if(confirm('Are you sure you want to delete this review?')) { document.getElementById('delete-review-<?= $review['id'] ?>').submit(); }">
                                                                Delete review
                                                            </a>
                                                            <form id="delete-review-<?= $review['id'] ?>" action="match.php?id=<?= $match_id ?>" method="POST" style="display: none;">
                                                                <input type="hidden" name="delete_review" value="1">
                                                                <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mt-2 text-sm text-gray-700">
                                            <?= nl2br(htmlspecialchars($review['comment'])) ?>
                                        </div>
                                        
                                        <!-- Reactions -->
                                        <div class="mt-3 flex items-center">
                                            <button type="button" onclick="react(<?= $review['id'] ?>, 'like')" id="btn-like-<?= $review['id'] ?>" class="flex items-center text-sm mr-4 focus:outline-none <?= $review['user_reaction'] === 'like' ? 'text-blue-500' : 'text-gray-500 hover:text-blue-500' ?>">
                                                <svg class="h-4 w-4 mr-1" fill="<?= $review['user_reaction'] === 'like' ? 'currentColor' : 'none' ?>" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"></path>
                                                </svg>
                                                <span id="count-like-<?= $review['id'] ?>"><?= $review['like_count'] ?: '' ?></span>
                                            </button>
                                            
                                            <button type="button" onclick="react(<?= $review['id'] ?>, 'dislike')" id="btn-dislike-<?= $review['id'] ?>" class="flex items-center text-sm mr-4 focus:outline-none <?= $review['user_reaction'] === 'dislike' ? 'text-red-500' : 'text-gray-500 hover:text-red-500' ?>">
                                                <svg class="h-4 w-4 mr-1" fill="<?= $review['user_reaction'] === 'dislike' ? 'currentColor' : 'none' ?>" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14H5.236a2 2 0 01-1.789-2.894l3.5-7A2 2 0 018.736 3h4.018a2 2 0 01.485.06l3.76.94m-7 10v5a2 2 0 002 2h.096c.5 0 .905-.405.905-.904 0-.715.211-1.413.608-2.008L17 13V4m-7 10h2m5-10h2a2 2 0 012 2v6a2 2 0 01-2 2h-2.5"></path>
                                                </svg>
                                                <span id="count-dislike-<?= $review['id'] ?>"><?= $review['dislike_count'] ?: '' ?></span>
                                            </button>

                                            <button type="button" onclick="toggleReply(<?= $review['id'] ?>)" class="flex items-center text-sm text-gray-500 hover:text-blue-500 focus:outline-none">
                                                <i class="far fa-comment-alt mr-1"></i>
                                                <span id="count-reply-<?= $review['id'] ?>"><?= $review['reply_count'] ?: '' ?></span>
                                            </button>
                                            
                                            <?php if (is_logged_in() && is_admin() && $review['user_id'] != $_SESSION['user_id']): ?>
                                                <form method="POST" action="match.php?id=<?= $match_id ?>#reviews" class="ml-auto">
                                                    <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                                                    <button type="submit" name="delete_review" class="text-xs text-red-600 hover:text-red-800" onclick="return confirm('Are you sure you want to delete this review?')">
                                                        Delete
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- Replies Section -->
                                        <div id="replies-section-<?= $review['id'] ?>" class="mt-4 pl-4 border-l-2 border-gray-100 <?= empty($review['replies']) ? 'hidden' : '' ?>">
                                            <div id="replies-list-<?= $review['id'] ?>" class="space-y-3">
                                                <?php foreach ($review['replies'] as $reply): ?>
                                                    <div class="flex items-start">
                                                        <img class="h-6 w-6 rounded-full mt-0.5" src="<?= get_avatar_url($reply['user_avatar'] ?? '', 24, $reply['user_name']) ?>" alt="Avatar">
                                                        <div class="ml-2 bg-gray-50 p-2 rounded-lg flex-1">
                                                            <div class="flex justify-between items-center text-xs mb-1">
                                                                <span class="font-semibold text-gray-800"><?= htmlspecialchars($reply['user_name']) ?></span>
                                                                <span class="text-gray-500"><?= time_elapsed_string($reply['created_at']) ?></span>
                                                            </div>
                                                            <p class="text-sm text-gray-700"><?= nl2br(htmlspecialchars($reply['content'])) ?></p>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                        
                                        <!-- Reply Form (Hidden by default) -->
                                        <div id="reply-form-container-<?= $review['id'] ?>" class="mt-3 hidden pl-4 border-l-2 border-gray-100">
                                            <?php if (is_logged_in()): ?>
                                                <div class="flex items-start">
                                                    <img class="h-8 w-8 rounded-full mr-2" src="<?= get_avatar_url($_SESSION['avatar_url'] ?? '', 32, $_SESSION['display_name'] ?? $_SESSION['username']) ?>" alt="My Avatar">
                                                    <div class="flex-1">
                                                        <textarea id="reply-input-<?= $review['id'] ?>" rows="2" class="w-full border border-gray-300 rounded-md p-2 text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Write a reply..."></textarea>
                                                        <div class="mt-2 text-right">
                                                            <button onclick="submitReply(<?= $review['id'] ?>)" class="bg-blue-600 text-white text-xs px-3 py-1.5 rounded hover:bg-blue-700">Reply</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <p class="text-sm text-gray-500"><a href="login.php?redirect=match.php?id=<?= $match_id ?>" class="text-blue-600 hover:underline">Log in</a> to reply.</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <?php if ($total_pages > 1): ?>
                        <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                            <div class="flex-1 flex justify-between sm:hidden">
                                <?php if ($page > 1): ?>
                                    <a href="?id=<?= $match_id ?>&page=<?= $page - 1 ?>#reviews" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                        Previous
                                    </a>
                                <?php endif; ?>
                                <?php if ($page < $total_pages): ?>
                                    <a href="?id=<?= $match_id ?>&page=<?= $page + 1 ?>#reviews" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                        Next
                                    </a>
                                <?php endif; ?>
                            </div>
                            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm text-gray-700">
                                        Showing <span class="font-medium"><?= $offset + 1 ?></span> to 
                                        <span class="font-medium"><?= min($offset + $per_page, count($reviews)) ?></span> of 
                                        <span class="font-medium"><?= count($reviews) ?></span> results
                                    </p>
                                </div>
                                <div>
                                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                        <?php if ($page > 1): ?>
                                            <a href="?id=<?= $match_id ?>&page=<?= $page - 1 ?>#reviews" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                                <span class="sr-only">Previous</span>
                                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                            <a href="?id=<?= $match_id ?>&page=<?= $i ?>#reviews" class="relative inline-flex items-center px-4 py-2 border <?= $i == $page ? 'border-blue-500 bg-blue-50 text-blue-600' : 'border-gray-300 bg-white text-gray-500 hover:bg-gray-50' ?> text-sm font-medium">
                                                <?= $i ?>
                                            </a>
                                        <?php endfor; ?>
                                        
                                        <?php if ($page < $total_pages): ?>
                                            <a href="?id=<?= $match_id ?>&page=<?= $page + 1 ?>#reviews" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                                <span class="sr-only">Next</span>
                                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </a>
                                        <?php endif; ?>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    // Tab switching functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Tab elements
        const tabs = {
            'summary-tab': document.getElementById('summary-tab'),
            'stats-tab': document.getElementById('stats-tab'),
            'reviews-tab': document.getElementById('reviews-tab')
        };
        
        const contents = {
            'summary-content': document.getElementById('summary-content'),
            'stats-content': document.getElementById('stats-content'),
            'reviews-content': document.getElementById('reviews-content')
        };
        
        // Function to switch tabs
        function switchTab(activeTabId) {
            // Hide all content
            Object.values(contents).forEach(content => {
                content.classList.add('hidden');
            });
            
            // Remove active styles from all tabs
            Object.values(tabs).forEach(tab => {
                tab.classList.remove('border-blue-500', 'text-blue-600');
                tab.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
            });
            
            // Show active content and style active tab
            const contentId = activeTabId.replace('-tab', '-content');
            if (contents[contentId]) {
                contents[contentId].classList.remove('hidden');
                tabs[activeTabId].classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
                tabs[activeTabId].classList.add('border-blue-500', 'text-blue-600');
                
                // Update URL hash
                window.location.hash = contentId.replace('-content', '');
            }
        }
        
        // Add click event listeners to tabs
        Object.keys(tabs).forEach(tabId => {
            tabs[tabId].addEventListener('click', () => switchTab(tabId));
        });
        
        // Check URL hash on page load
        const hash = window.location.hash.substring(1);
        if (hash && tabs[`${hash}-tab`]) {
            switchTab(`${hash}-tab`);
        } else if (hash === 'reviews') {
            switchTab('reviews-tab');
        }
        
        // Review form toggle
        const writeReviewBtn = document.getElementById('write-review-btn');
        const cancelReviewBtn = document.getElementById('cancel-review');
        const reviewFormContainer = document.getElementById('review-form-container');
        
        if (writeReviewBtn && reviewFormContainer) {
            writeReviewBtn.addEventListener('click', () => {
                reviewFormContainer.classList.toggle('hidden');
                if (!reviewFormContainer.classList.contains('hidden')) {
                    reviewFormContainer.scrollIntoView({ behavior: 'smooth' });
                }
            });
        }
        
        if (cancelReviewBtn && reviewFormContainer) {
            cancelReviewBtn.addEventListener('click', () => {
                reviewFormContainer.classList.add('hidden');
            });
        }
        
        // Star rating interaction
        const ratingInputs = document.querySelectorAll('.rating input');
        const ratingValue = document.querySelector('.rating-value');
        
        ratingInputs.forEach(input => {
            input.addEventListener('change', function() {
                const rating = this.value;
                if (ratingValue) {
                    ratingValue.textContent = `${rating}/10`;
                }
                
                // Update star colors
                const stars = this.parentNode.parentNode.querySelectorAll('svg');
                stars.forEach((star, index) => {
                    if (index < rating) {
                        star.classList.add('text-yellow-400');
                        star.classList.remove('text-gray-300');
                        star.setAttribute('fill', 'currentColor');
                    } else {
                        star.classList.remove('text-yellow-400');
                        star.classList.add('text-gray-300');
                        star.setAttribute('fill', 'none');
                    }
                });
            });
            
            // Hover effect
            input.addEventListener('mouseover', function() {
                const rating = this.value;
                const stars = this.parentNode.parentNode.querySelectorAll('svg');
                
                stars.forEach((star, index) => {
                    if (index < rating) {
                        star.classList.add('text-yellow-400');
                        star.classList.remove('text-gray-300');
                    } else {
                        star.classList.remove('text-yellow-400');
                        star.classList.add('text-gray-300');
                    }
                });
            });
            
            // Reset on mouseout if not selected
            input.addEventListener('mouseout', function() {
                const selectedRating = document.querySelector('.rating input:checked');
                const stars = this.parentNode.parentNode.querySelectorAll('svg');
                
                if (selectedRating) {
                    const rating = selectedRating.value;
                    stars.forEach((star, index) => {
                        if (index < rating) {
                            star.classList.add('text-yellow-400');
                            star.classList.remove('text-gray-300');
                        } else {
                            star.classList.remove('text-yellow-400');
                            star.classList.add('text-gray-300');
                        }
                    });
                } else {
                    stars.forEach(star => {
                        star.classList.remove('text-yellow-400');
                        star.classList.add('text-gray-300');
                    });
                }
            });
        });
    }); 
    
    function react(reviewId, type) {
        fetch(`${window.APP_CONFIG.baseUrl}/api/react.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                review_id: reviewId,
                type: type
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update counts
                document.getElementById('count-like-' + reviewId).textContent = data.likes > 0 ? data.likes : '';
                document.getElementById('count-dislike-' + reviewId).textContent = data.dislikes > 0 ? data.dislikes : '';
                
                // Update UI (colors/icons)
                const btnLike = document.getElementById('btn-like-' + reviewId);
                const btnDislike = document.getElementById('btn-dislike-' + reviewId);
                const svgLike = btnLike.querySelector('svg');
                const svgDislike = btnDislike.querySelector('svg');
                
                // Reset both - remove active classes, add inactive hover classes
                // Like button
                btnLike.classList.remove('text-blue-500'); 
                if (!btnLike.classList.contains('text-gray-500')) btnLike.classList.add('text-gray-500');
                if (!btnLike.classList.contains('hover:text-blue-500')) btnLike.classList.add('hover:text-blue-500');
                svgLike.setAttribute('fill', 'none');
                
                // Dislike button
                btnDislike.classList.remove('text-red-500');
                if (!btnDislike.classList.contains('text-gray-500')) btnDislike.classList.add('text-gray-500');
                if (!btnDislike.classList.contains('hover:text-red-500')) btnDislike.classList.add('hover:text-red-500');
                svgDislike.setAttribute('fill', 'none');
                
                // Apply new state
                if (data.new_status === 'like') {
                    btnLike.classList.add('text-blue-500');
                    btnLike.classList.remove('text-gray-500', 'hover:text-blue-500');
                    svgLike.setAttribute('fill', 'currentColor');
                } else if (data.new_status === 'dislike') {
                    btnDislike.classList.add('text-red-500');
                    btnDislike.classList.remove('text-gray-500', 'hover:text-red-500');
                    svgDislike.setAttribute('fill', 'currentColor');
                }
            } else {
                if (data.message === 'Must be logged in') {
                    window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.href);
                } else {
                    console.error('Error: ' + data.message);
                }
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function toggleReply(reviewId) {
        const form = document.getElementById('reply-form-container-' + reviewId);
        if (form) {
            form.classList.toggle('hidden');
            if (!form.classList.contains('hidden')) {
                const input = document.getElementById('reply-input-' + reviewId);
                if (input) input.focus();
            }
        }
    }

    function submitReply(reviewId) {
        const input = document.getElementById('reply-input-' + reviewId);
        const content = input.value.trim();
        
        if (!content) return;
        
        fetch(`${window.APP_CONFIG.baseUrl}/api/reply.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                review_id: reviewId,
                content: content
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Clear input
                input.value = '';
                toggleReply(reviewId); // Hide form
                
                // Update count
                const countSpan = document.getElementById('count-reply-' + reviewId);
                if (countSpan && data.reply_count !== undefined) {
                    countSpan.textContent = data.reply_count;
                }
                
                // Show section if hidden
                const section = document.getElementById('replies-section-' + reviewId);
                section.classList.remove('hidden');
                
                // Append reply
                const list = document.getElementById('replies-list-' + reviewId);
                const replyHtml = `
                    <div class="flex items-start fade-in">
                        <img class="h-6 w-6 rounded-full mt-0.5" src="${data.reply.avatar_url}" alt="Avatar">
                        <div class="ml-2 bg-gray-50 p-2 rounded-lg flex-1">
                            <div class="flex justify-between items-center text-xs mb-1">
                                <span class="font-semibold text-gray-800">${data.reply.user_name}</span>
                                <span class="text-gray-500">${data.reply.created_at}</span>
                            </div>
                            <p class="text-sm text-gray-700">${data.reply.content}</p>
                        </div>
                    </div>
                `;
                list.insertAdjacentHTML('beforeend', replyHtml);
                
            } else {
                if (data.message === 'Must be logged in') {
                    window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.href);
                } else {
                    alert('Error: ' + data.message);
                }
            }
        })
        .catch(error => console.error('Error:', error));
    }
</script>

<?php include 'includes/partials/footer.php'; ?>
<script src="assets/js/main.js"></script>
</body>
</html>
