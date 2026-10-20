<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
require_employer();

$base_path = '../';
$page_title = 'Candidate Pipeline';
$employerId = (int)$_SESSION['user']['id'];

// Get all jobs for this employer for filtering dropdown
$jobsStmt = $pdo->prepare('SELECT id, title FROM jobs WHERE employer_id = ? ORDER BY created_at DESC');
$jobsStmt->execute([$employerId]);
$myJobs = $jobsStmt->fetchAll();

$filterJobId = (int)($_GET['job_id'] ?? 0);
$filterStatus = trim($_GET['status'] ?? 'all');

// Build applications query
$sql = '
    SELECT a.*, u.name as candidate_name, u.email as candidate_email, u.phone as candidate_phone, 
           u.education as candidate_education, u.skills as candidate_skills, j.title as job_title, j.company as company_name
    FROM applications a
    JOIN users u ON a.user_id = u.id
    JOIN jobs j ON a.job_id = j.id
    WHERE j.employer_id = ?
';
$params = [$employerId];

if ($filterJobId > 0) {
    $sql .= ' AND j.id = ?';
    $params[] = $filterJobId;
}

if ($filterStatus !== '' && $filterStatus !== 'all') {
    $sql .= ' AND a.status = ?';
    $params[] = $filterStatus;
}

$sql .= ' ORDER BY a.applied_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$candidates = $stmt->fetchAll();

include '../includes/header.php';
?>
<section class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h2 fw-bold mb-1">Candidate Pipeline</h1>
            <p class="text-muted mb-0">Review student applications, inspect resumes, and manage hiring statuses.</p>
        </div>
        <a href="manage-jobs.php" class="btn btn-outline-primary">
            <i class="bi bi-briefcase me-1"></i>View Job Openings
        </a>
    </div>

    <!-- Filter Bar -->
    <div class="card p-3 shadow-sm mb-4 border-0">
        <form method="get" action="applicants.php" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label small fw-semibold">Filter by Job Opening</label>
                <select class="form-select" name="job_id">
                    <option value="0">All My Job Postings</option>
                    <?php foreach ($myJobs as $mj): ?>
                        <option value="<?= $mj['id'] ?>" <?= $filterJobId === (int)$mj['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($mj['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label small fw-semibold">Hiring Stage / Status</label>
                <select class="form-select" name="status">
                    <option value="all" <?= $filterStatus === 'all' ? 'selected' : '' ?>>All Candidate Stages</option>
                    <option value="applied" <?= $filterStatus === 'applied' ? 'selected' : '' ?>>Applied (New)</option>
                    <option value="reviewing" <?= $filterStatus === 'reviewing' ? 'selected' : '' ?>>Reviewing</option>
                    <option value="shortlisted" <?= $filterStatus === 'shortlisted' ? 'selected' : '' ?>>Shortlisted</option>
                    <option value="hired" <?= $filterStatus === 'hired' ? 'selected' : '' ?>>Hired / Offer</option>
                    <option value="rejected" <?= $filterStatus === 'rejected' ? 'selected' : '' ?>>Not Selected</option>
                </select>
            </div>

            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-funnel me-1"></i>Filter
                </button>
                <a href="applicants.php" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>

    <!-- Candidate List -->
    <?php if (empty($candidates)): ?>
        <div class="card p-5 text-center shadow-sm border-0">
            <i class="bi bi-people-fill fs-1 text-muted mb-3"></i>
            <h5 class="fw-bold">No candidates found</h5>
            <p class="text-muted mb-0">No applications match your selected filters. Adjust your filters or share your listings.</p>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($candidates as $cand): 
                $badge = match($cand['status']) {
                    'applied' => ['bg' => 'bg-primary-subtle', 'text' => 'text-primary', 'label' => 'Applied'],
                    'reviewing' => ['bg' => 'bg-info-subtle', 'text' => 'text-info', 'label' => 'Under Review'],
                    'shortlisted' => ['bg' => 'bg-warning-subtle', 'text' => 'text-warning', 'label' => 'Shortlisted 🎉'],
                    'hired' => ['bg' => 'bg-success-subtle', 'text' => 'text-success', 'label' => 'Hired 🏆'],
                    'rejected' => ['bg' => 'bg-danger-subtle', 'text' => 'text-danger', 'label' => 'Not Selected'],
                    default => ['bg' => 'bg-secondary-subtle', 'text' => 'text-secondary', 'label' => ucfirst($cand['status'])]
                };
            ?>
                <div class="col-12">
                    <div class="card p-4 shadow-sm border-0">
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h4 class="h5 fw-bold mb-0 text-dark"><?= htmlspecialchars($cand['candidate_name']) ?></h4>
                                    <span class="badge <?= $badge['bg'] ?> <?= $badge['text'] ?> px-3 py-2 fw-semibold">
                                        <?= $badge['label'] ?>
                                    </span>
                                </div>

                                <div class="text-muted small mb-3">
                                    Applied for <strong class="text-primary"><?= htmlspecialchars($cand['job_title']) ?></strong> on 
                                    <?= date('M d, Y h:i A', strtotime($cand['applied_at'])) ?>
                                </div>

                                <div class="row g-2 small mb-3">
                                    <div class="col-sm-6">
                                        <i class="bi bi-envelope text-primary me-1"></i><?= htmlspecialchars($cand['candidate_email']) ?>
                                    </div>
                                    <div class="col-sm-6">
                                        <i class="bi bi-telephone text-primary me-1"></i><?= htmlspecialchars($cand['candidate_phone'] ?: 'N/A') ?>
                                    </div>
                                    <div class="col-sm-12">
                                        <i class="bi bi-mortarboard text-primary me-1"></i><?= htmlspecialchars($cand['candidate_education'] ?: 'Education not specified') ?>
                                    </div>
                                    <?php if (!empty($cand['candidate_skills'])): ?>
                                        <div class="col-sm-12 mt-2">
                                            <div class="fw-semibold text-muted">Skills:</div>
                                            <div class="d-flex flex-wrap gap-1 mt-1">
                                                <?php foreach (explode(',', $cand['candidate_skills']) as $skill): ?>
                                                    <span class="badge bg-light text-dark border"><?= htmlspecialchars(trim($skill)) ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="d-flex gap-2">
                                    <a href="<?= $base_path . htmlspecialchars($cand['resume_path']) ?>" target="_blank" class="btn btn-outline-danger btn-sm px-3">
                                        <i class="bi bi-file-earmark-pdf-fill me-1"></i>Download / View Resume
                                    </a>
                                </div>
                            </div>

                            <!-- Right Column: Status Update & Recruiter Notes -->
                            <div class="col-lg-6 border-start-lg ps-lg-4">
                                <?php if (!empty($cand['cover_letter'])): ?>
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold text-muted">Candidate Cover Note</label>
                                        <div class="p-3 bg-light rounded small border">
                                            <?= nl2br(htmlspecialchars($cand['cover_letter'])) ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <form action="../actions/update-applicant-status.php" method="post" class="card bg-light p-3 border">
                                    <input type="hidden" name="application_id" value="<?= $cand['id'] ?>">

                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Update Candidate Status</label>
                                        <select class="form-select form-select-sm" name="status">
                                            <option value="applied" <?= $cand['status'] === 'applied' ? 'selected' : '' ?>>Applied (New)</option>
                                            <option value="reviewing" <?= $cand['status'] === 'reviewing' ? 'selected' : '' ?>>Reviewing (Under Screening)</option>
                                            <option value="shortlisted" <?= $cand['status'] === 'shortlisted' ? 'selected' : '' ?>>Shortlisted (Schedule Interview)</option>
                                            <option value="hired" <?= $cand['status'] === 'hired' ? 'selected' : '' ?>>Hired / Job Offer</option>
                                            <option value="rejected" <?= $cand['status'] === 'rejected' ? 'selected' : '' ?>>Not Selected (Rejected)</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Recruiter Notes / Next Steps</label>
                                        <textarea class="form-control form-control-sm" name="recruiter_notes" rows="2" placeholder="e.g. Technical interview scheduled for Friday at 3 PM..."><?= htmlspecialchars($cand['recruiter_notes'] ?? '') ?></textarea>
                                        <div class="form-text" style="font-size: 0.7rem;">Notes will be visible to the candidate as status updates.</div>
                                    </div>

                                    <div class="text-end">
                                        <button type="submit" class="btn btn-primary btn-sm px-3">
                                            <i class="bi bi-check2-circle me-1"></i>Save & Notify Candidate
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php include '../includes/footer.php'; ?>
