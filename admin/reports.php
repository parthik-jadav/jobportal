<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
require_admin();

$base_path = '../';
$page_title = 'System Reports & Analytics';

$stats = [
    'Total Students'      => $pdo->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn(),
    'Total Employers'     => $pdo->query("SELECT COUNT(*) FROM users WHERE role='employer'")->fetchColumn(),
    'Total Job Openings'  => $pdo->query("SELECT COUNT(*) FROM jobs")->fetchColumn(),
    'Active Openings'     => $pdo->query("SELECT COUNT(*) FROM jobs WHERE status='active'")->fetchColumn(),
    'Total Applications'  => $pdo->query("SELECT COUNT(*) FROM applications")->fetchColumn(),
    'Total Saved Jobs'    => $pdo->query("SELECT COUNT(*) FROM saved_jobs")->fetchColumn(),
    'Feedback Messages'   => $pdo->query("SELECT COUNT(*) FROM feedback")->fetchColumn(),
];

$funnel = $pdo->query("SELECT status, COUNT(*) as count FROM applications GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);

$categoryReport = $pdo->query("
    SELECT j.category, COUNT(DISTINCT j.id) as job_count, COUNT(a.id) as app_count
    FROM jobs j
    LEFT JOIN applications a ON j.id = a.job_id
    GROUP BY j.category
    ORDER BY job_count DESC
")->fetchAll();

include '../includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h2 fw-bold mb-1">Reports & Platform Analytics</h1>
            <p class="text-muted mb-0">Aggregate performance data, hiring funnels, and data exports.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="../actions/export-csv.php?type=applications" class="btn btn-outline-success btn-sm fw-semibold">
                <i class="bi bi-filetype-csv me-1"></i>Export Applications
            </a>
            <a href="../actions/export-csv.php?type=users" class="btn btn-outline-primary btn-sm fw-semibold">
                <i class="bi bi-filetype-csv me-1"></i>Export Users
            </a>
            <a href="../actions/export-csv.php?type=feedback" class="btn btn-outline-secondary btn-sm fw-semibold">
                <i class="bi bi-filetype-csv me-1"></i>Export Feedback
            </a>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Overview Stats Table -->
        <div class="col-lg-6">
            <div class="card border-0 h-100" style="box-shadow:var(--shadow-md);">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">
                        <i class="bi bi-bar-chart me-2" style="color:var(--brand-primary);"></i>Platform Totals
                    </h5>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($stats as $label => $val): ?>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span class="text-muted" style="font-size:.875rem;"><?= $label ?></span>
                            <span class="fw-bold text-dark fs-5"><?= number_format($val) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Application Funnel -->
        <div class="col-lg-6">
            <div class="card border-0 h-100" style="box-shadow:var(--shadow-md);">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">
                        <i class="bi bi-funnel me-2" style="color:var(--brand-primary);"></i>Candidate Hiring Pipeline
                    </h5>

                    <?php
                    $funnelStages = [
                        'applied'     => ['label' => '1. Applied (New)',        'color' => 'var(--brand-primary)'],
                        'reviewing'   => ['label' => '2. Under Review',         'color' => 'var(--brand-info)'],
                        'shortlisted' => ['label' => '3. Shortlisted',          'color' => 'var(--brand-accent)'],
                        'hired'       => ['label' => '4. Hired / Offers Made',  'color' => 'var(--brand-success)'],
                        'rejected'    => ['label' => '5. Not Selected',         'color' => 'var(--slate-400)']
                    ];
                    $totalApps = max(1, array_sum($funnel));
                    ?>

                    <div class="d-flex flex-column gap-4">
                        <?php foreach ($funnelStages as $key => $stage):
                            $cnt = $funnel[$key] ?? 0;
                            $pct = round(($cnt / $totalApps) * 100);
                        ?>
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-semibold" style="font-size:.85rem;color:var(--slate-700);"><?= $stage['label'] ?></span>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fw-bold text-dark"><?= $cnt ?></span>
                                    <span class="badge" style="background:var(--slate-100);color:var(--slate-600);font-size:.7rem;"><?= $pct ?>%</span>
                                </div>
                            </div>
                            <div class="funnel-bar progress" style="height:12px;background:var(--slate-100);border-radius:var(--radius-pill);">
                                <div class="funnel-fill progress-bar" role="progressbar" style="width:<?= $pct ?>%;background:<?= $stage['color'] ?>;border-radius:var(--radius-pill);" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Breakdown -->
    <div class="card border-0" style="box-shadow:var(--shadow-md);">
        <div class="d-flex justify-content-between align-items-center p-4 border-bottom">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-pie-chart me-2" style="color:var(--brand-primary);"></i>Jobs & Applications by Industry Domain
            </h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size:.875rem;">
                <thead style="background:var(--slate-50);">
                    <tr>
                        <th class="ps-4 border-0 text-muted fw-700" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;">Industry / Category</th>
                        <th class="text-center border-0 text-muted fw-700" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;">Total Openings</th>
                        <th class="text-center border-0 text-muted fw-700" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;">Applications</th>
                        <th class="pe-4 text-end border-0 text-muted fw-700" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;">Avg. Apps/Job</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categoryReport)): ?>
                        <tr><td colspan="4" class="text-center py-5 text-muted">No data available.</td></tr>
                    <?php else: ?>
                        <?php foreach ($categoryReport as $row):
                            $avg = $row['job_count'] > 0 ? round($row['app_count'] / $row['job_count'], 1) : 0;
                        ?>
                        <tr>
                            <td class="ps-4 fw-semibold text-dark"><?= htmlspecialchars($row['category'] ?: 'Uncategorized') ?></td>
                            <td class="text-center">
                                <span class="badge fw-semibold" style="background:var(--slate-100);color:var(--slate-700);font-size:.75rem;padding:.35rem .75rem;"><?= $row['job_count'] ?></span>
                            </td>
                            <td class="text-center">
                                <span class="badge fw-semibold" style="background:var(--brand-primary-lt);color:var(--brand-primary);font-size:.75rem;padding:.35rem .75rem;"><?= $row['app_count'] ?></span>
                            </td>
                            <td class="pe-4 text-end fw-semibold text-muted"><?= $avg ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
