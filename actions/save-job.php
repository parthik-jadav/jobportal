<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
require_student();

$job_id = (int)($_POST['job_id'] ?? 0);
if ($job_id > 0) {
    $stmt = $pdo->prepare('INSERT IGNORE INTO saved_jobs (user_id, job_id) VALUES (?, ?)');
    $stmt->execute([$_SESSION['user']['id'], $job_id]);
    flash('success', 'Job saved to your bookmarks.');
}

$redirect = $_SERVER['HTTP_REFERER'] ?? '../student/jobs.php';
header('Location: ' . $redirect);
exit;
