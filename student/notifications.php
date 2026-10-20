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
<section class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h2 fw-bold mb-1"><i class="bi bi-bell-fill text-primary me-2"></i>Notifications</h1>
            <p class="text-muted mb-0">Stay updated on your application statuses, review progress, and opportunities.</p>
        </div>
        <?php if (!empty($notifications)): ?>
            <a href="../actions/mark-notifications.php" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-check2-all me-1"></i>Mark all as read
            </a>
        <?php endif; ?>
    </div>

    <div class="card shadow-sm border-0">
        <?php if (empty($notifications)): ?>
            <div class="p-5 text-center">
                <i class="bi bi-bell-slash fs-1 text-muted mb-3"></i>
                <h5 class="fw-bold">No notifications yet</h5>
                <p class="text-muted mb-0">You're all caught up! New updates regarding your applications will appear here.</p>
            </div>
        <?php else: ?>
            <div class="list-group list-group-flush">
                <?php foreach ($notifications as $n): ?>
                    <div class="list-group-item p-3 <?= !$n['is_read'] ? 'bg-light' : '' ?> d-flex justify-content-between align-items-start gap-3">
                        <div class="d-flex gap-3">
                            <div class="mt-1">
                                <?php if (!$n['is_read']): ?>
                                    <span class="badge bg-primary p-1 rounded-circle" style="width: 10px; height: 10px; display: inline-block;"></span>
                                <?php else: ?>
                                    <i class="bi bi-bell text-muted"></i>
                                <?php endif; ?>
                            </div>
                            <div>
                                <h6 class="mb-1 fw-bold"><?= htmlspecialchars($n['title']) ?></h6>
                                <p class="text-muted mb-1 small"><?= htmlspecialchars($n['message']) ?></p>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    <i class="bi bi-clock me-1"></i><?= date('M d, Y h:i A', strtotime($n['created_at'])) ?>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <?php if (!empty($n['link'])): ?>
                                <a href="<?= $base_path . htmlspecialchars($n['link']) ?>" class="btn btn-outline-primary btn-sm">
                                    View
                                </a>
                            <?php endif; ?>
                            <?php if (!$n['is_read']): ?>
                                <a href="../actions/mark-notifications.php?id=<?= $n['id'] ?>" class="btn btn-sm btn-link text-muted p-0" title="Mark as read">
                                    <i class="bi bi-check-lg fs-5"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php include '../includes/footer.php'; ?>
