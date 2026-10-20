<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
require_student();

$base_path = '../';
$page_title = 'Find Jobs & Internships';

$userId = (int)$_SESSION['user']['id'];

// Get user profile details (to know if resume is present)
$userStmt = $pdo->prepare('SELECT resume_path FROM users WHERE id = ?');
$userStmt->execute([$userId]);
$currentUserProfile = $userStmt->fetch();
$hasProfileResume = !empty($currentUserProfile['resume_path']);

// Get list of job IDs already applied by this user
$appliedStmt = $pdo->prepare('SELECT job_id, status FROM applications WHERE user_id = ?');
$appliedStmt->execute([$userId]);
$appliedJobs = $appliedStmt->fetchAll(PDO::FETCH_KEY_PAIR); // [job_id => status]

// Get list of job IDs saved by this user
$savedStmt = $pdo->prepare('SELECT job_id FROM saved_jobs WHERE user_id = ?');
$savedStmt->execute([$userId]);
$savedJobIds = $savedStmt->fetchAll(PDO::FETCH_COLUMN);

// Filter inputs
$q = trim($_GET['q'] ?? '');
$type = trim($_GET['type'] ?? '');
$category = trim($_GET['category'] ?? '');
$experience = trim($_GET['experience'] ?? '');
$sort = trim($_GET['sort'] ?? 'newest');

// Build query
$sql = "SELECT * FROM jobs WHERE status = 'active'";
$params = [];

if ($q !== '') {
    $sql .= " AND (title LIKE ? OR company LIKE ? OR location LIKE ? OR description LIKE ? OR requirements LIKE ?)";
    $term = "%{$q}%";
    $params = array_merge($params, [$term, $term, $term, $term, $term]);
}

if ($type !== '' && $type !== 'All') {
    $sql .= " AND job_type = ?";
    $params[] = $type;
}

if ($category !== '' && $category !== 'All') {
    $sql .= " AND category = ?";
    $params[] = $category;
}

if ($experience !== '' && $experience !== 'All') {
    $sql .= " AND experience_level = ?";
    $params[] = $experience;
}

// Sorting
if ($sort === 'salary_desc') {
    $sql .= " ORDER BY is_featured DESC, salary DESC, created_at DESC";
} elseif ($sort === 'deadline') {
    $sql .= " ORDER BY is_featured DESC, deadline ASC, created_at DESC";
} else {
    $sql .= " ORDER BY is_featured DESC, created_at DESC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$jobs = $stmt->fetchAll();

// Distinct categories for filter dropdown
$catList = $pdo->query("SELECT DISTINCT category FROM jobs WHERE category IS NOT NULL AND category != ''")->fetchAll(PDO::FETCH_COLUMN);

include '../includes/header.php';
?>
<section class="container py-5">
    <!-- Header banner -->
    <div class="row align-items-center mb-4">
        <div class="col-md-8">
            <h1 class="h2 fw-bold mb-1">Explore Career Opportunities</h1>
            <p class="text-muted mb-0">Browse and filter verified internships and jobs from top companies.</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <a href="applications.php" class="btn btn-outline-primary">
                <i class="bi bi-send-check me-1"></i>Track My Applications (<?= count($appliedJobs) ?>)
            </a>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="card p-4 shadow-sm mb-4 border-0">
        <form method="get" action="jobs.php" class="row g-3">
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Keywords</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                    <input class="form-control" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Job title, company, skills...">
                </div>
            </div>

            <div class="col-md-2 col-sm-6">
                <label class="form-label small fw-semibold">Job Type</label>
                <select class="form-select" name="type">
                    <option value="All">All Types</option>
                    <?php foreach (['Internship', 'Full-time', 'Part-time', 'Remote'] as $t): ?>
                        <option value="<?= $t ?>" <?= $type === $t ? 'selected' : '' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2 col-sm-6">
                <label class="form-label small fw-semibold">Category</label>
                <select class="form-select" name="category">
                    <option value="All">All Categories</option>
                    <?php foreach ($catList as $c): ?>
                        <option value="<?= htmlspecialchars($c) ?>" <?= $category === $c ? 'selected' : '' ?>><?= htmlspecialchars($c) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2 col-sm-6">
                <label class="form-label small fw-semibold">Experience</label>
                <select class="form-select" name="experience">
                    <option value="All">All Levels</option>
                    <?php foreach (['Fresher', '1-2 years', '3-5 years', '5+ years'] as $exp): ?>
                        <option value="<?= $exp ?>" <?= $experience === $exp ? 'selected' : '' ?>><?= $exp ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2 col-sm-6">
                <label class="form-label small fw-semibold">Sort By</label>
                <select class="form-select" name="sort">
                    <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest First</option>
                    <option value="deadline" <?= $sort === 'deadline' ? 'selected' : '' ?>>Deadline Approaching</option>
                </select>
            </div>

            <div class="col-12 d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                <div class="small text-muted">
                    Found <b><?= count($jobs) ?></b> active opportunities
                </div>
                <div class="d-flex gap-2">
                    <a href="jobs.php" class="btn btn-outline-secondary btn-sm">Reset</a>
                    <button type="submit" class="btn btn-primary btn-sm px-4">Filter Jobs</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Job Cards Grid -->
    <div class="row g-4">
        <?php if (empty($jobs)): ?>
            <div class="col-12">
                <div class="card p-5 text-center shadow-sm">
                    <i class="bi bi-briefcase fs-1 text-muted mb-3"></i>
                    <h5 class="fw-bold">No matching jobs found</h5>
                    <p class="text-muted mb-3">Try loosening your search filters or browse all available positions.</p>
                    <div><a href="jobs.php" class="btn btn-outline-primary btn-sm">Clear Filters</a></div>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($jobs as $job): 
                $isApplied = isset($appliedJobs[$job['id']]);
                $isSaved = in_array($job['id'], $savedJobIds);
            ?>
                <div class="col-md-6">
                    <article class="card job-card h-100 p-4 shadow-sm border-0 position-relative <?= !empty($job['is_featured']) ? 'border-top border-4 border-warning' : '' ?>">
                        <?php if (!empty($job['is_featured'])): ?>
                            <span class="badge bg-warning text-dark position-absolute top-0 end-0 m-3 px-2 py-1 small fw-semibold">
                                <i class="bi bi-star-fill me-1"></i>Featured
                            </span>
                        <?php endif; ?>

                        <div class="d-flex justify-content-between align-items-start mb-2 pe-5">
                            <h4 class="h5 fw-bold mb-0 text-dark"><?= htmlspecialchars($job['title']) ?></h4>
                        </div>

                        <div class="job-meta mb-3 text-muted small">
                            <i class="bi bi-building me-1"></i><b><?= htmlspecialchars($job['company']) ?></b> &bull; 
                            <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($job['location']) ?> &bull;
                            <span class="badge bg-light text-dark border"><?= htmlspecialchars($job['category'] ?? 'General') ?></span>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <span class="badge bg-primary-subtle text-primary"><?= htmlspecialchars($job['job_type']) ?></span>
                            <span class="badge bg-secondary-subtle text-secondary"><?= htmlspecialchars($job['experience_level']) ?></span>
                            <span class="badge bg-success-subtle text-success"><?= htmlspecialchars($job['salary']) ?></span>
                        </div>

                        <p class="text-muted small mb-3 flex-grow-1" style="line-height: 1.5;">
                            <?= nl2br(htmlspecialchars($job['description'])) ?>
                        </p>

                        <div class="bg-light p-2 rounded small mb-3">
                            <b>Requirements:</b> <?= htmlspecialchars($job['requirements']) ?>
                            <?php if (!empty($job['deadline'])): ?>
                                <div class="text-danger small mt-1">
                                    <i class="bi bi-clock-history me-1"></i>Apply before: <?= date('M d, Y', strtotime($job['deadline'])) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-auto">
                            <!-- Save / Bookmark Button -->
                            <?php if ($isSaved): ?>
                                <form action="../actions/remove-job.php" method="post" class="d-inline">
                                    <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
                                    <button class="btn btn-outline-danger btn-sm" title="Remove from saved jobs">
                                        <i class="bi bi-bookmark-fill me-1"></i>Saved
                                    </button>
                                </form>
                            <?php else: ?>
                                <form action="../actions/save-job.php" method="post" class="d-inline">
                                    <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
                                    <button class="btn btn-outline-secondary btn-sm" title="Save for later">
                                        <i class="bi bi-bookmark me-1"></i>Save
                                    </button>
                                </form>
                            <?php endif; ?>

                            <!-- Apply Button / Status -->
                            <?php if ($isApplied): ?>
                                <a href="applications.php" class="btn btn-success btn-sm disabled">
                                    <i class="bi bi-check-circle-fill me-1"></i>Applied (<?= ucfirst($appliedJobs[$job['id']]) ?>)
                                </a>
                            <?php else: ?>
                                <button type="button" class="btn btn-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#applyModal<?= $job['id'] ?>">
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
                            <div class="modal-content">
                                <form action="../actions/apply-job.php" method="post" enctype="multipart/form-data">
                                    <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
                                    <div class="modal-header">
                                        <div>
                                            <h5 class="modal-title fw-bold mb-0">Apply for <?= htmlspecialchars($job['title']) ?></h5>
                                            <small class="text-muted"><?= htmlspecialchars($job['company']) ?> &bull; <?= htmlspecialchars($job['location']) ?></small>
                                        </div>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <!-- Resume Selection -->
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Resume Selection <span class="text-danger">*</span></label>
                                            <?php if ($hasProfileResume): ?>
                                                <div class="form-check p-3 border rounded mb-2 bg-light">
                                                    <input class="form-check-input" type="radio" name="resume_choice" id="useProfileResume<?= $job['id'] ?>" value="profile" checked onchange="toggleCustomResume(<?= $job['id'] ?>, false)">
                                                    <label class="form-check-label ms-1" for="useProfileResume<?= $job['id'] ?>">
                                                        <i class="bi bi-file-earmark-pdf-fill text-danger me-1"></i>
                                                        <strong>Use Profile Resume</strong> (<?= basename($currentUserProfile['resume_path']) ?>)
                                                    </label>
                                                </div>
                                                <div class="form-check p-3 border rounded">
                                                    <input class="form-check-input" type="radio" name="resume_choice" id="uploadNewResume<?= $job['id'] ?>" value="custom" onchange="toggleCustomResume(<?= $job['id'] ?>, true)">
                                                    <label class="form-check-label ms-1" for="uploadNewResume<?= $job['id'] ?>">
                                                        <strong>Upload a custom resume for this application</strong>
                                                    </label>
                                                </div>
                                                <div id="customResumeInput<?= $job['id'] ?>" class="mt-2 d-none">
                                                    <input class="form-control" type="file" name="custom_resume" accept=".pdf,.doc,.docx">
                                                    <small class="text-muted">PDF or DOCX (Max 5MB)</small>
                                                </div>
                                            <?php else: ?>
                                                <div class="alert alert-warning py-2 small mb-2">
                                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>You haven't uploaded a default resume to your profile yet.
                                                </div>
                                                <label class="form-label small">Upload Resume File (PDF / DOCX)</label>
                                                <input class="form-control" type="file" name="custom_resume" accept=".pdf,.doc,.docx" required>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Cover Letter -->
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Cover Note / Introduction (Optional)</label>
                                            <textarea class="form-control" name="cover_letter" rows="3" placeholder="Explain why you are an ideal fit for this role..."></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary btn-sm px-4">Submit Application</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<script>
function toggleCustomResume(jobId, show) {
    const el = document.getElementById('customResumeInput' + jobId);
    if (el) {
        if (show) el.classList.remove('d-none');
        else el.classList.add('d-none');
    }
}
</script>
<?php include '../includes/footer.php'; ?>
