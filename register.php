<?php
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    $role = $_SESSION['user']['role'];
    if ($role === 'admin') header('Location: admin/dashboard.php');
    elseif ($role === 'employer') header('Location: employer/dashboard.php');
    else header('Location: student/dashboard.php');
    exit;
}

$page_title = 'Create Account';
include 'includes/header.php';
?>

<div class="auth-wrapper" style="background:linear-gradient(135deg,#f1f5f9 0%,#e0e7ff 100%);align-items:flex-start;padding-top:2.5rem;padding-bottom:2.5rem;">
    <div class="auth-card" style="max-width:520px;">
        <!-- Logo -->
        <div class="auth-logo">
            <i class="bi bi-briefcase-fill"></i>
        </div>
        <h2 class="fw-bold text-center mb-1" style="font-size:1.5rem;">Create your account</h2>
        <p class="text-center text-muted mb-4" style="font-size:.9rem;">Join thousands of students and employers on JobPortal.</p>

        <form action="actions/register.php" method="post" id="registerForm">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

            <!-- Role Toggle -->
            <label class="form-label fw-semibold" style="font-size:.85rem;color:var(--slate-700);">I want to:</label>
            <div class="role-toggle mb-4">
                <div class="role-card active" id="roleCardStudent" onclick="selectRole('student')">
                    <input type="radio" name="role" id="role_student" value="student" class="d-none" checked>
                    <span class="role-icon">🎓</span>
                    <div class="role-name">Find Jobs</div>
                    <div style="font-size:.7rem;color:var(--slate-500);">Student</div>
                </div>
                <div class="role-card" id="roleCardEmployer" onclick="selectRole('employer')">
                    <input type="radio" name="role" id="role_employer" value="employer" class="d-none">
                    <span class="role-icon">🏢</span>
                    <div class="role-name">Hire Talent</div>
                    <div style="font-size:.7rem;color:var(--slate-500);">Employer</div>
                </div>
            </div>

            <!-- Full Name -->
            <div class="mb-3">
                <label class="form-label fw-semibold" style="font-size:.85rem;color:var(--slate-700);">Full Name</label>
                <div class="input-icon-wrap">
                    <i class="bi bi-person input-icon"></i>
                    <input class="form-control" name="name" placeholder="John Doe" required>
                </div>
            </div>

            <!-- Email -->
            <div class="mb-3">
                <label class="form-label fw-semibold" style="font-size:.85rem;color:var(--slate-700);">Email Address</label>
                <div class="input-icon-wrap">
                    <i class="bi bi-envelope input-icon"></i>
                    <input class="form-control" type="email" name="email" placeholder="name@example.com" required>
                </div>
            </div>

            <!-- Phone -->
            <div class="mb-3">
                <label class="form-label fw-semibold" style="font-size:.85rem;color:var(--slate-700);">Phone <span class="text-muted fw-normal">(optional)</span></label>
                <div class="input-icon-wrap">
                    <i class="bi bi-telephone input-icon"></i>
                    <input class="form-control" name="phone" placeholder="+91 98765 43210">
                </div>
            </div>

            <!-- Employer Fields -->
            <div id="employerFields" class="d-none">
                <div class="form-section mb-3" style="background:var(--slate-50);border-radius:var(--radius-lg);padding:1.25rem;border:1px solid var(--slate-200);">
                    <div class="form-section-label"><i class="bi bi-building me-1"></i>Company Information</div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:.85rem;color:var(--slate-700);">Company Name <span class="text-danger">*</span></label>
                        <input class="form-control" name="company_name" id="company_name_input" placeholder="e.g. Acme Tech Solutions">
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold" style="font-size:.85rem;color:var(--slate-700);">Company Website</label>
                        <input class="form-control" type="url" name="company_website" placeholder="https://example.com">
                    </div>
                </div>
            </div>

            <!-- Password -->
            <div class="mb-4">
                <label class="form-label fw-semibold" style="font-size:.85rem;color:var(--slate-700);">Password</label>
                <div class="input-icon-wrap">
                    <i class="bi bi-lock input-icon"></i>
                    <input class="form-control" type="password" name="password" placeholder="At least 6 characters" minlength="6" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold" style="font-size:1rem;">
                <i class="bi bi-person-plus me-1"></i> Create Account
            </button>
        </form>

        <div class="auth-divider"><span>already a member?</span></div>

        <p class="text-center mb-0">
            <a href="login.php" class="fw-bold text-primary" style="font-size:.9rem;">Sign in to your account →</a>
        </p>
    </div>
</div>

<script>
function selectRole(role) {
    const studentCard  = document.getElementById('roleCardStudent');
    const employerCard = document.getElementById('roleCardEmployer');
    const empFields    = document.getElementById('employerFields');
    const compInput    = document.getElementById('company_name_input');

    if (role === 'employer') {
        document.getElementById('role_employer').checked = true;
        employerCard.classList.add('active');
        studentCard.classList.remove('active');
        empFields.classList.remove('d-none');
        compInput.setAttribute('required', 'required');
    } else {
        document.getElementById('role_student').checked = true;
        studentCard.classList.add('active');
        employerCard.classList.remove('active');
        empFields.classList.add('d-none');
        compInput.removeAttribute('required');
    }
}
</script>

<?php include 'includes/footer.php'; ?>
