<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
require_employer();

$base_path = '../';
$page_title = 'Post a New Job';
$employerId = (int)$_SESSION['user']['id'];

$empStmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$empStmt->execute([$employerId]);
$emp = $empStmt->fetch();

include '../includes/header.php';
?>
<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card p-4 p-md-5 shadow-sm border-0">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="h3 fw-bold mb-1">Post a New Opening</h2>
                        <p class="text-muted mb-0">Publish an internship or job opening to connect with qualified applicants.</p>
                    </div>
                    <a href="manage-jobs.php" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i>Back to My Jobs
                    </a>
                </div>
                <hr class="mb-4">

                <form action="../actions/post-job.php" method="post">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Job Title <span class="text-danger">*</span></label>
                            <input class="form-control" name="title" placeholder="e.g. Associate Software Engineer, Marketing Intern" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                            <select class="form-select" name="category" required>
                                <option value="Technology & Engineering">Technology & Engineering</option>
                                <option value="Design & Creative">Design & Creative</option>
                                <option value="Marketing & Growth">Marketing & Growth</option>
                                <option value="Finance & Accounts">Finance & Accounts</option>
                                <option value="Human Resources">Human Resources</option>
                                <option value="Product & Operations">Product & Operations</option>
                                <option value="Sales & Business Dev">Sales & Business Dev</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Company Name <span class="text-danger">*</span></label>
                            <input class="form-control" name="company" value="<?= htmlspecialchars($emp['company_name'] ?: $emp['name']) ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Location <span class="text-danger">*</span></label>
                            <input class="form-control" name="location" placeholder="e.g. Remote, Mumbai, Bengaluru, Hybrid" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Employment Type <span class="text-danger">*</span></label>
                            <select class="form-select" name="job_type" required>
                                <option value="Internship">Internship</option>
                                <option value="Full-time" selected>Full-time</option>
                                <option value="Part-time">Part-time</option>
                                <option value="Remote">Remote</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Experience Level <span class="text-danger">*</span></label>
                            <select class="form-select" name="experience_level" required>
                                <option value="Fresher" selected>Fresher (0 - 1 years)</option>
                                <option value="1-2 years">1 - 2 years</option>
                                <option value="3-5 years">3 - 5 years</option>
                                <option value="5+ years">5+ years</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Salary / Stipend</label>
                            <input class="form-control" name="salary" placeholder="e.g. ₹15,000/month or ₹5 - ₹7 LPA">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Application Deadline</label>
                            <input class="form-control" type="date" name="deadline" value="<?= date('Y-m-d', strtotime('+30 days')) ?>">
                        </div>

                        <div class="col-md-6 d-flex align-items-center">
                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="is_featured">
                                <label class="form-check-label fw-semibold" for="is_featured">
                                    <i class="bi bi-star-fill text-warning me-1"></i>Mark as Featured Opportunity
                                </label>
                                <div class="form-text">Featured jobs receive higher visibility on top of the search results.</div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Job Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="description" rows="4" placeholder="Describe the role overview, day-to-day responsibilities, and team environment..." required></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Skills & Requirements <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="requirements" rows="3" placeholder="Key skills, tech stack, education requirements, tools (e.g. PHP, MySQL, JavaScript, Git)..." required></textarea>
                        </div>

                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold">
                                <i class="bi bi-cloud-arrow-up me-1"></i>Publish Job Opening
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
<?php include '../includes/footer.php'; ?>
