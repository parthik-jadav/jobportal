<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
require_admin();

$jobId = (int)($_POST['job_id'] ?? 0);
if ($jobId > 0) {
    $stmt = $pdo->prepare('UPDATE jobs SET is_featured = 1 - is_featured WHERE id = ?');
    $stmt->execute([$jobId]);
    flash('success', 'Featured status updated.');
}

$redirect = $_SERVER['HTTP_REFERER'] ?? '../admin/jobs.php';
header('Location: ' . $redirect);
exit;
