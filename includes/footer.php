</main>

<!-- ░░ FOOTER ░░ -->
<footer class="site-footer">
    <div class="container">
        <div class="row g-4">
            <!-- Brand Column -->
            <div class="col-lg-4 col-md-6">
                <div class="footer-brand">
                    <span class="footer-brand-icon"><i class="bi bi-briefcase-fill text-white" style="font-size:.75rem;"></i></span>
                    Job<span style="color:#a5b4fc;">Portal</span>
                </div>
                <p class="mb-3" style="font-size:.875rem;line-height:1.6;">India's next-gen platform connecting ambitious students with top technology companies and verified internship opportunities.</p>
                <div class="d-flex gap-2">
                    <a href="#" class="d-inline-flex align-items-center justify-content-center" style="width:32px;height:32px;background:rgba(255,255,255,.1);border-radius:.375rem;color:rgba(255,255,255,.6);font-size:.9rem;transition:.15s;">
                        <i class="bi bi-linkedin"></i>
                    </a>
                    <a href="#" class="d-inline-flex align-items-center justify-content-center" style="width:32px;height:32px;background:rgba(255,255,255,.1);border-radius:.375rem;color:rgba(255,255,255,.6);font-size:.9rem;transition:.15s;">
                        <i class="bi bi-twitter-x"></i>
                    </a>
                    <a href="#" class="d-inline-flex align-items-center justify-content-center" style="width:32px;height:32px;background:rgba(255,255,255,.1);border-radius:.375rem;color:rgba(255,255,255,.6);font-size:.9rem;transition:.15s;">
                        <i class="bi bi-instagram"></i>
                    </a>
                </div>
            </div>

            <!-- For Students -->
            <div class="col-lg-2 col-md-3 col-6">
                <h6>For Students</h6>
                <a href="register.php">Create Account</a>
                <a href="login.php">Login</a>
                <a href="student/jobs.php">Browse Jobs</a>
                <a href="student/applications.php">Track Applications</a>
                <a href="student/profile.php">My Profile</a>
            </div>

            <!-- For Employers -->
            <div class="col-lg-2 col-md-3 col-6">
                <h6>For Employers</h6>
                <a href="register.php">Register Company</a>
                <a href="employer/post-job.php">Post a Job</a>
                <a href="employer/dashboard.php">Recruiter Dashboard</a>
                <a href="employer/applicants.php">View Applicants</a>
            </div>

            <!-- Company -->
            <div class="col-lg-2 col-sm-6">
                <h6>Company</h6>
                <a href="about.php">About Us</a>
                <a href="features.php">Features</a>
                <a href="contact.php">Contact</a>
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
            </div>
        </div>

        <div class="footer-bottom d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
            <p class="mb-0" style="font-size:.8rem;">&copy; <?= date('Y') ?> JobPortal &mdash; All rights reserved.</p>
            <p class="mb-0" style="font-size:.8rem;">Crafted with <i class="bi bi-heart-fill text-danger"></i> for India's next-gen workforce.</p>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= $base_path ?? '' ?>js/script.js"></script>
</body>
</html>
