<?php
/**
 * This page shows goals, cards, and other key events that happen on the pitch.
 */
require_once 'auth-check.php';
$db = Database::getInstance()->getConnection();

// Validate ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: matches.php');
    exit;
}

$matchId = $_GET['id'];
$message = '';
$message_type = '';

// Handle POST actions (Add/Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add_event') {
            try {
                $stmt = $db->prepare("INSERT INTO match_events (match_id, team_id, player_id, related_player_id, event_type, minute) VALUES (?, ?, ?, ?, ?, ?)");
                $relatedPlayer = !empty($_POST['related_player_id']) ? $_POST['related_player_id'] : null;
                
                $stmt->execute([
                    $matchId,
                    $_POST['team_id'],
                    $_POST['player_id'],
                    $relatedPlayer,
                    $_POST['event_type'],
                    $_POST['minute']
                ]);
                
                $message = 'Event recorded successfully!';
                $message_type = 'success';
            } catch (Exception $e) {
                $message = 'Error recording event: ' . $e->getMessage();
                $message_type = 'error';
            }
        } elseif ($_POST['action'] === 'delete_event') {
            try {
                $stmt = $db->prepare("DELETE FROM match_events WHERE id = ? AND match_id = ?");
                $stmt->execute([$_POST['event_id'], $matchId]);
                $message = 'Event removed.';
                $message_type = 'success';
            } catch (Exception $e) {
                $message = 'Error removing event: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    }
}

// Basic match details to show who is playing at the top of the page.
$stmt = $db->prepare("
    SELECT m.*, ht.name as home_team_name, at.name as away_team_name 
    FROM matches m
    JOIN teams ht ON m.home_team = ht.id
    JOIN teams at ON m.away_team = at.id
    WHERE m.id = ?
");
$stmt->execute([$matchId]);
$match = $stmt->fetch();

if (!$match) {
    die("Match not found.");
}

// All events currently recorded
$stmt = $db->prepare("
    SELECT e.*, p.name as player_name, rp.name as related_player_name, t.name as team_name 
    FROM match_events e
    JOIN players p ON e.player_id = p.id
    LEFT JOIN players rp ON e.related_player_id = rp.id
    JOIN teams t ON e.team_id = t.id
    WHERE e.match_id = ?
    ORDER BY e.minute ASC
");
$stmt->execute([$matchId]);
$events = $stmt->fetchAll();

// Full squad lists for both teams so admin can pick the right players for each event.
$stmt = $db->prepare("SELECT id, name, number FROM players WHERE team_id = ? ORDER BY name ASC");
$stmt->execute([$match['home_team']]);
$home_players = $stmt->fetchAll();

$stmt->execute([$match['away_team']]);
$away_players = $stmt->fetchAll();

$pageTitle = "Manage Match Events";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events - Football Review Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="bg-gray-100">
<?php include 'includes/header.php'; ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- Navigation back to matches -->
    <div class="mb-6">
        <a href="matches.php" class="text-blue-600 hover:text-blue-800 flex items-center">
            <i class="fas fa-arrow-left mr-2"></i>Back to Matches
        </a>
    </div>

    <!-- Match Summary -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8 text-center">
        <h1 class="text-2xl font-bold text-gray-900">
            <?= htmlspecialchars($match['home_team_name']) ?> vs <?= htmlspecialchars($match['away_team_name']) ?>
        </h1>
        <p class="text-gray-500"><?= date('M j, Y', strtotime($match['match_date'])) ?></p>
    </div>

    <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-lg <?= $message_type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Add Event Form -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-bold mb-4">Add Event</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="add_event">
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Team</label>
                        <select name="team_id" id="teamSelect" class="w-full px-3 py-2 border rounded-lg" required onchange="filterPlayers()">
                            <option value="">Select Team</option>
                            <option value="<?= $match['home_team'] ?>"><?= htmlspecialchars($match['home_team_name']) ?></option>
                            <option value="<?= $match['away_team'] ?>"><?= htmlspecialchars($match['away_team_name']) ?></option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Player</label>
                        <select name="player_id" id="playerSelect" class="w-full px-3 py-2 border rounded-lg" required>
                            <option value="">Select Team First</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Event Type</label>
                        <select name="event_type" id="eventType" class="w-full px-3 py-2 border rounded-lg" required onchange="toggleSub()">
                            <option value="goal">Goal</option>
                            <option value="yellow_card">Yellow Card</option>
                            <option value="red_card">Red Card</option>
                            <option value="substitution">Substitution</option>
                        </select>
                    </div>

                    <div class="mb-4 hidden" id="subPlayerDiv">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Player OUT (for Sub)</label>
                        <select name="related_player_id" id="subPlayerSelect" class="w-full px-3 py-2 border rounded-lg">
                            <option value="">Select Player Out</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Select the player leaving the pitch.</p>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Minute</label>
                        <input type="number" name="minute" min="1" max="130" class="w-full px-3 py-2 border rounded-lg" required>
                    </div>

                    <button type="submit" class="w-full py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Add Event
                    </button>
                </form>
            </div>
        </div>

        <!-- Events List -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-bold mb-4">Current Match Events</h2>
                <?php if (empty($events)): ?>
                    <p class="text-gray-500 italic">No events recorded for this match.</p>
                <?php else: ?>
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-sm font-medium text-gray-500">Min</th>
                                <th class="px-6 py-3 text-sm font-medium text-gray-500">Team</th>
                                <th class="px-6 py-3 text-sm font-medium text-gray-500">Player</th>
                                <th class="px-6 py-3 text-sm font-medium text-gray-500">Event</th>
                                <th class="px-6 py-3 text-sm font-medium text-gray-500">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php 
                            $colors = [
                                'goal' => 'bg-green-100 text-green-800',
                                'yellow_card' => 'bg-yellow-100 text-yellow-800',
                                'red_card' => 'bg-red-100 text-red-800',
                                'substitution' => 'bg-blue-100 text-blue-800'
                            ];
                            $labels = [
                                'goal' => 'Goal',
                                'yellow_card' => 'Yellow Card',
                                'red_card' => 'Red Card',
                                'substitution' => 'Sub'
                            ];
                            ?>
                            <?php foreach ($events as $e): ?>
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-900 font-bold"><?= $e['minute'] ?>'</td>
                                    <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($e['team_name']) ?></td>
                                    <td class="px-3 py-4 text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($e['player_name']) ?>
                                        <?php if ($e['event_type'] === 'substitution'): ?>
                                            <span class="text-gray-400 text-xs block">for <?= htmlspecialchars($e['related_player_name'] ?? 'Unknown') ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold <?= $colors[$e['event_type']] ?>">
                                            <?= $labels[$e['event_type']] ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <form method="POST" onsubmit="return confirm('Delete this event?');">
                                            <input type="hidden" name="action" value="delete_event">
                                            <input type="hidden" name="event_id" value="<?= $e['id'] ?>">
                                            <button type="submit" class="text-red-600 hover:text-red-900">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
const homeId = <?= $match['home_team'] ?>;
const awayId = <?= $match['away_team'] ?>;
const homePlayers = <?= json_encode($home_players) ?>;
const awayPlayers = <?= json_encode($away_players) ?>;

function filterPlayers() {
    const teamId = document.getElementById('teamSelect').value;
    const playerSelect = document.getElementById('playerSelect');
    const subPlayerSelect = document.getElementById('subPlayerSelect');
    
    playerSelect.innerHTML = '<option value="">Select Player</option>';
    subPlayerSelect.innerHTML = '<option value="">Select Player Out</option>';
    
    let players = [];
    if (teamId == homeId) players = homePlayers;
    else if (teamId == awayId) players = awayPlayers;
    
    players.forEach(p => {
        const option = new Option(`${p.name} (#${p.number})`, p.id);
        playerSelect.add(option.cloneNode(true));
        subPlayerSelect.add(option);
    });
}

function toggleSub() {
    const type = document.getElementById('eventType').value;
    const div = document.getElementById('subPlayerDiv');
    if (type === 'substitution') {
        div.classList.remove('hidden');
    } else {
        div.classList.add('hidden');
    }
}
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>
