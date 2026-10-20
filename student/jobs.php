<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
require_student();

$base_path = '../';
$page_title = 'Find Jobs & Internships';
$userId = (int)$_SESSION['user']['id'];

$userStmt = $pdo->prepare('SELECT resume_path FROM users WHERE id = ?');
$userStmt->execute([$userId]);
$currentUserProfile = $userStmt->fetch();
$hasProfileResume = !empty($currentUserProfile['resume_path']);

$appliedStmt = $pdo->prepare('SELECT job_id, status FROM applications WHERE user_id = ?');
$appliedStmt->execute([$userId]);
$appliedJobs = $appliedStmt->fetchAll(PDO::FETCH_KEY_PAIR);

$savedStmt = $pdo->prepare('SELECT job_id FROM saved_jobs WHERE user_id = ?');
$savedStmt->execute([$userId]);
$savedJobIds = $savedStmt->fetchAll(PDO::FETCH_COLUMN);

$q          = trim($_GET['q'] ?? '');
$type       = trim($_GET['type'] ?? '');
$category   = trim($_GET['category'] ?? '');
$experience = trim($_GET['experience'] ?? '');
$sort       = trim($_GET['sort'] ?? 'newest');

$sql    = "SELECT * FROM jobs WHERE status = 'active'";
$params = [];

if ($q !== '') {
    $sql .= " AND (title LIKE ? OR company LIKE ? OR location LIKE ? OR description LIKE ? OR requirements LIKE ?)";
    $term = "%{$q}%";
    $params = array_merge($params, [$term, $term, $term, $term, $term]);
}
if ($type !== '' && $type !== 'All') { $sql .= " AND job_type = ?"; $params[] = $type; }
if ($category !== '' && $category !== 'All') { $sql .= " AND category = ?"; $params[] = $category; }
if ($experience !== '' && $experience !== 'All') { $sql .= " AND experience_level = ?"; $params[] = $experience; }

$sql .= match($sort) {
    'salary_desc' => " ORDER BY is_featured DESC, salary DESC, created_at DESC",
    'deadline'    => " ORDER BY is_featured DESC, deadline ASC, created_at DESC",
    default       => " ORDER BY is_featured DESC, created_at DESC"
};

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$jobs = $stmt->fetchAll();

$catList = $pdo->query("SELECT DISTINCT category FROM jobs WHERE category IS NOT NULL AND category != ''")->fetchAll(PDO::FETCH_COLUMN);

include '../includes/header.php';
?>

<div class="container py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h2 fw-bold mb-1">Explore Opportunities</h1>
            <p class="text-muted mb-0">Browse verified internships and jobs from top companies.</p>
        </div>
        <a href="applications.php" class="btn btn-outline-primary fw-semibold">
            <i class="bi bi-send-check me-1"></i>My Applications (<?= count($appliedJobs) ?>)
        </a>
    </div>

    <!-- ░░ Filter Bar ░░ -->
    <div class="card border-0 mb-4" style="box-shadow:var(--shadow-md);">
        <div class="card-body p-4">
            <form method="get" action="jobs.php" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold" style="font-size:.82rem;color:var(--slate-700);text-transform:uppercase;letter-spacing:.04em;">Keywords</label>
                    <div class="input-icon-wrap">
                        <i class="bi bi-search input-icon"></i>
                        <input class="form-control" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Title, company, skills...">
                    </div>
                </div>
                <div class="col-md-2 col-sm-6">
                    <label class="form-label fw-semibold" style="font-size:.82rem;color:var(--slate-700);text-transform:uppercase;letter-spacing:.04em;">Job Type</label>
                    <select class="form-select" name="type">
                        <option value="All">All Types</option>
                        <?php foreach (['Internship', 'Full-time', 'Part-time', 'Remote'] as $t): ?>
                            <option value="<?= $t ?>" <?= $type === $t ? 'selected' : '' ?>><?= $t ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 col-sm-6">
                    <label class="form-label fw-semibold" style="font-size:.82rem;color:var(--slate-700);text-transform:uppercase;letter-spacing:.04em;">Category</label>
                    <select class="form-select" name="category">
                        <option value="All">All Categories</option>
                        <?php foreach ($catList as $c): ?>
                            <option value="<?= htmlspecialchars($c) ?>" <?= $category === $c ? 'selected' : '' ?>><?= htmlspecialchars($c) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 col-sm-6">
                    <label class="form-label fw-semibold" style="font-size:.82rem;color:var(--slate-700);text-transform:uppercase;letter-spacing:.04em;">Experience</label>
                    <select class="form-select" name="experience">
                        <option value="All">All Levels</option>
                        <?php foreach (['Fresher', '1-2 years', '3-5 years', '5+ years'] as $exp): ?>
                            <option value="<?= $exp ?>" <?= $experience === $exp ? 'selected' : '' ?>><?= $exp ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 col-sm-6">
                    <label class="form-label fw-semibold" style="font-size:.82rem;color:var(--slate-700);text-transform:uppercase;letter-spacing:.04em;">Sort By</label>
                    <select class="form-select" name="sort">
                        <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest First</option>
                        <option value="deadline" <?= $sort === 'deadline' ? 'selected' : '' ?>>Deadline Soon</option>
                    </select>
                </div>
                <div class="col-12 d-flex justify-content-between align-items-center pt-2 border-top">
                    <div class="small text-muted">
                        <i class="bi bi-funnel me-1"></i>Found <strong><?= count($jobs) ?></strong> active opportunities
                    </div>
                    <div class="d-flex gap-2">
                        <a href="jobs.php" class="btn btn-outline-secondary btn-sm fw-semibold">
                            <i class="bi bi-x me-1"></i>Reset
                        </a>
                        <button type="submit" class="btn btn-primary btn-sm px-4 fw-semibold">
                            <i class="bi bi-search me-1"></i>Filter Jobs
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- ░░ Job Cards Grid ░░ -->
    <div class="row g-4">
        <?php if (empty($jobs)): ?>
            <div class="col-12">
                <div class="card border-0" style="box-shadow:var(--shadow-md);">
                    <div class="empty-state">
                        <div class="empty-icon"><i class="bi bi-briefcase"></i></div>
                        <h5>No matching jobs found</h5>
                        <p>Try loosening your filters or browse all available positions.</p>
                        <a href="jobs.php" class="btn btn-primary px-4">Clear Filters</a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($jobs as $job):
                $isApplied = isset($appliedJobs[$job['id']]);
                $isSaved   = in_array($job['id'], $savedJobIds);
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
                <article class="card job-card h-100 p-4 position-relative <?= !empty($job['is_featured']) ? 'featured-card' : '' ?>">
                    <?php if (!empty($job['is_featured'])): ?>
                        <span class="position-absolute top-0 end-0 m-3 badge" style="background:#fef3c7;color:#92400e;font-size:.7rem;font-weight:700;">
                            <i class="bi bi-star-fill me-1"></i>Featured
                        </span>
                    <?php endif; ?>

                    <!-- Company Avatar + Title -->
                    <div class="d-flex align-items-start gap-3 mb-3 pe-5">
                        <div class="company-avatar <?= $avClass ?>"><?= htmlspecialchars($initials) ?></div>
                        <div class="flex-grow-1">
                            <h4 class="h5 fw-bold mb-1 text-dark" style="line-height:1.2;"><?= htmlspecialchars($job['title']) ?></h4>
                            <div class="text-muted" style="font-size:.8rem;">
                                <i class="bi bi-building me-1"></i><strong><?= htmlspecialchars($job['company']) ?></strong>
                                &bull; <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($job['location']) ?>
                            </div>
                        </div>
                    </div>

                    <!-- Badges Row -->
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="badge job-type-badge <?= $typeBadgeClass ?>"><?= htmlspecialchars($job['job_type']) ?></span>
                        <?php if (!empty($job['experience_level'])): ?>
                            <span class="badge" style="background:var(--slate-100);color:var(--slate-700);font-size:.7rem;font-weight:600;"><?= htmlspecialchars($job['experience_level']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($job['salary'])): ?>
                            <span class="badge" style="background:#d1fae5;color:#065f46;font-size:.7rem;font-weight:600;"><i class="bi bi-currency-rupee"></i><?= htmlspecialchars($job['salary']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($job['category'])): ?>
                            <span class="badge" style="background:#ede9fe;color:#7c3aed;font-size:.7rem;font-weight:600;"><?= htmlspecialchars($job['category']) ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Description -->
                    <p class="text-muted mb-3" style="font-size:.85rem;line-height:1.55;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                        <?= htmlspecialchars($job['description']) ?>
                    </p>

                    <!-- Requirements & Deadline -->
                    <div class="rounded-xl p-3 mb-3" style="background:var(--slate-50);border:1px solid var(--slate-100);">
                        <div class="fw-semibold mb-1" style="font-size:.75rem;color:var(--slate-600);text-transform:uppercase;letter-spacing:.04em;">Requirements</div>
                        <div style="font-size:.82rem;color:var(--slate-700);"><?= htmlspecialchars($job['requirements']) ?></div>
                        <?php if (!empty($job['deadline'])): ?>
                            <div class="deadline-warn mt-1">
                                <i class="bi bi-clock me-1"></i>Apply before <?= date('M d, Y', strtotime($job['deadline'])) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex justify-content-between align-items-center pt-3 border-top mt-auto">
                        <?php if ($isSaved): ?>
                            <form action="../actions/remove-job.php" method="post" class="d-inline">
                                <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
                                <button class="btn btn-sm btn-saved table-btn">
                                    <i class="bi bi-bookmark-check-fill me-1"></i>Saved
                                </button>
                            </form>
                        <?php else: ?>
                            <form action="../actions/save-job.php" method="post" class="d-inline">
                                <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
                                <button class="btn btn-sm btn-outline-secondary table-btn">
                                    <i class="bi bi-bookmark me-1"></i>Save
                                </button>
                            </form>
                        <?php endif; ?>

                        <?php if ($isApplied):
                            $rawSt = $appliedJobs[$job['id']];
                            $stLabel = strtolower($rawSt) === 'applied' ? 'Applied' : ucfirst($rawSt);
                        ?>
                            <span class="badge px-3 py-2 fw-semibold" style="background:#d1fae5;color:#065f46;font-size:.78rem;border-radius:var(--radius-pill);">
                                <i class="bi bi-check-circle-fill me-1"></i><?= $stLabel ?>
                            </span>
                        <?php else: ?>
                            <button type="button" class="btn btn-primary btn-sm px-3 fw-semibold" style="border-radius:var(--radius-pill);" data-bs-toggle="modal" data-bs-target="#applyModal<?= $job['id'] ?>">
                                <i class="bi bi-send me-1"></i>Apply Now
                            </button>
                        <?php endif; ?>
                    </div>
                </article>
            </div>

            <!-- Apply Modal -->
            <?php if (!$isApplied): ?>
            <div class="modal fade" id="applyModal<?= $job['id'] ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0" style="border-radius:var(--radius-xl);box-shadow:var(--shadow-xl);">
                        <form action="../actions/apply-job.php" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
                            <div class="modal-header border-0 pb-0">
                                <div>
                                    <h5 class="modal-title fw-bold mb-0"><?= htmlspecialchars($job['title']) ?></h5>
                                    <small class="text-muted"><?= htmlspecialchars($job['company']) ?> &bull; <?= htmlspecialchars($job['location']) ?></small>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body pt-3">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold" style="font-size:.85rem;">Resume <span class="text-danger">*</span></label>
                                    <?php if ($hasProfileResume): ?>
                                        <div class="p-3 rounded-xl mb-2" style="background:var(--slate-50);border:1px solid var(--slate-200);">
                                            <div class="form-check mb-0">
                                                <input class="form-check-input" type="radio" name="resume_choice" id="useProfileResume<?= $job['id'] ?>" value="profile" checked onchange="toggleCustomResume(<?= $job['id'] ?>, false)">
                                                <label class="form-check-label fw-semibold" for="useProfileResume<?= $job['id'] ?>">
                                                    <i class="bi bi-file-earmark-pdf-fill text-danger me-1"></i>Use Profile Resume
                                                    <small class="text-muted d-block fw-normal"><?= basename($currentUserProfile['resume_path']) ?></small>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="p-3 rounded-xl" style="background:var(--slate-50);border:1px solid var(--slate-200);">
                                            <div class="form-check mb-0">
                                                <input class="form-check-input" type="radio" name="resume_choice" id="uploadNewResume<?= $job['id'] ?>" value="custom" onchange="toggleCustomResume(<?= $job['id'] ?>, true)">
                                                <label class="form-check-label fw-semibold" for="uploadNewResume<?= $job['id'] ?>">
                                                    Upload different resume for this application
                                                </label>
                                            </div>
                                        </div>
                                        <div id="customResumeInput<?= $job['id'] ?>" class="mt-2 d-none">
                                            <input class="form-control" type="file" name="custom_resume" accept=".pdf,.doc,.docx">
                                            <small class="text-muted">PDF or DOCX (Max 5MB)</small>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-warning py-2 small mb-2">
                                            <i class="bi bi-exclamation-triangle-fill me-1"></i>No profile resume found. Please upload one below.
                                        </div>
                                        <input class="form-control" type="file" name="custom_resume" accept=".pdf,.doc,.docx" required>
                                    <?php endif; ?>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label fw-semibold" style="font-size:.85rem;">Cover Note <span class="text-muted fw-normal">(Optional)</span></label>
                                    <textarea class="form-control" name="cover_letter" rows="3" placeholder="Explain why you're an ideal fit for this role..."></textarea>
                                </div>
                            </div>
                            <div class="modal-footer border-0 pt-0">
                                <button type="button" class="btn btn-outline-secondary btn-sm fw-semibold" style="border-radius:var(--radius-pill);" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary btn-sm px-4 fw-semibold" style="border-radius:var(--radius-pill);">
                                    <i class="bi bi-send me-1"></i>Submit Application
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div><!-- /row -->
</div><!-- /container -->

<script>
function toggleCustomResume(jobId, show) {
    const el = document.getElementById('customResumeInput' + jobId);
    if (el) { el.classList.toggle('d-none', !show); }
}
</script>

<?php include '../includes/footer.php'; ?>
