<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
require_auth();

$user = current_user();
if (!in_array($user['role'], ['employer', 'admin'])) {
    flash('error', 'Only employers and administrators can post jobs.');
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../employer/manage-jobs.php');
    exit;
}

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
$employerId = $user['role'] === 'employer' ? (int)$user['id'] : null;

if (!$title || !$company || !$location || !$description || !$requirements) {
    flash('error', 'Please fill in all required fields marked with an asterisk (*).');
    header('Location: ' . ($user['role'] === 'admin' ? '../admin/jobs.php' : '../employer/post-job.php'));
    exit;
}

try {
    $stmt = $pdo->prepare('
        INSERT INTO jobs (employer_id, title, company, category, location, job_type, experience_level, salary, deadline, description, requirements, is_featured, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "active")
    ');
    $stmt->execute([
        $employerId,
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
        $isFeatured
    ]);

    flash('success', 'Job posting "' . htmlspecialchars($title) . '" published successfully!');
    if ($user['role'] === 'admin') {
        header('Location: ../admin/jobs.php');
    } else {
        header('Location: ../employer/manage-jobs.php');
    }
    exit;
} catch (Exception $e) {
    flash('error', 'Failed to publish job: ' . $e->getMessage());
    header('Location: ../employer/post-job.php');
    exit;
}
