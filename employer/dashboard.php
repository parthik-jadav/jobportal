<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
require_employer();

$base_path = '../';
$page_title = 'Employer Dashboard';
$employerId = (int)$_SESSION['user']['id'];

// Get employer user & company info
$empStmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$empStmt->execute([$employerId]);
$employer = $empStmt->fetch();

// Statistics
$jobsCountStmt = $pdo->prepare('SELECT COUNT(*) FROM jobs WHERE employer_id = ? AND status = "active"');
$jobsCountStmt->execute([$employerId]);
$activeJobsCount = (int)$jobsCountStmt->fetchColumn();

$appStatsStmt = $pdo->prepare('
    SELECT 
        COUNT(*) as total_apps,
        SUM(CASE WHEN a.status = "reviewing" THEN 1 ELSE 0 END) as reviewing_count,
        SUM(CASE WHEN a.status = "shortlisted" THEN 1 ELSE 0 END) as shortlisted_count,
        SUM(CASE WHEN a.status = "hired" THEN 1 ELSE 0 END) as hired_count
    FROM applications a
    JOIN jobs j ON a.job_id = j.id
    WHERE j.employer_id = ?
');
$appStatsStmt->execute([$employerId]);
$appStats = $appStatsStmt->fetch();

// Recent candidates / applicants
$recentApplicantsStmt = $pdo->prepare('
    SELECT a.*, u.name as candidate_name, u.email as candidate_email, u.phone as candidate_phone, u.education, j.title as job_title
    FROM applications a
    JOIN users u ON a.user_id = u.id
    JOIN jobs j ON a.job_id = j.id
    WHERE j.employer_id = ?
    ORDER BY a.applied_at DESC
    LIMIT 5
');
$recentApplicantsStmt->execute([$employerId]);
$recentApplicants = $recentApplicantsStmt->fetchAll();

include '../includes/header.php';
?>
<section class="container py-5">
    <!-- Hero / Recruiter Header -->
    <div class="card p-4 p-md-5 mb-4 shadow-sm border-0 text-white" style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <span class="badge bg-warning text-dark mb-2 px-3 py-1 fw-bold">Employer Hub</span>
                <h1 class="display-6 fw-bold mb-1"><?= htmlspecialchars($employer['company_name'] ?: $employer['name']) ?></h1>
                <p class="lead mb-0 opacity-75" style="font-size: 1.1rem;">
                    Manage your job listings, track incoming candidate applications, and hire top talent.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <a href="post-job.php" class="btn btn-warning text-dark fw-bold px-4 py-2 me-2">
                    <i class="bi bi-plus-circle me-1"></i>Post a New Job
                </a>
                <a href="manage-jobs.php" class="btn btn-outline-light px-3 py-2">
                    <i class="bi bi-briefcase me-1"></i>Manage Listings
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card p-3 shadow-sm border-start border-4 border-primary">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold">Active Postings</div>
                        <div class="display-6 fw-bold text-dark mt-1"><?= $activeJobsCount ?></div>
                    </div>
                    <i class="bi bi-briefcase-fill fs-1 text-primary opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card p-3 shadow-sm border-start border-4 border-info">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold">Total Applicants</div>
                        <div class="display-6 fw-bold text-info mt-1"><?= $appStats['total_apps'] ?? 0 ?></div>
                    </div>
                    <i class="bi bi-people-fill fs-1 text-info opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card p-3 shadow-sm border-start border-4 border-warning">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold">Shortlisted</div>
                        <div class="display-6 fw-bold text-warning mt-1"><?= $appStats['shortlisted_count'] ?? 0 ?></div>
                    </div>
                    <i class="bi bi-star-fill fs-1 text-warning opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card p-3 shadow-sm border-start border-4 border-success">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold">Offers Made / Hired</div>
                        <div class="display-6 fw-bold text-success mt-1"><?= $appStats['hired_count'] ?? 0 ?></div>
                    </div>
                    <i class="bi bi-check-circle-fill fs-1 text-success opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Applicants Pipeline -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0"><i class="bi bi-person-lines-fill text-primary me-2"></i>Recent Candidate Applications</h5>
            <a href="applicants.php" class="btn btn-sm btn-outline-primary">View All Applicants</a>
        </div>
        <div class="card-body p-0">
            <?php if (empty($recentApplicants)): ?>
                <div class="p-5 text-center text-muted">
                    <i class="bi bi-people fs-1 d-block mb-3"></i>
                    <h5 class="fw-bold">No candidates have applied yet</h5>
                    <p class="mb-3">Post openings or share listings to start attracting candidates.</p>
                    <a href="post-job.php" class="btn btn-primary btn-sm px-4">Post a Job Opening</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Candidate</th>
                                <th>Role Applied</th>
                                <th>Applied On</th>
                                <th>Resume</th>
                                <th>Status</th>
                                <th class="pe-4 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentApplicants as $cand): 
                                $statusBadge = match($cand['status']) {
                                    'applied' => 'bg-primary-subtle text-primary',
                                    'reviewing' => 'bg-info-subtle text-info',
                                    'shortlisted' => 'bg-warning-subtle text-warning',
                                    'hired' => 'bg-success-subtle text-success',
                                    'rejected' => 'bg-danger-subtle text-danger',
                                    default => 'bg-secondary-subtle text-secondary'
                                };
                            ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($cand['candidate_name']) ?></div>
                                        <div class="small text-muted"><?= htmlspecialchars($cand['candidate_email']) ?> &bull; <?= htmlspecialchars($cand['candidate_phone'] ?: 'No phone') ?></div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold small"><?= htmlspecialchars($cand['job_title']) ?></div>
                                    </td>
                                    <td>
                                        <div class="small text-muted"><?= date('M d, Y', strtotime($cand['applied_at'])) ?></div>
                                    </td>
                                    <td>
                                        <a href="<?= $base_path . htmlspecialchars($cand['resume_path']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm">
                                            <i class="bi bi-file-earmark-pdf text-danger me-1"></i>Resume
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge <?= $statusBadge ?> px-3 py-2 text-capitalize">
                                            <?= htmlspecialchars($cand['status']) ?>
                                        </span>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <a href="applicants.php?job_id=<?= $cand['job_id'] ?>" class="btn btn-outline-primary btn-sm">
                                            Manage Status
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php include '../includes/footer.php'; ?>
