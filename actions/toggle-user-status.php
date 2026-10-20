<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
require_admin();

$userId = (int)($_POST['user_id'] ?? 0);
$currentAdminId = (int)$_SESSION['user']['id'];

if ($userId <= 0 || $userId === $currentAdminId) {
    flash('error', 'Cannot modify this user.');
    header('Location: ../admin/users.php');
    exit;
}

$stmt = $pdo->prepare('SELECT id, status FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

if ($user) {
    $newStatus = ($user['status'] === 'active') ? 'inactive' : 'active';
    $updateStmt = $pdo->prepare('UPDATE users SET status = ? WHERE id = ?');
    $updateStmt->execute([$newStatus, $userId]);
    flash('success', 'User account status changed to ' . ucfirst($newStatus) . '.');
}

header('Location: ../admin/users.php');
exit;
