<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
require_admin();

$userId = (int)($_POST['user_id'] ?? 0);
$currentAdminId = (int)$_SESSION['user']['id'];

if ($userId <= 0 || $userId === $currentAdminId) {
    flash('error', 'Cannot delete this user.');
    header('Location: ../admin/users.php');
    exit;
}

try {
    $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    flash('success', 'User account deleted successfully.');
} catch (Exception $e) {
    flash('error', 'Failed to delete user: ' . $e->getMessage());
}

header('Location: ../admin/users.php');
exit;
