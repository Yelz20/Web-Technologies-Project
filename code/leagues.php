<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$db = Database::getInstance()->getConnection();

// Get all leagues
$stmt = $db->query("SELECT * FROM competitions ORDER BY name ASC");
$leagues = $stmt->fetchAll();

// If a league is selected, get its teams
$selected_league_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$selected_league = null;
$teams = [];

if ($selected_league_id) {
    // Get league details
    $stmt = $db->prepare("SELECT * FROM competitions WHERE id = ?");
    $stmt->execute([$selected_league_id]);
    $selected_league = $stmt->fetch();
    
    // Get teams in this league
    if ($selected_league) {
        $stmt = $db->prepare("SELECT * FROM teams WHERE competition_id = ? ORDER BY name ASC");
        $stmt->execute([$selected_league_id]);
        $teams = $stmt->fetchAll();
    }
} else {
    // If no league selected, select the first one by default (Premier League or just first)
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Football Review - Leagues</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="bg-gray-100">
    <?php include 'includes/partials/header.php'; ?>

    <main class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8 text-gray-800"><i class="fas fa-trophy text-yellow-500 mr-2"></i> Leagues</h1>

        <?php if (!$selected_league): ?>
            <!-- League Selection Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($leagues as $league): ?>
                    <a href="leagues.php?id=<?= $league['id'] ?>" class="block group">
                        <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                            <div class="h-32 bg-gradient-to-r from-blue-800 to-blue-600 flex items-center justify-center p-4">
                                <?php if ($league['logo']): ?>
                                    <img src="<?= htmlspecialchars($league['logo']) ?>" alt="<?= htmlspecialchars($league['name']) ?>" class="h-24 w-24 object-contain filter drop-shadow-lg">
                                <?php else: ?>
                                    <span class="text-4xl text-white font-bold tracking-wider"><?= substr($league['name'], 0, 1) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="p-6">
                                <h2 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-blue-600 transition-colors"><?= htmlspecialchars($league['name']) ?></h2>
                                <p class="text-gray-500 text-sm mb-4"><?= htmlspecialchars($league['country']) ?></p>
                                <div class="flex items-center text-blue-600 font-medium text-sm">
                                    View Teams <i class="fas fa-arrow-right ml-2 transform group-hover:translate-x-1 transition-transform"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Selected League Details -->
            <div class="mb-6">
                <a href="leagues.php" class="text-blue-600 hover:text-blue-800 font-medium flex items-center mb-4">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Leagues
                </a>
                
                <div class="bg-white rounded-xl shadow-md overflow-hidden mb-8">
                    <div class="bg-gradient-to-r from-blue-800 to-blue-600 p-8 text-white flex items-center">
                        <?php if ($selected_league['logo']): ?>
                            <img src="<?= htmlspecialchars($selected_league['logo']) ?>" alt="<?= htmlspecialchars($selected_league['name']) ?>" class="h-32 w-32 object-contain mr-8 bg-white rounded-lg p-2">
                        <?php endif; ?>
                        <div>
                            <h1 class="text-4xl font-bold mb-2"><?= htmlspecialchars($selected_league['name']) ?></h1>
                            <p class="text-xl opacity-90"><?= htmlspecialchars($selected_league['country']) ?></p>
                        </div>
                    </div>
                </div>

                <h2 class="text-2xl font-bold mb-6 text-gray-800">Teams</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <?php foreach ($teams as $team): ?>
                        <div class="bg-white rounded-lg shadow hover:shadow-md transition-shadow p-6 flex flex-col items-center text-center">
                            <div class="h-24 w-24 mb-4 flex items-center justify-center">
                                <?php if ($team['logo']): ?>
                                    <img src="<?= htmlspecialchars($team['logo']) ?>" alt="<?= htmlspecialchars($team['name']) ?>" class="max-h-full max-w-full object-contain">
                                <?php else: ?>
                                    <div class="h-16 w-16 bg-gray-200 rounded-full flex items-center justify-center text-gray-500 font-bold text-xl">
                                        <?= substr($team['name'], 0, 2) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <h3 class="font-bold text-lg text-gray-900 mb-1"><?= htmlspecialchars($team['name']) ?></h3>
                            <?php if ($team['stadium']): ?>
                                <p class="text-xs text-gray-500"><i class="fas fa-map-marker-alt mr-1"></i> <?= htmlspecialchars($team['stadium']) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <?php include 'includes/partials/footer.php'; ?>
</body>
</html>
