<?php
// Check if the user is authorized.
require_once 'auth-check.php';
$db = Database::getInstance()->getConnection();

// Admin always sees the most recent data.
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Handle any changes the admin makes.
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            if ($_POST['action'] === 'add' || $_POST['action'] === 'edit') {
                // Check to make sure nobody pits a team against itself.
                if ($_POST['home_team'] === $_POST['away_team']) {
                    throw new Exception('Home team and away team must be different!');
                }
                
                // Check to see if the teams actually belong in the league being selected.
                if ($_POST['competition_id']) {
                    $leagueId = $_POST['competition_id'];
                    
                    // Check if both teams have played in this league.
                    $homeTeamCheck = $db->prepare("SELECT COUNT(*) FROM matches 
                        WHERE (home_team = ? OR away_team = ?) AND competition_id = ?");
                    $homeTeamCheck->execute([$_POST['home_team'], $_POST['home_team'], $leagueId]);
                    
                    $awayTeamCheck = $db->prepare("SELECT COUNT(*) FROM matches 
                        WHERE (home_team = ? OR away_team = ?) AND competition_id = ?");
                    $awayTeamCheck->execute([$_POST['away_team'], $_POST['away_team'], $leagueId]);
                    
                    // Get team names for error message.
                    $homeTeamName = $db->prepare("SELECT name FROM teams WHERE id = ?");
                    $homeTeamName->execute([$_POST['home_team']]);
                    $homeTeamNameStr = $homeTeamName->fetchColumn();
                    
                    $awayTeamName = $db->prepare("SELECT name FROM teams WHERE id = ?");
                    $awayTeamName->execute([$_POST['away_team']]);
                    $awayTeamNameStr = $awayTeamName->fetchColumn();
                    
                    $leagueName = $db->prepare("SELECT name FROM competitions WHERE id = ?");
                    $leagueName->execute([$leagueId]);
                    $leagueNameStr = $leagueName->fetchColumn();
                    
                    // For new matches being added to this league combination.
                    if ($homeTeamCheck->fetchColumn() == 0 || $awayTeamCheck->fetchColumn() == 0) {
                        // Allow if it's the first match in this league (both teams have 0)
                        // But warn if teams are from clearly different leagues
                        $homeLeague = $db->prepare("SELECT c.name FROM competitions c 
                            JOIN matches m ON c.id = m.competition_id 
                            WHERE (m.home_team = ? OR m.away_team = ?) 
                            GROUP BY c.id ORDER BY COUNT(*) DESC LIMIT 1");
                        $homeLeague->execute([$_POST['home_team'], $_POST['home_team']]);
                        $homeLeagueName = $homeLeague->fetchColumn();
                        
                        $awayLeague = $db->prepare("SELECT c.name FROM competitions c 
                            JOIN matches m ON c.id = m.competition_id 
                            WHERE (m.home_team = ? OR m.away_team = ?) 
                            GROUP BY c.id ORDER BY COUNT(*) DESC LIMIT 1");
                        $awayLeague->execute([$_POST['away_team'], $_POST['away_team']]);
                        $awayLeagueName = $awayLeague->fetchColumn();
                        
                        if ($homeLeagueName && $awayLeagueName && $homeLeagueName !== $awayLeagueName && $homeLeagueName !== $leagueNameStr && $awayLeagueName !== $leagueNameStr) {
                            throw new Exception("Warning: $homeTeamNameStr typically plays in $homeLeagueName and $awayTeamNameStr typically plays in $awayLeagueName. This match is being added to $leagueNameStr.");
                        }
                    }
                }
            }
            
            if ($_POST['action'] === 'add') {
                
                // Add new match
                $stmt = $db->prepare("INSERT INTO matches (home_team, away_team, competition_id, match_date, match_time, status, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([
                    $_POST['home_team'],
                    $_POST['away_team'],
                    $_POST['competition_id'],
                    $_POST['match_date'],
                    $_POST['match_time'],
                    $_POST['status']
                ]);
                $message = 'Match added successfully!';
                $message_type = 'success';
            } elseif ($_POST['action'] === 'edit') {
                // Validate teams are different
                if ($_POST['home_team'] === $_POST['away_team']) {
                    throw new Exception('Home team and away team must be different!');
                }
                
                // Update match
                $stmt = $db->prepare("UPDATE matches SET home_team = ?, away_team = ?, competition_id = ?, match_date = ?, match_time = ?, status = ?, home_team_score = ?, away_team_score = ? WHERE id = ?");
                $stmt->execute([
                    $_POST['home_team'],
                    $_POST['away_team'],
                    $_POST['competition_id'],
                    $_POST['match_date'],
                    $_POST['match_time'],
                    $_POST['status'],
                    $_POST['home_team_score'] ?: null,
                    $_POST['away_team_score'] ?: null,
                    $_POST['id']
                ]);
                $message = 'Match updated successfully!';
                $message_type = 'success';
            } elseif ($_POST['action'] === 'delete') {
                // Delete match
                $stmt = $db->prepare("DELETE FROM matches WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                $message = 'Match deleted successfully!';
                $message_type = 'success';
            }
        }
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $message_type = 'error';
    }
}

// Handles the search and filtering logic.
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$league = $_GET['league'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Build query with filters
$whereConditions = [];
$params = [];

if ($search) {
    $whereConditions[] = "(ht.name LIKE ? OR at.name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($status) {
    $whereConditions[] = "m.status = ?";
    $params[] = $status;
}
if ($league) {
    $whereConditions[] = "m.competition_id = ?";
    $params[] = $league;
}

$whereClause = $whereConditions ? " WHERE " . implode(" AND ", $whereConditions) : "";

// Calculate how many matches match our filters.
$countStmt = $db->prepare("SELECT COUNT(*) FROM matches m 
    JOIN teams ht ON m.home_team = ht.id 
    JOIN teams at ON m.away_team = at.id" . $whereClause);
$countStmt->execute($params);
$totalMatches = $countStmt->fetchColumn();
$totalPages = ceil($totalMatches / $perPage);

// Fetch the actual match details, including the team names and logos.
$stmt = $db->prepare("SELECT m.*, 
    ht.name as home_team_name, ht.logo as home_logo,
    at.name as away_team_name, at.logo as away_logo,
    c.name as competition_name
    FROM matches m 
    JOIN teams ht ON m.home_team = ht.id 
    JOIN teams at ON m.away_team = at.id 
    LEFT JOIN competitions c ON m.competition_id = c.id" . $whereClause . " 
    ORDER BY m.match_date DESC, m.match_time DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$matches = $stmt->fetchAll();

// Pull in the lists of teams and leagues.
$teams = $db->query("SELECT id, name, logo FROM teams ORDER BY name ASC")->fetchAll();

$leagues = $db->query("SELECT c.id, c.name, COUNT(DISTINCT m.id) as match_count 
    FROM competitions c 
    LEFT JOIN matches m ON c.id = m.competition_id 
    GROUP BY c.id, c.name 
    ORDER BY c.name ASC")->fetchAll();

// Get team-league associations for validation.
$teamLeaguesStmt = $db->query("
    SELECT DISTINCT t.id as team_id, m.competition_id as league_id
    FROM teams t
    JOIN matches m ON (t.id = m.home_team OR t.id = m.away_team)
    WHERE m.competition_id IS NOT NULL
");
$teamLeagues = [];
while ($row = $teamLeaguesStmt->fetch()) {
    if (!isset($teamLeagues[$row['team_id']])) {
        $teamLeagues[$row['team_id']] = [];
    }
    $teamLeagues[$row['team_id']][] = $row['league_id'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Matches - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
<?php include 'includes/header.php'; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Manage Matches</h1>
            <p class="mt-2 text-gray-600">Add, edit, or remove football matches</p>
        </div>
        <button onclick="showAddModal()" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
            <i class="fas fa-plus mr-2"></i>Add New Match
        </button>
    </div>

    <!-- Success/Error Messages -->
    <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-lg <?= $message_type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="mb-6 bg-white rounded-lg shadow-md p-4">
        <form method="GET" action="matches.php" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                   placeholder="Search teams..." 
                   class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            
            <select name="status" onchange="this.form.submit()" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">All Statuses</option>
                <option value="scheduled" <?= $status === 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                <option value="in_play" <?= $status === 'in_play' ? 'selected' : '' ?>>In Play</option>
                <option value="FT" <?= $status === 'FT' ? 'selected' : '' ?>>Full Time</option>
                <option value="postponed" <?= $status === 'postponed' ? 'selected' : '' ?>>Postponed</option>
                <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>
            
            <select name="league" onchange="this.form.submit()" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">All Leagues</option>
                <?php foreach ($leagues as $l): ?>
                    <option value="<?= $l['id'] ?>" <?= $league == $l['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($l['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-filter mr-2"></i>Filter
                </button>
                <?php if ($search || $status || $league): ?>
                    <a href="matches.php" class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 flex items-center justify-center">
                        <i class="fas fa-times"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Matches List -->
    <div class="space-y-4">
        <?php if (empty($matches)): ?>
            <div class="bg-white rounded-lg shadow-md p-8 text-center">
                <i class="fas fa-futbol text-6xl text-gray-300 mb-4"></i>
                <p class="text-gray-500 mb-4">No matches found.</p>
                <button onclick="showAddModal()" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-plus mr-2"></i>Add Your First Match
                </button>
            </div>
        <?php else: ?>
            <?php foreach ($matches as $match): ?>
                <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                    <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                        <!-- Here we show the core details of the match, like the competition and date -->
                        <div class="flex-1 w-full">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-sm text-gray-500">
                                    <i class="fas fa-trophy mr-1"></i><?= htmlspecialchars($match['competition_name'] ?? 'No League') ?>
                                </span>
                                <span class="text-sm text-gray-500">
                                    <i class="far fa-calendar-alt mr-1"></i><?= date('M j, Y', strtotime($match['match_date'])) ?>
                                    <span class="mx-2">|</span>
                                    <i class="far fa-clock mr-1"></i><?= date('g:i A', strtotime($match['match_time'])) ?>
                                </span>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <!-- Our home team display with their local crest -->
                                <div class="flex items-center flex-1">
                                    <?php if ($match['home_logo']): ?>
                                        <img src="<?= htmlspecialchars(get_logo_url($match['home_logo'])) ?>" 
                                             alt="<?= htmlspecialchars($match['home_team_name']) ?>" 
                                             class="h-10 w-10 object-contain mr-3">
                                    <?php else: ?>
                                        <div class="h-10 w-10 bg-gray-200 rounded-full flex items-center justify-center mr-3">
                                            <i class="fas fa-shield-alt text-gray-400"></i>
                                        </div>
                                    <?php endif; ?>
                                    <span class="font-semibold text-gray-900"><?= htmlspecialchars($match['home_team_name']) ?></span>
                                </div>

                                <!-- The scoreline or current status of the game -->
                                <div class="text-center px-8">
                                    <?php if ($match['status'] === 'FT' || $match['status'] === 'ET' || $match['status'] === 'PEN'): ?>
                                        <div class="text-3xl font-bold text-gray-900">
                                            <?= ($match['home_team_score'] ?? '-') ?> - <?= ($match['away_team_score'] ?? '-') ?>
                                        </div>
                                        <span class="text-xs text-gray-500"><?= $match['status'] ?></span>
                                    <?php else: ?>
                                        <span class="px-4 py-2 bg-blue-100 text-blue-700 rounded-full text-sm font-medium">
                                            <?= ucfirst(str_replace('_', ' ', $match['status'])) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <!-- And finally the away team on the other side -->
                                <div class="flex items-center justify-end flex-1">
                                    <span class="font-semibold text-gray-900"><?= htmlspecialchars($match['away_team_name']) ?></span>
                                    <?php if ($match['away_logo']): ?>
                                        <img src="<?= htmlspecialchars(get_logo_url($match['away_logo'])) ?>" 
                                             alt="<?= htmlspecialchars($match['away_team_name']) ?>" 
                                             class="h-10 w-10 object-contain ml-3">
                                    <?php else: ?>
                                        <div class="h-10 w-10 bg-gray-200 rounded-full flex items-center justify-center ml-3">
                                            <i class="fas fa-shield-alt text-gray-400"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                        </div>

                        <!-- These buttons allow the admin to take action on this specific match -->
                        <div class="flex gap-2">
                            <a href="../match.php?id=<?= $match['id'] ?>" target="_blank" 
                               class="px-4 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                                <i class="fas fa-eye mr-1"></i>View
                            </a>
                            <button onclick='editMatch(<?= htmlspecialchars(json_encode([
                                "id" => $match["id"],
                                "home_team" => $match["home_team"],
                                "away_team" => $match["away_team"],
                                "competition_id" => $match["competition_id"],
                                "match_date" => $match["match_date"],
                                "match_time" => $match["match_time"],
                                "status" => $match["status"],
                                "home_team_score" => $match["home_team_score"] ?? null,
                                "away_team_score" => $match["away_team_score"] ?? null
                            ]), ENT_QUOTES) ?>)' 
                                    class="px-4 py-2 text-sm bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </button>
                            <a href="match-events.php?id=<?= $match['id'] ?>" 
                               class="px-4 py-2 text-sm bg-green-100 text-green-700 rounded-lg hover:bg-green-200" title="Manage Goalscorers/Cards">
                                <i class="fas fa-futbol mr-1"></i>Events
                            </a>
                            <button onclick="confirmDelete(<?= $match['id'] ?>, <?= htmlspecialchars(json_encode($match['home_team_name']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($match['away_team_name']), ENT_QUOTES) ?>)" 
                                    class="px-4 py-2 text-sm bg-red-100 text-red-700 rounded-lg hover:bg-red-200">
                                <i class="fas fa-trash mr-1"></i>Delete
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Page Design -->
    <?php if ($totalPages > 1): ?>
        <div class="mt-8 flex items-center justify-between bg-white rounded-lg shadow-md px-6 py-4">
            <div class="text-sm text-gray-700">
                Showing <?= $offset + 1 ?> to <?= min($offset + $perPage, $totalMatches) ?> of <?= $totalMatches ?> matches
            </div>
            <div class="flex gap-2">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?><?= $status ? '&status=' . urlencode($status) : '' ?><?= $league ? '&league=' . urlencode($league) : '' ?>" 
                       class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        Previous
                    </a>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <a href="?page=<?= $i ?><?= $search ? '&search=' . urlencode($search) : '' ?><?= $status ? '&status=' . urlencode($status) : '' ?><?= $league ? '&league=' . urlencode($league) : '' ?>" 
                       class="px-4 py-2 <?= $i === $page ? 'bg-blue-600 text-white' : 'bg-white border border-gray-300 hover:bg-gray-50' ?> rounded-lg">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?><?= $status ? '&status=' . urlencode($status) : '' ?><?= $league ? '&league=' . urlencode($league) : '' ?>" 
                       class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        Next
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Add/Edit Modal -->
<div id="matchModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-2xl w-full p-6 max-h-screen overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
            <h3 id="modalTitle" class="text-xl font-bold text-gray-900">Add New Match</h3>
            <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>
        
        <form method="POST" id="matchForm">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="id" id="matchId">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Home Team *</label>
                    <select name="home_team" id="homeTeam" required 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Select Home Team</option>
                        <?php foreach ($teams as $team): ?>
                            <option value="<?= $team['id'] ?>"><?= htmlspecialchars($team['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Away Team *</label>
                    <select name="away_team" id="awayTeam" required 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Select Away Team</option>
                        <?php foreach ($teams as $team): ?>
                            <option value="<?= $team['id'] ?>"><?= htmlspecialchars($team['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Competition/League *</label>
                <select name="competition_id" id="competitionId" required 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Select League</option>
                    <?php foreach ($leagues as $l): ?>
                        <option value="<?= $l['id'] ?>"><?= htmlspecialchars($l['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Match Date *</label>
                    <input type="date" name="match_date" id="matchDate" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Match Time *</label>
                    <input type="time" name="match_time" id="matchTime" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>

            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                <select name="status" id="status" required 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="scheduled">Scheduled</option>
                    <option value="in_play">In Play</option>
                    <option value="FT">Full Time</option>
                    <option value="postponed">Postponed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            
            <div id="scoreSection" class="hidden grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Home Score</label>
                    <input type="number" name="home_team_score" id="homeScore" min="0" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Away Score</label>
                    <input type="number" name="away_team_score" id="awayScore" min="0" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>
            
            <div class="flex gap-3">
                <button type="submit" class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-save mr-2"></i><span id="submitText">Add Match</span>
                </button>
                <a id="manageEventsLink" href="#" class="hidden flex-1 px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-center">
                    <i class="fas fa-futbol mr-2"></i>Manage Events
                </a>
                <button type="button" onclick="closeModal()" class="px-6 py-3 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- This is a hidden form we use to handle deletions safely -->
<form method="POST" id="deleteForm" class="hidden">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="deleteId">
</form>

<script>
// Show/hide score section based on status
document.getElementById('status').addEventListener('change', function() {
    const scoreSection = document.getElementById('scoreSection');
    const completedStatuses = ['FT', 'ET', 'PEN'];
    if (completedStatuses.includes(this.value)) {
        scoreSection.classList.remove('hidden');
    } else {
        scoreSection.classList.add('hidden');
    }
});

function showAddModal() {
    document.getElementById('modalTitle').textContent = 'Add New Match';
    document.getElementById('formAction').value = 'add';
    document.getElementById('submitText').textContent = 'Add Match';
    document.getElementById('matchForm').reset();
    document.getElementById('matchId').value = '';
    document.getElementById('scoreSection').classList.add('hidden');
    document.getElementById('manageEventsLink').classList.add('hidden'); // Hide events button for new match
    document.getElementById('matchModal').classList.remove('hidden');
}

function editMatch(match) {
    document.getElementById('modalTitle').textContent = 'Edit Match';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('submitText').textContent = 'Update Match';
    document.getElementById('matchId').value = match.id;
    document.getElementById('homeTeam').value = match.home_team;
    document.getElementById('awayTeam').value = match.away_team;
    document.getElementById('competitionId').value = match.competition_id || '';
    document.getElementById('matchDate').value = match.match_date;
    document.getElementById('matchTime').value = match.match_time;
    document.getElementById('status').value = match.status;
    document.getElementById('homeScore').value = match.home_team_score || '';
    document.getElementById('awayScore').value = match.away_team_score || '';
    
    // Show score section if status is completed
    const completedStatuses = ['FT', 'ET', 'PEN'];
    if (completedStatuses.includes(match.status)) {
        document.getElementById('scoreSection').classList.remove('hidden');
    }
    
    // Show and update events button
    const eventsLink = document.getElementById('manageEventsLink');
    eventsLink.href = 'match-events.php?id=' + match.id;
    eventsLink.classList.remove('hidden');
    
    document.getElementById('matchModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('matchModal').classList.add('hidden');
}

function confirmDelete(id, homeTeam, awayTeam) {
    if (confirm(`Confirm Deletion?\n\nAre you sure you want to delete the match "${homeTeam} vs ${awayTeam}"?\n\nThis action cannot be undone.`)) {
        document.getElementById('deleteId').value = id;
        document.getElementById('deleteForm').submit();
    }
}

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});

// Close modal on background click
document.getElementById('matchModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>
