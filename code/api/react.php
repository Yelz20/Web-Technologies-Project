<?php
require_once '../includes/config.php';

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
$type = isset($input['type']) ? $input['type'] : '';
$user_id = $_SESSION['user_id'];

if (!$review_id || !in_array($type, ['like', 'dislike'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

$db = Database::getInstance()->getConnection();

try {
    // Check existing reaction
    $stmt = $db->prepare("SELECT type FROM reactions WHERE review_id = ? AND user_id = ?");
    $stmt->execute([$review_id, $user_id]);
    $existing = $stmt->fetchColumn();

    $new_status = null; // null means removed

    if ($existing) {
        if ($existing === $type) {
            // Toggle off (remove)
            $stmt = $db->prepare("DELETE FROM reactions WHERE review_id = ? AND user_id = ?");
            $stmt->execute([$review_id, $user_id]);
            $new_status = null;
        } else {
            // Change type (e.g. like -> dislike)
            $stmt = $db->prepare("UPDATE reactions SET type = ? WHERE review_id = ? AND user_id = ?");
            $stmt->execute([$type, $review_id, $user_id]);
            $new_status = $type;
        }
    } else {
        // Insert new
        $stmt = $db->prepare("INSERT INTO reactions (review_id, user_id, type) VALUES (?, ?, ?)");
        $stmt->execute([$review_id, $user_id, $type]);
        $new_status = $type;
    }

    // Get new counts
    $stmt = $db->prepare("SELECT COUNT(*) FROM reactions WHERE review_id = ? AND type = 'like'");
    $stmt->execute([$review_id]);
    $likes = $stmt->fetchColumn();

    $stmt = $db->prepare("SELECT COUNT(*) FROM reactions WHERE review_id = ? AND type = 'dislike'");
    $stmt->execute([$review_id]);
    $dislikes = $stmt->fetchColumn();

    echo json_encode([
        'success' => true,
        'new_status' => $new_status,
        'likes' => $likes,
        'dislikes' => $dislikes
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
