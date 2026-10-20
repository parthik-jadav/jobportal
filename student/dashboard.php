<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
require_student();

$base_path = '../';
$page_title = 'Student Dashboard';
$userId = (int)$_SESSION['user']['id'];

// Get user info
$userStmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$userStmt->execute([$userId]);
$user = $userStmt->fetch();

// Application stats
$appStmt = $pdo->prepare('
    SELECT 
        COUNT(*) as total_apps,
        SUM(CASE WHEN status = "shortlisted" THEN 1 ELSE 0 END) as shortlisted_count,
        SUM(CASE WHEN status = "hired" THEN 1 ELSE 0 END) as hired_count
    FROM applications 
    WHERE user_id = ?
');
$appStmt->execute([$userId]);
$appStats = $appStmt->fetch();

// Saved jobs count
$savedStmt = $pdo->prepare('SELECT COUNT(*) FROM saved_jobs WHERE user_id = ?');
$savedStmt->execute([$userId]);
$savedCount = $savedStmt->fetchColumn();

// Available jobs count
$availJobsCount = $pdo->query("SELECT COUNT(*) FROM jobs WHERE status = 'active'")->fetchColumn();

// Recent applications
$recentAppsStmt = $pdo->prepare('
    SELECT a.*, j.title, j.company, j.location, j.job_type 
    FROM applications a
    JOIN jobs j ON a.job_id = j.id
    WHERE a.user_id = ?
    ORDER BY a.applied_at DESC
    LIMIT 5
');
$recentAppsStmt->execute([$userId]);
$recentApps = $recentAppsStmt->fetchAll();

// Recommended jobs
$recJobsStmt = $pdo->prepare('
    SELECT * FROM jobs 
    WHERE status = "active" 
      AND id NOT IN (SELECT job_id FROM applications WHERE user_id = ?)
    ORDER BY is_featured DESC, created_at DESC 
    LIMIT 4
');
$recJobsStmt->execute([$userId]);
$recommendedJobs = $recJobsStmt->fetchAll();

$firstName = explode(' ', $user['name'])[0];

include '../includes/header.php';
?>

<div class="container py-4">

    <!-- ░░ Dashboard Hero ░░ -->
    <div class="dash-hero mb-4 fade-up">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <span class="portal-badge">Student Portal</span>
                <h1 class="mb-1">Welcome back, <?= htmlspecialchars($firstName) ?>! 👋</h1>
                <p class="mb-0">Discover internships, track applications, and accelerate your career journey.</p>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0 d-flex gap-2 justify-content-lg-end flex-wrap">
                <a href="jobs.php" class="btn btn-light fw-bold text-primary px-4">
                    <i class="bi bi-search me-1"></i>Find Jobs
                </a>
                <a href="profile.php" class="btn fw-semibold px-3" style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.25);">
                    <i class="bi bi-pencil-square me-1"></i>Profile
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
                    <div class="kpi-value"><?= $availJobsCount ?></div>
                    <div class="kpi-label">Open Positions</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3 fade-up delay-1">
            <div class="kpi-card kpi-sky">
                <div class="kpi-icon-wrap"><i class="bi bi-send-check-fill"></i></div>
                <div>
                    <div class="kpi-value"><?= $appStats['total_apps'] ?? 0 ?></div>
                    <div class="kpi-label">Applied Jobs</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3 fade-up delay-2">
            <div class="kpi-card kpi-amber">
                <div class="kpi-icon-wrap"><i class="bi bi-trophy-fill"></i></div>
                <div>
                    <div class="kpi-value"><?= ($appStats['shortlisted_count'] + $appStats['hired_count']) ?? 0 ?></div>
                    <div class="kpi-label">Shortlisted</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3 fade-up delay-2">
            <div class="kpi-card kpi-emerald">
                <div class="kpi-icon-wrap"><i class="bi bi-bookmark-heart-fill"></i></div>
                <div>
                    <div class="kpi-value"><?= $savedCount ?></div>
                    <div class="kpi-label">Saved Jobs</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ░░ Main Content Row ░░ -->
    <div class="row g-4">

        <!-- Recent Applications -->
        <div class="col-lg-7">
            <div class="card border-0 h-100" style="box-shadow:var(--shadow-md);">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-clock-history me-2" style="color:var(--brand-primary);"></i>Recent Applications
                        </h5>
                        <a href="applications.php" class="btn btn-outline-primary btn-sm fw-semibold">View All</a>
                    </div>

                    <?php if (empty($recentApps)): ?>
                        <div class="empty-state" style="padding:2.5rem 1rem;">
                            <div class="empty-icon"><i class="bi bi-send-x"></i></div>
                            <h5>No applications yet</h5>
                            <p>Start your journey by applying to open positions.</p>
                            <a href="jobs.php" class="btn btn-primary btn-sm px-4">Browse Jobs Now</a>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recentApps as $app):
                                $pipelineSteps = ['applied', 'reviewing', 'shortlisted', 'hired'];
                                $isRejected = $app['status'] === 'rejected';
                                $currentStepIdx = array_search($app['status'], $pipelineSteps);
                            ?>
                                <div class="list-group-item px-0 py-3 border-0 border-bottom">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <div class="fw-bold text-dark" style="font-size:.9rem;"><?= htmlspecialchars($app['title']) ?></div>
                                            <div class="text-muted" style="font-size:.78rem;">
                                                <i class="bi bi-building me-1"></i><?= htmlspecialchars($app['company']) ?>
                                                &bull; <?= date('M d, Y', strtotime($app['applied_at'])) ?>
                                            </div>
                                        </div>
                                        <span class="badge" style="background:var(--brand-primary-lt);color:var(--brand-primary);font-size:.7rem;font-weight:600;"><?= htmlspecialchars($app['job_type']) ?></span>
                                    </div>
                                    <!-- Pipeline Stepper -->
                                    <?php if (!$isRejected): ?>
                                    <div class="pipeline-stepper mt-2">
                                        <?php foreach ($pipelineSteps as $idx => $step):
                                            $isDone   = $currentStepIdx !== false && $idx < $currentStepIdx;
                                            $isActive = $currentStepIdx !== false && $idx === $currentStepIdx;
                                            $stepClass = $isDone ? 'step-done' : ($isActive ? 'step-active' : '');
                                        ?>
                                        <div class="pipeline-step <?= $stepClass ?>">
                                            <div class="step-dot">
                                                <?= $isDone ? '<i class="bi bi-check2"></i>' : ($isActive ? '<i class="bi bi-circle-fill" style="font-size:.4rem;"></i>' : ($idx + 1)) ?>
                                            </div>
                                            <div class="step-label"><?= ucfirst($step) ?></div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php else: ?>
                                    <span class="badge mt-2" style="background:#ffe4e6;color:#e11d48;font-size:.72rem;font-weight:600;"><i class="bi bi-x-circle me-1"></i>Not Selected</span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recommended Jobs -->
        <div class="col-lg-5">
            <div class="card border-0 h-100" style="box-shadow:var(--shadow-md);">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-stars me-2" style="color:var(--brand-accent);"></i>Recommended For You
                        </h5>
                        <a href="jobs.php" class="btn btn-outline-primary btn-sm fw-semibold">Explore All</a>
                    </div>

                    <?php if (empty($recommendedJobs)): ?>
                        <p class="text-muted small py-3">No new recommendations right now. Check back soon!</p>
                    <?php else: ?>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($recommendedJobs as $rj):
                                $initials = avatarInitials($rj['company']);
                                $avClass  = avatarColorClass($rj['company']);
                            ?>
                                <div class="d-flex align-items-start gap-3 p-3 rounded-xl" style="background:var(--slate-50);border:1px solid var(--slate-100);">
                                    <div class="company-avatar <?= $avClass ?>" style="width:38px;height:38px;font-size:.8rem;border-radius:var(--radius-sm);"><?= htmlspecialchars($initials) ?></div>
                                    <div class="flex-grow-1 min-width-0">
                                        <div class="fw-bold text-dark" style="font-size:.875rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($rj['title']) ?></div>
                                        <div class="text-muted" style="font-size:.75rem;"><?= htmlspecialchars($rj['company']) ?> &bull; <?= htmlspecialchars($rj['location']) ?></div>
                                        <div class="d-flex align-items-center justify-content-between mt-1">
                                            <span style="font-size:.75rem;color:var(--brand-success);font-weight:600;"><?= htmlspecialchars($rj['salary']) ?></span>
                                            <a href="jobs.php?q=<?= urlencode($rj['title']) ?>" class="btn btn-primary btn-sm py-0 px-2" style="font-size:.72rem;">Apply</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div><!-- /row -->
</div><!-- /container -->

<?php include '../includes/footer.php'; ?>
