<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Must be logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

$review_id = isset($input['review_id']) ? (int)$input['review_id'] : 0;
$content = isset($input['content']) ? trim($input['content']) : '';
$user_id = $_SESSION['user_id'];

if (!$review_id || empty($content)) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

$db = Database::getInstance()->getConnection();

try {
    // Insert reply
    $stmt = $db->prepare("INSERT INTO review_replies (review_id, user_id, content) VALUES (?, ?, ?)");
    $stmt->execute([$review_id, $user_id, $content]);
    $reply_id = $db->lastInsertId();

    // Get user details for response
    $stmt = $db->prepare("SELECT display_name, username, avatar FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get reply count
    $stmt = $db->prepare("SELECT COUNT(*) FROM review_replies WHERE review_id = ?");
    $stmt->execute([$review_id]);
    $count = $stmt->fetchColumn();

    // Fetch formatted avatar URL
    $user_name = $user['display_name'] ?: $user['username'];
    $avatar_url = get_avatar_url($user['avatar'], 40, $user_name);

    echo json_encode([
        'success' => true,
        'reply' => [
            'id' => $reply_id,
            'user_name' => $user_name,
            'avatar_url' => $avatar_url,
            'content' => nl2br(htmlspecialchars($content)),
            'created_at' => 'Just now'
        ],
        'reply_count' => $count
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
