<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
require_auth();

$user = current_user();
if (!in_array($user['role'], ['employer', 'admin'])) {
    header('Location: ../login.php');
    exit;
}

$jobId = (int)($_POST['job_id'] ?? 0);
if ($jobId <= 0) {
    flash('error', 'Invalid job ID.');
    header('Location: ../employer/manage-jobs.php');
    exit;
}

if ($user['role'] === 'employer') {
    $stmt = $pdo->prepare('DELETE FROM jobs WHERE id = ? AND employer_id = ?');
    $stmt->execute([$jobId, $user['id']]);
} else {
    $stmt = $pdo->prepare('DELETE FROM jobs WHERE id = ?');
    $stmt->execute([$jobId]);
}

flash('success', 'Job posting deleted successfully.');
$redirect = $_SERVER['HTTP_REFERER'] ?? ($user['role'] === 'admin' ? '../admin/jobs.php' : '../employer/manage-jobs.php');
header('Location: ' . $redirect);
exit;
