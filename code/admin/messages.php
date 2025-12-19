<?php
require_once 'auth-check.php';

$db = Database::getInstance()->getConnection();
$admin_id = $_SESSION['user_id'];

// Handle admin reply
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['message'], $_POST['user_id'])) {
        $fan_id = (int)$_POST['user_id'];
        $message = trim($_POST['message']);
        
        if (!empty($message)) {
            // Admin sends message to specific fan
            $stmt = $db->prepare("INSERT INTO support_messages (user_id, admin_id, message, is_admin, created_at) VALUES (?, ?, ?, 1, NOW())");
            $stmt->execute([$fan_id, $admin_id, $message]);
        }
        
        header('Location: messages.php?user_id=' . $fan_id);
        exit();
    }
    // Handle message deletion
    elseif (isset($_POST['action'], $_POST['user_id'])) {
        $fan_id = (int)$_POST['user_id'];
        
        if ($_POST['action'] === 'delete_user_messages') {
            try {
                // Start transaction
                $db->beginTransaction();
                
                // Delete ALL messages in this conversation, regardless of direction or format
                $stmt = $db->prepare("DELETE FROM support_messages WHERE 
                    (user_id = ? AND admin_id = ?) OR  -- Fan to admin
                    (user_id = ? AND admin_id = ?) OR  -- Admin to fan
                    (user_id = ? AND admin_id = ?)     -- Catch any other format
                ");
                $stmt->execute([$fan_id, $admin_id, $admin_id, $fan_id, $admin_id, $fan_id]);
                
                // Also delete any messages where the fan is involved, regardless of admin_id
                $stmt = $db->prepare("DELETE FROM support_messages WHERE user_id = ?");
                $stmt->execute([$fan_id]);
                
                // Commit the transaction
                $db->commit();
                
                $_SESSION['success_message'] = 'All messages in this conversation have been deleted.';
            } catch (Exception $e) {
                // Rollback the transaction on error
                $db->rollBack();
                $_SESSION['error_message'] = 'Error deleting messages: ' . $e->getMessage();
            }
        } 
        elseif ($_POST['action'] === 'delete_single_message' && isset($_POST['message_id'])) {
            try {
                // Delete a single message
                $message_id = (int)$_POST['message_id'];
                $stmt = $db->prepare("DELETE FROM support_messages WHERE id = ? AND (user_id = ? OR admin_id = ?)");
                $stmt->execute([$message_id, $fan_id, $admin_id]);
                
                if ($stmt->rowCount() > 0) {
                    $_SESSION['success_message'] = 'Message has been deleted.';
                } else {
                    $_SESSION['error_message'] = 'Message not found or you do not have permission to delete it.';
                }
            } catch (Exception $e) {
                $_SESSION['error_message'] = 'Error deleting message: ' . $e->getMessage();
            }
        }
        
        header('Location: messages.php?user_id=' . $fan_id);
        exit();
    }
}

// Get selected user ID
$selected_user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;

// Get all fans who have sent messages (admin_id is NULL for fan messages)
$stmt = $db->query("SELECT DISTINCT u.id, u.username, u.display_name, u.avatar, 
    (SELECT COUNT(*) FROM support_messages WHERE user_id = u.id) as message_count,
    (SELECT created_at FROM support_messages WHERE user_id = u.id ORDER BY created_at DESC LIMIT 1) as last_message_at
    FROM users u
    WHERE EXISTS (SELECT 1 FROM support_messages WHERE user_id = u.id AND is_admin = 0)
    AND u.role != 'admin'
    ORDER BY last_message_at DESC");
$users_with_messages = $stmt->fetchAll();

// Get all messages for selected fan
$messages = [];
if ($selected_user_id) {
    $stmt = $db->prepare("SELECT sm.*, u.username, u.avatar, u.display_name
        FROM support_messages sm 
        JOIN users u ON sm.user_id = u.id 
        WHERE sm.user_id = ? OR (sm.is_admin = 1 AND sm.admin_id = ?)
        ORDER BY sm.created_at ASC");
    $stmt->execute([$selected_user_id, $selected_user_id]);
    $messages = $stmt->fetchAll();
}

// Get selected fan info
$selected_fan = null;
if ($selected_user_id) {
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$selected_user_id]);
    $selected_fan = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="bg-gray-100">
<?php include 'includes/header.php'; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            <?= htmlspecialchars($_SESSION['success_message']) ?>
            <?php unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            <?= htmlspecialchars($_SESSION['error_message']) ?>
            <?php unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Fan Support Messages</h1>
        <p class="mt-2 text-gray-600">View and respond to fan inquiries</p>
    </div>

    <div class="bg-white rounded-lg shadow-lg overflow-hidden" style="height: 600px;">
        <div class="flex h-full">
            <!-- Fan List Sidebar -->
            <div class="w-full md:w-1/3 border-r border-gray-200 flex flex-col h-full">
                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                    <h2 class="font-semibold text-gray-900">Fan Conversations</h2>
                </div>
                
                <div class="flex-1 overflow-y-auto">
                    <?php if (empty($users_with_messages)): ?>
                        <div class="p-8 text-center">
                            <i class="fas fa-inbox text-6xl text-gray-300 mb-3"></i>
                            <p class="text-gray-500">No messages yet</p>
                            <p class="text-xs text-gray-400 mt-2">Fans can message support from the "Chat with Us" page</p>
                        </div>
                    <?php else: ?>
                        <div class="divide-y divide-gray-200">
                            <?php foreach ($users_with_messages as $user): ?>
                                <a href="?user_id=<?= $user['id'] ?>" 
                                   class="block p-4 hover:bg-gray-50 transition-colors <?= $selected_user_id == $user['id'] ? 'bg-blue-50 border-l-4 border-blue-600' : 'border-l-4 border-transparent' ?>">
                                    <div class="flex items-start">
                                        <img src="<?= get_avatar_url($user['avatar'] ?? '', 40) ?>" 
                                             alt="<?= htmlspecialchars($user['display_name'] ?? $user['username'] ?? 'User') ?>"
                                             class="h-10 w-10 rounded-full object-cover">
                                        <div class="ml-3 flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate">
                                                <?= htmlspecialchars($user['display_name'] ?? $user['username'] ?? 'User') ?>
                                            </p>
                                            <p class="text-xs text-gray-500 truncate">
                                                <?php 
                                                    $stmt = $db->prepare("SELECT message FROM support_messages WHERE (user_id = ? AND is_admin = 0) OR (admin_id = ? AND is_admin = 1) ORDER BY created_at DESC LIMIT 1");
                                                    $stmt->execute([$user['id'], $user['id']]);
                                                    $last_message = $stmt->fetch();
                                                    echo !empty($last_message['message']) ? 
                                                        (strlen($last_message['message']) > 30 ? 
                                                            substr($last_message['message'], 0, 30) . '...' : 
                                                            $last_message['message']) : 
                                                        'No messages yet';
                                                ?>
                                            </p>
                                        </div>
                                        <div class="text-xs text-gray-400 text-right">
                                            <?= time_elapsed_string($user['last_message_at']) ?>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Chat Area -->
            <div class="flex-1 flex flex-col h-full">
                <?php if ($selected_user_id && $selected_fan): ?>
                    <!-- Chat Header -->
                    <div class="bg-white px-6 py-4 border-b border-gray-200 flex-shrink-0">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <img src="<?= get_avatar_url($selected_fan['avatar'] ?? '', 40) ?>" 
                                     alt="<?= htmlspecialchars($selected_fan['display_name'] ?? $selected_fan['username'] ?? 'User') ?>"
                                     class="h-10 w-10 rounded-full object-cover">
                                <div class="ml-3">
                                    <p class="text-sm font-semibold text-gray-900"><?= htmlspecialchars($selected_fan['display_name'] ?? $selected_fan['username'] ?? 'User') ?></p>
                                    <p class="text-xs text-gray-500">Fan</p>
                                </div>
                            </div>
                            <form method="POST" action="messages.php" onsubmit="return confirm('Are you sure you want to delete ALL messages in this conversation? This action cannot be undone.');">
                                <input type="hidden" name="action" value="delete_user_messages">
                                <input type="hidden" name="user_id" value="<?= $selected_user_id ?>">
                                <button type="submit" class="p-2 text-red-500 hover:text-red-700 focus:outline-none" title="Delete all messages in this conversation">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Messages -->
                    <div class="flex-1 overflow-y-auto p-6 bg-gray-100" id="messages-container" style="background-color: #f0f2f5;">
                        <?php if (empty($messages)): ?>
                            <div class="text-center py-12">
                                <i class="fas fa-comment-dots text-6xl text-gray-300 mb-4"></i>
                                <p class="text-gray-500">No messages in this conversation</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($messages as $msg): ?>
                                <?php if ($msg['is_admin']): ?>
                                    <!-- Admin message (right) -->
                                    <div class="flex items-start justify-end mb-4">
                                        <div class="mr-3 text-right flex-1">
                                            <div class="bg-blue-600 text-white rounded-lg shadow p-4 inline-block text-left max-w-md">
                                                <p class="text-sm font-semibold mb-1"><?= htmlspecialchars($msg['display_name'] ?? 'Admin') ?></p>
                                                <p><?= nl2br(htmlspecialchars($msg['message'])) ?></p>
                                                <p class="text-xs text-blue-100 mt-2"><?= time_elapsed_string($msg['created_at']) ?></p>
                                            </div>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <div class="h-10 w-10 rounded-full bg-blue-600 flex items-center justify-center">
                                                <i class="fas fa-user-shield text-white"></i>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <!-- Fan message (left) -->
                                    <div class="flex items-start mb-4">
                                        <div class="flex-shrink-0">
                                            <img src="<?= get_avatar_url($selected_fan['avatar'] ?? '', 40) ?>" 
                                                 alt="<?= htmlspecialchars($selected_fan['display_name'] ?? 'Fan') ?>"
                                                 class="h-10 w-10 rounded-full">
                                        </div>
                                        <div class="ml-3 flex-1">
                                            <div class="bg-white rounded-lg shadow p-4 max-w-md relative group">
                                                <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <form method="POST" action="messages.php" onsubmit="return confirm('Are you sure you want to delete this message?');" class="inline">
                                                        <input type="hidden" name="action" value="delete_single_message">
                                                        <input type="hidden" name="user_id" value="<?= $selected_user_id ?>">
                                                        <input type="hidden" name="message_id" value="<?= $msg['id'] ?>">
                                                        <button type="submit" class="text-red-500 hover:text-red-700 text-xs">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                                <p class="text-sm font-semibold text-gray-900 mb-1">
                                                    <?php 
                                                    if (isset($msg['is_admin']) && $msg['is_admin']) {
                                                        echo 'Admin';
                                                    } else {
                                                        echo htmlspecialchars($selected_fan['display_name'] ?? $selected_fan['username'] ?? 'Fan');
                                                    }
                                                    ?>
                                                </p>
                                                <p class="text-gray-700"><?= nl2br(htmlspecialchars($msg['message'])) ?></p>
                                                <p class="text-xs text-gray-500 mt-2"><?= time_elapsed_string($msg['created_at']) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Reply Form -->
                    <div class="border-t border-gray-200 px-6 py-4 bg-white flex-shrink-0" id="message-form" style="border-bottom-right-radius: 0.5rem;">
                        <form method="POST" action="messages.php" id="message-form-element" class="flex gap-2">
                            <input type="hidden" name="user_id" value="<?= $selected_user_id ?>">
                            <input type="text" 
                                   name="message" 
                                   id="message-input"
                                   required
                                   placeholder="Type your response to <?= htmlspecialchars($selected_fan['display_name'] ?? 'fan') ?>..."
                                   class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   autocomplete="off">
                            <button type="submit" 
                                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex-shrink-0">
                                <i class="fas fa-paper-plane mr-2"></i>Send
                            </button>
                        </form>
                    </div>
                    
                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const form = document.getElementById('message-form-element');
                        const messagesContainer = document.getElementById('messages-container');
                        const messageInput = document.getElementById('message-input');
                        
                        // Scroll to bottom of messages
                        function scrollToBottom() {
                            if (messagesContainer) {
                                messagesContainer.scrollTop = messagesContainer.scrollHeight;
                            }
                        }
                        
                        // Initial scroll to bottom
                        scrollToBottom();
                        
                        // Handle form submission with fetch API for better UX
                        if (form) {
                            form.addEventListener('submit', function(e) {
                                e.preventDefault();
                                
                                const formData = new FormData(form);
                                const message = formData.get('message').trim();
                                
                                if (!message) return;
                                
                                // Add loading state
                                const submitButton = form.querySelector('button[type="submit"]');
                                const originalButtonText = submitButton.innerHTML;
                                submitButton.disabled = true;
                                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Sending...';
                                
                                fetch('messages.php', {
                                    method: 'POST',
                                    body: formData
                                })
                                .then(response => {
                                    if (response.redirected) {
                                        window.location.href = response.url;
                                    }
                                    return response.text();
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    alert('Failed to send message. Please try again.');
                                })
                                .finally(() => {
                                    submitButton.disabled = false;
                                    submitButton.innerHTML = originalButtonText;
                                });
                                
                                // Clear input and refocus
                                if (messageInput) {
                                    messageInput.value = '';
                                    messageInput.focus();
                                }
                            });
                        }
                        
                        // Auto-focus the message input when the page loads
                        if (messageInput) {
                            messageInput.focus();
                            
                            // Handle Enter key to submit
                            messageInput.addEventListener('keydown', function(e) {
                                if (e.key === 'Enter' && !e.shiftKey) {
                                    e.preventDefault();
                                    form.dispatchEvent(new Event('submit'));
                                }
                            });
                        }
                    });
                    </script>
                <?php else: ?>
                    <div class="flex-1 flex items-center justify-center">
                        <div class="text-center">
                            <i class="fas fa-comments text-6xl text-gray-300 mb-4"></i>
                            <p class="text-gray-500 text-lg">Select a fan to view their conversation</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


<?php include 'includes/footer.php'; ?>
</body>
</html>
