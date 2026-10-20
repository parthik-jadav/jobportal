<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function require_student(string $redirect = '../login.php'): void {
    if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
        flash('error', 'Please log in as a student to access this page.');
        header('Location: ' . $redirect);
        exit;
    }
}

function require_employer(string $redirect = '../login.php'): void {
    if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'employer') {
        flash('error', 'Please log in as an employer/recruiter to access this page.');
        header('Location: ' . $redirect);
        exit;
    }
}

function require_admin(string $redirect = 'login.php'): void {
    if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        flash('error', 'Administrator access required.');
        header('Location: ' . $redirect);
        exit;
    }
}

function require_auth(string $redirect = '../login.php'): void {
    if (empty($_SESSION['user'])) {
        flash('error', 'Please log in to continue.');
        header('Location: ' . $redirect);
        exit;
    }
}

function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool {
    return !empty($_SESSION['user']);
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(?string $token): bool {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

function flash(string $key, ?string $message = null): ?string {
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }
    $value = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $value;
}

function add_notification(PDO $pdo, int $userId, string $title, string $message, ?string $link = null): bool {
    try {
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, link) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$userId, $title, $message, $link]);
    } catch (Exception $e) {
        return false;
    }
}

function get_unread_notifications_count(PDO $pdo, int $userId): int {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    } catch (Exception $e) {
        return 0;
    }
}
