<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../register.php');
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$password = $_POST['password'] ?? '';
$role = in_array($_POST['role'] ?? '', ['student', 'employer']) ? $_POST['role'] : 'student';
$phone = trim($_POST['phone'] ?? '');
$companyName = trim($_POST['company_name'] ?? '');
$companyWebsite = trim($_POST['company_website'] ?? '');

if (!$name || !$email || strlen($password) < 6) {
    flash('error', 'Please provide a valid name, email, and password of at least 6 characters.');
    header('Location: ../register.php');
    exit;
}

if ($role === 'employer' && empty($companyName)) {
    flash('error', 'Company name is required for employer registration.');
    header('Location: ../register.php');
    exit;
}

try {
    $stmt = $pdo->prepare('
        INSERT INTO users (name, email, password, role, phone, company_name, company_website, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, "active")
    ');
    $stmt->execute([
        $name,
        $email,
        password_hash($password, PASSWORD_DEFAULT),
        $role,
        $phone,
        $role === 'employer' ? $companyName : null,
        $role === 'employer' ? $companyWebsite : null
    ]);

    // Send welcome notification
    $newUserId = (int)$pdo->lastInsertId();
    add_notification(
        $pdo,
        $newUserId,
        'Welcome to JobPortal!',
        $role === 'employer' ? 'Get started by creating your company profile and posting job openings.' : 'Complete your profile and upload your resume to start applying to jobs.',
        $role === 'employer' ? 'employer/dashboard.php' : 'student/profile.php'
    );

    flash('success', 'Your ' . ucfirst($role) . ' account has been created successfully! Please log in.');
    header('Location: ../login.php');
    exit;
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        flash('error', 'That email address is already registered. Please log in.');
    } else {
        flash('error', 'Could not complete registration. Please try again.');
    }
    header('Location: ../register.php');
    exit;
}
