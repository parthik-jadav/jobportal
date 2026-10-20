<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
require_employer();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../employer/profile.php');
    exit;
}

$employerId = (int)$_SESSION['user']['id'];
$name = trim($_POST['name'] ?? '');
$companyName = trim($_POST['company_name'] ?? '');
$companyWebsite = trim($_POST['company_website'] ?? '');
$companyAbout = trim($_POST['company_about'] ?? '');
$phone = trim($_POST['phone'] ?? '');

if (!$name || !$companyName) {
    flash('error', 'Contact name and company name are required.');
    header('Location: ../employer/profile.php');
    exit;
}

try {
    $stmt = $pdo->prepare('
        UPDATE users 
        SET name = ?, company_name = ?, company_website = ?, company_about = ?, phone = ?
        WHERE id = ?
    ');
    $stmt->execute([$name, $companyName, $companyWebsite, $companyAbout, $phone, $employerId]);

    $_SESSION['user']['name'] = $name;
    $_SESSION['user']['company_name'] = $companyName;

    flash('success', 'Company profile updated successfully.');
} catch (Exception $e) {
    flash('error', 'Failed to update company profile: ' . $e->getMessage());
}

header('Location: ../employer/profile.php');
exit;
