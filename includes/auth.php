<?php
if (session_status() === PHP_SESSION_NONE) session_start();

function require_student(): void {
    if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
        header('Location: ../login.php'); exit;
    }
}
function require_admin(): void {
    if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        header('Location: login.php'); exit;
    }
}
function flash(string $key, ?string $message = null): ?string {
    if ($message !== null) { $_SESSION['flash'][$key] = $message; return null; }
    $value = $_SESSION['flash'][$key] ?? null; unset($_SESSION['flash'][$key]); return $value;
}
