<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    flash('error', 'Please provide both email and password.');
    header('Location: ../login.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    if (isset($user['status']) && $user['status'] === 'inactive') {
        flash('error', 'Your account has been suspended. Please contact the portal administrator.');
        header('Location: ../login.php');
        exit;
    }

    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id' => (int)$user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role'],
        'company_name' => $user['company_name'] ?? null
    ];

    flash('success', 'Welcome back, ' . htmlspecialchars($user['name']) . '!');

    if ($user['role'] === 'admin') {
        header('Location: ../admin/dashboard.php');
    } elseif ($user['role'] === 'employer') {
        header('Location: ../employer/dashboard.php');
    } else {
        header('Location: ../student/dashboard.php');
    }
    exit;
}

flash('error', 'Invalid email or password.');
header('Location: ../login.php');
exit;
