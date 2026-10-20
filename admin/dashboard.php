<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
require_admin();

$base_path = '../';
$page_title = 'Admin Dashboard';

$kpiData = [
    'Students'     => ['count' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn(),    'icon' => 'bi-mortarboard-fill',    'kpi' => 'kpi-indigo',  'link' => 'users.php?role=student'],
    'Employers'    => ['count' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'employer'")->fetchColumn(),   'icon' => 'bi-building-fill',       'kpi' => 'kpi-sky',     'link' => 'users.php?role=employer'],
    'Total Jobs'   => ['count' => $pdo->query("SELECT COUNT(*) FROM jobs")->fetchColumn(),                           'icon' => 'bi-briefcase-fill',      'kpi' => 'kpi-emerald', 'link' => 'jobs.php'],
    'Applications' => ['count' => $pdo->query("SELECT COUNT(*) FROM applications")->fetchColumn(),                   'icon' => 'bi-send-check-fill',     'kpi' => 'kpi-amber',   'link' => 'reports.php'],
    'Feedback'     => ['count' => $pdo->query("SELECT COUNT(*) FROM feedback")->fetchColumn(),                       'icon' => 'bi-chat-left-dots-fill', 'kpi' => 'kpi-rose',    'link' => 'feedback.php'],
];

$recentApps = $pdo->query('
    SELECT a.*, j.title as job_title, j.company as company_name, u.name as candidate_name 
    FROM applications a
    JOIN jobs j ON a.job_id = j.id
    JOIN users u ON a.user_id = u.id
    ORDER BY a.applied_at DESC
    LIMIT 5
')->fetchAll();

$recentJobs = $pdo->query('SELECT * FROM jobs ORDER BY created_at DESC LIMIT 6')->fetchAll();

include '../includes/header.php';
?>

<div class="container py-4">

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h2 fw-bold mb-1">Administrator Dashboard</h1>
            <p class="text-muted mb-0">System-wide monitoring, user accounts, listings moderation, and exports.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="jobs.php" class="btn btn-outline-primary fw-semibold">
                <i class="bi bi-briefcase me-1"></i>Manage Jobs
            </a>
            <a href="users.php" class="btn btn-outline-secondary fw-semibold">
                <i class="bi bi-people me-1"></i>Manage Users
            </a>
            <a href="reports.php" class="btn btn-primary fw-semibold">
                <i class="bi bi-graph-up me-1"></i>Reports & Exports
            </a>
        </div>
    </div>

    <!-- ░░ KPI Grid ░░ -->
    <div class="row g-3 mb-4">
        <?php foreach ($kpiData as $label => $data): ?>
        <div class="col-6 col-md-4 col-lg">
            <a href="<?= $data['link'] ?>" class="text-decoration-none">
                <div class="card kpi-card <?= $data['kpi'] ?> h-100 fade-up">
                    <div class="kpi-icon-wrap"><i class="bi <?= $data['icon'] ?>"></i></div>
                    <div>
                        <div class="kpi-value h3 fw-bold"><?= number_format($data['count']) ?></div>
                        <div class="kpi-label"><?= $label ?></div>
                    </div>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- ░░ Content Columns ░░ -->
    <div class="row g-4">

        <!-- Recent Applications -->
        <div class="col-lg-7">
            <div class="card border-0 h-100" style="box-shadow:var(--shadow-md);">
                <div class="d-flex justify-content-between align-items-center p-4 border-bottom">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-clock-history me-2" style="color:var(--brand-primary);"></i>Recent Applications
                    </h5>
                    <a href="../actions/export-csv.php?type=applications" class="btn btn-sm btn-outline-success fw-semibold">
                        <i class="bi bi-download me-1"></i>Export CSV
                    </a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($recentApps)): ?>
                        <p class="p-4 text-muted mb-0">No applications submitted yet.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="font-size:.875rem;">
                                <thead style="background:var(--slate-50);">
                                    <tr>
                                        <th class="ps-4 border-0 text-muted fw-700" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;">Candidate</th>
                                        <th class="border-0 text-muted fw-700" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;">Job</th>
                                        <th class="border-0 text-muted fw-700" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;">Status</th>
                                        <th class="pe-4 text-end border-0 text-muted fw-700" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;">Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentApps as $app):
                                        $badge = match($app['status']) {
                                            'applied'     => ['bg' => '#e0e7ff', 'text' => '#4338ca'],
                                            'reviewing'   => ['bg' => '#cffafe', 'text' => '#0e7490'],
                                            'shortlisted' => ['bg' => '#fef3c7', 'text' => '#92400e'],
                                            'hired'       => ['bg' => '#d1fae5', 'text' => '#065f46'],
                                            'rejected'    => ['bg' => '#ffe4e6', 'text' => '#9f1239'],
                                            default       => ['bg' => '#f1f5f9', 'text' => '#475569']
                                        };
                                    ?>
                                    <tr>
                                        <td class="ps-4 fw-semibold"><?= htmlspecialchars($app['candidate_name']) ?></td>
                                        <td>
                                            <div><?= htmlspecialchars($app['job_title']) ?></div>
                                            <div class="text-muted" style="font-size:.75rem;"><?= htmlspecialchars($app['company_name']) ?></div>
                                        </td>
                                        <td>
                                            <span class="badge px-2 py-1 fw-semibold" style="background:<?= $badge['bg'] ?>;color:<?= $badge['text'] ?>;font-size:.72rem;text-transform:capitalize;">
                                                <?= htmlspecialchars($app['status']) ?>
                                            </span>
                                        </td>
                                        <td class="pe-4 text-end text-muted" style="font-size:.78rem;"><?= date('M d, Y', strtotime($app['applied_at'])) ?></td>
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
            <div class="card border-0 h-100" style="box-shadow:var(--shadow-md);">
                <div class="d-flex justify-content-between align-items-center p-4 border-bottom">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-briefcase me-2" style="color:var(--brand-primary);"></i>Recent Job Listings
                    </h5>
                    <a href="jobs.php" class="btn btn-sm btn-outline-primary fw-semibold">Manage All</a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($recentJobs)): ?>
                        <p class="p-4 text-muted mb-0">No job postings yet.</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recentJobs as $rj):
                                $initials = avatarInitials($rj['company']);
                                $avClass  = avatarColorClass($rj['company']);
                            ?>
                            <div class="list-group-item px-4 py-3 d-flex align-items-center gap-3">
                                <div class="company-avatar <?= $avClass ?>" style="width:36px;height:36px;font-size:.72rem;flex-shrink:0;"><?= htmlspecialchars($initials) ?></div>
                                <div class="flex-grow-1 min-width-0">
                                    <div class="fw-semibold text-dark" style="font-size:.875rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($rj['title']) ?></div>
                                    <div class="text-muted" style="font-size:.75rem;"><?= htmlspecialchars($rj['company']) ?></div>
                                </div>
                                <span class="badge flex-shrink-0" style="<?= $rj['status'] === 'active' ? 'background:#d1fae5;color:#065f46;' : 'background:#f1f5f9;color:#475569;' ?>font-size:.68rem;font-weight:700;">
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
</div>

<?php include '../includes/footer.php'; ?>
