<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
require_student();

$base_path = '../';
$page_title = 'My Profile';

$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user']['id']]);
$user = $stmt->fetch();

// Calculate profile completion score
$score = 25;
if (!empty($user['phone'])) $score += 15;
if (!empty($user['education'])) $score += 20;
if (!empty($user['skills'])) $score += 20;
if (!empty($user['resume_path'])) $score += 20;

$userInitials = avatarInitials($user['name']);

include '../includes/header.php';
?>

<div class="container py-4">
    <div class="row g-4">
        <!-- Sidebar profile summary -->
        <div class="col-lg-4">
            <div class="card p-4 text-center border-0 mb-4" style="box-shadow:var(--shadow-md);">
                <div class="company-avatar av-color-0 mx-auto mb-3" style="width:84px;height:84px;font-size:1.75rem;border-radius:50%;">
                    <?= htmlspecialchars($userInitials) ?>
                </div>
                <h4 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($user['name']) ?></h4>
                <p class="text-muted small mb-3"><?= htmlspecialchars($user['email']) ?></p>

                <!-- Profile Completeness Bar -->
                <div class="p-3 rounded-xl mb-3 text-start" style="background:var(--slate-50);border:1px solid var(--slate-100);">
                    <div class="d-flex justify-content-between small mb-2">
                        <span class="fw-bold" style="font-size:.78rem;color:var(--slate-700);text-transform:uppercase;">Profile Completeness</span>
                        <span class="fw-bold" style="color:var(--brand-primary);"><?= $score ?>%</span>
                    </div>
                    <div class="progress" style="height:8px;border-radius:var(--radius-pill);background:var(--slate-200);">
                        <div class="progress-bar" role="progressbar" style="width:<?= $score ?>%;background:var(--brand-primary);border-radius:var(--radius-pill);" aria-valuenow="<?= $score ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>

                <div class="list-group list-group-flush text-start small">
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-0 border-bottom">
                        <span class="text-muted"><i class="bi bi-mortarboard text-primary me-2"></i>Education</span>
                        <span class="fw-semibold text-truncate" style="max-width:160px;"><?= htmlspecialchars($user['education'] ?: 'Not set') ?></span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-0 border-bottom">
                        <span class="text-muted"><i class="bi bi-telephone text-primary me-2"></i>Phone</span>
                        <span class="fw-semibold"><?= htmlspecialchars($user['phone'] ?: 'Not set') ?></span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-0">
                        <span class="text-muted"><i class="bi bi-file-earmark-pdf text-danger me-2"></i>Resume</span>
                        <?php if (!empty($user['resume_path'])): ?>
                            <span class="badge" style="background:#d1fae5;color:#065f46;font-weight:600;">Uploaded</span>
                        <?php else: ?>
                            <span class="badge" style="background:#fef3c7;color:#92400e;font-weight:600;">Missing</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Quick Navigation -->
            <div class="card p-3 border-0" style="box-shadow:var(--shadow-md);">
                <div class="list-group list-group-flush">
                    <a href="applications.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-2 py-2 border-0 rounded-md">
                        <span class="fw-semibold"><i class="bi bi-send-check me-2 text-primary"></i>My Applications</span>
                        <i class="bi bi-chevron-right text-muted small"></i>
                    </a>
                    <a href="saved-jobs.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-2 py-2 border-0 rounded-md">
                        <span class="fw-semibold"><i class="bi bi-bookmark-heart me-2 text-primary"></i>Saved Opportunities</span>
                        <i class="bi bi-chevron-right text-muted small"></i>
                    </a>
                    <a href="jobs.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-2 py-2 border-0 rounded-md">
                        <span class="fw-semibold"><i class="bi bi-search me-2 text-primary"></i>Browse Openings</span>
                        <i class="bi bi-chevron-right text-muted small"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Edit Form -->
        <div class="col-lg-8">
            <div class="card p-4 p-md-5 border-0" style="box-shadow:var(--shadow-md);">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h3 class="h4 mb-1 fw-bold text-dark">Profile Settings</h3>
                        <p class="text-muted small mb-0">Update your personal information, qualifications, and default resume.</p>
                    </div>
                    <span class="badge px-3 py-2 fw-semibold" style="background:var(--brand-primary-lt);color:var(--brand-primary);">Student Account</span>
                </div>

                <form action="../actions/update-profile.php" method="post" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:.85rem;color:var(--slate-700);">Full Name <span class="text-danger">*</span></label>
                            <input class="form-control" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:.85rem;color:var(--slate-700);">Email Address</label>
                            <input class="form-control bg-light" value="<?= htmlspecialchars($user['email']) ?>" readonly title="Email cannot be changed directly">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:.85rem;color:var(--slate-700);">Phone Number</label>
                            <input class="form-control" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="+91 98765 43210">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:.85rem;color:var(--slate-700);">Education / Degree</label>
                            <input class="form-control" name="education" value="<?= htmlspecialchars($user['education'] ?? '') ?>" placeholder="e.g. B.Tech Computer Science (2025)">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size:.85rem;color:var(--slate-700);">Skills & Competencies</label>
                            <textarea class="form-control" name="skills" rows="3" placeholder="Comma separated, e.g. PHP, JavaScript, React, MySQL, Git"><?= htmlspecialchars($user['skills'] ?? '') ?></textarea>
                            <div class="form-text" style="font-size:.78rem;">List key technical abilities, tools, and certifications to match job recommendations.</div>
                        </div>

                        <!-- Resume Section -->
                        <div class="col-12">
                            <div class="p-3 rounded-xl mt-2" style="background:var(--slate-50);border:1px solid var(--slate-200);">
                                <label class="form-label fw-bold mb-1" style="font-size:.9rem;color:var(--slate-800);">
                                    <i class="bi bi-file-earmark-arrow-up text-primary me-1"></i>Default Resume / CV
                                </label>
                                <p class="small text-muted mb-3">Attach your standard resume used for 1-click applications. Formats: PDF, DOC, DOCX (Max 5MB).</p>

                                <?php if (!empty($user['resume_path'])): ?>
                                    <div class="d-flex align-items-center justify-content-between p-3 mb-3 bg-white rounded-lg" style="border:1px solid var(--slate-200);">
                                        <div class="d-flex align-items-center gap-3">
                                            <i class="bi bi-file-earmark-pdf-fill text-danger fs-3"></i>
                                            <div>
                                                <div class="fw-semibold text-dark" style="font-size:.875rem;"><?= basename($user['resume_path']) ?></div>
                                                <div class="text-muted" style="font-size:0.75rem;">Active resume on file</div>
                                            </div>
                                        </div>
                                        <a href="<?= $base_path . htmlspecialchars($user['resume_path']) ?>" target="_blank" class="btn btn-outline-primary btn-sm fw-semibold">
                                            <i class="bi bi-eye me-1"></i>Preview
                                        </a>
                                    </div>
                                <?php endif; ?>

                                <input class="form-control" type="file" name="resume" accept=".pdf,.doc,.docx">
                            </div>
                        </div>

                        <div class="col-12 mt-4 text-end">
                            <button type="submit" class="btn btn-primary px-4 py-2 fw-bold">
                                <i class="bi bi-check2-circle me-1"></i>Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
