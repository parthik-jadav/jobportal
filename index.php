<?php
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/auth.php';

$page_title = 'Find your next career opportunity';

// Live statistics
$activeJobsCount = (int)$pdo->query("SELECT COUNT(*) FROM jobs WHERE status = 'active'")->fetchColumn();
$employersCount  = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'employer'")->fetchColumn();
$studentsCount   = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();

// Featured & recent jobs
$featuredJobs = $pdo->query("
    SELECT * FROM jobs 
    WHERE status = 'active' 
    ORDER BY is_featured DESC, created_at DESC 
    LIMIT 4
")->fetchAll();

include 'includes/header.php';
?>

<!-- ░░ HERO ░░ -->
<section class="hero">
    <div class="hero-glow"></div>
    <div class="container position-relative">
        <div class="text-center mb-5">
            <span class="filter-pill mb-3 d-inline-flex" style="background:rgba(255,255,255,.12);border-color:rgba(255,255,255,.25);">
                <i class="bi bi-rocket-takeoff-fill"></i> India's Next-Gen Hiring & Internship Portal
            </span>
            <h1 class="display-4 fw-800 text-white mb-3 fade-up" style="letter-spacing:-0.02em;">
                Your Career Journey<br class="d-none d-md-block"> Starts Here
            </h1>
            <p class="lead mb-5 text-white opacity-75 col-lg-6 mx-auto fade-up delay-1">
                Connect with top technology companies, discover paid internships, and launch your professional career today.
            </p>

            <!-- Floating Search Bar -->
            <div class="row justify-content-center mb-4 fade-up delay-2">
                <div class="col-lg-9 col-xl-8">
                    <form action="student/jobs.php" method="get" class="search-card">
                        <div class="row g-2 align-items-center">
                            <div class="col-lg-5">
                                <div class="input-icon-wrap">
                                    <i class="bi bi-search input-icon"></i>
                                    <input type="text" name="q" class="form-control ps-5 border-0" placeholder="Job title, skills, or company...">
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <select name="type" class="form-select border-0">
                                    <option value="All">All Job Types</option>
                                    <option value="Internship">Internship</option>
                                    <option value="Full-time">Full-time</option>
                                    <option value="Part-time">Part-time</option>
                                    <option value="Remote">Remote</option>
                                </select>
                            </div>
                            <div class="col-lg-3">
                                <button type="submit" class="btn-search btn fw-700">
                                    <i class="bi bi-search me-1"></i> Search Jobs
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Quick Filter Pills -->
            <div class="filter-pills justify-content-center fade-up delay-3">
                <span class="text-white opacity-50 small fw-semibold me-1 d-flex align-items-center">Popular:</span>
                <a href="student/jobs.php?type=Internship" class="filter-pill"><i class="bi bi-laptop"></i> Internships</a>
                <a href="student/jobs.php?type=Remote" class="filter-pill"><i class="bi bi-house-door"></i> Remote</a>
                <a href="student/jobs.php?category=Technology" class="filter-pill"><i class="bi bi-code-slash"></i> Technology</a>
                <a href="student/jobs.php?type=Full-time" class="filter-pill"><i class="bi bi-briefcase"></i> Full-time</a>
            </div>

            <!-- CTA Buttons -->
            <div class="d-flex justify-content-center gap-3 flex-wrap mt-4 fade-up">
                <?php if (!is_logged_in()): ?>
                    <a class="btn btn-light btn-lg px-4 fw-bold text-primary shadow-brand" href="register.php">
                        <i class="bi bi-mortarboard me-2"></i>Join as Student
                    </a>
                    <a class="btn btn-nav-outline btn-lg px-4" href="register.php" style="font-size:1rem;padding:.7rem 1.5rem !important;">
                        <i class="bi bi-building me-2"></i>Post a Job
                    </a>
                <?php else: ?>
                    <?php
                    $dashLink = match($_SESSION['user']['role']) {
                        'admin'    => 'admin/dashboard.php',
                        'employer' => 'employer/dashboard.php',
                        default    => 'student/dashboard.php'
                    };
                    ?>
                    <a class="btn btn-light btn-lg px-5 fw-bold text-primary shadow-brand" href="<?= $dashLink ?>">
                        <i class="bi bi-speedometer2 me-2"></i>Go to My Dashboard
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- ░░ STATS BAR ░░ -->
<section class="stats-bar py-4">
    <div class="container">
        <div class="row g-3 text-center justify-content-center">
            <div class="col-4 col-md-3">
                <div class="stat-chip">
                    <div class="stat-number h2 fw-bold"><?= number_format($activeJobsCount) ?>+</div>
                    <div class="stat-label">Active Openings</div>
                </div>
            </div>
            <div class="col-4 col-md-3">
                <div class="stat-chip">
                    <div class="stat-number h2 fw-bold" style="color:#10b981;"><?= number_format($employersCount) ?>+</div>
                    <div class="stat-label">Partner Companies</div>
                </div>
            </div>
            <div class="col-4 col-md-3">
                <div class="stat-chip">
                    <div class="stat-number h2 fw-bold" style="color:#7c3aed;"><?= number_format($studentsCount) ?>+</div>
                    <div class="stat-label">Registered Talent</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ░░ FEATURED JOBS ░░ -->
<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
            <div>
                <span class="section-label"><i class="bi bi-star-fill"></i> Featured</span>
                <h2 class="section-title mb-1">Top Opportunities Right Now</h2>
                <p class="section-sub">Hand-picked internships & roles from verified partner employers.</p>
            </div>
            <a href="student/jobs.php" class="btn btn-outline-primary fw-semibold">
                Explore All <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4">
            <?php foreach ($featuredJobs as $job):
                $initials = avatarInitials($job['company']);
                $avClass  = avatarColorClass($job['company']);
                $typeBadgeClass = match(strtolower($job['job_type'])) {
                    'internship' => 'badge-internship',
                    'full-time'  => 'badge-fulltime',
                    'part-time'  => 'badge-parttime',
                    'remote'     => 'badge-remote',
                    default      => 'badge-internship'
                };
            ?>
            <div class="col-md-6">
                <div class="card job-card h-100 p-4 <?= !empty($job['is_featured']) ? 'featured-card' : '' ?> position-relative">
                    <?php if (!empty($job['is_featured'])): ?>
                        <span class="badge position-absolute top-0 end-0 m-3" style="background:#fef3c7;color:#92400e;font-size:.7rem;font-weight:700;">
                            <i class="bi bi-star-fill me-1"></i>Featured
                        </span>
                    <?php endif; ?>

                    <div class="d-flex align-items-start gap-3 mb-3 pe-5">
                        <div class="company-avatar <?= $avClass ?>"><?= htmlspecialchars($initials) ?></div>
                        <div class="flex-grow-1">
                            <h4 class="h5 fw-bold mb-1 text-dark"><?= htmlspecialchars($job['title']) ?></h4>
                            <div class="text-muted small">
                                <i class="bi bi-building me-1"></i><strong><?= htmlspecialchars($job['company']) ?></strong>
                                &bull; <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($job['location']) ?>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="badge job-type-badge <?= $typeBadgeClass ?>"><?= htmlspecialchars($job['job_type']) ?></span>
                        <?php if (!empty($job['category'])): ?>
                            <span class="badge" style="background:var(--slate-100);color:var(--slate-700);font-weight:600;font-size:.7rem;"><?= htmlspecialchars($job['category']) ?></span>
                        <?php endif; ?>
                        <span class="badge" style="background:#d1fae5;color:#065f46;font-size:.7rem;font-weight:600;"><i class="bi bi-currency-rupee"></i><?= htmlspecialchars($job['salary']) ?></span>
                    </div>

                    <p class="text-muted small mb-3" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;line-height:1.55;">
                        <?= htmlspecialchars($job['description']) ?>
                    </p>

                    <div class="d-flex justify-content-between align-items-center pt-3 border-top mt-auto">
                        <span class="text-muted" style="font-size:.78rem;"><i class="bi bi-clock me-1"></i><?= date('M d', strtotime($job['created_at'])) ?></span>
                        <a href="student/jobs.php?q=<?= urlencode($job['title']) ?>" class="btn btn-primary btn-sm px-3 fw-semibold">
                            <i class="bi bi-send me-1"></i>View & Apply
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <?php if (empty($featuredJobs)): ?>
            <div class="col-12">
                <div class="empty-state">
                    <div class="empty-icon"><i class="bi bi-briefcase"></i></div>
                    <h5>Jobs Coming Soon</h5>
                    <p>Employers are setting up their listings. Check back soon!</p>
                    <a href="register.php" class="btn btn-primary px-4">Register as Employer</a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ░░ VALUE PROPOSITION ░░ -->
<section class="py-5" style="background:var(--slate-50);">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-label"><i class="bi bi-people-fill"></i> Platform</span>
            <h2 class="section-title mb-2">Designed for Both Sides of Hiring</h2>
            <p class="section-sub">Whether launching your career or scaling your tech team, JobPortal has you covered.</p>
        </div>

        <div class="row g-4">
            <!-- Students -->
            <div class="col-md-6">
                <div class="value-card">
                    <div class="value-icon-wrap" style="background:#e0e7ff;">
                        <i class="bi bi-mortarboard-fill" style="color:#4f46e5;"></i>
                    </div>
                    <h3 class="h4 fw-bold mb-2">For Students & Graduates</h3>
                    <p class="text-muted mb-4" style="font-size:.9rem;">Build your profile once, upload your resume, and apply to thousands of verified internships with one click. Track application progress in real-time.</p>
                    <ul class="list-unstyled mb-4 d-flex flex-column gap-2" style="font-size:.875rem;">
                        <li class="d-flex align-items-center gap-2">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width:20px;height:20px;background:#d1fae5;color:#059669;font-size:.7rem;flex-shrink:0;"><i class="bi bi-check2-all"></i></span>
                            One-click applications with profile resume
                        </li>
                        <li class="d-flex align-items-center gap-2">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width:20px;height:20px;background:#d1fae5;color:#059669;font-size:.7rem;flex-shrink:0;"><i class="bi bi-check2-all"></i></span>
                            Live application status tracking (Shortlisted, Hired)
                        </li>
                        <li class="d-flex align-items-center gap-2">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width:20px;height:20px;background:#d1fae5;color:#059669;font-size:.7rem;flex-shrink:0;"><i class="bi bi-check2-all"></i></span>
                            Save favorite openings to apply later
                        </li>
                    </ul>
                    <a href="register.php" class="btn btn-outline-primary fw-semibold px-4">
                        <i class="bi bi-mortarboard me-1"></i>Get Started as Student
                    </a>
                </div>
            </div>

            <!-- Employers -->
            <div class="col-md-6">
                <div class="value-card">
                    <div class="value-icon-wrap" style="background:#d1fae5;">
                        <i class="bi bi-building-fill" style="color:#059669;"></i>
                    </div>
                    <h3 class="h4 fw-bold mb-2">For Employers & Recruiters</h3>
                    <p class="text-muted mb-4" style="font-size:.9rem;">Post job openings in minutes. Review candidate profiles, download resumes directly, and communicate hiring decisions seamlessly.</p>
                    <ul class="list-unstyled mb-4 d-flex flex-column gap-2" style="font-size:.875rem;">
                        <li class="d-flex align-items-center gap-2">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width:20px;height:20px;background:#d1fae5;color:#059669;font-size:.7rem;flex-shrink:0;"><i class="bi bi-check2-all"></i></span>
                            Dedicated recruiter pipeline dashboard
                        </li>
                        <li class="d-flex align-items-center gap-2">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width:20px;height:20px;background:#d1fae5;color:#059669;font-size:.7rem;flex-shrink:0;"><i class="bi bi-check2-all"></i></span>
                            Filter candidate resumes & cover letters
                        </li>
                        <li class="d-flex align-items-center gap-2">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width:20px;height:20px;background:#d1fae5;color:#059669;font-size:.7rem;flex-shrink:0;"><i class="bi bi-check2-all"></i></span>
                            Instant in-app candidate notifications
                        </li>
                    </ul>
                    <a href="register.php" class="btn btn-outline-success fw-semibold px-4">
                        <i class="bi bi-building me-1"></i>Post Jobs as Employer
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ░░ CTA BOTTOM ░░ -->
<section class="py-5" style="background:linear-gradient(135deg,#312e81 0%,#4f46e5 100%);">
    <div class="container text-center text-white">
        <h2 class="fw-800 mb-3 display-6">Ready to find your dream opportunity?</h2>
        <p class="opacity-75 mb-4 lead">Join thousands of students who've already kickstarted their careers through JobPortal.</p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="register.php" class="btn btn-light btn-lg px-5 fw-bold text-primary shadow-brand">
                <i class="bi bi-rocket-takeoff me-2"></i>Get Started Free
            </a>
            <a href="student/jobs.php" class="btn btn-outline-light btn-lg px-5 fw-semibold">
                Browse Jobs <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
