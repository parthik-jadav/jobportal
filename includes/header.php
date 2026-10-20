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

// Helper: get company avatar initials color class
if (!function_exists('avatarColorClass')) {
    function avatarColorClass(string $name): string {
        return 'av-color-' . (abs(crc32($name)) % 8);
    }
}
if (!function_exists('avatarInitials')) {
    function avatarInitials(string $name): string {
        $words = preg_split('/\s+/', trim($name));
        if (count($words) >= 2) return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        return strtoupper(substr($name, 0, 2));
    }
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

<!-- ░░ NAVBAR ░░ -->
<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container">
        <!-- Brand -->
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= $base_path ?>index.php">
            <span class="brand-icon"><i class="bi bi-briefcase-fill"></i></span>
            <span>Job<span style="color:#a5b4fc;">Portal</span></span>
        </a>

        <!-- Hamburger -->
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMain" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div id="navMain" class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-1">

                <?php if ($currentUser && $currentUser['role'] === 'student'): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= $base_path ?>student/dashboard.php"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $base_path ?>student/jobs.php"><i class="bi bi-search me-1"></i>Find Jobs</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $base_path ?>student/applications.php"><i class="bi bi-send-check me-1"></i>Applications</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $base_path ?>student/saved-jobs.php"><i class="bi bi-bookmark-heart me-1"></i>Saved</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $base_path ?>student/profile.php"><i class="bi bi-person me-1"></i>Profile</a></li>
                    <li class="nav-item ms-1">
                        <a class="nav-link notif-bell position-relative" href="<?= $base_path ?>student/notifications.php" title="Notifications">
                            <i class="bi bi-bell"></i>
                            <?php if ($unreadCount > 0): ?>
                                <span class="badge bg-danger notif-badge"><?= min($unreadCount, 9) ?><?= $unreadCount > 9 ? '+' : '' ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="btn btn-nav-outline" href="<?= $base_path ?>student/logout.php"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
                    </li>

                <?php elseif ($currentUser && $currentUser['role'] === 'employer'): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= $base_path ?>employer/dashboard.php"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $base_path ?>employer/manage-jobs.php"><i class="bi bi-briefcase me-1"></i>My Jobs</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $base_path ?>employer/applicants.php"><i class="bi bi-people me-1"></i>Applicants</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $base_path ?>employer/profile.php"><i class="bi bi-building me-1"></i>Company</a></li>
                    <li class="nav-item ms-2">
                        <a class="btn btn-nav-cta" href="<?= $base_path ?>employer/post-job.php"><i class="bi bi-plus-circle me-1"></i>Post a Job</a>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="btn btn-nav-outline" href="<?= $base_path ?>student/logout.php"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
                    </li>

                <?php elseif ($currentUser && $currentUser['role'] === 'admin'): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= $base_path ?>admin/dashboard.php"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $base_path ?>admin/jobs.php"><i class="bi bi-briefcase me-1"></i>Jobs</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $base_path ?>admin/users.php"><i class="bi bi-people me-1"></i>Users</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $base_path ?>admin/feedback.php"><i class="bi bi-chat-left-dots me-1"></i>Feedback</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $base_path ?>admin/reports.php"><i class="bi bi-graph-up me-1"></i>Reports</a></li>
                    <li class="nav-item ms-2">
                        <a class="btn btn-nav-outline" href="<?= $base_path ?>admin/logout.php"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
                    </li>

                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= $base_path ?>index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $base_path ?>features.php">Features</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $base_path ?>about.php">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $base_path ?>contact.php">Contact</a></li>
                    <li class="nav-item ms-2">
                        <a class="btn btn-nav-outline" href="<?= $base_path ?>login.php">Log In</a>
                    </li>
                    <li class="nav-item ms-1">
                        <a class="btn btn-nav-cta" href="<?= $base_path ?>register.php">Get Started</a>
                    </li>
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
                'error'   => 'alert-danger',
                'success' => 'alert-success',
                'warning' => 'alert-warning',
                default   => 'alert-info'
            };
            $alertIcon = match($type) {
                'error'   => 'bi-exclamation-circle-fill',
                'success' => 'bi-check-circle-fill',
                'warning' => 'bi-exclamation-triangle-fill',
                default   => 'bi-info-circle-fill'
            };
        ?>
            <div class="alert flash-alert <?= $alertClass ?> alert-dismissible fade show" role="alert">
                <i class="bi <?= $alertIcon ?>"></i>
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endforeach; unset($_SESSION['flash']); ?>
    </div>
<?php endif; ?>
