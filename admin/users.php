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
<section class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h2 fw-bold mb-1">User Account Management</h1>
            <p class="text-muted mb-0">Manage registered students, employer accounts, and administrators.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="../actions/export-csv.php?type=users" class="btn btn-outline-success">
                <i class="bi bi-filetype-csv me-1"></i>Export Users CSV
            </a>
        </div>
    </div>

    <!-- Filter Tabs -->
    <ul class="nav nav-pills mb-4">
        <li class="nav-item">
            <a class="nav-link <?= $roleFilter === 'all' ? 'active' : '' ?>" href="users.php">All Users</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $roleFilter === 'student' ? 'active' : '' ?>" href="users.php?role=student">Students</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $roleFilter === 'employer' ? 'active' : '' ?>" href="users.php?role=employer">Employers</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $roleFilter === 'admin' ? 'active' : '' ?>" href="users.php?role=admin">Admins</a>
        </li>
    </ul>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
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
                        <tr><td colspan="7" class="text-center py-4 text-muted">No users found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($users as $u): 
                            $isSelf = ((int)$u['id'] === $currentAdminId);
                            $roleBadge = match($u['role']) {
                                'admin' => 'bg-danger-subtle text-danger',
                                'employer' => 'bg-info-subtle text-info',
                                default => 'bg-primary-subtle text-primary'
                            };
                        ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($u['name']) ?></div>
                                    <div class="text-muted small"><?= htmlspecialchars($u['email']) ?></div>
                                </td>
                                <td>
                                    <span class="badge <?= $roleBadge ?> text-capitalize px-3 py-1">
                                        <?= htmlspecialchars($u['role']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($u['role'] === 'employer' && !empty($u['company_name'])): ?>
                                        <div class="small fw-semibold"><?= htmlspecialchars($u['company_name']) ?></div>
                                        <?php if (!empty($u['company_website'])): ?>
                                            <a href="<?= htmlspecialchars($u['company_website']) ?>" target="_blank" class="text-muted" style="font-size:0.75rem;">
                                                <?= htmlspecialchars($u['company_website']) ?>
                                            </a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted small">&mdash;</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="small text-muted"><?= htmlspecialchars($u['phone'] ?: 'N/A') ?></div>
                                </td>
                                <td>
                                    <div class="small text-muted"><?= date('M d, Y', strtotime($u['created_at'])) ?></div>
                                </td>
                                <td>
                                    <?php if ($u['status'] === 'active'): ?>
                                        <span class="badge bg-success-subtle text-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger">Suspended</span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-4 text-end">
                                    <?php if (!$isSelf): ?>
                                        <div class="d-inline-flex gap-1">
                                            <!-- Toggle Status Form -->
                                            <form action="../actions/toggle-user-status.php" method="post" class="d-inline">
                                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                <button class="btn btn-outline-secondary btn-sm" title="Suspend or Activate account">
                                                    <?= $u['status'] === 'active' ? 'Suspend' : 'Activate' ?>
                                                </button>
                                            </form>

                                            <!-- Delete User Form -->
                                            <form action="../actions/delete-user.php" method="post" class="d-inline">
                                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                <button class="btn btn-outline-danger btn-sm" onclick="return confirm('Permanently delete this user account and all their records?');" title="Delete account">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted border">Current User</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php include '../includes/footer.php'; ?>
