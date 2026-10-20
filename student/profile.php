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
$score = 25; // Base for account creation
if (!empty($user['phone'])) $score += 15;
if (!empty($user['education'])) $score += 20;
if (!empty($user['skills'])) $score += 20;
if (!empty($user['resume_path'])) $score += 20;

include '../includes/header.php';
?>
<section class="container py-5">
    <div class="row g-4">
        <!-- Sidebar profile summary -->
        <div class="col-lg-4">
            <div class="card p-4 text-center shadow-sm mb-4">
                <div class="avatar-circle mx-auto mb-3 bg-primary text-white d-flex align-items-center justify-content-center rounded-circle fs-1" style="width: 90px; height: 90px;">
                    <i class="bi bi-person-fill"></i>
                </div>
                <h4 class="mb-1"><?= htmlspecialchars($user['name']) ?></h4>
                <p class="text-muted small mb-3"><?= htmlspecialchars($user['email']) ?></p>

                <div class="text-start mb-3">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="fw-semibold">Profile Completeness</span>
                        <span class="text-primary fw-bold"><?= $score ?>%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= $score ?>%" aria-valuenow="<?= $score ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>

                <div class="list-group list-group-flush text-start small">
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="bi bi-mortarboard text-primary me-2"></i>Education</span>
                        <span class="text-muted text-truncate" style="max-width: 150px;"><?= htmlspecialchars($user['education'] ?: 'Not set') ?></span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="bi bi-telephone text-primary me-2"></i>Phone</span>
                        <span class="text-muted"><?= htmlspecialchars($user['phone'] ?: 'Not set') ?></span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="bi bi-file-earmark-pdf text-primary me-2"></i>Resume</span>
                        <?php if (!empty($user['resume_path'])): ?>
                            <span class="badge bg-success-subtle text-success">Uploaded</span>
                        <?php else: ?>
                            <span class="badge bg-warning-subtle text-warning">Missing</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Quick Navigation -->
            <div class="card p-3 shadow-sm">
                <div class="list-group list-group-flush">
                    <a href="applications.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-send-check me-2 text-primary"></i>My Applications</span>
                        <i class="bi bi-chevron-right text-muted small"></i>
                    </a>
                    <a href="saved-jobs.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-bookmark-heart me-2 text-primary"></i>Saved Opportunities</span>
                        <i class="bi bi-chevron-right text-muted small"></i>
                    </a>
                    <a href="jobs.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-search me-2 text-primary"></i>Browse Openings</span>
                        <i class="bi bi-chevron-right text-muted small"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Edit Form -->
        <div class="col-lg-8">
            <div class="card p-4 shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="h4 mb-0 fw-bold">Edit Profile</h3>
                    <span class="badge bg-primary-subtle text-primary px-3 py-2">Student Account</span>
                </div>
                <hr>

                <form action="../actions/update-profile.php" method="post" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <input class="form-control" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email Address</label>
                            <input class="form-control bg-light" value="<?= htmlspecialchars($user['email']) ?>" readonly title="Email cannot be changed directly">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone Number</label>
                            <input class="form-control" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="+91 98765 43210">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Education / Degree</label>
                            <input class="form-control" name="education" value="<?= htmlspecialchars($user['education'] ?? '') ?>" placeholder="e.g. B.Tech Computer Science (2025)">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Skills & Competencies</label>
                            <textarea class="form-control" name="skills" rows="3" placeholder="Comma separated, e.g. PHP, JavaScript, React, MySQL, Git"><?= htmlspecialchars($user['skills'] ?? '') ?></textarea>
                            <div class="form-text">List your technical proficiencies, tools, and certifications.</div>
                        </div>

                        <!-- Resume Section -->
                        <div class="col-12">
                            <div class="card bg-light border p-3 mt-2">
                                <label class="form-label fw-bold mb-1">
                                    <i class="bi bi-file-earmark-arrow-up text-primary me-1"></i>Resume / Curriculum Vitae
                                </label>
                                <p class="small text-muted mb-2">Upload your resume to apply for positions with one click. Accepted formats: PDF, DOC, DOCX (Max 5MB).</p>

                                <?php if (!empty($user['resume_path'])): ?>
                                    <div class="d-flex align-items-center justify-content-between p-2 mb-3 bg-white border rounded">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-file-earmark-pdf-fill text-danger fs-4"></i>
                                            <div>
                                                <div class="fw-semibold small"><?= basename($user['resume_path']) ?></div>
                                                <div class="text-muted" style="font-size: 0.75rem;">Current active resume on file</div>
                                            </div>
                                        </div>
                                        <a href="<?= $base_path . htmlspecialchars($user['resume_path']) ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-eye me-1"></i>Preview
                                        </a>
                                    </div>
                                <?php endif; ?>

                                <input class="form-control" type="file" name="resume" accept=".pdf,.doc,.docx">
                            </div>
                        </div>

                        <div class="col-12 mt-4 text-end">
                            <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold">
                                <i class="bi bi-check2-circle me-1"></i>Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
<?php include '../includes/footer.php'; ?>
