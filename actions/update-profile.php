<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
require_student();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../student/profile.php');
    exit;
}

$userId = (int)$_SESSION['user']['id'];
$name = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$education = trim($_POST['education'] ?? '');
$skills = trim($_POST['skills'] ?? '');

if (!$name) {
    flash('error', 'Name cannot be empty.');
    header('Location: ../student/profile.php');
    exit;
}

// Handle resume upload if provided
$resumePath = null;
if (!empty($_FILES['resume']['name']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
    $fileTmp = $_FILES['resume']['tmp_name'];
    $fileSize = $_FILES['resume']['size'];
    $fileName = $_FILES['resume']['name'];
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    $allowedExts = ['pdf', 'doc', 'docx'];
    if (!in_array($ext, $allowedExts)) {
        flash('error', 'Invalid resume format. Only PDF, DOC, and DOCX files are allowed.');
        header('Location: ../student/profile.php');
        exit;
    }

    if ($fileSize > 5 * 1024 * 1024) { // 5MB limit
        flash('error', 'Resume file is too large. Maximum allowed size is 5MB.');
        header('Location: ../student/profile.php');
        exit;
    }

    $uploadDir = __DIR__ . '/../uploads/resumes/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $safeFileName = 'resume_' . $userId . '_' . time() . '.' . $ext;
    $destination = $uploadDir . $safeFileName;

    if (move_uploaded_file($fileTmp, $destination)) {
        $resumePath = 'uploads/resumes/' . $safeFileName;
    } else {
        flash('error', 'Failed to save uploaded resume file.');
        header('Location: ../student/profile.php');
        exit;
    }
}

try {
    if ($resumePath) {
        $stmt = $pdo->prepare('UPDATE users SET name = ?, phone = ?, education = ?, skills = ?, resume_path = ? WHERE id = ?');
        $stmt->execute([$name, $phone, $education, $skills, $resumePath, $userId]);
    } else {
        $stmt = $pdo->prepare('UPDATE users SET name = ?, phone = ?, education = ?, skills = ? WHERE id = ?');
        $stmt->execute([$name, $phone, $education, $skills, $userId]);
    }

    $_SESSION['user']['name'] = $name;
    flash('success', 'Profile updated successfully.');
} catch (Exception $e) {
    flash('error', 'Could not update profile: ' . $e->getMessage());
}

header('Location: ../student/profile.php');
exit;
