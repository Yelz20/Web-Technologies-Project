<?php
// matches.php
require_once 'includes/config.php';

$db = Database::getInstance()->getConnection();

$search = isset($_GET['search']) ? trim($_GET['search']) : null;
$team_id = isset($_GET['team']) ? (int)$_GET['team'] : null;

// Helper function to get matches
function get_matches($db, $type_or_filter, $limit = 50) {
    // If array, it's a filter (search mode)
    if (is_array($type_or_filter)) {
        $filters = $type_or_filter;
        $where_clauses = ["1=1"];
        $params = [];
        
        if (!empty($filters['search'])) {
            $where_clauses[] = "(ht.name LIKE ? OR at.name LIKE ?)";
            $params[] = "%{$filters['search']}%";
            $params[] = "%{$filters['search']}%";
        }
        
        if (!empty($filters['team_id'])) {
            $where_clauses[] = "(m.home_team = ? OR m.away_team = ?)";
            $params[] = $filters['team_id'];
            $params[] = $filters['team_id'];
        }
        
        $where = implode(' AND ', $where_clauses);
        $order = "m.match_date DESC, m.match_time DESC";
    } else {
        // Legacy string mode
        switch ($type_or_filter) {
            case 'upcoming':
                $where = "m.status = 'scheduled'";
                $order = "m.match_date ASC, m.match_time ASC";
                break;
            case 'in_play':
                $where = "m.status = 'in_play'";
                $order = "m.match_date DESC, m.match_time DESC";
                break;
            case 'postponed':
                $where = "m.status = 'postponed'";
                $order = "m.match_date DESC, m.match_time DESC";
                break;
            case 'cancelled':
                $where = "m.status = 'cancelled'";
                $order = "m.match_date DESC, m.match_time DESC";
                break;
            case 'played':
                // Exclude scheduled, in_play, postponed, cancelled
                $where = "m.status IN ('FT', 'ET', 'PEN')";
                $order = "m.match_date DESC, m.match_time DESC";
                break;
            default:
                $where = "m.status != 'scheduled'";
                $order = "m.match_date DESC, m.match_time DESC";
        }
        $params = [];
    }

    $sql = "
        SELECT m.*, 
               ht.name as home_team_name, 
               at.name as away_team_name,
               ht.logo as home_team_logo,
               at.logo as away_team_logo,
               c.name as competition_name,
               (SELECT COUNT(*) FROM reviews WHERE match_id = m.id) as review_count
        FROM matches m
        LEFT JOIN teams ht ON m.home_team = ht.id
        LEFT JOIN teams at ON m.away_team = at.id
        LEFT JOIN competitions c ON m.competition_id = c.id
        WHERE $where
        ORDER BY $order
        LIMIT $limit
    ";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

$is_search = ($search || $team_id);

if ($is_search) {
    $search_results = get_matches($db, ['search' => $search, 'team_id' => $team_id]);
    $in_play_matches = [];
    $upcoming_matches = [];
    $played_matches = [];
    $postponed_matches = [];
    $cancelled_matches = [];
} else {
    $in_play_matches = get_matches($db, 'in_play');
    $upcoming_matches = get_matches($db, 'upcoming');
    $played_matches = get_matches($db, 'played');
    $postponed_matches = get_matches($db, 'postponed');
    $cancelled_matches = get_matches($db, 'cancelled');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Football Review - Matches</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="bg-gray-100">
<?php include 'includes/partials/header.php'; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Football Matches</h1>
        <?php if (is_logged_in() && is_admin()): ?>
            <a href="admin/match-add.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                <i class="fas fa-plus mr-2"></i> Add New Match
            </a>
        <?php endif; ?>
    </div>

    <?php if ($is_search): ?>
        <!-- Search Results Section -->
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-800 mb-4 border-l-4 border-purple-500 pl-3">
                <?php if ($search): ?>
                    Search Results for "<?= htmlspecialchars($search) ?>"
                <?php elseif ($team_id): ?>
                    Matches for Selected Team
                <?php else: ?>
                    All Matches
                <?php endif; ?>
            </h2>
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <?php if (empty($search_results)): ?>
                    <div class="p-6 text-center text-gray-500">No matches found.</div>
                <?php else: ?>
                    <ul class="divide-y divide-gray-200">
                        <?php foreach ($search_results as $match): ?>
                            <?php 
                                $is_played = ($match['status'] === 'FT'); // Simple check, or check != scheduled 
                                $home_bold = ($is_played && $match['home_team_score'] > $match['away_team_score']) ? 'font-bold' : '';
                                $away_bold = ($is_played && $match['away_team_score'] > $match['home_team_score']) ? 'font-bold' : '';
                            ?>
                            <li>
                                <a href="match.php?id=<?= $match['id'] ?>" class="block hover:bg-gray-50 transition duration-150 ease-in-out">
                                    <div class="px-4 py-4 sm:px-6">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center flex-1">
                                                <!-- Home -->
                                                <div class="flex items-center w-1/3 justify-end">
                                                    <span class="text-sm font-medium text-gray-900 <?= $home_bold ?> mr-3"><?= htmlspecialchars($match['home_team_name']) ?></span>
                                                    <?php if (!empty($match['home_team_logo'])): ?>
                                                        <img class="h-8 w-8 object-contain" src="<?= htmlspecialchars($match['home_team_logo']) ?>" alt="">
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <!-- VS / Score -->
                                                <div class="w-24 text-center px-2">
                                                    <?php if ($is_played): ?>
                                                        <div class="text-lg font-bold text-gray-900 bg-gray-100 px-3 py-1 rounded">
                                                            <?= $match['home_team_score'] ?> - <?= $match['away_team_score'] ?>
                                                        </div> 
                                                    <?php else: ?>
                                                        <span class="text-xs font-bold text-blue-600 bg-blue-100 px-2 py-1 rounded-full">VS</span>
                                                    <?php endif; ?>
                                                    <div class="text-xs text-gray-500 mt-1"><?= date('M j, Y', strtotime($match['match_date'])) ?></div>
                                                </div>
                                                
                                                <!-- Away -->
                                                <div class="flex items-center w-1/3">
                                                    <?php if (!empty($match['away_team_logo'])): ?>
                                                        <img class="h-8 w-8 object-contain mr-3" src="<?= htmlspecialchars($match['away_team_logo']) ?>" alt="">
                                                    <?php endif; ?>
                                                    <span class="text-sm font-medium text-gray-900 <?= $away_bold ?>"><?= htmlspecialchars($match['away_team_name']) ?></span>
                                                </div>
                                            </div>
                                            <div class="ml-4 text-right">
                                                <?php if ($match['review_count'] > 0): ?>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                        <?= $match['review_count'] ?> reviews
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-xs text-gray-400">No reviews</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
    
        <!-- In Play Matches Section -->
        <?php if (!empty($in_play_matches)): ?>
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-800 mb-4 border-l-4 border-red-600 pl-3 flex items-center">
                <span class="relative flex h-3 w-3 mr-3">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                </span>
                Live Matches
            </h2>
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <ul class="divide-y divide-gray-200">
                    <?php foreach ($in_play_matches as $match): ?>
                        <li>
                            <a href="match.php?id=<?= $match['id'] ?>" class="block hover:bg-gray-50 transition duration-150 ease-in-out">
                                <div class="px-4 py-4 sm:px-6">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center flex-1">
                                            <!-- Home -->
                                            <div class="flex items-center w-1/3 justify-end">
                                                <span class="text-sm font-medium text-gray-900 mr-3"><?= htmlspecialchars($match['home_team_name']) ?></span>
                                                <?php if (!empty($match['home_team_logo'])): ?>
                                                    <img class="h-8 w-8 object-contain" src="<?= htmlspecialchars(get_logo_url($match['home_team_logo'])) ?>" alt="">
                                                <?php endif; ?>
                                            </div>
                                            
                                            <!-- Center -->
                                            <div class="w-24 text-center px-2">
                                                <span class="text-xs font-bold text-red-600 bg-red-100 px-2 py-1 rounded-full animate-pulse">LIVE</span>
                                                <div class="text-xs text-gray-500 mt-1">In Play</div>
                                            </div>
                                            
                                            <!-- Away -->
                                            <div class="flex items-center w-1/3">
                                                <?php if (!empty($match['away_team_logo'])): ?>
                                                    <img class="h-8 w-8 object-contain mr-3" src="<?= htmlspecialchars(get_logo_url($match['away_team_logo'])) ?>" alt="">
                                                <?php endif; ?>
                                                <span class="text-sm font-medium text-gray-900"><?= htmlspecialchars($match['away_team_name']) ?></span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <i class="fas fa-chevron-right text-gray-400"></i>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>

        <!-- Upcoming Matches Section -->
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-800 mb-4 border-l-4 border-blue-500 pl-3">Upcoming Matches</h2>
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <?php if (empty($upcoming_matches)): ?>
                    <div class="p-6 text-center text-gray-500">No upcoming matches scheduled.</div>
                <?php else: ?>
                    <ul class="divide-y divide-gray-200">
                        <?php foreach ($upcoming_matches as $match): ?>
                            <li>
                                <a href="match.php?id=<?= $match['id'] ?>" class="block hover:bg-gray-50 transition duration-150 ease-in-out">
                                    <div class="px-4 py-4 sm:px-6">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center flex-1">
                                                <!-- Home -->
                                                <div class="flex items-center w-1/3 justify-end">
                                                    <span class="text-sm font-medium text-gray-900 mr-3"><?= htmlspecialchars($match['home_team_name']) ?></span>
                                                    <?php if (!empty($match['home_team_logo'])): ?>
                                                        <img class="h-8 w-8 object-contain" src="<?= htmlspecialchars($match['home_team_logo']) ?>" alt="">
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <!-- VS / Time -->
                                                <div class="w-24 text-center px-2">
                                                    <span class="text-xs font-bold text-blue-600 bg-blue-100 px-2 py-1 rounded-full">VS</span>
                                                    <div class="text-xs text-gray-500 mt-1"><?= date('M j, Y', strtotime($match['match_date'])) ?></div>
                                                    <div class="text-xs text-gray-500"><?= date('g:i A', strtotime($match['match_time'])) ?></div>
                                                </div>
                                                
                                                <!-- Away -->
                                                <div class="flex items-center w-1/3">
                                                    <?php if (!empty($match['away_team_logo'])): ?>
                                                        <img class="h-8 w-8 object-contain mr-3" src="<?= htmlspecialchars($match['away_team_logo']) ?>" alt="">
                                                    <?php endif; ?>
                                                    <span class="text-sm font-medium text-gray-900"><?= htmlspecialchars($match['away_team_name']) ?></span>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <i class="fas fa-chevron-right text-gray-400"></i>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <!-- Postponed Matches Section -->
        <?php if (!empty($postponed_matches)): ?>
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-800 mb-4 border-l-4 border-yellow-500 pl-3">Postponed Matches</h2>
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <ul class="divide-y divide-gray-200">
                    <?php foreach ($postponed_matches as $match): ?>
                        <li>
                            <a href="match.php?id=<?= $match['id'] ?>" class="block hover:bg-gray-50 transition duration-150 ease-in-out">
                                <div class="px-4 py-4 sm:px-6">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center flex-1">
                                            <!-- Home -->
                                            <div class="flex items-center w-1/3 justify-end">
                                                <span class="text-sm font-medium text-gray-900 mr-3"><?= htmlspecialchars($match['home_team_name']) ?></span>
                                                <?php if (!empty($match['home_team_logo'])): ?>
                                                    <img class="h-8 w-8 object-contain" src="<?= htmlspecialchars($match['home_team_logo']) ?>" alt="">
                                                <?php endif; ?>
                                            </div>
                                            
                                            <!-- Center -->
                                            <div class="w-24 text-center px-2">
                                                <span class="text-xs font-bold text-yellow-700 bg-yellow-100 px-2 py-1 rounded-full">Postponed</span>
                                                <div class="text-xs text-gray-500 mt-1"><?= date('M j', strtotime($match['match_date'])) ?></div>
                                            </div>
                                            
                                            <!-- Away -->
                                            <div class="flex items-center w-1/3">
                                                <?php if (!empty($match['away_team_logo'])): ?>
                                                    <img class="h-8 w-8 object-contain mr-3" src="<?= htmlspecialchars($match['away_team_logo']) ?>" alt="">
                                                <?php endif; ?>
                                                <span class="text-sm font-medium text-gray-900"><?= htmlspecialchars($match['away_team_name']) ?></span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <i class="fas fa-chevron-right text-gray-400"></i>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>

        <!-- Cancelled Matches Section -->
        <?php if (!empty($cancelled_matches)): ?>
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-800 mb-4 border-l-4 border-gray-500 pl-3">Cancelled Matches</h2>
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <ul class="divide-y divide-gray-200">
                    <?php foreach ($cancelled_matches as $match): ?>
                        <li>
                            <a href="match.php?id=<?= $match['id'] ?>" class="block hover:bg-gray-50 transition duration-150 ease-in-out">
                                <div class="px-4 py-4 sm:px-6">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center flex-1">
                                            <!-- Home -->
                                            <div class="flex items-center w-1/3 justify-end">
                                                <span class="text-sm font-medium text-gray-900 mr-3"><?= htmlspecialchars($match['home_team_name']) ?></span>
                                                <?php if (!empty($match['home_team_logo'])): ?>
                                                    <img class="h-8 w-8 object-contain" src="<?= htmlspecialchars($match['home_team_logo']) ?>" alt="">
                                                <?php endif; ?>
                                            </div>
                                            
                                            <!-- Center -->
                                            <div class="w-24 text-center px-2">
                                                <span class="text-xs font-bold text-gray-700 bg-gray-200 px-2 py-1 rounded-full">Cancelled</span>
                                                <div class="text-xs text-gray-500 mt-1"><?= date('M j', strtotime($match['match_date'])) ?></div>
                                            </div>
                                            
                                            <!-- Away -->
                                            <div class="flex items-center w-1/3">
                                                <?php if (!empty($match['away_team_logo'])): ?>
                                                    <img class="h-8 w-8 object-contain mr-3" src="<?= htmlspecialchars($match['away_team_logo']) ?>" alt="">
                                                <?php endif; ?>
                                                <span class="text-sm font-medium text-gray-900"><?= htmlspecialchars($match['away_team_name']) ?></span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <i class="fas fa-chevron-right text-gray-400"></i>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>

        <!-- Played Matches Section -->
        <div>
            <h2 class="text-2xl font-bold text-gray-800 mb-4 border-l-4 border-green-500 pl-3">Matches Played</h2>
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <?php if (empty($played_matches)): ?>
                    <div class="p-6 text-center text-gray-500">No matches played yet.</div>
                <?php else: ?>
                    <ul class="divide-y divide-gray-200">
                        <?php foreach ($played_matches as $match): ?>
                            <li>
                                <a href="match.php?id=<?= $match['id'] ?>" class="block hover:bg-gray-50 transition duration-150 ease-in-out">
                                    <div class="px-4 py-4 sm:px-6">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center flex-1">
                                                <!-- Home -->
                                                <div class="flex items-center w-1/3 justify-end">
                                                    <span class="text-sm font-medium <?= $match['home_team_score'] > $match['away_team_score'] ? 'text-gray-900 font-bold' : 'text-gray-600' ?> mr-3"><?= htmlspecialchars($match['home_team_name']) ?></span>
                                                    <?php if (!empty($match['home_team_logo'])): ?>
                                                        <img class="h-8 w-8 object-contain" src="<?= htmlspecialchars($match['home_team_logo']) ?>" alt="">
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <!-- Score -->
                                                <div class="w-24 text-center px-2">
                                                    <div class="text-lg font-bold text-gray-900 bg-gray-100 px-3 py-1 rounded">
                                                        <?= $match['home_team_score'] ?> - <?= $match['away_team_score'] ?>
                                                    </div>
                                                    <div class="text-xs text-gray-500 mt-1"><?= date('M j', strtotime($match['match_date'])) ?></div>
                                                </div>
                                                
                                                <!-- Away -->
                                                <div class="flex items-center w-1/3">
                                                    <?php if (!empty($match['away_team_logo'])): ?>
                                                        <img class="h-8 w-8 object-contain mr-3" src="<?= htmlspecialchars($match['away_team_logo']) ?>" alt="">
                                                    <?php endif; ?>
                                                    <span class="text-sm font-medium <?= $match['away_team_score'] > $match['home_team_score'] ? 'text-gray-900 font-bold' : 'text-gray-600' ?>"><?= htmlspecialchars($match['away_team_name']) ?></span>
                                                </div>
                                            </div>
                                            <div class="ml-4 text-right">
                                                <?php if ($match['review_count'] > 0): ?>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                        <?= $match['review_count'] ?> reviews
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-xs text-gray-400">No reviews</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php include 'includes/partials/footer.php'; ?>
</body>
</html>
