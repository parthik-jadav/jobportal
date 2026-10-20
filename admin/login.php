<?php
session_start();
if (!empty($_SESSION['user']) && $_SESSION['user']['role'] === 'admin') {
    header('Location: dashboard.php');
    exit;
}
$base_path = '../';
$page_title = 'Admin Login';
include '../includes/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card">
        <!-- Logo -->
        <div class="auth-logo" style="background:#0f172a;">
            <i class="bi bi-shield-lock-fill text-warning"></i>
        </div>
        <h2 class="fw-bold text-center mb-1" style="font-size:1.5rem;">Admin Access</h2>
        <p class="text-center text-muted mb-4" style="font-size:.9rem;">Sign in to JobPortal Administration Suite</p>

        <form action="../actions/login.php" method="post">
            <div class="mb-3">
                <label class="form-label fw-semibold" style="font-size:.85rem;color:var(--slate-700);">Administrator Email</label>
                <div class="input-icon-wrap">
                    <i class="bi bi-envelope input-icon"></i>
                    <input class="form-control" type="email" name="email" placeholder="admin@jobportal.test" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold" style="font-size:.85rem;color:var(--slate-700);">Password</label>
                <div class="input-icon-wrap">
                    <i class="bi bi-lock input-icon"></i>
                    <input class="form-control" type="password" name="password" placeholder="Admin password" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold" style="font-size:1rem;background:#0f172a !important;border-color:#0f172a !important;">
                <i class="bi bi-shield-check me-1"></i> Log in as Admin
            </button>
        </form>

        <div class="auth-divider"><span>portal navigation</span></div>

        <p class="text-center text-muted mb-0" style="font-size:.875rem;">
            Looking for student login? <a href="../login.php" class="fw-bold text-primary">Student Login</a>
        </p>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
