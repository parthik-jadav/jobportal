<?php
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/auth.php';

$page_title = 'Features & Capabilities';
include 'includes/header.php';

$featuresList = [
    [
        'category' => 'For Students',
        'icon'     => 'bi-mortarboard-fill',
        'iconBg'   => '#e0e7ff',
        'iconColor'=> '#4f46e5',
        'items'    => [
            'Student Registration & Profile Management',
            'PDF/DOCX Resume Upload and Storage',
            'One-Click Job & Internship Applications',
            'Custom Cover Letter Attachment',
            'Live Application Status Tracker (Applied, Reviewing, Shortlisted, Hired)',
            'Opportunity Bookmarking & Saved Jobs Hub',
            'Real-Time In-App Notification Center'
        ]
    ],
    [
        'category' => 'For Employers & Recruiters',
        'icon'     => 'bi-building-fill',
        'iconBg'   => '#d1fae5',
        'iconColor'=> '#059669',
        'items'    => [
            'Dedicated Employer Portal & Company Profile',
            'Job Opening Creation (Categories, Deadlines, Salary, Experience)',
            'Listing Lifecycle Management (Active, Closed, Featured)',
            'Candidate Review Pipeline per Job Listing',
            'One-Click Resume Download & Preview',
            'Status Updates (Shortlist, Hire, Reject) with Recruiter Notes',
            'Automated Candidate Alert Notifications'
        ]
    ],
    [
        'category' => 'Platform & Administration',
        'icon'     => 'bi-shield-lock-fill',
        'iconBg'   => '#f1f5f9',
        'iconColor'=> '#475569',
        'items'    => [
            'Administrator Supervision & User Moderation',
            'Job Listing Approval & Feature Toggling',
            'Data Export to CSV (Applications, Users, Feedback)',
            'Interactive Hiring Pipeline Funnel Analytics',
            'Multi-faceted Search by Type, Category, Experience, and Keywords',
            'Inquiry & Feedback Management System'
        ]
    ]
];
?>

<!-- Hero -->
<section class="hero" style="padding:4rem 0 3rem;">
    <div class="container text-center position-relative">
        <span class="filter-pill mb-3 d-inline-flex" style="background:rgba(255,255,255,.12);border-color:rgba(255,255,255,.25);">
            <i class="bi bi-grid-fill"></i> Full Feature List
        </span>
        <h1 class="display-5 fw-800 text-white mb-3">Everything You Need to Hire or Get Hired</h1>
        <p class="lead text-white opacity-75 col-lg-6 mx-auto mb-0">
            Explore all the built-in tools connecting students with employers — and empowering administrators.
        </p>
    </div>
</section>

<div class="container py-5">
    <div class="row g-4">
        <?php foreach ($featuresList as $group): ?>
        <div class="col-lg-4">
            <div class="value-card h-100">
                <div class="value-icon-wrap mb-3" style="background:<?= $group['iconBg'] ?>;">
                    <i class="bi <?= $group['icon'] ?>" style="color:<?= $group['iconColor'] ?>;font-size:1.5rem;"></i>
                </div>
                <h4 class="h5 fw-bold mb-3 text-dark"><?= $group['category'] ?></h4>
                <ul class="list-unstyled mb-0 d-flex flex-column gap-3" style="font-size:.875rem;">
                    <?php foreach ($group['items'] as $item): ?>
                    <li class="d-flex align-items-start gap-2">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width:20px;height:20px;background:#d1fae5;color:#059669;font-size:.7rem;margin-top:2px;">
                            <i class="bi bi-check2-all"></i>
                        </span>
                        <span class="text-muted"><?= htmlspecialchars($item) ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="text-center mt-5 py-4">
        <h2 class="fw-bold mb-3">Ready to get started?</h2>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="register.php" class="btn btn-primary btn-lg px-5 fw-bold shadow-brand">
                <i class="bi bi-mortarboard me-1"></i>Join as Student
            </a>
            <a href="student/jobs.php" class="btn btn-outline-primary btn-lg px-5 fw-semibold">
                Browse Openings <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
