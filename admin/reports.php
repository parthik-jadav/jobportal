<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
require_admin();

$base_path = '../';
$page_title = 'System Reports & Analytics';

// High level stats
$stats = [
    'Total Registered Students' => $pdo->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn(),
    'Total Registered Employers' => $pdo->query("SELECT COUNT(*) FROM users WHERE role='employer'")->fetchColumn(),
    'Total Job Openings' => $pdo->query("SELECT COUNT(*) FROM jobs")->fetchColumn(),
    'Active Job Openings' => $pdo->query("SELECT COUNT(*) FROM jobs WHERE status='active'")->fetchColumn(),
    'Total Applications Submitted' => $pdo->query("SELECT COUNT(*) FROM applications")->fetchColumn(),
    'Total Saved Jobs' => $pdo->query("SELECT COUNT(*) FROM saved_jobs")->fetchColumn(),
    'User Feedback Messages' => $pdo->query("SELECT COUNT(*) FROM feedback")->fetchColumn(),
];

// Applications funnel breakdown
$funnelStmt = $pdo->query("
    SELECT status, COUNT(*) as count 
    FROM applications 
    GROUP BY status
");
$funnel = $funnelStmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Category breakdown
$catStmt = $pdo->query("
    SELECT j.category, COUNT(DISTINCT j.id) as job_count, COUNT(a.id) as app_count
    FROM jobs j
    LEFT JOIN applications a ON j.id = a.job_id
    GROUP BY j.category
    ORDER BY job_count DESC
");
$categoryReport = $catStmt->fetchAll();

include '../includes/header.php';
?>
<section class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h2 fw-bold mb-1">Reports & Platform Analytics</h1>
            <p class="text-muted mb-0">Aggregate performance data, hiring funnels, and data exports.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="../actions/export-csv.php?type=applications" class="btn btn-outline-success btn-sm">
                <i class="bi bi-filetype-csv me-1"></i>Export Applications
            </a>
            <a href="../actions/export-csv.php?type=users" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-filetype-csv me-1"></i>Export Users
            </a>
            <a href="../actions/export-csv.php?type=feedback" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-filetype-csv me-1"></i>Export Feedback
            </a>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Overview Table -->
        <div class="col-lg-6">
            <div class="card p-4 shadow-sm border-0 h-100">
                <h5 class="fw-bold mb-3"><i class="bi bi-bar-chart text-primary me-2"></i>Platform Totals</h5>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Metric Item</th>
                                <th class="text-end">Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stats as $label => $val): ?>
                                <tr>
                                    <td class="text-muted"><?= $label ?></td>
                                    <td class="text-end fw-bold fs-6 text-dark"><?= number_format($val) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Application Status Funnel -->
        <div class="col-lg-6">
            <div class="card p-4 shadow-sm border-0 h-100">
                <h5 class="fw-bold mb-3"><i class="bi bi-funnel text-primary me-2"></i>Candidate Hiring Pipeline Funnel</h5>
                
                <?php 
                $funnelStages = [
                    'applied' => ['label' => '1. Applied (New)', 'color' => 'bg-primary'],
                    'reviewing' => ['label' => '2. Under Review', 'color' => 'bg-info'],
                    'shortlisted' => ['label' => '3. Shortlisted', 'color' => 'bg-warning'],
                    'hired' => ['label' => '4. Hired / Offers Made', 'color' => 'bg-success'],
                    'rejected' => ['label' => '5. Not Selected', 'color' => 'bg-secondary']
                ];
                $totalApps = max(1, array_sum($funnel));
                ?>

                <div class="d-flex flex-column gap-3">
                    <?php foreach ($funnelStages as $key => $stage): 
                        $cnt = $funnel[$key] ?? 0;
                        $pct = round(($cnt / $totalApps) * 100);
                    ?>
                        <div>
                            <div class="d-flex justify-content-between small fw-semibold mb-1">
                                <span><?= $stage['label'] ?></span>
                                <span><?= $cnt ?> candidates (<?= $pct ?>%)</span>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar <?= $stage['color'] ?>" role="progressbar" style="width: <?= $pct ?>%" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Breakdown Table -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-0"><i class="bi bi-pie-chart text-primary me-2"></i>Job Listings & Applications by Industry Domain</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Industry / Category</th>
                            <th class="text-center">Total Openings</th>
                            <th class="text-center">Total Applications Received</th>
                            <th class="pe-4 text-end">Avg. Apps per Job</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($categoryReport)): ?>
                            <tr><td colspan="4" class="text-center py-4 text-muted">No data available.</td></tr>
                        <?php else: ?>
                            <?php foreach ($categoryReport as $row): 
                                $avg = $row['job_count'] > 0 ? round($row['app_count'] / $row['job_count'], 1) : 0;
                            ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-dark">
                                        <?= htmlspecialchars($row['category'] ?: 'Uncategorized') ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border px-3 py-1"><?= $row['job_count'] ?></span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary-subtle text-primary px-3 py-1"><?= $row['app_count'] ?></span>
                                    </td>
                                    <td class="pe-4 text-end fw-semibold text-muted">
                                        <?= $avg ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
<?php include '../includes/footer.php'; ?>
