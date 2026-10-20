<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
require_admin();

$type = $_GET['type'] ?? 'applications';
$filename = 'export_' . $type . '_' . date('Y-m-d_His') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

$output = fopen('php://output', 'w');

if ($type === 'applications') {
    fputcsv($output, ['Application ID', 'Job Title', 'Company', 'Candidate Name', 'Candidate Email', 'Candidate Phone', 'Status', 'Recruiter Notes', 'Applied Date']);

    $stmt = $pdo->query('
        SELECT a.id, j.title, j.company, u.name, u.email, u.phone, a.status, a.recruiter_notes, a.applied_at
        FROM applications a
        JOIN jobs j ON a.job_id = j.id
        JOIN users u ON a.user_id = u.id
        ORDER BY a.applied_at DESC
    ');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row);
    }
} elseif ($type === 'users') {
    fputcsv($output, ['User ID', 'Name', 'Email', 'Role', 'Company Name', 'Phone', 'Education', 'Status', 'Registered Date']);

    $stmt = $pdo->query('
        SELECT id, name, email, role, company_name, phone, education, status, created_at
        FROM users
        ORDER BY created_at DESC
    ');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row);
    }
} elseif ($type === 'feedback') {
    fputcsv($output, ['Feedback ID', 'Name', 'Email', 'Message', 'Submitted Date']);

    $stmt = $pdo->query('SELECT id, name, email, message, created_at FROM feedback ORDER BY created_at DESC');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row);
    }
}

fclose($output);
exit;
