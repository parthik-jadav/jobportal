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
    LIMIT 4
');
$recentAppsStmt->execute([$userId]);
$recentApps = $recentAppsStmt->fetchAll();

// Recommended jobs
$recJobsStmt = $pdo->prepare('
    SELECT * FROM jobs 
    WHERE status = "active" 
      AND id NOT IN (SELECT job_id FROM applications WHERE user_id = ?)
    ORDER BY is_featured DESC, created_at DESC 
    LIMIT 3
');
$recJobsStmt->execute([$userId]);
$recommendedJobs = $recJobsStmt->fetchAll();

include '../includes/header.php';
?>
<section class="container py-5">
    <!-- Hero / Welcome Card -->
    <div class="card bg-primary text-white p-4 p-md-5 mb-4 shadow-sm border-0 position-relative overflow-hidden" style="background: linear-gradient(135deg, #0d6efd 0%, #4f46e5 100%);">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <span class="badge bg-white text-primary mb-2 px-3 py-1 fw-bold">Student Portal</span>
                <h1 class="display-6 fw-bold mb-2">Welcome back, <?= htmlspecialchars($user['name']) ?>!</h1>
                <p class="lead mb-0 opacity-75" style="font-size: 1.1rem;">
                    Discover internships, track your active applications, and accelerate your career.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <a href="jobs.php" class="btn btn-warning text-dark fw-bold px-4 py-2 me-2">
                    <i class="bi bi-search me-1"></i>Find Jobs
                </a>
                <a href="profile.php" class="btn btn-outline-light px-3 py-2">
                    <i class="bi bi-pencil-square me-1"></i>Edit Profile
                </a>
            </div>
        </div>
    </div>

    <!-- Metrics Row -->
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card p-3 shadow-sm border-start border-4 border-primary">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold">Available Openings</div>
                        <div class="display-6 fw-bold text-dark mt-1"><?= $availJobsCount ?></div>
                    </div>
                    <i class="bi bi-briefcase fs-1 text-primary opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card p-3 shadow-sm border-start border-4 border-info">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold">Applied Jobs</div>
                        <div class="display-6 fw-bold text-info mt-1"><?= $appStats['total_apps'] ?? 0 ?></div>
                    </div>
                    <i class="bi bi-send-check fs-1 text-info opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card p-3 shadow-sm border-start border-4 border-warning">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold">Shortlisted / Offers</div>
                        <div class="display-6 fw-bold text-warning mt-1"><?= ($appStats['shortlisted_count'] + $appStats['hired_count']) ?? 0 ?></div>
                    </div>
                    <i class="bi bi-trophy fs-1 text-warning opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card p-3 shadow-sm border-start border-4 border-success">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold">Saved Jobs</div>
                        <div class="display-6 fw-bold text-success mt-1"><?= $savedCount ?></div>
                    </div>
                    <i class="bi bi-bookmark-heart fs-1 text-success opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Recent Applications -->
        <div class="col-lg-7">
            <div class="card p-4 shadow-sm h-100 border-0">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0"><i class="bi bi-clock-history text-primary me-2"></i>Recent Applications</h5>
                    <a href="applications.php" class="btn btn-sm btn-link text-decoration-none">View All</a>
                </div>

                <?php if (empty($recentApps)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-file-earmark-x fs-1 mb-2 d-block"></i>
                        No applications submitted yet.
                        <div class="mt-2"><a href="jobs.php" class="btn btn-sm btn-primary">Browse Jobs Now</a></div>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recentApps as $app): 
                            $statusBadge = match($app['status']) {
                                'applied' => 'bg-primary-subtle text-primary',
                                'reviewing' => 'bg-info-subtle text-info',
                                'shortlisted' => 'bg-warning-subtle text-warning',
                                'hired' => 'bg-success-subtle text-success',
                                'rejected' => 'bg-danger-subtle text-danger',
                                default => 'bg-secondary-subtle text-secondary'
                            };
                        ?>
                            <div class="list-group-item px-0 py-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold"><?= htmlspecialchars($app['title']) ?></div>
                                    <div class="text-muted small">
                                        <?= htmlspecialchars($app['company']) ?> &bull; <?= date('M d, Y', strtotime($app['applied_at'])) ?>
                                    </div>
                                </div>
                                <span class="badge <?= $statusBadge ?> px-3 py-2 text-capitalize">
                                    <?= htmlspecialchars($app['status']) ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recommended Jobs -->
        <div class="col-lg-5">
            <div class="card p-4 shadow-sm h-100 border-0">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0"><i class="bi bi-stars text-warning me-2"></i>Recommended For You</h5>
                    <a href="jobs.php" class="btn btn-sm btn-link text-decoration-none">Explore All</a>
                </div>

                <?php if (empty($recommendedJobs)): ?>
                    <p class="text-muted small">No new recommendations right now. Check back soon!</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recommendedJobs as $rj): ?>
                            <div class="list-group-item px-0 py-3">
                                <div class="d-flex justify-content-between">
                                    <h6 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($rj['title']) ?></h6>
                                    <span class="badge bg-primary-subtle text-primary"><?= htmlspecialchars($rj['job_type']) ?></span>
                                </div>
                                <div class="small text-muted mb-2"><?= htmlspecialchars($rj['company']) ?> &bull; <?= htmlspecialchars($rj['location']) ?></div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small fw-semibold text-success"><?= htmlspecialchars($rj['salary']) ?></span>
                                    <a href="jobs.php?q=<?= urlencode($rj['title']) ?>" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size:0.8rem;">View</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php include '../includes/footer.php'; ?>
