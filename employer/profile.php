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

include '../includes/header.php';
?>
<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card p-4 p-md-5 shadow-sm border-0">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="h3 fw-bold mb-1">Company Profile</h2>
                        <p class="text-muted mb-0">Update your company details and recruitment contact information.</p>
                    </div>
                    <span class="badge bg-primary-subtle text-primary px-3 py-2">Employer</span>
                </div>
                <hr class="mb-4">

                <form action="../actions/update-employer-profile.php" method="post">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Company Name <span class="text-danger">*</span></label>
                            <input class="form-control" name="company_name" value="<?= htmlspecialchars($emp['company_name'] ?? '') ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Company Website</label>
                            <input class="form-control" type="url" name="company_website" value="<?= htmlspecialchars($emp['company_website'] ?? '') ?>" placeholder="https://example.com">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Recruiter / Contact Name <span class="text-danger">*</span></label>
                            <input class="form-control" name="name" value="<?= htmlspecialchars($emp['name']) ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Contact Email</label>
                            <input class="form-control bg-light" value="<?= htmlspecialchars($emp['email']) ?>" readonly title="Email cannot be changed directly">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Contact Phone</label>
                            <input class="form-control" name="phone" value="<?= htmlspecialchars($emp['phone'] ?? '') ?>" placeholder="+91 98765 43210">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">About the Company</label>
                            <textarea class="form-control" name="company_about" rows="4" placeholder="Briefly describe what your organization does, team size, work culture, and mission..."><?= htmlspecialchars($emp['company_about'] ?? '') ?></textarea>
                        </div>

                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold">
                                <i class="bi bi-check2-circle me-1"></i>Save Company Profile
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
<?php include '../includes/footer.php'; ?>
