<?php
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/auth.php';

$page_title = 'Features & Capabilities';
include 'includes/header.php';

$featuresList = [
    [
        'category' => 'For Students',
        'icon' => 'bi-mortarboard-fill',
        'color' => 'primary',
        'items' => [
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
        'icon' => 'bi-building-fill',
        'color' => 'success',
        'items' => [
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
        'icon' => 'bi-shield-lock-fill',
        'color' => 'dark',
        'items' => [
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
<section class="container py-5">
    <div class="text-center mb-5">
        <h1 class="display-6 fw-bold mb-2">Platform Features & Capabilities</h1>
        <p class="text-muted col-md-7 mx-auto">Explore all the built-in tools connecting students with employers and empowering administrators.</p>
    </div>

    <div class="row g-4">
        <?php foreach ($featuresList as $group): ?>
            <div class="col-lg-4">
                <div class="card h-100 p-4 shadow-sm border-0 border-top border-4 border-<?= $group['color'] ?>">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <i class="bi <?= $group['icon'] ?> fs-2 text-<?= $group['color'] ?>"></i>
                        <h4 class="h5 fw-bold mb-0 text-dark"><?= $group['category'] ?></h4>
                    </div>
                    <ul class="list-unstyled mb-0 d-flex flex-column gap-3 small text-muted">
                        <?php foreach ($group['items'] as $item): ?>
                            <li class="d-flex align-items-start gap-2">
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                <span><?= htmlspecialchars($item) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="text-center mt-5">
        <a href="register.php" class="btn btn-primary btn-lg px-4 me-2">Join JobPortal Today</a>
        <a href="student/jobs.php" class="btn btn-outline-secondary btn-lg px-4">Browse Openings</a>
    </div>
</section>
<?php include 'includes/footer.php'; ?>
