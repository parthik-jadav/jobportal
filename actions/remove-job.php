<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
require_student();

$job_id = (int)($_POST['job_id'] ?? 0);
if ($job_id > 0) {
    $stmt = $pdo->prepare('DELETE FROM saved_jobs WHERE user_id = ? AND job_id = ?');
    $stmt->execute([$_SESSION['user']['id'], $job_id]);
    flash('success', 'Job removed from your saved list.');
}

$redirect = $_SERVER['HTTP_REFERER'] ?? '../student/saved-jobs.php';
header('Location: ' . $redirect);
exit;
