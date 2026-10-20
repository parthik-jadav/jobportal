<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
require_employer();

$base_path = '../';
$page_title = 'Edit Job Opening';
$employerId = (int)$_SESSION['user']['id'];
$jobId = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM jobs WHERE id = ? AND employer_id = ?');
$stmt->execute([$jobId, $employerId]);
$job = $stmt->fetch();

if (!$job) {
    flash('error', 'Job posting not found.');
    header('Location: manage-jobs.php');
    exit;
}

include '../includes/header.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card p-4 p-md-5 border-0" style="box-shadow:var(--shadow-md);">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="h3 fw-bold mb-1 text-dark">Edit Job Opening</h2>
                        <p class="text-muted mb-0 small">Update listing details, deadline, and requirements.</p>
                    </div>
                    <a href="manage-jobs.php" class="btn btn-outline-secondary btn-sm fw-semibold">
                        <i class="bi bi-arrow-left me-1"></i>Back to My Jobs
                    </a>
                </div>

                <form action="../actions/edit-job.php" method="post">
                    <input type="hidden" name="job_id" value="<?= $job['id'] ?>">

                    <div class="form-section">
                        <div class="form-section-label"><i class="bi bi-card-heading me-1"></i>Basic Role Information</div>
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold" style="font-size:.85rem;color:var(--slate-700);">Job Title <span class="text-danger">*</span></label>
                                <input class="form-control" name="title" value="<?= htmlspecialchars($job['title']) ?>" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold" style="font-size:.85rem;color:var(--slate-700);">Category <span class="text-danger">*</span></label>
                                <select class="form-select" name="category" required>
                                    <?php foreach (['Technology & Engineering', 'Design & Creative', 'Marketing & Growth', 'Finance & Accounts', 'Human Resources', 'Product & Operations', 'Sales & Business Dev', 'Other'] as $cat): ?>
                                        <option value="<?= $cat ?>" <?= ($job['category'] ?? '') === $cat ? 'selected' : '' ?>><?= $cat ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold" style="font-size:.85rem;color:var(--slate-700);">Company Name <span class="text-danger">*</span></label>
                                <input class="form-control" name="company" value="<?= htmlspecialchars($job['company']) ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold" style="font-size:.85rem;color:var(--slate-700);">Location <span class="text-danger">*</span></label>
                                <input class="form-control" name="location" value="<?= htmlspecialchars($job['location']) ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="form-section-label"><i class="bi bi-sliders me-1"></i>Role Details & Compensation</div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold" style="font-size:.85rem;color:var(--slate-700);">Employment Type <span class="text-danger">*</span></label>
                                <select class="form-select" name="job_type" required>
                                    <?php foreach (['Internship', 'Full-time', 'Part-time', 'Remote'] as $jt): ?>
                                        <option value="<?= $jt ?>" <?= $job['job_type'] === $jt ? 'selected' : '' ?>><?= $jt ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold" style="font-size:.85rem;color:var(--slate-700);">Experience Level <span class="text-danger">*</span></label>
                                <select class="form-select" name="experience_level" required>
                                    <?php foreach (['Fresher', '1-2 years', '3-5 years', '5+ years'] as $exp): ?>
                                        <option value="<?= $exp ?>" <?= ($job['experience_level'] ?? '') === $exp ? 'selected' : '' ?>><?= $exp ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold" style="font-size:.85rem;color:var(--slate-700);">Salary / Stipend</label>
                                <input class="form-control" name="salary" value="<?= htmlspecialchars($job['salary']) ?>">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold" style="font-size:.85rem;color:var(--slate-700);">Application Deadline</label>
                                <input class="form-control" type="date" name="deadline" value="<?= htmlspecialchars($job['deadline'] ?? '') ?>">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold" style="font-size:.85rem;color:var(--slate-700);">Status</label>
                                <select class="form-select" name="status">
                                    <option value="active" <?= $job['status'] === 'active' ? 'selected' : '' ?>>Active (Accepting applications)</option>
                                    <option value="closed" <?= $job['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
                                </select>
                            </div>

                            <div class="col-md-4 d-flex align-items-center">
                                <div class="form-check mt-3">
                                    <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="is_featured" <?= !empty($job['is_featured']) ? 'checked' : '' ?>>
                                    <label class="form-check-label fw-semibold" for="is_featured" style="font-size:.875rem;">
                                        <i class="bi bi-star-fill text-warning me-1"></i>Featured Opportunity
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="form-section-label"><i class="bi bi-file-text me-1"></i>Description & Requirements</div>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold" style="font-size:.85rem;color:var(--slate-700);">Job Description <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="description" rows="4" required><?= htmlspecialchars($job['description']) ?></textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold" style="font-size:.85rem;color:var(--slate-700);">Skills & Requirements <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="requirements" rows="3" required><?= htmlspecialchars($job['requirements']) ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-primary px-5 py-2 fw-bold">
                            <i class="bi bi-check2-circle me-1"></i>Save Job Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
