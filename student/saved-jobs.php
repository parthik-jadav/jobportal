<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
require_student();

$base_path = '../';
$page_title = 'Saved Opportunities';
$userId = (int)$_SESSION['user']['id'];

$stmt = $pdo->prepare('
    SELECT j.*, s.created_at as saved_at 
    FROM saved_jobs s 
    JOIN jobs j ON j.id = s.job_id 
    WHERE s.user_id = ? 
    ORDER BY s.created_at DESC
');
$stmt->execute([$userId]);
$jobs = $stmt->fetchAll();

// Applied jobs lookup
$appliedStmt = $pdo->prepare('SELECT job_id, status FROM applications WHERE user_id = ?');
$appliedStmt->execute([$userId]);
$appliedJobs = $appliedStmt->fetchAll(PDO::FETCH_KEY_PAIR);

include '../includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h2 fw-bold mb-1">Saved Opportunities</h1>
            <p class="text-muted mb-0">Jobs and internships you have bookmarked for later review and application.</p>
        </div>
        <a href="jobs.php" class="btn btn-outline-primary fw-semibold">
            <i class="bi bi-search me-1"></i>Explore More Jobs
        </a>
    </div>

    <?php if (empty($jobs)): ?>
        <div class="card border-0" style="box-shadow:var(--shadow-md);">
            <div class="empty-state">
                <div class="empty-icon"><i class="bi bi-bookmark-heart"></i></div>
                <h5>No saved opportunities yet</h5>
                <p>When browsing positions, click "Save" to keep track of interesting opportunities here.</p>
                <a href="jobs.php" class="btn btn-primary px-4 fw-semibold">
                    <i class="bi bi-briefcase me-1"></i>Browse Openings
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($jobs as $job): 
                $isApplied = isset($appliedJobs[$job['id']]);
                $initials  = avatarInitials($job['company']);
                $avClass   = avatarColorClass($job['company']);
                $typeBadgeClass = match(strtolower($job['job_type'])) {
                    'internship' => 'badge-internship',
                    'full-time'  => 'badge-fulltime',
                    'part-time'  => 'badge-parttime',
                    'remote'     => 'badge-remote',
                    default      => 'badge-internship'
                };
            ?>
                <div class="col-md-6">
                    <div class="card job-card h-100 p-4 border-0 d-flex flex-column" style="box-shadow:var(--shadow-md);">
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <div class="company-avatar <?= $avClass ?>"><?= htmlspecialchars($initials) ?></div>
                            <div class="flex-grow-1">
                                <h4 class="h5 fw-bold mb-1 text-dark"><?= htmlspecialchars($job['title']) ?></h4>
                                <div class="text-muted small">
                                    <i class="bi bi-building me-1"></i><strong><?= htmlspecialchars($job['company']) ?></strong> &bull; 
                                    <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($job['location']) ?>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <span class="badge job-type-badge <?= $typeBadgeClass ?>"><?= htmlspecialchars($job['job_type']) ?></span>
                            <?php if (!empty($job['experience_level'])): ?>
                                <span class="badge" style="background:var(--slate-100);color:var(--slate-700);font-size:.7rem;font-weight:600;"><?= htmlspecialchars($job['experience_level']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($job['salary'])): ?>
                                <span class="badge" style="background:#d1fae5;color:#065f46;font-size:.7rem;font-weight:600;"><i class="bi bi-currency-rupee"></i><?= htmlspecialchars($job['salary']) ?></span>
                            <?php endif; ?>
                        </div>

                        <p class="text-muted small mb-3 flex-grow-1" style="display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;line-height:1.55;">
                            <?= htmlspecialchars($job['description']) ?>
                        </p>

                        <div class="d-flex justify-content-between align-items-center pt-3 border-top mt-auto">
                            <form action="../actions/remove-job.php" method="post" class="d-inline">
                                <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
                                <button class="btn btn-outline-danger btn-sm fw-semibold" onclick="return confirm('Remove this job from your saved list?');">
                                    <i class="bi bi-trash me-1"></i>Remove
                                </button>
                            </form>

                            <?php if ($isApplied): ?>
                                <span class="badge px-3 py-2 fw-semibold" style="background:#d1fae5;color:#065f46;font-size:.78rem;">
                                    <i class="bi bi-check-circle-fill me-1"></i>Applied (<?= ucfirst($appliedJobs[$job['id']]) ?>)
                                </span>
                            <?php else: ?>
                                <a href="jobs.php?q=<?= urlencode($job['title']) ?>" class="btn btn-primary btn-sm px-3 fw-semibold">
                                    <i class="bi bi-send me-1"></i>Apply Now
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
