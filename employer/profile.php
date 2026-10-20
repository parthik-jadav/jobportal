<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
require_employer();

$base_path = '../';
$page_title = 'Company Profile';
$employerId = (int)$_SESSION['user']['id'];

$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$employerId]);
$emp = $stmt->fetch();

$companyName = $emp['company_name'] ?: $emp['name'];
$companyInitials = avatarInitials($companyName);

include '../includes/header.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Company Header -->
            <div class="company-header-card mb-4">
                <div class="d-flex align-items-center gap-4">
                    <div class="company-logo-placeholder"><?= htmlspecialchars($companyInitials) ?></div>
                    <div>
                        <h2 class="fw-bold mb-1" style="font-size:1.4rem;"><?= htmlspecialchars($companyName) ?></h2>
                        <div style="color:rgba(255,255,255,.6);font-size:.875rem;">
                            <i class="bi bi-envelope me-1"></i><?= htmlspecialchars($emp['email']) ?>
                            <?php if (!empty($emp['company_website'])): ?>
                                &bull; <i class="bi bi-globe me-1"></i><?= htmlspecialchars($emp['company_website']) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card p-4 p-md-5 border-0" style="box-shadow:var(--shadow-md);">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h3 class="h4 mb-1 fw-bold text-dark">Company Profile Settings</h3>
                        <p class="text-muted small mb-0">Update your company details and recruitment contact information.</p>
                    </div>
                    <span class="badge px-3 py-2 fw-semibold" style="background:#fef3c7;color:#92400e;">Employer Account</span>
                </div>

                <form action="../actions/update-employer-profile.php" method="post">
                    <div class="form-section">
                        <div class="form-section-label"><i class="bi bi-building me-1"></i>Company Information</div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" style="font-size:.85rem;color:var(--slate-700);">Company Name <span class="text-danger">*</span></label>
                                <input class="form-control" name="company_name" value="<?= htmlspecialchars($emp['company_name'] ?? '') ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold" style="font-size:.85rem;color:var(--slate-700);">Company Website</label>
                                <input class="form-control" type="url" name="company_website" value="<?= htmlspecialchars($emp['company_website'] ?? '') ?>" placeholder="https://example.com">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold" style="font-size:.85rem;color:var(--slate-700);">About the Company</label>
                                <textarea class="form-control" name="company_about" rows="4" placeholder="Briefly describe what your organization does, team size, work culture, and mission..."><?= htmlspecialchars($emp['company_about'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="form-section-label"><i class="bi bi-person-badge me-1"></i>Recruiter Contact</div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" style="font-size:.85rem;color:var(--slate-700);">Recruiter / Contact Name <span class="text-danger">*</span></label>
                                <input class="form-control" name="name" value="<?= htmlspecialchars($emp['name']) ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold" style="font-size:.85rem;color:var(--slate-700);">Contact Email</label>
                                <input class="form-control" style="background:var(--slate-50);" value="<?= htmlspecialchars($emp['email']) ?>" readonly title="Email cannot be changed directly">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold" style="font-size:.85rem;color:var(--slate-700);">Contact Phone</label>
                                <input class="form-control" name="phone" value="<?= htmlspecialchars($emp['phone'] ?? '') ?>" placeholder="+91 98765 43210">
                            </div>
                        </div>
                    </div>

                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-primary px-5 py-2 fw-bold">
                            <i class="bi bi-check2-circle me-1"></i>Save Company Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
