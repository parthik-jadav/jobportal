<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
require_student();

$base_path = '../';
$page_title = 'My Applications';

$userId = (int)$_SESSION['user']['id'];

// Fetch applications with job details
$stmt = $pdo->prepare('
    SELECT a.*, j.title, j.company, j.location, j.job_type, j.salary 
    FROM applications a
    JOIN jobs j ON a.job_id = j.id
    WHERE a.user_id = ?
    ORDER BY a.applied_at DESC
');
$stmt->execute([$userId]);
$applications = $stmt->fetchAll();

// Counts for quick stats
$total = count($applications);
$reviewing = 0;
$shortlisted = 0;
$hired = 0;
foreach ($applications as $app) {
    if ($app['status'] === 'reviewing') $reviewing++;
    if ($app['status'] === 'shortlisted') $shortlisted++;
    if ($app['status'] === 'hired') $hired++;
}

include '../includes/header.php';
?>
<section class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h2 fw-bold mb-1">My Applications</h1>
            <p class="text-muted mb-0">Track the real-time hiring status of your submitted job applications.</p>
        </div>
        <a href="jobs.php" class="btn btn-primary">
            <i class="bi bi-search me-1"></i>Find More Jobs
        </a>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card p-3 shadow-sm border-start border-4 border-primary">
                <div class="text-muted small">Total Submissions</div>
                <div class="h3 fw-bold mb-0"><?= $total ?></div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card p-3 shadow-sm border-start border-4 border-info">
                <div class="text-muted small">Under Review</div>
                <div class="h3 fw-bold mb-0 text-info"><?= $reviewing ?></div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card p-3 shadow-sm border-start border-4 border-warning">
                <div class="text-muted small">Shortlisted</div>
                <div class="h3 fw-bold mb-0 text-warning"><?= $shortlisted ?></div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card p-3 shadow-sm border-start border-4 border-success">
                <div class="text-muted small">Offers / Hired</div>
                <div class="h3 fw-bold mb-0 text-success"><?= $hired ?></div>
            </div>
        </div>
    </div>

    <?php if (empty($applications)): ?>
        <div class="card p-5 text-center shadow-sm">
            <i class="bi bi-send-x fs-1 text-muted mb-3"></i>
            <h4 class="fw-bold">No applications submitted yet</h4>
            <p class="text-muted mb-4">You have not applied for any positions yet. Explore openings and start applying today!</p>
            <div>
                <a href="jobs.php" class="btn btn-primary px-4 py-2">
                    <i class="bi bi-search me-1"></i>Browse Openings
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Job Details</th>
                            <th>Job Type</th>
                            <th>Applied Date</th>
                            <th>Resume</th>
                            <th>Status</th>
                            <th class="pe-4 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($applications as $app): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($app['title']) ?></div>
                                    <div class="text-muted small">
                                        <i class="bi bi-building me-1"></i><?= htmlspecialchars($app['company']) ?> &bull; 
                                        <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($app['location']) ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary"><?= htmlspecialchars($app['job_type']) ?></span>
                                </td>
                                <td>
                                    <div class="small text-muted">
                                        <?= date('M d, Y', strtotime($app['applied_at'])) ?>
                                    </div>
                                    <div class="text-muted" style="font-size: 0.75rem;">
                                        <?= date('h:i A', strtotime($app['applied_at'])) ?>
                                    </div>
                                </td>
                                <td>
                                    <a href="<?= $base_path . htmlspecialchars($app['resume_path']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm" title="Preview Resume Used">
                                        <i class="bi bi-file-earmark-pdf text-danger me-1"></i>Resume
                                    </a>
                                </td>
                                <td>
                                    <?php
                                    $badge = match($app['status']) {
                                        'applied' => ['bg' => 'bg-primary-subtle', 'text' => 'text-primary', 'label' => 'Applied', 'icon' => 'bi-send'],
                                        'reviewing' => ['bg' => 'bg-info-subtle', 'text' => 'text-info', 'label' => 'Reviewing', 'icon' => 'bi-clock-history'],
                                        'shortlisted' => ['bg' => 'bg-warning-subtle', 'text' => 'text-warning', 'label' => 'Shortlisted', 'icon' => 'bi-star-fill'],
                                        'hired' => ['bg' => 'bg-success-subtle', 'text' => 'text-success', 'label' => 'Hired', 'icon' => 'bi-check-circle-fill'],
                                        'rejected' => ['bg' => 'bg-danger-subtle', 'text' => 'text-danger', 'label' => 'Not Selected', 'icon' => 'bi-x-circle'],
                                        default => ['bg' => 'bg-secondary-subtle', 'text' => 'text-secondary', 'label' => ucfirst($app['status']), 'icon' => 'bi-circle']
                                    };
                                    ?>
                                    <span class="badge <?= $badge['bg'] ?> <?= $badge['text'] ?> px-3 py-2 fw-semibold">
                                        <i class="bi <?= $badge['icon'] ?> me-1"></i><?= $badge['label'] ?>
                                    </span>
                                </td>
                                <td class="pe-4 text-end">
                                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#appDetailModal<?= $app['id'] ?>">
                                        <i class="bi bi-info-circle me-1"></i>Details
                                    </button>

                                    <!-- Application Detail Modal -->
                                    <div class="modal fade" id="appDetailModal<?= $app['id'] ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered text-start">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold"><?= htmlspecialchars($app['title']) ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <div class="fw-semibold text-muted small">Company</div>
                                                        <div><?= htmlspecialchars($app['company']) ?> &mdash; <?= htmlspecialchars($app['location']) ?></div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <div class="fw-semibold text-muted small">Compensation</div>
                                                        <div><?= htmlspecialchars($app['salary'] ?: 'Not specified') ?></div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <div class="fw-semibold text-muted small">Current Application Status</div>
                                                        <div class="mt-1">
                                                            <span class="badge <?= $badge['bg'] ?> <?= $badge['text'] ?> px-3 py-2">
                                                                <i class="bi <?= $badge['icon'] ?> me-1"></i><?= $badge['label'] ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <?php if (!empty($app['cover_letter'])): ?>
                                                        <div class="mb-3">
                                                            <div class="fw-semibold text-muted small">Your Cover Letter / Note</div>
                                                            <div class="p-2 bg-light rounded small mt-1"><?= nl2br(htmlspecialchars($app['cover_letter'])) ?></div>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($app['recruiter_notes'])): ?>
                                                        <div class="mb-3">
                                                            <div class="fw-semibold text-muted small">Recruiter Updates / Notes</div>
                                                            <div class="p-2 bg-warning-subtle text-warning-emphasis rounded small mt-1">
                                                                <i class="bi bi-chat-quote-fill me-1"></i><?= nl2br(htmlspecialchars($app['recruiter_notes'])) ?>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</section>
<?php include '../includes/footer.php'; ?>
