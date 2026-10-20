<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
require_student();

$base_path = '../';
$page_title = 'My Applications';
$userId = (int)$_SESSION['user']['id'];

$stmt = $pdo->prepare('
    SELECT a.*, j.title, j.company, j.location, j.job_type, j.salary 
    FROM applications a
    JOIN jobs j ON a.job_id = j.id
    WHERE a.user_id = ?
    ORDER BY a.applied_at DESC
');
$stmt->execute([$userId]);
$applications = $stmt->fetchAll();

$total = count($applications);
$reviewing = $shortlisted = $hired = 0;
foreach ($applications as $app) {
    if ($app['status'] === 'reviewing')   $reviewing++;
    if ($app['status'] === 'shortlisted') $shortlisted++;
    if ($app['status'] === 'hired')       $hired++;
}

include '../includes/header.php';
?>

<div class="container py-4">

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h2 fw-bold mb-1">My Applications</h1>
            <p class="text-muted mb-0">Track real-time hiring status of your submitted applications.</p>
        </div>
        <a href="jobs.php" class="btn btn-primary fw-semibold">
            <i class="bi bi-search me-1"></i>Find More Jobs
        </a>
    </div>

    <!-- ░░ KPI Row ░░ -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="kpi-card kpi-indigo">
                <div class="kpi-icon-wrap"><i class="bi bi-send-fill"></i></div>
                <div>
                    <div class="kpi-value"><?= $total ?></div>
                    <div class="kpi-label">Total Applied</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="kpi-card kpi-sky">
                <div class="kpi-icon-wrap"><i class="bi bi-clock-history"></i></div>
                <div>
                    <div class="kpi-value"><?= $reviewing ?></div>
                    <div class="kpi-label">Under Review</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="kpi-card kpi-amber">
                <div class="kpi-icon-wrap"><i class="bi bi-star-fill"></i></div>
                <div>
                    <div class="kpi-value"><?= $shortlisted ?></div>
                    <div class="kpi-label">Shortlisted</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="kpi-card kpi-emerald">
                <div class="kpi-icon-wrap"><i class="bi bi-patch-check-fill"></i></div>
                <div>
                    <div class="kpi-value"><?= $hired ?></div>
                    <div class="kpi-label">Hired</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ░░ Applications Table ░░ -->
    <?php if (empty($applications)): ?>
        <div class="card border-0" style="box-shadow:var(--shadow-md);">
            <div class="empty-state">
                <div class="empty-icon"><i class="bi bi-send-x"></i></div>
                <h5>No applications yet</h5>
                <p>You haven't applied to any positions. Start exploring openings!</p>
                <a href="jobs.php" class="btn btn-primary px-5">
                    <i class="bi bi-search me-1"></i>Browse Openings
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="card border-0" style="box-shadow:var(--shadow-md);overflow:hidden;">
            <div class="table-responsive">
                <table class="table aura-table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Company & Job Title</th>
                            <th>Type</th>
                            <th>Applied Date</th>
                            <th>Pipeline Stage</th>
                            <th>Resume</th>
                            <th class="pe-4 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($applications as $app):
                            $pipelineSteps = ['applied', 'reviewing', 'shortlisted', 'hired'];
                            $isRejected    = $app['status'] === 'rejected';
                            $currentStepIdx = array_search($app['status'], $pipelineSteps);

                            $badge = match($app['status']) {
                                'applied'     => ['bg' => '#e0e7ff', 'text' => '#4338ca', 'icon' => 'bi-send', 'label' => 'Applied'],
                                'reviewing'   => ['bg' => '#cffafe', 'text' => '#0e7490', 'icon' => 'bi-clock-history', 'label' => 'Reviewing'],
                                'shortlisted' => ['bg' => '#fef3c7', 'text' => '#92400e', 'icon' => 'bi-star-fill', 'label' => 'Shortlisted'],
                                'hired'       => ['bg' => '#d1fae5', 'text' => '#065f46', 'icon' => 'bi-check-circle-fill', 'label' => 'Hired'],
                                'rejected'    => ['bg' => '#ffe4e6', 'text' => '#9f1239', 'icon' => 'bi-x-circle', 'label' => 'Not Selected'],
                                default       => ['bg' => '#f1f5f9', 'text' => '#475569', 'icon' => 'bi-circle', 'label' => ucfirst($app['status'])]
                            };

                            $initials = avatarInitials($app['company']);
                            $avClass  = avatarColorClass($app['company']);
                            $typeBadgeClass = match(strtolower($app['job_type'])) {
                                'internship' => 'badge-internship',
                                'full-time'  => 'badge-fulltime',
                                'part-time'  => 'badge-parttime',
                                'remote'     => 'badge-remote',
                                default      => 'badge-internship'
                            };
                        ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="company-avatar <?= $avClass ?>" style="width:40px;height:40px;font-size:.82rem;flex-shrink:0;">
                                        <?= htmlspecialchars($initials) ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size:.95rem;"><?= htmlspecialchars($app['title']) ?></div>
                                        <div class="text-muted" style="font-size:.78rem;">
                                            <i class="bi bi-building me-1"></i><?= htmlspecialchars($app['company']) ?> &bull; <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($app['location']) ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge job-type-badge <?= $typeBadgeClass ?>"><?= htmlspecialchars($app['job_type']) ?></span>
                            </td>
                            <td>
                                <div class="text-muted" style="font-size:.8rem;">
                                    <i class="bi bi-calendar3 me-1"></i><?= date('M d, Y', strtotime($app['applied_at'])) ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge px-3 py-1 fw-semibold" style="background:<?= $badge['bg'] ?>;color:<?= $badge['text'] ?>;font-size:.78rem;border-radius:var(--radius-pill);">
                                    <i class="bi <?= $badge['icon'] ?> me-1"></i><?= $badge['label'] ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($app['resume_path'])): ?>
                                    <a href="<?= $base_path . htmlspecialchars($app['resume_path']) ?>" target="_blank" class="table-btn btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-file-earmark-pdf text-danger me-1"></i>Resume
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small">None</span>
                                <?php endif; ?>
                            </td>
                            <td class="pe-4 text-end">
                                <button type="button" class="table-btn btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#appDetailModal<?= $app['id'] ?>">
                                    <i class="bi bi-info-circle me-1"></i>Details
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Detail Modals with full pipeline stepper -->
        <?php foreach ($applications as $app):
            $pipelineSteps = ['applied', 'reviewing', 'shortlisted', 'hired'];
            $isRejected    = $app['status'] === 'rejected';
            $currentStepIdx = array_search($app['status'], $pipelineSteps);

            $badge = match($app['status']) {
                'applied'     => ['bg' => '#e0e7ff', 'text' => '#4338ca', 'icon' => 'bi-send', 'label' => 'Applied'],
                'reviewing'   => ['bg' => '#cffafe', 'text' => '#0e7490', 'icon' => 'bi-clock-history', 'label' => 'Reviewing'],
                'shortlisted' => ['bg' => '#fef3c7', 'text' => '#92400e', 'icon' => 'bi-star-fill', 'label' => 'Shortlisted'],
                'hired'       => ['bg' => '#d1fae5', 'text' => '#065f46', 'icon' => 'bi-check-circle-fill', 'label' => 'Hired'],
                'rejected'    => ['bg' => '#ffe4e6', 'text' => '#9f1239', 'icon' => 'bi-x-circle', 'label' => 'Not Selected'],
                default       => ['bg' => '#f1f5f9', 'text' => '#475569', 'icon' => 'bi-circle', 'label' => ucfirst($app['status'])]
            };
        ?>
        <div class="modal fade" id="appDetailModal<?= $app['id'] ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0" style="border-radius:var(--radius-xl);box-shadow:var(--shadow-xl);">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold"><?= htmlspecialchars($app['title']) ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="text-muted" style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;">Company</div>
                                <div class="fw-semibold"><?= htmlspecialchars($app['company']) ?></div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted" style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;">Location</div>
                                <div class="fw-semibold"><?= htmlspecialchars($app['location']) ?></div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted" style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;">Salary</div>
                                <div class="fw-semibold"><?= htmlspecialchars($app['salary'] ?: 'Not specified') ?></div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted" style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;">Status</div>
                                <span class="badge px-2 py-1 fw-semibold" style="background:<?= $badge['bg'] ?>;color:<?= $badge['text'] ?>;font-size:.75rem;">
                                    <i class="bi <?= $badge['icon'] ?> me-1"></i><?= $badge['label'] ?>
                                </span>
                            </div>
                        </div>

                        <!-- Pipeline Stepper in Modal -->
                        <?php if (!$isRejected): ?>
                        <div class="mt-4 pt-3 border-top">
                            <div class="text-muted mb-2" style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;">Application Progress</div>
                            <div class="pipeline-stepper">
                                <?php foreach ($pipelineSteps as $idx => $step):
                                    $isDone   = $currentStepIdx !== false && $idx < $currentStepIdx;
                                    $isActive = $currentStepIdx !== false && $idx === $currentStepIdx;
                                    $cls = $isDone ? 'step-done' : ($isActive ? 'step-active' : '');
                                ?>
                                <div class="pipeline-step <?= $cls ?>">
                                    <div class="step-dot">
                                        <?= $isDone ? '<i class="bi bi-check2"></i>' : ($isActive ? '<i class="bi bi-circle-fill" style="font-size:.35rem;"></i>' : ($idx + 1)) ?>
                                    </div>
                                    <div class="step-label"><?= ucfirst($step) ?></div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($app['cover_letter'])): ?>
                        <div class="mt-3 p-3 rounded-xl" style="background:var(--slate-50);border:1px solid var(--slate-200);">
                            <div class="text-muted mb-1" style="font-size:.72rem;font-weight:700;text-transform:uppercase;">Your Cover Letter</div>
                            <div style="font-size:.85rem;"><?= nl2br(htmlspecialchars($app['cover_letter'])) ?></div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($app['recruiter_notes'])): ?>
                        <div class="mt-3 p-3 rounded-xl" style="background:#fef3c7;border:1px solid #fde68a;">
                            <div class="text-muted mb-1" style="font-size:.72rem;font-weight:700;text-transform:uppercase;color:#92400e;">Recruiter Notes</div>
                            <div style="font-size:.85rem;color:#78350f;">
                                <i class="bi bi-chat-quote-fill me-1"></i><?= nl2br(htmlspecialchars($app['recruiter_notes'])) ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-outline-secondary btn-sm fw-semibold" style="border-radius:var(--radius-pill);padding:0.35rem 1.4rem;" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>

<?php include '../includes/footer.php'; ?>
