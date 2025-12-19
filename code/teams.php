<?php
require_once 'includes/config.php';

// Get all teams
$db = Database::getInstance()->getConnection();
$stmt = $db->query("SELECT * FROM teams ORDER BY name ASC");
$teams = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Football Review - Teams</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="bg-gray-100">
<?php include 'includes/partials/header.php'; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Football Teams</h1>
        <p class="mt-2 text-gray-600">Browse all teams in our database</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <?php foreach ($teams as $team): ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                <div class="p-6 text-center">
                    <?php if ($team['logo']): ?>
                        <img src="<?= htmlspecialchars($team['logo']) ?>" 
                             alt="<?= htmlspecialchars($team['name']) ?>" 
                             class="h-24 mx-auto mb-4 object-contain">
                    <?php else: ?>
                        <div class="h-24 w-24 mx-auto mb-4 bg-gray-200 rounded-full flex items-center justify-center">
                            <i class="fas fa-shield-alt text-4xl text-gray-400"></i>
                        </div>
                    <?php endif; ?>
                    
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                        <?= htmlspecialchars($team['name']) ?>
                    </h3>
                    
                    <a href="matches.php?team=<?= $team['id'] ?>" 
                       class="inline-block mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm">
                        View Matches
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (empty($teams)): ?>
        <div class="bg-white rounded-lg shadow-md p-12 text-center">
            <i class="fas fa-shield-alt text-6xl text-gray-300 mb-4"></i>
            <h2 class="text-2xl font-semibold text-gray-700 mb-2">No Teams Found</h2>
            <p class="text-gray-500">Teams will appear here once they are added to the database.</p>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/partials/footer.php'; ?>
</body>
</html>
