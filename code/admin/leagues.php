<?php
require_once 'auth-check.php';
$db = Database::getInstance()->getConnection();

// Handle form submissions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            if ($_POST['action'] === 'add') {
                // Add new league
                $stmt = $db->prepare("INSERT INTO competitions (name, country, logo, created_at) VALUES (?, ?, ?, NOW())");
                $stmt->execute([
                    $_POST['name'],
                    $_POST['country'],
                    $_POST['logo']
                ]);
                $message = 'League added successfully!';
                $message_type = 'success';
            } elseif ($_POST['action'] === 'edit') {
                // Update league
                $stmt = $db->prepare("UPDATE competitions SET name = ?, country = ?, logo = ? WHERE id = ?");
                $stmt->execute([
                    $_POST['name'],
                    $_POST['country'],
                    $_POST['logo'],
                    $_POST['id']
                ]);
                $message = 'League updated successfully!';
                $message_type = 'success';
            } elseif ($_POST['action'] === 'delete') {
                // Check if league has matches
                $stmt = $db->prepare("SELECT COUNT(*) FROM matches WHERE competition_id = ?");
                $stmt->execute([$_POST['id']]);
                $matchCount = $stmt->fetchColumn();
                
                if ($matchCount > 0) {
                    $message = "Cannot delete league. It has $matchCount associated match(es).";
                    $message_type = 'error';
                } else {
                    // Delete league
                    $stmt = $db->prepare("DELETE FROM competitions WHERE id = ?");
                    $stmt->execute([$_POST['id']]);
                    $message = 'League deleted successfully!';
                    $message_type = 'success';
                }
            }
        }
    } catch (PDOException $e) {
        $message = 'Error: ' . $e->getMessage();
        $message_type = 'error';
    }
}

// Get search and pagination parameters
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 15;
$offset = ($page - 1) * $perPage;

// Build query with search
$whereClause = '';
$params = [];
if ($search) {
    $whereClause = " WHERE name LIKE ? OR country LIKE ?";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Get total count
$countStmt = $db->prepare("SELECT COUNT(*) FROM competitions" . $whereClause);
$countStmt->execute($params);
$totalLeagues = $countStmt->fetchColumn();
$totalPages = ceil($totalLeagues / $perPage);

// Get leagues
$stmt = $db->prepare("SELECT * FROM competitions" . $whereClause . " ORDER BY name ASC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$leagues = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Leagues - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
<?php include 'includes/header.php'; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Manage Leagues</h1>
            <p class="mt-2 text-gray-600">Add, edit, or remove football leagues & competitions</p>
        </div>
        <button onclick="showAddModal()" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
            <i class="fas fa-plus mr-2"></i>Add New League
        </button>
    </div>

    <!-- Success/Error Messages -->
    <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-lg <?= $message_type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Search Bar -->
    <div class="mb-6 bg-white rounded-lg shadow-md p-4">
        <form method="GET" class="flex gap-4">
            <div class="flex-1">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                       placeholder="Search leagues or countries..." 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i class="fas fa-search mr-2"></i>Search
            </button>
            <?php if ($search): ?>
                <a href="leagues.php" class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    <i class="fas fa-times mr-2"></i>Clear
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Leagues Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php if (empty($leagues)): ?>
            <div class="col-span-full bg-white rounded-lg shadow-md p-8 text-center">
                <i class="fas fa-trophy text-6xl text-gray-300 mb-4"></i>
                <p class="text-gray-500 mb-4">No leagues found.</p>
                <button onclick="showAddModal()" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-plus mr-2"></i>Add Your First League
                </button>
            </div>
        <?php else: ?>
            <?php foreach ($leagues as $league): ?>
                <?php
                // Count matches for this league
                $stmt = $db->prepare("SELECT COUNT(*) FROM matches WHERE competition_id = ?");
                $stmt->execute([$league['id']]);
                $matchCount = $stmt->fetchColumn();
                ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                    <div class="p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <?php if ($league['logo']): ?>
                                    <img src="<?= htmlspecialchars(get_logo_url($league['logo'])) ?>" 
                                         alt="<?= htmlspecialchars($league['name']) ?>" 
                                         class="h-16 w-16 object-contain mb-3">
                                <?php else: ?>
                                    <div class="h-16 w-16 bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-lg flex items-center justify-center mb-3">
                                        <i class="fas fa-trophy text-3xl text-white"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <h3 class="text-xl font-semibold text-gray-900 mb-2"><?= htmlspecialchars($league['name']) ?></h3>
                        <p class="text-gray-600 mb-4">
                            <i class="fas fa-globe mr-2"></i><?= htmlspecialchars($league['country']) ?>
                        </p>
                        
                        <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                            <span class="text-sm text-gray-500">
                                <i class="fas fa-futbol mr-1"></i><?= $matchCount ?> match(es)
                            </span>
                            <div class="flex gap-2">
                                <button onclick='editLeague(<?= json_encode($league) ?>)' 
                                        class="px-3 py-1 text-sm text-blue-600 hover:bg-blue-50 rounded">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="confirmDelete(<?= $league['id'] ?>, '<?= htmlspecialchars($league['name'], ENT_QUOTES) ?>', <?= $matchCount ?>)" 
                                        class="px-3 py-1 text-sm text-red-600 hover:bg-red-50 rounded">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="mt-8 flex items-center justify-between bg-white rounded-lg shadow-md px-6 py-4">
            <div class="text-sm text-gray-700">
                Showing <?= $offset + 1 ?> to <?= min($offset + $perPage, $totalLeagues) ?> of <?= $totalLeagues ?> leagues
            </div>
            <div class="flex gap-2">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>" 
                       class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        Previous
                    </a>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <a href="?page=<?= $i ?><?= $search ? '&search=' . urlencode($search) : '' ?>" 
                       class="px-4 py-2 <?= $i === $page ? 'bg-blue-600 text-white' : 'bg-white border border-gray-300 hover:bg-gray-50' ?> rounded-lg">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>" 
                       class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        Next
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Add/Edit Modal -->
<div id="leagueModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-md w-full p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 id="modalTitle" class="text-xl font-bold text-gray-900">Add New League</h3>
            <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>
        
        <form method="POST" id="leagueForm">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="id" id="leagueId">
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">League Name *</label>
                <input type="text" name="name" id="leagueName" required 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       placeholder="e.g., Premier League">
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Country *</label>
                <input type="text" name="country" id="leagueCountry" required 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       placeholder="e.g., England">
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Logo URL or Path</label>
                <input type="text" name="logo" id="leagueLogo"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       placeholder="https://example.com/logo.png or filename.png">
                <p class="mt-1 text-sm text-gray-500">Enter the URL or local filename (e.g. premier_league.png) of the league's logo</p>
            </div>
            
            <div class="flex gap-3">
                <button type="submit" class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-save mr-2"></i><span id="submitText">Add League</span>
                </button>
                <button type="button" onclick="closeModal()" class="px-6 py-3 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Form -->
<form method="POST" id="deleteForm" class="hidden">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="deleteId">
</form>

<script>
function showAddModal() {
    document.getElementById('modalTitle').textContent = 'Add New League';
    document.getElementById('formAction').value = 'add';
    document.getElementById('submitText').textContent = 'Add League';
    document.getElementById('leagueForm').reset();
    document.getElementById('leagueId').value = '';
    document.getElementById('leagueModal').classList.remove('hidden');
}

function editLeague(league) {
    document.getElementById('modalTitle').textContent = 'Edit League';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('submitText').textContent = 'Update League';
    document.getElementById('leagueId').value = league.id;
    document.getElementById('leagueName').value = league.name;
    document.getElementById('leagueCountry').value = league.country;
    document.getElementById('leagueLogo').value = league.logo || '';
    document.getElementById('leagueModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('leagueModal').classList.add('hidden');
}

function confirmDelete(id, name, matchCount) {
    if (matchCount > 0) {
        alert(`Cannot delete "${name}". This league has ${matchCount} associated match(es). Please remove or reassign those matches first.`);
        return;
    }
    
    if (confirm(`Are you sure you want to delete "${name}"? This action cannot be undone.`)) {
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
document.getElementById('leagueModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>
