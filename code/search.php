<?php
// search.php
require_once 'includes/config.php';

$query = $_GET['q'] ?? '';
$query = trim($query);

if (empty($query)) {
    header('Location: index.php');
    exit();
}

$db = Database::getInstance()->getConnection();

// Check for exact team match
$stmt = $db->prepare("SELECT name FROM teams WHERE name LIKE :query LIMIT 1");
$stmt->execute([':query' => $query]);
$team = $stmt->fetch(PDO::FETCH_ASSOC);

if ($team) {
    header('Location: matches.php?search=' . urlencode($team['name']));
    exit();
}

// Check for matches that contain the team name or similar
header('Location: matches.php?search=' . urlencode($query));
exit();
