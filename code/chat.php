<?php
require_once 'includes/config.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit();
}

$db = Database::getInstance()->getConnection();
$user_id = $_SESSION['user_id'];

// Handle message submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    if (!empty($message)) {
        // Fan sends message (admin_id is NULL for fan messages - goes to all admins)
        $stmt = $db->prepare("INSERT INTO support_messages (user_id, admin_id, message, is_admin) VALUES (?, NULL, ?, 0)");
        $stmt->execute([$user_id, $message]);
    }
    header('Location: chat.php');
    exit();
}

// Get conversation messages for this fan only
$stmt = $db->prepare("SELECT sm.*, u.username, u.avatar, u.display_name
    FROM support_messages sm 
    JOIN users u ON sm.user_id = u.id 
    WHERE sm.user_id = ? OR (sm.is_admin = 1 AND sm.admin_id = ?)
    ORDER BY sm.created_at ASC");
$stmt->execute([$user_id, $user_id]);
$messages = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Football Review - Chat with Support</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="bg-gray-100">
<?php include 'includes/partials/header.php'; ?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="bg-blue-600 px-6 py-4">
            <h1 class="text-2xl font-bold text-white flex items-center">
                <i class="fas fa-comments mr-3"></i>
                Chat with Support
            </h1>
            <p class="text-blue-100 text-sm mt-1">Our admin team will respond as soon as possible</p>
        </div>

        <div class="h-96 overflow-y-auto p-6 bg-gray-50" id="chat-container">
            <?php if (empty($messages)): ?>
                <div class="text-center py-12">
                    <i class="fas fa-comment-dots text-6xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500">No messages yet. Start the conversation!</p>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($messages as $msg): ?>
                        <?php if ($msg['is_admin']): ?>
                            <!-- Admin message -->
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="h-10 w-10 rounded-full bg-blue-600 flex items-center justify-center">
                                        <i class="fas fa-user-shield text-white"></i>
                                    </div>
                                </div>
                                <div class="ml-3 flex-1">
                                    <div class="bg-white rounded-lg shadow p-4 max-w-md">
                                        <p class="text-sm font-semibold text-gray-900 mb-1">
                                            <?= htmlspecialchars($msg['display_name'] ?? 'Support Team') ?>
                                        </p>
                                        <p class="text-gray-700"><?= nl2br(htmlspecialchars($msg['message'])) ?></p>
                                        <p class="text-xs text-gray-500 mt-2"><?= time_elapsed_string($msg['created_at']) ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- User message -->
                            <div class="flex items-start justify-end">
                                <div class="mr-3 flex-1 text-right">
                                    <div class="bg-blue-600 text-white rounded-lg shadow p-4 inline-block text-left max-w-md">
                                        <p class="text-sm font-semibold mb-1">You</p>
                                        <p><?= nl2br(htmlspecialchars($msg['message'])) ?></p>
                                        <p class="text-xs text-blue-100 mt-2"><?= time_elapsed_string($msg['created_at']) ?></p>
                                    </div>
                                </div>
                                <div class="flex-shrink-0">
                                    <img src="<?= get_avatar_url($_SESSION['avatar_url'] ?? '', 40) ?>" 
                                         alt="You"
                                         class="h-10 w-10 rounded-full">
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="border-t border-gray-200 p-4">
            <form method="POST" class="flex gap-2">
                <input type="text" name="message" required
                       placeholder="Type your message to support..."
                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button type="submit" 
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-paper-plane mr-2"></i>Send
                </button>
            </form>
        </div>
    </div>

    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-600"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm text-blue-800">
                    <strong>Note:</strong> Our support team typically responds within 24 hours. For urgent issues, please check our <a href="index.php" class="underline">FAQ section</a>.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-scroll to bottom of chat
const chatContainer = document.getElementById('chat-container');
if (chatContainer) {
    chatContainer.scrollTop = chatContainer.scrollHeight;
}
</script>

<?php include 'includes/partials/footer.php'; ?>
</body>
</html>
