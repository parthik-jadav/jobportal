<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
require_employer();

$base_path = '../';
$page_title = 'Manage My Jobs';
$employerId = (int)$_SESSION['user']['id'];

// Fetch all jobs for this employer with applicant count
$stmt = $pdo->prepare('
    SELECT j.*, COUNT(a.id) as applicant_count 
    FROM jobs j
    LEFT JOIN applications a ON j.id = a.job_id
    WHERE j.employer_id = ?
    GROUP BY j.id
    ORDER BY j.created_at DESC
');
$stmt->execute([$employerId]);
$jobs = $stmt->fetchAll();

include '../includes/header.php';
?>
<section class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h2 fw-bold mb-1">Manage Job Openings</h1>
            <p class="text-muted mb-0">Oversee your active and closed job postings and manage candidates.</p>
        </div>
        <a href="post-job.php" class="btn btn-warning text-dark fw-bold">
            <i class="bi bi-plus-circle me-1"></i>Post a New Job
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <?php if (empty($jobs)): ?>
            <div class="p-5 text-center text-muted">
                <i class="bi bi-briefcase fs-1 mb-3 d-block"></i>
                <h5 class="fw-bold">You haven't posted any jobs yet</h5>
                <p class="mb-4">Post your first internship or job to start receiving applications from students.</p>
                <a href="post-job.php" class="btn btn-primary px-4">Post Your First Job</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Job Title & Details</th>
                            <th>Type / Exp</th>
                            <th>Deadline</th>
                            <th>Applicants</th>
                            <th>Status</th>
                            <th class="pe-4 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($jobs as $job): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($job['title']) ?></div>
                                    <div class="text-muted small">
                                        <span class="badge bg-light text-dark border me-1"><?= htmlspecialchars($job['category']) ?></span>
                                        <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($job['location']) ?> &bull;
                                        <span class="text-success fw-semibold"><?= htmlspecialchars($job['salary']) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="small fw-semibold"><?= htmlspecialchars($job['job_type']) ?></div>
                                    <div class="text-muted small"><?= htmlspecialchars($job['experience_level']) ?></div>
                                </td>
                                <td>
                                    <?php if (!empty($job['deadline'])): ?>
                                        <div class="small text-muted"><?= date('M d, Y', strtotime($job['deadline'])) ?></div>
                                    <?php else: ?>
                                        <span class="text-muted small">No deadline</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="applicants.php?job_id=<?= $job['id'] ?>" class="btn btn-sm btn-outline-primary position-relative">
                                        <i class="bi bi-people me-1"></i>Candidates
                                        <span class="badge bg-primary text-white ms-1"><?= $job['applicant_count'] ?></span>
                                    </a>
                                </td>
                                <td>
                                    <?php if ($job['status'] === 'active'): ?>
                                        <span class="badge bg-success-subtle text-success px-3 py-2">
                                            <i class="bi bi-check-circle me-1"></i>Active
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary px-3 py-2">
                                            <i class="bi bi-dash-circle me-1"></i>Closed
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="d-inline-flex gap-1">
                                        <!-- Status Toggle Form -->
                                        <form action="../actions/toggle-job-status.php" method="post" class="d-inline">
                                            <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
                                            <button class="btn btn-outline-secondary btn-sm" title="Toggle status (Active / Closed)">
                                                <?= $job['status'] === 'active' ? 'Close' : 'Reopen' ?>
                                            </button>
                                        </form>

                                        <!-- Edit Job Button -->
                                        <a href="edit-job.php?id=<?= $job['id'] ?>" class="btn btn-outline-primary btn-sm" title="Edit Job">
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        <!-- Delete Job Form -->
                                        <form action="../actions/delete-job.php" method="post" class="d-inline">
                                            <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
                                            <button class="btn btn-outline-danger btn-sm" title="Delete job posting" onclick="return confirm('Are you sure you want to delete this job and all associated applications?');">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php include '../includes/footer.php'; ?>
