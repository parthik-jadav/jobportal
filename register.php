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

$page_title = 'Register';
include 'includes/header.php';
?>
<section class="container py-5">
    <div class="auth-card card p-4 shadow-sm" style="max-width: 580px;">
        <h2 class="fw-bold mb-1">Create an Account</h2>
        <p class="text-muted mb-4">Join as a student looking for jobs or an employer hiring top talent.</p>

        <form action="actions/register.php" method="post" id="registerForm">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

            <!-- Role Selector -->
            <label class="form-label fw-semibold">I want to:</label>
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <input type="radio" class="btn-check" name="role" id="role_student" value="student" checked onchange="toggleEmployerFields(false)">
                    <label class="btn btn-outline-primary w-100 py-2 d-flex flex-column align-items-center" for="role_student">
                        <i class="bi bi-mortarboard-fill fs-4 mb-1"></i>
                        <span>Find Jobs (Student)</span>
                    </label>
                </div>
                <div class="col-6">
                    <input type="radio" class="btn-check" name="role" id="role_employer" value="employer" onchange="toggleEmployerFields(true)">
                    <label class="btn btn-outline-primary w-100 py-2 d-flex flex-column align-items-center" for="role_employer">
                        <i class="bi bi-building fs-4 mb-1"></i>
                        <span>Hire Talent (Employer)</span>
                    </label>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input class="form-control" name="name" placeholder="John Doe" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input class="form-control" type="email" name="email" placeholder="name@example.com" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Phone Number (optional)</label>
                <input class="form-control" name="phone" placeholder="+91 98765 43210">
            </div>

            <!-- Employer Only Fields -->
            <div id="employerFields" class="d-none border-top pt-3 mt-3">
                <h6 class="fw-bold text-primary mb-3"><i class="bi bi-briefcase me-1"></i>Company Information</h6>
                <div class="mb-3">
                    <label class="form-label">Company / Organization Name <span class="text-danger">*</span></label>
                    <input class="form-control" name="company_name" id="company_name_input" placeholder="e.g. Acme Tech Solutions">
                </div>
                <div class="mb-3">
                    <label class="form-label">Company Website</label>
                    <input class="form-control" type="url" name="company_website" placeholder="https://example.com">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input class="form-control" type="password" name="password" placeholder="At least 6 characters" minlength="6" required>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold mt-2">
                <i class="bi bi-person-plus me-1"></i>Create Account
            </button>
        </form>

        <p class="mt-4 mb-0 text-center text-muted">
            Already have an account? <a href="login.php" class="fw-semibold text-primary">Log in here</a>
        </p>
    </div>
</section>

<script>
function toggleEmployerFields(isEmployer) {
    const el = document.getElementById('employerFields');
    const compInput = document.getElementById('company_name_input');
    if (isEmployer) {
        el.classList.remove('d-none');
        compInput.setAttribute('required', 'required');
    } else {
        el.classList.add('d-none');
        compInput.removeAttribute('required');
    }
}
</script>
<?php include 'includes/footer.php'; ?>
