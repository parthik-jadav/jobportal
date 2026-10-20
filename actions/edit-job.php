<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
require_auth();

$user = current_user();
if (!in_array($user['role'], ['employer', 'admin'])) {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../employer/manage-jobs.php');
    exit;
}

$jobId = (int)($_POST['job_id'] ?? 0);
$title = trim($_POST['title'] ?? '');
$company = trim($_POST['company'] ?? '');
$category = trim($_POST['category'] ?? 'Technology & Engineering');
$location = trim($_POST['location'] ?? '');
$jobType = $_POST['job_type'] ?? 'Full-time';
$experienceLevel = $_POST['experience_level'] ?? 'Fresher';
$salary = trim($_POST['salary'] ?? 'Not specified');
$deadline = !empty($_POST['deadline']) ? $_POST['deadline'] : null;
$description = trim($_POST['description'] ?? '');
$requirements = trim($_POST['requirements'] ?? '');
$isFeatured = !empty($_POST['is_featured']) ? 1 : 0;
$status = in_array($_POST['status'] ?? '', ['active', 'closed']) ? $_POST['status'] : 'active';

if ($jobId <= 0 || !$title || !$company || !$location || !$description || !$requirements) {
    flash('error', 'Please fill in all required fields.');
    header('Location: ../employer/edit-job.php?id=' . $jobId);
    exit;
}

// Verification
if ($user['role'] === 'employer') {
    $stmt = $pdo->prepare('SELECT id FROM jobs WHERE id = ? AND employer_id = ?');
    $stmt->execute([$jobId, $user['id']]);
} else {
    $stmt = $pdo->prepare('SELECT id FROM jobs WHERE id = ?');
    $stmt->execute([$jobId]);
}

if (!$stmt->fetch()) {
    flash('error', 'Job posting not found or unauthorized.');
    header('Location: ../employer/manage-jobs.php');
    exit;
}

try {
    $updateStmt = $pdo->prepare('
        UPDATE jobs 
        SET title = ?, company = ?, category = ?, location = ?, job_type = ?, 
            experience_level = ?, salary = ?, deadline = ?, description = ?, 
            requirements = ?, is_featured = ?, status = ?
        WHERE id = ?
    ');
    $updateStmt->execute([
        $title,
        $company,
        $category,
        $location,
        $jobType,
        $experienceLevel,
        $salary ?: 'Not specified',
        $deadline,
        $description,
        $requirements,
        $isFeatured,
        $status,
        $jobId
    ]);

    flash('success', 'Job posting updated successfully.');
    if ($user['role'] === 'admin') {
        header('Location: ../admin/jobs.php');
    } else {
        header('Location: ../employer/manage-jobs.php');
    }
    exit;
} catch (Exception $e) {
    flash('error', 'Update failed: ' . $e->getMessage());
    header('Location: ../employer/edit-job.php?id=' . $jobId);
    exit;
}
