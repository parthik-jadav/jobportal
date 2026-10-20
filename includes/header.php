<?php
$page_title = $page_title ?? 'JobPortal';
$base_path = $base_path ?? '';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$currentUser = $_SESSION['user'] ?? null;
$unreadCount = 0;
if ($currentUser && isset($pdo)) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$currentUser['id']]);
        $unreadCount = (int)$stmt->fetchColumn();
    } catch (Exception $e) {}
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($page_title) ?> | JobPortal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="<?= $base_path ?>css/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="<?= $base_path ?>index.php">
            <i class="bi bi-briefcase-fill"></i>
            <span>JobPortal</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div id="nav" class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-1">
                <?php if ($currentUser && $currentUser['role'] === 'student'): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= $base_path ?>student/dashboard.php"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $base_path ?>student/jobs.php"><i class="bi bi-search me-1"></i>Find Jobs</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $base_path ?>student/applications.php"><i class="bi bi-send-check me-1"></i>Applications</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $base_path ?>student/saved-jobs.php"><i class="bi bi-bookmark-heart me-1"></i>Saved</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $base_path ?>student/profile.php"><i class="bi bi-person me-1"></i>Profile</a></li>
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="<?= $base_path ?>student/notifications.php" title="Notifications">
                            <i class="bi bi-bell"></i>
                            <?php if ($unreadCount > 0): ?>
                                <span class="position-absolute top-1 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:0.65rem;">
                                    <?= $unreadCount ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-outline-light btn-sm px-3" href="<?= $base_path ?>student/logout.php"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
                    </li>

                <?php elseif ($currentUser && $currentUser['role'] === 'employer'): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= $base_path ?>employer/dashboard.php"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $base_path ?>employer/manage-jobs.php"><i class="bi bi-briefcase me-1"></i>My Jobs</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $base_path ?>employer/applicants.php"><i class="bi bi-people me-1"></i>Applicants</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $base_path ?>employer/profile.php"><i class="bi bi-building me-1"></i>Company</a></li>
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-warning btn-sm fw-semibold text-dark" href="<?= $base_path ?>employer/post-job.php"><i class="bi bi-plus-circle me-1"></i>Post a Job</a>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-outline-light btn-sm px-3" href="<?= $base_path ?>student/logout.php"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
                    </li>

                <?php elseif ($currentUser && $currentUser['role'] === 'admin'): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= $base_path ?>admin/dashboard.php"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $base_path ?>admin/jobs.php"><i class="bi bi-briefcase me-1"></i>Jobs</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $base_path ?>admin/users.php"><i class="bi bi-people me-1"></i>Users</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $base_path ?>admin/feedback.php"><i class="bi bi-chat-left-dots me-1"></i>Feedback</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $base_path ?>admin/reports.php"><i class="bi bi-graph-up me-1"></i>Reports</a></li>
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-outline-light btn-sm px-3" href="<?= $base_path ?>admin/logout.php"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
                    </li>

                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= $base_path ?>index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $base_path ?>features.php">Features</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $base_path ?>about.php">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $base_path ?>contact.php">Contact</a></li>
                    <li class="nav-item ms-lg-2"><a class="btn btn-outline-light btn-sm px-3" href="<?= $base_path ?>login.php">Login</a></li>
                    <li class="nav-item ms-lg-1"><a class="btn btn-light btn-sm px-3 fw-semibold text-primary" href="<?= $base_path ?>register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<main>
<?php if (!empty($_SESSION['flash'])): ?>
    <div class="container mt-3">
        <?php foreach ($_SESSION['flash'] as $type => $message): 
            $alertClass = match($type) {
                'error' => 'alert-danger',
                'success' => 'alert-success',
                'warning' => 'alert-warning',
                default => 'alert-info'
            };
        ?>
            <div class="alert <?= $alertClass ?> alert-dismissible fade show shadow-sm" role="alert">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endforeach; unset($_SESSION['flash']); ?>
    </div>
<?php endif; ?>
