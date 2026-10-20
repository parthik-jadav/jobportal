<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
require_admin();

$base_path = '../';
$page_title = 'User Accounts Management';
$currentAdminId = (int)$_SESSION['user']['id'];

$roleFilter = trim($_GET['role'] ?? 'all');
$sql = "SELECT * FROM users";
$params = [];

if (in_array($roleFilter, ['student', 'employer', 'admin'])) {
    $sql .= " WHERE role = ?";
    $params[] = $roleFilter;
}
$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h2 fw-bold mb-1">User Account Management</h1>
            <p class="text-muted mb-0">Manage registered students, employer accounts, and administrators.</p>
        </div>
        <a href="../actions/export-csv.php?type=users" class="btn btn-outline-success fw-semibold">
            <i class="bi bi-filetype-csv me-1"></i>Export Users CSV
        </a>
    </div>

    <!-- Filter Tabs -->
    <div class="d-flex gap-2 mb-4 flex-wrap">
        <?php foreach (['all' => 'All Users', 'student' => 'Students', 'employer' => 'Employers', 'admin' => 'Admins'] as $key => $label): ?>
            <a class="btn btn-sm fw-semibold <?= $roleFilter === $key ? 'btn-primary' : 'btn-outline-secondary' ?>"
               href="users.php<?= $key !== 'all' ? '?role=' . $key : '' ?>">
                <?= $label ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="card border-0" style="box-shadow:var(--shadow-md);">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 aura-table" style="border:none;border-radius:0;">
                <thead>
                    <tr>
                        <th class="ps-4">User</th>
                        <th>Role</th>
                        <th>Company / Org</th>
                        <th>Phone</th>
                        <th>Joined</th>
                        <th>Status</th>
                        <th class="pe-4 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr><td colspan="7" class="text-center py-5 text-muted">No users found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($users as $u):
                            $isSelf = ((int)$u['id'] === $currentAdminId);
                            $uInitials = avatarInitials($u['name']);
                            $uAvClass  = avatarColorClass($u['name']);
                            $roleBadge = match($u['role']) {
                                'admin'    => ['bg' => '#ffe4e6', 'text' => '#9f1239'],
                                'employer' => ['bg' => '#cffafe', 'text' => '#0e7490'],
                                default    => ['bg' => '#e0e7ff', 'text' => '#4338ca']
                            };
                        ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="company-avatar <?= $uAvClass ?>" style="width:36px;height:36px;font-size:.72rem;"><?= htmlspecialchars($uInitials) ?></div>
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size:.875rem;"><?= htmlspecialchars($u['name']) ?></div>
                                        <div class="text-muted" style="font-size:.75rem;"><?= htmlspecialchars($u['email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge rounded-pill px-2.5 py-1 fw-semibold text-capitalize" style="background:<?= $roleBadge['bg'] ?>;color:<?= $roleBadge['text'] ?>;font-size:.72rem;">
                                    <?= htmlspecialchars($u['role']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($u['role'] === 'employer' && !empty($u['company_name'])): ?>
                                    <div class="fw-semibold" style="font-size:.85rem;"><?= htmlspecialchars($u['company_name']) ?></div>
                                    <?php if (!empty($u['company_website'])): ?>
                                        <a href="<?= htmlspecialchars($u['company_website']) ?>" target="_blank" class="text-muted" style="font-size:.72rem;">
                                            <?= htmlspecialchars($u['company_website']) ?>
                                        </a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted small">&mdash;</span>
                                <?php endif; ?>
                            </td>
                            <td><div class="text-muted small"><?= htmlspecialchars($u['phone'] ?: 'N/A') ?></div></td>
                            <td><div class="text-muted small"><?= date('M d, Y', strtotime($u['created_at'])) ?></div></td>
                            <td>
                                <?php if ($u['status'] === 'active'): ?>
                                    <span class="badge rounded-pill px-2.5 py-1 fw-semibold" style="background:#d1fae5;color:#065f46;font-size:.72rem;">Active</span>
                                <?php else: ?>
                                    <span class="badge rounded-pill px-2.5 py-1 fw-semibold" style="background:#ffe4e6;color:#9f1239;font-size:.72rem;">Suspended</span>
                                <?php endif; ?>
                            </td>
                            <td class="pe-4 text-end">
                                <?php if (!$isSelf): ?>
                                    <div class="d-inline-flex gap-1.5 align-items-center">
                                        <form action="../actions/toggle-user-status.php" method="post" class="d-inline">
                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                            <button class="btn btn-outline-secondary table-btn" style="font-size:.72rem;" title="Suspend or Activate">
                                                <?= $u['status'] === 'active' ? 'Suspend' : 'Activate' ?>
                                            </button>
                                        </form>
                                        <form action="../actions/delete-user.php" method="post" class="d-inline">
                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                            <button class="table-btn-icon text-danger border-danger" style="font-size:.75rem;" onclick="return confirm('Permanently delete this user account?');" title="Delete account">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <span class="badge rounded-pill" style="background:#f1f5f9;color:#64748b;font-size:.72rem;">Current User</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
