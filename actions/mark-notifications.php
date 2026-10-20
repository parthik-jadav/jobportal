<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
require_auth();

$userId = (int)$_SESSION['user']['id'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $stmt = $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $userId]);
} else {
    $stmt = $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ?');
    $stmt->execute([$userId]);
}

$redirect = $_SERVER['HTTP_REFERER'] ?? '../student/notifications.php';
header('Location: ' . $redirect);
exit;
