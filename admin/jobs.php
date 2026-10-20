<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
require_admin();

$base_path = '../';
$page_title = 'Manage All Jobs';

// Query all jobs with employer information and applicant count
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
<section class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h2 fw-bold mb-1">Job Moderation & Listings</h1>
            <p class="text-muted mb-0">Manage all job postings, feature top openings, and inspect applicants.</p>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#adminAddJobModal">
            <i class="bi bi-plus-circle me-1"></i>Add New Job
        </button>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
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
                        <tr><td colspan="7" class="text-center py-4 text-muted">No jobs posted yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($jobs as $job): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($job['title']) ?></div>
                                    <div class="text-muted small">
                                        <i class="bi bi-building me-1"></i><?= htmlspecialchars($job['company']) ?> &bull; 
                                        <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($job['location']) ?> &bull;
                                        <span class="text-success fw-semibold"><?= htmlspecialchars($job['salary']) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="badge bg-light text-dark border"><?= htmlspecialchars($job['category']) ?></div>
                                    <div class="small text-muted mt-1"><?= htmlspecialchars($job['job_type']) ?> &bull; <?= htmlspecialchars($job['experience_level']) ?></div>
                                </td>
                                <td>
                                    <?php if ($job['employer_name']): ?>
                                        <div class="small fw-semibold"><?= htmlspecialchars($job['employer_name']) ?></div>
                                        <div class="text-muted" style="font-size:0.75rem;"><?= htmlspecialchars($job['employer_email']) ?></div>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary">System Admin</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-primary text-white"><?= $job['applicant_count'] ?> Applicants</span>
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
                                        <span class="badge bg-success-subtle text-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary">Closed</span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="d-inline-flex gap-1">
                                        <form action="../actions/toggle-job-status.php" method="post" class="d-inline">
                                            <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
                                            <button class="btn btn-outline-secondary btn-sm" title="Toggle active/closed">
                                                <?= $job['status'] === 'active' ? 'Close' : 'Reopen' ?>
                                            </button>
                                        </form>

                                        <form action="../actions/delete-job.php" method="post" class="d-inline">
                                            <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
                                            <button class="btn btn-outline-danger btn-sm" onclick="return confirm('Permanently delete this job posting?');">
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
</section>

<!-- Admin Add Job Modal -->
<div class="modal fade" id="adminAddJobModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="../actions/post-job.php" method="post">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add New Job Listing</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Job Title <span class="text-danger">*</span></label>
                            <input class="form-control" name="title" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
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
                            <label class="form-label fw-semibold">Company Name <span class="text-danger">*</span></label>
                            <input class="form-control" name="company" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Location <span class="text-danger">*</span></label>
                            <input class="form-control" name="location" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Type</label>
                            <select class="form-select" name="job_type">
                                <option value="Internship">Internship</option>
                                <option value="Full-time" selected>Full-time</option>
                                <option value="Part-time">Part-time</option>
                                <option value="Remote">Remote</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Experience</label>
                            <select class="form-select" name="experience_level">
                                <option value="Fresher">Fresher</option>
                                <option value="1-2 years">1-2 years</option>
                                <option value="3-5 years">3-5 years</option>
                                <option value="5+ years">5+ years</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Salary</label>
                            <input class="form-control" name="salary" placeholder="e.g. ₹5 LPA">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Deadline</label>
                            <input class="form-control" type="date" name="deadline" value="<?= date('Y-m-d', strtotime('+30 days')) ?>">
                        </div>
                        <div class="col-md-6 d-flex align-items-center">
                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="admin_featured">
                                <label class="form-check-label fw-semibold" for="admin_featured">Featured Job</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="description" rows="3" required></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Requirements <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="requirements" rows="2" required></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4">Publish Job</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
