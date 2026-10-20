<?php
require '../config/database.php';
require '../includes/auth.php';
require_admin();

if (isset($_GET['delete'])) {
    $pdo->prepare('DELETE FROM feedback WHERE id=?')->execute([(int)$_GET['delete']]);
    flash('success', 'Feedback removed.');
    header('Location: feedback.php');
    exit;
}
$base_path = '../';
$page_title = 'Manage Feedback';
$feedback = $pdo->query('SELECT * FROM feedback ORDER BY created_at DESC')->fetchAll();
include '../includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h2 fw-bold mb-1">User Feedback</h1>
            <p class="text-muted mb-0">View and manage feedback messages submitted through the contact form.</p>
        </div>
        <a href="../actions/export-csv.php?type=feedback" class="btn btn-outline-success fw-semibold">
            <i class="bi bi-filetype-csv me-1"></i>Export Feedback CSV
        </a>
    </div>

    <div class="card border-0" style="box-shadow:var(--shadow-md);">
        <?php if (!$feedback): ?>
            <div class="empty-state">
                <div class="empty-icon"><i class="bi bi-chat-left-dots"></i></div>
                <h5>No feedback yet</h5>
                <p>User feedback messages will appear here when submitted through the contact form.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 aura-table" style="border:none;border-radius:0;">
                    <thead>
                        <tr>
                            <th class="ps-4">Name</th>
                            <th>Email</th>
                            <th>Message</th>
                            <th>Date</th>
                            <th class="pe-4 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($feedback as $item): ?>
                        <tr>
                            <td class="ps-4 fw-semibold text-dark"><?= htmlspecialchars($item['name']) ?></td>
                            <td class="text-muted small"><?= htmlspecialchars($item['email']) ?></td>
                            <td style="max-width:300px;font-size:.875rem;"><?= nl2br(htmlspecialchars($item['message'])) ?></td>
                            <td class="text-muted small"><?= date('d M Y', strtotime($item['created_at'])) ?></td>
                            <td class="pe-4 text-end">
                                <a class="btn btn-sm btn-outline-danger fw-semibold"
                                   href="feedback.php?delete=<?= $item['id'] ?>"
                                   onclick="return confirm('Delete this feedback message?');">
                                    <i class="bi bi-trash me-1"></i>Delete
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
