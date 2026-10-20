<?php
session_start();
$page_title = 'About Us';
include 'includes/header.php';
?>

<!-- Hero -->
<section class="hero" style="padding:4rem 0 3rem;">
    <div class="container text-center position-relative">
        <span class="filter-pill mb-3 d-inline-flex" style="background:rgba(255,255,255,.12);border-color:rgba(255,255,255,.25);">
            <i class="bi bi-info-circle"></i> About JobPortal
        </span>
        <h1 class="display-5 fw-800 text-white mb-3">India's Next-Gen Hiring & Internship Portal</h1>
        <p class="lead text-white opacity-75 col-lg-6 mx-auto mb-0">
            Connecting ambitious students with verified employers — built to make hiring simple, transparent, and fast.
        </p>
    </div>
</section>

<div class="container py-5">
    <div class="row g-5 align-items-center mb-5">
        <div class="col-lg-6">
            <span class="section-label"><i class="bi bi-lightbulb-fill"></i> Our Mission</span>
            <h2 class="section-title mb-3">Why We Built JobPortal</h2>
            <p class="text-muted mb-4">JobPortal was created to bridge the gap between talented students looking for their first break and top technology companies searching for fresh talent. We believe every student deserves a fair shot at a great career opportunity.</p>
            <p class="text-muted mb-4">Our platform provides a frictionless one-click application system, real-time hiring pipeline visibility, and a robust employer toolkit — all under one modern interface.</p>
            <div class="d-flex gap-3 flex-wrap">
                <a href="register.php" class="btn btn-primary fw-bold px-4">
                    <i class="bi bi-rocket-takeoff me-1"></i>Get Started Free
                </a>
                <a href="features.php" class="btn btn-outline-primary fw-semibold px-4">
                    View All Features
                </a>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="row g-3">
                <div class="col-6">
                    <div class="kpi-card kpi-indigo">
                        <div class="kpi-icon-wrap"><i class="bi bi-mortarboard-fill"></i></div>
                        <div>
                            <div class="kpi-value">500+</div>
                            <div class="kpi-label">Students Helped</div>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="kpi-card kpi-emerald">
                        <div class="kpi-icon-wrap"><i class="bi bi-building-fill"></i></div>
                        <div>
                            <div class="kpi-value">50+</div>
                            <div class="kpi-label">Partner Companies</div>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="kpi-card kpi-amber">
                        <div class="kpi-icon-wrap"><i class="bi bi-briefcase-fill"></i></div>
                        <div>
                            <div class="kpi-value">200+</div>
                            <div class="kpi-label">Jobs Posted</div>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="kpi-card kpi-sky">
                        <div class="kpi-icon-wrap"><i class="bi bi-patch-check-fill"></i></div>
                        <div>
                            <div class="kpi-value">95%</div>
                            <div class="kpi-label">Satisfaction Rate</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Values -->
    <div class="text-center mb-5">
        <span class="section-label"><i class="bi bi-stars"></i> Our Values</span>
        <h2 class="section-title mb-2">Built on Three Core Principles</h2>
        <p class="section-sub">Everything we build is guided by our commitment to these values.</p>
    </div>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="value-card text-center">
                <div class="value-icon-wrap mx-auto mb-3" style="background:#e0e7ff;"><i class="bi bi-shield-check-fill" style="color:#4f46e5;font-size:1.5rem;"></i></div>
                <h5 class="fw-bold mb-2">Transparency</h5>
                <p class="text-muted" style="font-size:.9rem;">We believe job seekers deserve full visibility into their application status — no guessing games, just real-time honest updates.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="value-card text-center">
                <div class="value-icon-wrap mx-auto mb-3" style="background:#d1fae5;"><i class="bi bi-lightning-charge-fill" style="color:#059669;font-size:1.5rem;"></i></div>
                <h5 class="fw-bold mb-2">Efficiency</h5>
                <p class="text-muted" style="font-size:.9rem;">One-click applications, instant notifications, and streamlined recruiter pipelines — designed to save time for everyone.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="value-card text-center">
                <div class="value-icon-wrap mx-auto mb-3" style="background:#fef3c7;"><i class="bi bi-people-fill" style="color:#d97706;font-size:1.5rem;"></i></div>
                <h5 class="fw-bold mb-2">Inclusivity</h5>
                <p class="text-muted" style="font-size:.9rem;">From fresher students to experienced hires — our platform is designed to give every candidate a fair and equal opportunity.</p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
