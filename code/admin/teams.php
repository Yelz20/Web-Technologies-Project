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
                // Add new team
                $stmt = $db->prepare("INSERT INTO teams (name, logo, competition_id, created_at) VALUES (?, ?, ?, NOW())");
                $stmt->execute([
                    $_POST['name'],
                    $_POST['logo'],
                    $_POST['competition_id'] ?: null
                ]);
                $message = 'Team added successfully!';
                $message_type = 'success';
            } elseif ($_POST['action'] === 'edit') {
                // Update team
                $stmt = $db->prepare("UPDATE teams SET name = ?, logo = ?, competition_id = ? WHERE id = ?");
                $stmt->execute([
                    $_POST['name'],
                    $_POST['logo'],
                    $_POST['competition_id'] ?: null,
                    $_POST['id']
                ]);
                $message = 'Team updated successfully!';
                $message_type = 'success';
            } elseif ($_POST['action'] === 'delete') {
                // Check if team has matches
                $stmt = $db->prepare("SELECT COUNT(*) FROM matches WHERE home_team = ? OR away_team = ?");
                $stmt->execute([$_POST['id'], $_POST['id']]);
                $matchCount = $stmt->fetchColumn();
                
                if ($matchCount > 0) {
                    $message = "Cannot delete team. It has $matchCount associated match(es).";
                    $message_type = 'error';
                } else {
                    // Delete team
                    $stmt = $db->prepare("DELETE FROM teams WHERE id = ?");
                    $stmt->execute([$_POST['id']]);
                    $message = 'Team deleted successfully!';
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
    $whereClause = " WHERE name LIKE ?";
    $params[] = "%$search%";
}

// Get total count
$countStmt = $db->prepare("SELECT COUNT(*) FROM teams" . $whereClause);
$countStmt->execute($params);
$totalTeams = $countStmt->fetchColumn();
$totalPages = ceil($totalTeams / $perPage);

// Get teams
$stmt = $db->prepare("SELECT * FROM teams" . $whereClause . " ORDER BY name ASC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$teams = $stmt->fetchAll();

// Get available leagues
$leagues = $db->query("SELECT id, name FROM competitions ORDER BY name ASC")->fetchAll();

// Get team for editing
$editTeam = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM teams WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editTeam = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Teams - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
<?php include 'includes/header.php'; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Manage Teams</h1>
            <p class="mt-2 text-gray-600">Add, edit, or remove football teams</p>
        </div>
        <div class="flex gap-2">
            <button onclick="showAddModal()" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-plus mr-2"></i>Add New Team
            </button>
        </div>
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
                       placeholder="Search teams..." 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i class="fas fa-search mr-2"></i>Search
            </button>
            <?php if ($search): ?>
                <a href="teams.php" class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    <i class="fas fa-times mr-2"></i>Clear
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Teams Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Logo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Team Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Matches</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($teams)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                No teams found. <button onclick="showAddModal()" class="text-blue-600 hover:underline">Add your first team</button>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($teams as $team): ?>
                            <?php
                            // Count matches for this team
                            $stmt = $db->prepare("SELECT COUNT(*) FROM matches WHERE home_team = ? OR away_team = ?");
                            $stmt->execute([$team['id'], $team['id']]);
                            $matchCount = $stmt->fetchColumn();
                            ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($team['logo']): ?>
                                        <img src="<?= htmlspecialchars(get_logo_url($team['logo'])) ?>" alt="<?= htmlspecialchars($team['name']) ?>" class="h-10 w-10 object-contain">
                                    <?php else: ?>
                                        <div class="h-10 w-10 bg-gray-200 rounded-full flex items-center justify-center">
                                            <i class="fas fa-shield-alt text-gray-400"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($team['name']) ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-500"><?= $matchCount ?> match(es)</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button onclick='editTeam(<?= json_encode($team) ?>)' class="text-blue-600 hover:text-blue-900 mr-4">
                                        <i class="fas fa-edit mr-1"></i>Edit
                                    </button>
                                    <button onclick="confirmDelete(<?= $team['id'] ?>, '<?= htmlspecialchars($team['name'], ENT_QUOTES) ?>', <?= $matchCount ?>)" class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash mr-1"></i>Delete
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Page Design -->
        <?php if ($totalPages > 1): ?>
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Showing <?= $offset + 1 ?> to <?= min($offset + $perPage, $totalTeams) ?> of <?= $totalTeams ?> teams
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
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add/Edit Modal -->
<div id="teamModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-md w-full p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 id="modalTitle" class="text-xl font-bold text-gray-900">Add New Team</h3>
            <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>
        
        <form method="POST" id="teamForm">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="id" id="teamId">
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Team Name *</label>
                <input type="text" name="name" id="teamName" required 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       placeholder="e.g., Manchester United">
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Primary League</label>
                <select name="competition_id" id="teamLeague" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Select League (Optional)</option>
                    <?php foreach ($leagues as $l): ?>
                        <option value="<?= $l['id'] ?>"><?= htmlspecialchars($l['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Logo URL or Path</label>
                <input type="text" name="logo" id="teamLogo"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       placeholder="https://example.com/logo.png or filename.png">
                <p class="mt-1 text-sm text-gray-500">Enter the URL or local filename (e.g. juventus.png)</p>
            </div>
            
            <div class="flex gap-3">
                <button type="submit" class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-save mr-2"></i><span id="submitText">Add Team</span>
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
    document.getElementById('modalTitle').textContent = 'Add New Team';
    document.getElementById('formAction').value = 'add';
    document.getElementById('submitText').textContent = 'Add Team';
    document.getElementById('teamForm').reset();
    document.getElementById('teamId').value = '';
    document.getElementById('teamModal').classList.remove('hidden');
}

function editTeam(team) {
    document.getElementById('modalTitle').textContent = 'Edit Team';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('submitText').textContent = 'Update Team';
    document.getElementById('teamId').value = team.id;
    document.getElementById('teamName').value = team.name;
    document.getElementById('teamLeague').value = team.competition_id || '';
    document.getElementById('teamLogo').value = team.logo || '';
    document.getElementById('teamModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('teamModal').classList.add('hidden');
}

function confirmDelete(id, name, matchCount) {
    if (matchCount > 0) {
        alert(`Cannot delete "${name}". This team has ${matchCount} associated match(es). Please remove or reassign those matches first.`);
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
document.getElementById('teamModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>
