<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
require_admin();

$base_path = '../';
$page_title = 'Admin Dashboard';

// Metrics
$stats = [
    'Students' => [
        'count' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn(),
        'icon' => 'bi-mortarboard-fill',
        'color' => 'primary',
        'link' => 'users.php?role=student'
    ],
    'Employers' => [
        'count' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'employer'")->fetchColumn(),
        'icon' => 'bi-building-fill',
        'color' => 'info',
        'link' => 'users.php?role=employer'
    ],
    'Total Jobs' => [
        'count' => $pdo->query("SELECT COUNT(*) FROM jobs")->fetchColumn(),
        'icon' => 'bi-briefcase-fill',
        'color' => 'success',
        'link' => 'jobs.php'
    ],
    'Applications' => [
        'count' => $pdo->query("SELECT COUNT(*) FROM applications")->fetchColumn(),
        'icon' => 'bi-send-check-fill',
        'color' => 'warning',
        'link' => 'reports.php'
    ],
    'Feedback' => [
        'count' => $pdo->query("SELECT COUNT(*) FROM feedback")->fetchColumn(),
        'icon' => 'bi-chat-left-dots-fill',
        'color' => 'danger',
        'link' => 'feedback.php'
    ]
];

// Recent applications
$recentAppsStmt = $pdo->query('
    SELECT a.*, j.title as job_title, j.company as company_name, u.name as candidate_name 
    FROM applications a
    JOIN jobs j ON a.job_id = j.id
    JOIN users u ON a.user_id = u.id
    ORDER BY a.applied_at DESC
    LIMIT 5
');
$recentApps = $recentAppsStmt->fetchAll();

// Recent jobs
$recentJobsStmt = $pdo->query('SELECT * FROM jobs ORDER BY created_at DESC LIMIT 5');
$recentJobs = $recentJobsStmt->fetchAll();

include '../includes/header.php';
?>
<section class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h2 fw-bold mb-1">Administrator Dashboard</h1>
            <p class="text-muted mb-0">System-wide monitoring, user accounts, listings moderation, and exports.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="jobs.php" class="btn btn-outline-primary">
                <i class="bi bi-briefcase me-1"></i>Manage Jobs
            </a>
            <a href="users.php" class="btn btn-outline-secondary">
                <i class="bi bi-people me-1"></i>Manage Users
            </a>
            <a href="reports.php" class="btn btn-primary">
                <i class="bi bi-graph-up me-1"></i>Reports & Exports
            </a>
        </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="row g-3 mb-4">
        <?php foreach ($stats as $label => $data): ?>
            <div class="col-sm-6 col-md-4 col-lg">
                <a href="<?= $data['link'] ?>" class="text-decoration-none">
                    <div class="card p-3 shadow-sm border-start border-4 border-<?= $data['color'] ?> h-100">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small fw-semibold"><?= $label ?></div>
                                <div class="h3 fw-bold text-dark mt-1 mb-0"><?= $data['count'] ?></div>
                            </div>
                            <i class="bi <?= $data['icon'] ?> fs-2 text-<?= $data['color'] ?> opacity-50"></i>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Content Columns -->
    <div class="row g-4">
        <!-- Recent Applications Across Portal -->
        <div class="col-lg-7">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0"><i class="bi bi-clock-history text-primary me-2"></i>Recent System Applications</h5>
                    <a href="../actions/export-csv.php?type=applications" class="btn btn-sm btn-outline-success">
                        <i class="bi bi-download me-1"></i>Export CSV
                    </a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($recentApps)): ?>
                        <p class="p-4 text-muted mb-0">No applications submitted yet.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 small">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">Candidate</th>
                                        <th>Target Job</th>
                                        <th>Status</th>
                                        <th class="pe-3 text-end">Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentApps as $app): 
                                        $badge = match($app['status']) {
                                            'applied' => 'bg-primary-subtle text-primary',
                                            'reviewing' => 'bg-info-subtle text-info',
                                            'shortlisted' => 'bg-warning-subtle text-warning',
                                            'hired' => 'bg-success-subtle text-success',
                                            'rejected' => 'bg-danger-subtle text-danger',
                                            default => 'bg-secondary-subtle text-secondary'
                                        };
                                    ?>
                                        <tr>
                                            <td class="ps-3 fw-bold"><?= htmlspecialchars($app['candidate_name']) ?></td>
                                            <td>
                                                <div><?= htmlspecialchars($app['job_title']) ?></div>
                                                <div class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($app['company_name']) ?></div>
                                            </td>
                                            <td>
                                                <span class="badge <?= $badge ?> px-2 py-1 text-capitalize"><?= htmlspecialchars($app['status']) ?></span>
                                            </td>
                                            <td class="pe-3 text-end text-muted">
                                                <?= date('M d, Y', strtotime($app['applied_at'])) ?>
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

        <!-- Recent Jobs -->
        <div class="col-lg-5">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0"><i class="bi bi-briefcase text-primary me-2"></i>Recent Job Listings</h5>
                    <a href="jobs.php" class="btn btn-sm btn-link text-decoration-none">Manage All</a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($recentJobs)): ?>
                        <p class="p-4 text-muted mb-0">No job postings in the database.</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recentJobs as $rj): ?>
                                <div class="list-group-item px-3 py-3 d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($rj['title']) ?></div>
                                        <div class="text-muted small">
                                            <?= htmlspecialchars($rj['company']) ?> &bull; 
                                            <span class="badge bg-light text-dark border"><?= htmlspecialchars($rj['job_type']) ?></span>
                                        </div>
                                    </div>
                                    <span class="badge <?= $rj['status'] === 'active' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' ?> px-2 py-1">
                                        <?= ucfirst($rj['status']) ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php include '../includes/footer.php'; ?>
