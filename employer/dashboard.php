<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
require_employer();

$base_path = '../';
$page_title = 'Employer Dashboard';
$employerId = (int)$_SESSION['user']['id'];

$empStmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$empStmt->execute([$employerId]);
$employer = $empStmt->fetch();

$jobsCountStmt = $pdo->prepare('SELECT COUNT(*) FROM jobs WHERE employer_id = ? AND status = "active"');
$jobsCountStmt->execute([$employerId]);
$activeJobsCount = (int)$jobsCountStmt->fetchColumn();

$totalJobsStmt = $pdo->prepare('SELECT COUNT(*) FROM jobs WHERE employer_id = ?');
$totalJobsStmt->execute([$employerId]);
$totalJobsCount = (int)$totalJobsStmt->fetchColumn();

$appStatsStmt = $pdo->prepare('
    SELECT 
        COUNT(*) as total_apps,
        SUM(CASE WHEN a.status = "reviewing"   THEN 1 ELSE 0 END) as reviewing_count,
        SUM(CASE WHEN a.status = "shortlisted" THEN 1 ELSE 0 END) as shortlisted_count,
        SUM(CASE WHEN a.status = "hired"       THEN 1 ELSE 0 END) as hired_count
    FROM applications a
    JOIN jobs j ON a.job_id = j.id
    WHERE j.employer_id = ?
');
$appStatsStmt->execute([$employerId]);
$appStats = $appStatsStmt->fetch();

$recentApplicantsStmt = $pdo->prepare('
    SELECT a.*, u.name as candidate_name, u.email as candidate_email, u.phone as candidate_phone, j.title as job_title
    FROM applications a
    JOIN users u ON a.user_id = u.id
    JOIN jobs j ON a.job_id = j.id
    WHERE j.employer_id = ?
    ORDER BY a.applied_at DESC
    LIMIT 5
');
$recentApplicantsStmt->execute([$employerId]);
$recentApplicants = $recentApplicantsStmt->fetchAll();

$companyName = $employer['company_name'] ?: $employer['name'];
$companyInitials = avatarInitials($companyName);

include '../includes/header.php';
?>

<div class="container py-4">

    <!-- ░░ Company Header Card ░░ -->
    <div class="company-header-card mb-4 fade-up">
        <div class="row align-items-center">
            <div class="col-lg-8 d-flex align-items-center gap-4">
                <div class="company-logo-placeholder"><?= htmlspecialchars($companyInitials) ?></div>
                <div>
                    <div class="portal-badge" style="background:rgba(245,158,11,.2);color:#fcd34d;border-color:rgba(245,158,11,.3);">Employer Hub</div>
                    <h1 class="fw-bold mb-1" style="font-size:1.6rem;"><?= htmlspecialchars($companyName) ?></h1>
                    <div style="color:rgba(255,255,255,.6);font-size:.875rem;">
                        <i class="bi bi-envelope me-1"></i><?= htmlspecialchars($employer['email']) ?>
                        <?php if (!empty($employer['company_website'])): ?>
                            &bull; <i class="bi bi-globe me-1"></i><a href="<?= htmlspecialchars($employer['company_website']) ?>" target="_blank" style="color:rgba(255,255,255,.6);"><?= htmlspecialchars($employer['company_website']) ?></a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mt-3 mt-lg-0 d-flex gap-2 justify-content-lg-end flex-wrap">
                <a href="post-job.php" class="btn fw-bold px-4" style="background:var(--brand-accent);color:#1c1917;border:none;">
                    <i class="bi bi-plus-circle me-1"></i>Post a Job
                </a>
                <a href="manage-jobs.php" class="btn fw-semibold px-3" style="background:rgba(255,255,255,.12);color:#fff;border:1px solid rgba(255,255,255,.25);">
                    <i class="bi bi-briefcase me-1"></i>Manage
                </a>
            </div>
        </div>
    </div>

    <!-- ░░ KPI Cards ░░ -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3 fade-up delay-1">
            <div class="kpi-card kpi-indigo">
                <div class="kpi-icon-wrap"><i class="bi bi-briefcase-fill"></i></div>
                <div>
                    <div class="kpi-value"><?= $activeJobsCount ?></div>
                    <div class="kpi-label">Active Postings</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3 fade-up delay-1">
            <div class="kpi-card kpi-sky">
                <div class="kpi-icon-wrap"><i class="bi bi-people-fill"></i></div>
                <div>
                    <div class="kpi-value"><?= $appStats['total_apps'] ?? 0 ?></div>
                    <div class="kpi-label">Total Applicants</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3 fade-up delay-2">
            <div class="kpi-card kpi-amber">
                <div class="kpi-icon-wrap"><i class="bi bi-star-fill"></i></div>
                <div>
                    <div class="kpi-value"><?= $appStats['shortlisted_count'] ?? 0 ?></div>
                    <div class="kpi-label">Shortlisted</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3 fade-up delay-2">
            <div class="kpi-card kpi-emerald">
                <div class="kpi-icon-wrap"><i class="bi bi-patch-check-fill"></i></div>
                <div>
                    <div class="kpi-value"><?= $appStats['hired_count'] ?? 0 ?></div>
                    <div class="kpi-label">Offers / Hired</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ░░ Recent Applicants Table ░░ -->
    <div class="card border-0 mb-0" style="box-shadow:var(--shadow-md);">
        <div class="card-body p-0">
            <div class="d-flex justify-content-between align-items-center p-4 border-bottom">
                <h5 class="fw-bold mb-0">
                    <i class="bi bi-person-lines-fill me-2" style="color:var(--brand-primary);"></i>Recent Candidate Applications
                </h5>
                <a href="applicants.php" class="btn btn-outline-primary btn-sm fw-semibold">View All</a>
            </div>

            <?php if (empty($recentApplicants)): ?>
                <div class="empty-state">
                    <div class="empty-icon"><i class="bi bi-people"></i></div>
                    <h5>No applications yet</h5>
                    <p>Post openings or share your listings to attract candidates.</p>
                    <a href="post-job.php" class="btn btn-primary px-4">Post a Job Opening</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 aura-table" style="border:none;border-radius:0;">
                        <thead>
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
                                $badge = match($cand['status']) {
                                    'applied'     => ['bg' => '#e0e7ff', 'text' => '#4338ca', 'label' => 'Applied'],
                                    'reviewing'   => ['bg' => '#cffafe', 'text' => '#0e7490', 'label' => 'Reviewing'],
                                    'shortlisted' => ['bg' => '#fef3c7', 'text' => '#92400e', 'label' => 'Shortlisted'],
                                    'hired'       => ['bg' => '#d1fae5', 'text' => '#065f46', 'label' => 'Hired'],
                                    'rejected'    => ['bg' => '#ffe4e6', 'text' => '#9f1239', 'label' => 'Not Selected'],
                                    default       => ['bg' => '#f1f5f9', 'text' => '#475569', 'label' => ucfirst($cand['status'])]
                                };
                                $candInitials = avatarInitials($cand['candidate_name']);
                                $candAvClass  = avatarColorClass($cand['candidate_name']);
                            ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="company-avatar <?= $candAvClass ?>" style="width:36px;height:36px;font-size:.75rem;"><?= htmlspecialchars($candInitials) ?></div>
                                        <div>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($cand['candidate_name']) ?></div>
                                            <div class="text-muted" style="font-size:.75rem;"><?= htmlspecialchars($cand['candidate_email']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold" style="font-size:.875rem;"><?= htmlspecialchars($cand['job_title']) ?></div>
                                </td>
                                <td>
                                    <div class="text-muted" style="font-size:.8rem;"><?= date('M d, Y', strtotime($cand['applied_at'])) ?></div>
                                </td>
                                <td>
                                    <a href="<?= $base_path . htmlspecialchars($cand['resume_path']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary fw-semibold" style="font-size:.75rem;">
                                        <i class="bi bi-file-earmark-pdf text-danger me-1"></i>Resume
                                    </a>
                                </td>
                                <td>
                                    <span class="badge px-2 py-1 fw-semibold" style="background:<?= $badge['bg'] ?>;color:<?= $badge['text'] ?>;font-size:.75rem;">
                                        <?= $badge['label'] ?>
                                    </span>
                                </td>
                                <td class="pe-4 text-end">
                                    <a href="applicants.php?job_id=<?= $cand['job_id'] ?>" class="btn btn-sm btn-outline-primary fw-semibold" style="font-size:.75rem;">
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

</div>

<?php include '../includes/footer.php'; ?>
