<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
require_admin();

$base_path = '../';
$page_title = 'Manage All Jobs';

$stmt = $pdo->query('
    SELECT j.*, u.name as employer_name, u.email as employer_email, COUNT(a.id) as applicant_count
    FROM jobs j
    LEFT JOIN users u ON j.employer_id = u.id
    LEFT JOIN applications a ON j.id = a.job_id
    GROUP BY j.id
    ORDER BY j.created_at DESC
');
$jobs = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h2 fw-bold mb-1">Job Moderation & Listings</h1>
            <p class="text-muted mb-0">Manage all job postings, feature top openings, and inspect applicants.</p>
        </div>
        <button type="button" class="btn btn-primary fw-semibold" data-bs-toggle="modal" data-bs-target="#adminAddJobModal">
            <i class="bi bi-plus-circle me-1"></i>Add New Job
        </button>
    </div>

    <div class="card border-0" style="box-shadow:var(--shadow-md);">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 aura-table" style="border:none;border-radius:0;">
                <thead>
                    <tr>
                        <th class="ps-4">Job Title & Company</th>
                        <th>Category / Type</th>
                        <th>Employer</th>
                        <th>Applicants</th>
                        <th>Featured</th>
                        <th>Status</th>
                        <th class="pe-4 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($jobs)): ?>
                        <tr><td colspan="7" class="text-center py-5 text-muted">No jobs posted yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($jobs as $job):
                            $initials = avatarInitials($job['company']);
                            $avClass  = avatarColorClass($job['company']);
                        ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="company-avatar <?= $avClass ?>" style="width:36px;height:36px;font-size:.72rem;flex-shrink:0;"><?= htmlspecialchars($initials) ?></div>
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size:.875rem;"><?= htmlspecialchars($job['title']) ?></div>
                                        <div class="text-muted" style="font-size:.75rem;">
                                            <?= htmlspecialchars($job['company']) ?> &bull; <?= htmlspecialchars($job['location']) ?>
                                            <span class="fw-semibold text-success">&bull; <?= htmlspecialchars($job['salary']) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="badge" style="background:var(--slate-100);color:var(--slate-700);font-size:.7rem;font-weight:600;"><?= htmlspecialchars($job['category']) ?></div>
                                <div class="text-muted mt-1" style="font-size:.75rem;"><?= htmlspecialchars($job['job_type']) ?> &bull; <?= htmlspecialchars($job['experience_level']) ?></div>
                            </td>
                            <td>
                                <?php if ($job['employer_name']): ?>
                                    <div class="fw-semibold" style="font-size:.85rem;"><?= htmlspecialchars($job['employer_name']) ?></div>
                                    <div class="text-muted" style="font-size:.72rem;"><?= htmlspecialchars($job['employer_email']) ?></div>
                                <?php else: ?>
                                    <span class="badge" style="background:#f1f5f9;color:#475569;font-size:.72rem;">System Admin</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge fw-semibold" style="background:var(--brand-primary-lt);color:var(--brand-primary);font-size:.75rem;"><?= $job['applicant_count'] ?> Applicants</span>
                            </td>
                            <td>
                                <form action="../actions/toggle-featured.php" method="post" class="d-inline">
                                    <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
                                    <button class="btn btn-sm <?= !empty($job['is_featured']) ? 'btn-warning text-dark' : 'btn-outline-secondary' ?>" title="Toggle Featured status">
                                        <i class="bi bi-star<?= !empty($job['is_featured']) ? '-fill' : '' ?>"></i>
                                    </button>
                                </form>
                            </td>
                            <td>
                                <?php if ($job['status'] === 'active'): ?>
                                    <span class="badge px-2 py-1 fw-semibold" style="background:#d1fae5;color:#065f46;font-size:.72rem;">Active</span>
                                <?php else: ?>
                                    <span class="badge px-2 py-1 fw-semibold" style="background:#f1f5f9;color:#475569;font-size:.72rem;">Closed</span>
                                <?php endif; ?>
                            </td>
                            <td class="pe-4 text-end">
                                <div class="d-inline-flex gap-1">
                                    <form action="../actions/toggle-job-status.php" method="post" class="d-inline">
                                        <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
                                        <button class="btn btn-outline-secondary btn-sm fw-semibold" style="font-size:.72rem;" title="Toggle active/closed">
                                            <?= $job['status'] === 'active' ? 'Close' : 'Reopen' ?>
                                        </button>
                                    </form>
                                    <form action="../actions/delete-job.php" method="post" class="d-inline">
                                        <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
                                        <button class="btn btn-outline-danger btn-sm" style="font-size:.72rem;" title="Delete" onclick="return confirm('Permanently delete this job posting?');">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Admin Add Job Modal -->
<div class="modal fade" id="adminAddJobModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0" style="border-radius:var(--radius-xl);box-shadow:var(--shadow-xl);">
            <form action="../actions/post-job.php" method="post">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Add New Job Listing</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold" style="font-size:.85rem;">Job Title <span class="text-danger">*</span></label>
                            <input class="form-control" name="title" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:.85rem;">Category <span class="text-danger">*</span></label>
                            <select class="form-select" name="category" required>
                                <option value="Technology & Engineering">Technology & Engineering</option>
                                <option value="Design & Creative">Design & Creative</option>
                                <option value="Marketing & Growth">Marketing & Growth</option>
                                <option value="Finance & Accounts">Finance & Accounts</option>
                                <option value="Human Resources">Human Resources</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:.85rem;">Company Name <span class="text-danger">*</span></label>
                            <input class="form-control" name="company" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:.85rem;">Location <span class="text-danger">*</span></label>
                            <input class="form-control" name="location" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:.85rem;">Type</label>
                            <select class="form-select" name="job_type">
                                <option value="Internship">Internship</option>
                                <option value="Full-time" selected>Full-time</option>
                                <option value="Part-time">Part-time</option>
                                <option value="Remote">Remote</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:.85rem;">Experience</label>
                            <select class="form-select" name="experience_level">
                                <option value="Fresher">Fresher</option>
                                <option value="1-2 years">1-2 years</option>
                                <option value="3-5 years">3-5 years</option>
                                <option value="5+ years">5+ years</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:.85rem;">Salary</label>
                            <input class="form-control" name="salary" placeholder="e.g. ₹5 LPA">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:.85rem;">Deadline</label>
                            <input class="form-control" type="date" name="deadline" value="<?= date('Y-m-d', strtotime('+30 days')) ?>">
                        </div>
                        <div class="col-md-6 d-flex align-items-center">
                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="admin_featured">
                                <label class="form-check-label fw-semibold" for="admin_featured"><i class="bi bi-star-fill text-warning me-1"></i>Featured Job</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size:.85rem;">Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="description" rows="3" required></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size:.85rem;">Requirements <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="requirements" rows="2" required></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm fw-semibold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-semibold">Publish Job</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
