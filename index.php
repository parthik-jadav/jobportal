<?php
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/auth.php';

$page_title = 'Find your next career opportunity';

// Live statistics
$activeJobsCount = (int)$pdo->query("SELECT COUNT(*) FROM jobs WHERE status = 'active'")->fetchColumn();
$employersCount = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'employer'")->fetchColumn();
$studentsCount = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();

// Fetch featured & recent jobs
$featuredJobs = $pdo->query("
    SELECT * FROM jobs 
    WHERE status = 'active' 
    ORDER BY is_featured DESC, created_at DESC 
    LIMIT 4
")->fetchAll();

include 'includes/header.php';
?>
<!-- Hero Section -->
<section class="hero text-center py-5 position-relative">
    <div class="container py-4">
        <span class="badge bg-light text-primary px-3 py-2 fw-semibold rounded-pill mb-3">
            <i class="bi bi-rocket-takeoff-fill me-1"></i>India's Next-Gen Hiring & Internship Portal
        </span>
        <h1 class="display-4 fw-bold mb-3 text-white">Your Career Journey Starts Here</h1>
        <p class="lead col-lg-7 mx-auto mb-4 text-white opacity-90">
            Connect with top technology companies, discover paid internships, and launch your professional career today.
        </p>

        <!-- Quick Search Bar in Hero -->
        <div class="row justify-content-center mb-4">
            <div class="col-lg-8">
                <form action="student/jobs.php" method="get" class="card p-2 p-md-3 shadow border-0 bg-white">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-5">
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-0"><i class="bi bi-search text-muted"></i></span>
                                <input type="text" name="q" class="form-control border-0" placeholder="Job title, skills, or company...">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select name="type" class="form-select border-0">
                                <option value="All">All Job Types</option>
                                <option value="Internship">Internship</option>
                                <option value="Full-time">Full-time</option>
                                <option value="Remote">Remote</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100 fw-semibold py-2">
                                Search Jobs
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <?php if (!is_logged_in()): ?>
                <a class="btn btn-light btn-lg px-4 fw-bold text-primary shadow-sm" href="register.php">
                    <i class="bi bi-mortarboard me-1"></i>Join as Student
                </a>
                <a class="btn btn-outline-light btn-lg px-4 fw-bold" href="register.php">
                    <i class="bi bi-building me-1"></i>Post a Job (Employer)
                </a>
            <?php else: ?>
                <?php 
                $dashLink = match($_SESSION['user']['role']) {
                    'admin' => 'admin/dashboard.php',
                    'employer' => 'employer/dashboard.php',
                    default => 'student/dashboard.php'
                };
                ?>
                <a class="btn btn-warning btn-lg px-4 fw-bold text-dark shadow-sm" href="<?= $dashLink ?>">
                    <i class="bi bi-speedometer2 me-1"></i>Go to My Dashboard
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Stats Bar -->
<section class="bg-white py-4 border-bottom shadow-sm">
    <div class="container">
        <div class="row text-center g-3">
            <div class="col-md-4">
                <div class="h2 fw-bold text-primary mb-0"><?= number_format($activeJobsCount) ?>+</div>
                <div class="text-muted small">Active Openings</div>
            </div>
            <div class="col-md-4">
                <div class="h2 fw-bold text-success mb-0"><?= number_format($employersCount) ?>+</div>
                <div class="text-muted small">Hiring Companies</div>
            </div>
            <div class="col-md-4">
                <div class="h2 fw-bold text-info mb-0"><?= number_format($studentsCount) ?>+</div>
                <div class="text-muted small">Registered Students</div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Jobs -->
<section class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 class="h3 fw-bold mb-1">Featured Opportunities</h2>
            <p class="text-muted mb-0">Hand-picked internships and roles from verified partner employers.</p>
        </div>
        <a href="student/jobs.php" class="btn btn-outline-primary">
            Explore All Openings <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>

    <div class="row g-4">
        <?php foreach ($featuredJobs as $job): ?>
            <div class="col-md-6">
                <div class="card job-card h-100 p-4 shadow-sm border-0 position-relative <?= !empty($job['is_featured']) ? 'border-top border-4 border-warning' : '' ?>">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h4 class="h5 fw-bold mb-0 text-dark"><?= htmlspecialchars($job['title']) ?></h4>
                        <span class="badge bg-primary-subtle text-primary"><?= htmlspecialchars($job['job_type']) ?></span>
                    </div>

                    <div class="text-muted small mb-3">
                        <i class="bi bi-building me-1"></i><b><?= htmlspecialchars($job['company']) ?></b> &bull; 
                        <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($job['location']) ?> &bull;
                        <span class="text-success fw-semibold"><?= htmlspecialchars($job['salary']) ?></span>
                    </div>

                    <p class="text-muted small mb-3 flex-grow-1" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                        <?= htmlspecialchars($job['description']) ?>
                    </p>

                    <div class="d-flex justify-content-between align-items-center pt-3 border-top mt-auto">
                        <span class="badge bg-light text-dark border"><?= htmlspecialchars($job['category'] ?? 'General') ?></span>
                        <a href="student/jobs.php?q=<?= urlencode($job['title']) ?>" class="btn btn-primary btn-sm px-3">
                            <i class="bi bi-send me-1"></i>View & Apply
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Two-Sided Platform Value -->
<section class="bg-light py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="h3 fw-bold mb-2">Designed for Both Sides of the Hiring Table</h2>
            <p class="text-muted">Whether looking to start your career or scale your technical team, JobPortal has you covered.</p>
        </div>

        <div class="row g-4">
            <div class="col-md-6">
                <div class="card p-4 p-md-5 h-100 shadow-sm border-0 border-start border-4 border-primary">
                    <div class="text-primary fs-1 mb-3"><i class="bi bi-mortarboard-fill"></i></div>
                    <h3 class="h4 fw-bold mb-2">For Students & Graduates</h3>
                    <p class="text-muted mb-4">Build your profile once, upload your resume, and apply to thousands of verified internships with one click. Track application progress in real-time.</p>
                    <ul class="list-unstyled mb-4 small text-muted d-flex flex-column gap-2">
                        <li><i class="bi bi-check2 text-success me-2"></i>One-click instant applications with profile resume</li>
                        <li><i class="bi bi-check2 text-success me-2"></i>Live application status tracking (Shortlisted, Hired)</li>
                        <li><i class="bi bi-check2 text-success me-2"></i>Save favorite openings to apply later</li>
                    </ul>
                    <div class="mt-auto">
                        <a href="register.php" class="btn btn-outline-primary">Get Started as Student</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card p-4 p-md-5 h-100 shadow-sm border-0 border-start border-4 border-success">
                    <div class="text-success fs-1 mb-3"><i class="bi bi-building-fill"></i></div>
                    <h3 class="h4 fw-bold mb-2">For Employers & Recruiters</h3>
                    <p class="text-muted mb-4">Post job and internship openings in minutes. Review candidate profiles, download resumes directly, and communicate hiring decisions seamlessly.</p>
                    <ul class="list-unstyled mb-4 small text-muted d-flex flex-column gap-2">
                        <li><i class="bi bi-check2 text-success me-2"></i>Dedicated recruiter pipeline dashboard</li>
                        <li><i class="bi bi-check2 text-success me-2"></i>Filter candidate resumes & cover letters</li>
                        <li><i class="bi bi-check2 text-success me-2"></i>Instant in-app candidate notifications</li>
                    </ul>
                    <div class="mt-auto">
                        <a href="register.php" class="btn btn-outline-success">Post Jobs as Employer</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php include 'includes/footer.php'; ?>
