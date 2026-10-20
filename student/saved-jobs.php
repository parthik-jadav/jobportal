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
<section class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h2 fw-bold mb-1"><i class="bi bi-bookmark-heart text-danger me-2"></i>Saved Opportunities</h1>
            <p class="text-muted mb-0">Jobs and internships you have bookmarked for later application.</p>
        </div>
        <a href="jobs.php" class="btn btn-outline-primary">
            <i class="bi bi-search me-1"></i>Explore More Jobs
        </a>
    </div>

    <?php if (empty($jobs)): ?>
        <div class="card p-5 text-center shadow-sm">
            <i class="bi bi-bookmark-x fs-1 text-muted mb-3"></i>
            <h5 class="fw-bold">No saved jobs yet</h5>
            <p class="text-muted mb-4">When browsing positions, click "Save" to keep track of interesting opportunities here.</p>
            <div>
                <a href="jobs.php" class="btn btn-primary px-4 py-2">
                    <i class="bi bi-briefcase me-1"></i>Browse Openings
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($jobs as $job): 
                $isApplied = isset($appliedJobs[$job['id']]);
            ?>
                <div class="col-md-6">
                    <div class="card job-card h-100 p-4 shadow-sm border-0 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h4 class="h5 fw-bold mb-0"><?= htmlspecialchars($job['title']) ?></h4>
                            <span class="badge bg-primary-subtle text-primary"><?= htmlspecialchars($job['job_type']) ?></span>
                        </div>

                        <div class="text-muted small mb-3">
                            <i class="bi bi-building me-1"></i><b><?= htmlspecialchars($job['company']) ?></b> &bull; 
                            <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($job['location']) ?>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <span class="badge bg-secondary-subtle text-secondary"><?= htmlspecialchars($job['experience_level']) ?></span>
                            <span class="badge bg-success-subtle text-success"><?= htmlspecialchars($job['salary']) ?></span>
                        </div>

                        <p class="text-muted small mb-3 flex-grow-1">
                            <?= nl2br(htmlspecialchars($job['description'])) ?>
                        </p>

                        <div class="small text-muted mb-3">
                            <b>Requirements:</b> <?= htmlspecialchars($job['requirements']) ?>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-3 border-top mt-auto">
                            <form action="../actions/remove-job.php" method="post" class="d-inline">
                                <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
                                <button class="btn btn-outline-danger btn-sm" onclick="return confirm('Remove this job from your saved list?');">
                                    <i class="bi bi-trash me-1"></i>Remove
                                </button>
                            </form>

                            <?php if ($isApplied): ?>
                                <span class="badge bg-success-subtle text-success px-3 py-2">
                                    <i class="bi bi-check-circle-fill me-1"></i>Applied (<?= ucfirst($appliedJobs[$job['id']]) ?>)
                                </span>
                            <?php else: ?>
                                <a href="jobs.php?q=<?= urlencode($job['title']) ?>" class="btn btn-primary btn-sm px-3">
                                    <i class="bi bi-send me-1"></i>Apply Now
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php include '../includes/footer.php'; ?>
