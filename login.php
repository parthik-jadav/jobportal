<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    $role = $_SESSION['user']['role'];
    if ($role === 'admin') header('Location: admin/dashboard.php');
    elseif ($role === 'employer') header('Location: employer/dashboard.php');
    else header('Location: student/dashboard.php');
    exit;
}

$page_title = 'Login';
include 'includes/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card">
        <!-- Logo -->
        <div class="auth-logo">
            <i class="bi bi-briefcase-fill"></i>
        </div>
        <h2 class="fw-bold text-center mb-1" style="font-size:1.5rem;">Welcome back</h2>
        <p class="text-center text-muted mb-4" style="font-size:.9rem;">Sign in to your JobPortal account</p>

        <form action="actions/login.php" method="post">
            <div class="mb-3">
                <label class="form-label fw-semibold" style="font-size:.85rem;color:var(--slate-700);">Email Address</label>
                <div class="input-icon-wrap">
                    <i class="bi bi-envelope input-icon"></i>
                    <input class="form-control" type="email" name="email" placeholder="you@example.com" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold" style="font-size:.85rem;color:var(--slate-700);">Password</label>
                <div class="input-icon-wrap">
                    <i class="bi bi-lock input-icon"></i>
                    <input class="form-control" type="password" name="password" placeholder="Your password" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold" style="font-size:1rem;">
                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
            </button>
        </form>

        <div class="auth-divider"><span>or</span></div>

        <p class="text-center text-muted mb-0" style="font-size:.875rem;">
            Don't have an account? <a href="register.php" class="fw-bold text-primary">Create one free</a>
        </p>
        <p class="text-center mt-2 mb-0" style="font-size:.8rem;">
            <a href="admin/login.php" class="text-muted">Admin login →</a>
        </p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
