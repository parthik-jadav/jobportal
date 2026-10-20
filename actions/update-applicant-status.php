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
    header('Location: ../employer/applicants.php');
    exit;
}

$appId = (int)($_POST['application_id'] ?? 0);
$status = $_POST['status'] ?? 'applied';
$recruiterNotes = trim($_POST['recruiter_notes'] ?? '');

$allowedStatuses = ['applied', 'reviewing', 'shortlisted', 'rejected', 'hired'];
if (!in_array($status, $allowedStatuses) || $appId <= 0) {
    flash('error', 'Invalid status update request.');
    header('Location: ../employer/applicants.php');
    exit;
}

// Fetch application + job info to check permission
$appStmt = $pdo->prepare('
    SELECT a.*, j.employer_id, j.title as job_title, j.company as company_name, u.name as candidate_name 
    FROM applications a
    JOIN jobs j ON a.job_id = j.id
    JOIN users u ON a.user_id = u.id
    WHERE a.id = ?
');
$appStmt->execute([$appId]);
$app = $appStmt->fetch();

if (!$app) {
    flash('error', 'Application not found.');
    header('Location: ../employer/applicants.php');
    exit;
}

if ($user['role'] === 'employer' && (int)$app['employer_id'] !== (int)$user['id']) {
    flash('error', 'Unauthorized to modify this application.');
    header('Location: ../employer/applicants.php');
    exit;
}

try {
    $updateStmt = $pdo->prepare('UPDATE applications SET status = ?, recruiter_notes = ? WHERE id = ?');
    $updateStmt->execute([$status, $recruiterNotes, $appId]);

    // Send in-app notification to the student candidate
    $statusLabels = [
        'reviewing' => 'is now under review',
        'shortlisted' => 'has been shortlisted! 🎉',
        'hired' => 'has resulted in an offer / hired! 🏆',
        'rejected' => 'has been updated: Not selected at this time',
        'applied' => 'status has been reset to Applied'
    ];
    $message = "Your application for \"{$app['job_title']}\" at {$app['company_name']} {$statusLabels[$status]}.";

    add_notification(
        $pdo,
        (int)$app['user_id'],
        'Application Status Updated: ' . ucfirst($status),
        $message,
        'student/applications.php'
    );

    flash('success', 'Candidate status updated to ' . ucfirst($status) . ' and candidate notified.');
} catch (Exception $e) {
    flash('error', 'Failed to update candidate status: ' . $e->getMessage());
}

$redirect = $_SERVER['HTTP_REFERER'] ?? '../employer/applicants.php';
header('Location: ' . $redirect);
exit;
