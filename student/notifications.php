<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
require_auth();

$base_path = '../';
$page_title = 'Notifications';
$userId = (int)$_SESSION['user']['id'];

$stmt = $pdo->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$userId]);
$notifications = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h2 fw-bold mb-1">Notifications</h1>
            <p class="text-muted mb-0">Stay updated on your application statuses, review progress, and opportunities.</p>
        </div>
        <?php if (!empty($notifications)): ?>
            <a href="../actions/mark-notifications.php" class="btn btn-outline-secondary btn-sm fw-semibold">
                <i class="bi bi-check2-all me-1"></i>Mark all as read
            </a>
        <?php endif; ?>
    </div>

    <div class="card border-0" style="box-shadow:var(--shadow-md);">
        <?php if (empty($notifications)): ?>
            <div class="empty-state">
                <div class="empty-icon"><i class="bi bi-bell-slash"></i></div>
                <h5>No notifications yet</h5>
                <p>You're all caught up! New updates regarding your applications will appear here.</p>
            </div>
        <?php else: ?>
            <div class="notif-timeline">
                <?php foreach ($notifications as $n): ?>
                    <div class="notif-item list-group-item <?= !$n['is_read'] ? 'unread' : '' ?>">
                        <div class="notif-dot" style="<?= !$n['is_read'] ? 'background:var(--brand-primary-lt);color:var(--brand-primary);' : 'background:var(--slate-100);color:var(--slate-400);' ?>">
                            <i class="bi <?= !$n['is_read'] ? 'bi-bell-fill' : 'bi-bell' ?>"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-bold text-dark" style="font-size:.9rem;"><?= htmlspecialchars($n['title']) ?></h6>
                            <p class="text-muted mb-1" style="font-size:.85rem;line-height:1.5;"><?= htmlspecialchars($n['message']) ?></p>
                            <div class="text-muted" style="font-size:0.75rem;">
                                <i class="bi bi-clock me-1"></i><?= date('M d, Y &bull; h:i A', strtotime($n['created_at'])) ?>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-shrink-0">
                            <?php if (!empty($n['link'])): ?>
                                <a href="<?= $base_path . htmlspecialchars($n['link']) ?>" class="btn btn-outline-primary btn-sm fw-semibold" style="font-size:.78rem;">
                                    View
                                </a>
                            <?php endif; ?>
                            <?php if (!$n['is_read']): ?>
                                <a href="../actions/mark-notifications.php?id=<?= $n['id'] ?>" class="btn btn-sm btn-link text-muted p-1" title="Mark as read">
                                    <i class="bi bi-check2 fs-5"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
