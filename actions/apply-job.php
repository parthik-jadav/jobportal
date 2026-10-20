<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
require_student();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../student/jobs.php');
    exit;
}

$userId = (int)$_SESSION['user']['id'];
$jobId = (int)($_POST['job_id'] ?? 0);
$coverLetter = trim($_POST['cover_letter'] ?? '');

if ($jobId <= 0) {
    flash('error', 'Invalid job selected.');
    header('Location: ../student/jobs.php');
    exit;
}

// Fetch job
$jobStmt = $pdo->prepare('SELECT * FROM jobs WHERE id = ?');
$jobStmt->execute([$jobId]);
$job = $jobStmt->fetch();

if (!$job) {
    flash('error', 'Job posting not found.');
    header('Location: ../student/jobs.php');
    exit;
}

// Check if already applied
$checkStmt = $pdo->prepare('SELECT id FROM applications WHERE user_id = ? AND job_id = ?');
$checkStmt->execute([$userId, $jobId]);
if ($checkStmt->fetch()) {
    flash('warning', 'You have already applied for this position.');
    header('Location: ../student/applications.php');
    exit;
}

// Fetch user's existing resume
$uStmt = $pdo->prepare('SELECT name, resume_path FROM users WHERE id = ?');
$uStmt->execute([$userId]);
$user = $uStmt->fetch();
$resumePath = $user['resume_path'] ?? null;

// Check if a tailored resume was uploaded with this application
if (!empty($_FILES['custom_resume']['name']) && $_FILES['custom_resume']['error'] === UPLOAD_ERR_OK) {
    $fileTmp = $_FILES['custom_resume']['tmp_name'];
    $fileSize = $_FILES['custom_resume']['size'];
    $fileName = $_FILES['custom_resume']['name'];
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    $allowedExts = ['pdf', 'doc', 'docx'];
    if (!in_array($ext, $allowedExts)) {
        flash('error', 'Invalid resume format. Only PDF, DOC, and DOCX files are accepted.');
        header('Location: ../student/jobs.php');
        exit;
    }

    if ($fileSize > 5 * 1024 * 1024) {
        flash('error', 'Resume exceeds 5MB limit.');
        header('Location: ../student/jobs.php');
        exit;
    }

    $uploadDir = __DIR__ . '/../uploads/resumes/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $customFileName = 'app_' . $userId . '_' . $jobId . '_' . time() . '.' . $ext;
    if (move_uploaded_file($fileTmp, $uploadDir . $customFileName)) {
        $resumePath = 'uploads/resumes/' . $customFileName;
    }
}

if (empty($resumePath)) {
    flash('error', 'Please upload a resume in your profile or attach one directly to complete your application.');
    header('Location: ../student/profile.php');
    exit;
}

try {
    $appStmt = $pdo->prepare('
        INSERT INTO applications (user_id, job_id, resume_path, cover_letter, status) 
        VALUES (?, ?, ?, ?, "applied")
    ');
    $appStmt->execute([$userId, $jobId, $resumePath, $coverLetter]);

    // Student notification
    add_notification(
        $pdo,
        $userId,
        'Application Submitted',
        'You successfully applied for "' . $job['title'] . '" at ' . $job['company'] . '.',
        'student/applications.php'
    );

    // Employer notification if job has employer assigned
    if (!empty($job['employer_id'])) {
        add_notification(
            $pdo,
            (int)$job['employer_id'],
            'New Candidate Applied',
            htmlspecialchars($user['name']) . ' applied for your opening: "' . $job['title'] . '".',
            'employer/applicants.php?job_id=' . $jobId
        );
    }

    flash('success', 'Application submitted successfully! You can track its status below.');
    header('Location: ../student/applications.php');
    exit;
} catch (Exception $e) {
    flash('error', 'Could not submit application: ' . $e->getMessage());
    header('Location: ../student/jobs.php');
    exit;
}
