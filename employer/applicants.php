<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
require_employer();

$base_path = '../';
$page_title = 'Candidate Pipeline';
$employerId = (int)$_SESSION['user']['id'];

$jobsStmt = $pdo->prepare('SELECT id, title FROM jobs WHERE employer_id = ? ORDER BY created_at DESC');
$jobsStmt->execute([$employerId]);
$myJobs = $jobsStmt->fetchAll();

$filterJobId = (int)($_GET['job_id'] ?? 0);
$filterStatus = trim($_GET['status'] ?? 'all');

$sql = '
    SELECT a.*, u.name as candidate_name, u.email as candidate_email, u.phone as candidate_phone, 
           u.education as candidate_education, u.skills as candidate_skills, j.title as job_title, j.company as company_name
    FROM applications a
    JOIN users u ON a.user_id = u.id
    JOIN jobs j ON a.job_id = j.id
    WHERE j.employer_id = ?
';
$params = [$employerId];

if ($filterJobId > 0) { $sql .= ' AND j.id = ?'; $params[] = $filterJobId; }
if ($filterStatus !== '' && $filterStatus !== 'all') { $sql .= ' AND a.status = ?'; $params[] = $filterStatus; }
$sql .= ' ORDER BY a.applied_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$candidates = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h2 fw-bold mb-1">Candidate Pipeline</h1>
            <p class="text-muted mb-0">Review applications, inspect resumes, and manage hiring statuses.</p>
        </div>
        <a href="manage-jobs.php" class="btn btn-outline-primary fw-semibold">
            <i class="bi bi-briefcase me-1"></i>View Job Openings
        </a>
    </div>

    <!-- Filter Bar -->
    <div class="card border-0 mb-4" style="box-shadow:var(--shadow-md);">
        <div class="card-body p-4">
            <form method="get" action="applicants.php" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-semibold" style="font-size:.82rem;color:var(--slate-700);text-transform:uppercase;letter-spacing:.04em;">Filter by Job Opening</label>
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
                    <label class="form-label fw-semibold" style="font-size:.82rem;color:var(--slate-700);text-transform:uppercase;letter-spacing:.04em;">Hiring Stage</label>
                    <select class="form-select" name="status">
                        <option value="all" <?= $filterStatus === 'all' ? 'selected' : '' ?>>All Stages</option>
                        <option value="applied" <?= $filterStatus === 'applied' ? 'selected' : '' ?>>Applied (New)</option>
                        <option value="reviewing" <?= $filterStatus === 'reviewing' ? 'selected' : '' ?>>Reviewing</option>
                        <option value="shortlisted" <?= $filterStatus === 'shortlisted' ? 'selected' : '' ?>>Shortlisted</option>
                        <option value="hired" <?= $filterStatus === 'hired' ? 'selected' : '' ?>>Hired / Offer</option>
                        <option value="rejected" <?= $filterStatus === 'rejected' ? 'selected' : '' ?>>Not Selected</option>
                    </select>
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100 fw-semibold">
                        <i class="bi bi-funnel me-1"></i>Filter
                    </button>
                    <a href="applicants.php" class="btn btn-outline-secondary fw-semibold">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Candidate List -->
    <?php if (empty($candidates)): ?>
        <div class="card border-0" style="box-shadow:var(--shadow-md);">
            <div class="empty-state">
                <div class="empty-icon"><i class="bi bi-people"></i></div>
                <h5>No candidates found</h5>
                <p>No applications match your selected filters. Adjust filters or share your listings.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="d-flex flex-column gap-3">
            <?php foreach ($candidates as $cand):
                $badge = match($cand['status']) {
                    'applied'     => ['bg' => '#e0e7ff', 'text' => '#4338ca', 'label' => 'Applied'],
                    'reviewing'   => ['bg' => '#cffafe', 'text' => '#0e7490', 'label' => 'Under Review'],
                    'shortlisted' => ['bg' => '#fef3c7', 'text' => '#92400e', 'label' => 'Shortlisted 🎉'],
                    'hired'       => ['bg' => '#d1fae5', 'text' => '#065f46', 'label' => 'Hired 🏆'],
                    'rejected'    => ['bg' => '#ffe4e6', 'text' => '#9f1239', 'label' => 'Not Selected'],
                    default       => ['bg' => '#f1f5f9', 'text' => '#475569', 'label' => ucfirst($cand['status'])]
                };
                $candInitials = avatarInitials($cand['candidate_name']);
                $candAvClass  = avatarColorClass($cand['candidate_name']);
            ?>
            <div class="card border-0" style="box-shadow:var(--shadow-md);">
                <div class="card-body p-4">
                    <div class="row g-4">
                        <!-- Candidate Info -->
                        <div class="col-lg-6">
                            <div class="d-flex align-items-start gap-3 mb-3">
                                <div class="company-avatar <?= $candAvClass ?>" style="width:48px;height:48px;font-size:.9rem;"><?= htmlspecialchars($candInitials) ?></div>
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-start justify-content-between gap-2">
                                        <h4 class="h5 fw-bold mb-0 text-dark"><?= htmlspecialchars($cand['candidate_name']) ?></h4>
                                        <span class="badge px-2 py-1 fw-semibold flex-shrink-0" style="background:<?= $badge['bg'] ?>;color:<?= $badge['text'] ?>;font-size:.72rem;">
                                            <?= $badge['label'] ?>
                                        </span>
                                    </div>
                                    <div class="text-muted small mt-1">
                                        Applied for <strong class="text-primary"><?= htmlspecialchars($cand['job_title']) ?></strong><br>
                                        <?= date('M d, Y h:i A', strtotime($cand['applied_at'])) ?>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex flex-column gap-1 mb-3 small">
                                <div><i class="bi bi-envelope text-primary me-2"></i><?= htmlspecialchars($cand['candidate_email']) ?></div>
                                <div><i class="bi bi-telephone text-primary me-2"></i><?= htmlspecialchars($cand['candidate_phone'] ?: 'N/A') ?></div>
                                <div><i class="bi bi-mortarboard text-primary me-2"></i><?= htmlspecialchars($cand['candidate_education'] ?: 'Education not specified') ?></div>
                            </div>

                            <?php if (!empty($cand['candidate_skills'])): ?>
                            <div class="mb-3">
                                <div class="text-muted mb-1" style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;">Skills</div>
                                <div class="d-flex flex-wrap gap-1">
                                    <?php foreach (explode(',', $cand['candidate_skills']) as $skill): ?>
                                        <span class="badge" style="background:var(--brand-primary-lt);color:var(--brand-primary);font-size:.7rem;font-weight:600;border-radius:var(--radius-pill);padding:0.25rem 0.6rem;"><?= htmlspecialchars(trim($skill)) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <a href="<?= $base_path . htmlspecialchars($cand['resume_path']) ?>" target="_blank" class="table-btn btn btn-outline-danger btn-sm">
                                <i class="bi bi-file-earmark-pdf-fill me-1"></i>Download / View Resume
                            </a>
                        </div>

                        <!-- Right Column: Status Update -->
                        <div class="col-lg-6">
                            <?php if (!empty($cand['cover_letter'])): ?>
                            <div class="p-3 rounded-xl mb-3" style="background:var(--slate-50);border:1px solid var(--slate-200);border-radius:12px;">
                                <div class="text-muted mb-1" style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;">Cover Note</div>
                                <div class="small text-dark" style="line-height:1.55;"><?= nl2br(htmlspecialchars($cand['cover_letter'])) ?></div>
                            </div>
                            <?php endif; ?>

                            <form action="../actions/update-applicant-status.php" method="post" class="p-4" style="background:var(--slate-50);border:1px solid var(--slate-200);border-radius:14px;">
                                <input type="hidden" name="application_id" value="<?= $cand['id'] ?>">

                                <div class="mb-3">
                                    <label class="form-label fw-bold" style="font-size:.85rem;color:var(--slate-700);">Update Hiring Status</label>
                                    <select class="form-select" name="status" style="border-radius:10px;">
                                        <option value="applied" <?= $cand['status'] === 'applied' ? 'selected' : '' ?>>Applied (New)</option>
                                        <option value="reviewing" <?= $cand['status'] === 'reviewing' ? 'selected' : '' ?>>Reviewing</option>
                                        <option value="shortlisted" <?= $cand['status'] === 'shortlisted' ? 'selected' : '' ?>>Shortlisted</option>
                                        <option value="hired" <?= $cand['status'] === 'hired' ? 'selected' : '' ?>>Hired / Offer</option>
                                        <option value="rejected" <?= $cand['status'] === 'rejected' ? 'selected' : '' ?>>Not Selected</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold" style="font-size:.85rem;color:var(--slate-700);">Recruiter Notes / Next Steps</label>
                                    <textarea class="form-control" name="recruiter_notes" rows="2" style="border-radius:10px;" placeholder="e.g. Technical interview scheduled for Friday at 3 PM..."><?= htmlspecialchars($cand['recruiter_notes'] ?? '') ?></textarea>
                                    <div class="form-text" style="font-size:.72rem;">Notes will be visible to the candidate as status updates.</div>
                                </div>

                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-semibold" style="border-radius:var(--radius-pill);">
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
</div>

<?php include '../includes/footer.php'; ?>
