<?php
session_start();
$page_title = 'Contact Us';
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="row g-4">
                <!-- Contact Info -->
                <div class="col-md-4">
                    <div class="mb-4">
                        <span class="section-label mb-3"><i class="bi bi-chat-left-dots-fill"></i> Contact</span>
                        <h2 class="h3 fw-bold mb-2 text-dark">Get in Touch</h2>
                        <p class="text-muted" style="font-size:.9rem;">Have a question or feedback? Send us a message and we'll get back to you quickly.</p>
                    </div>
                    <div class="d-flex flex-column gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="kpi-icon-wrap kpi-indigo" style="width:40px;height:40px;font-size:1rem;border-radius:var(--radius-md);background:#e0e7ff;color:#4f46e5;display:flex;align-items:center;justify-content:center;">
                                <i class="bi bi-envelope-fill"></i>
                            </div>
                            <div>
                                <div class="fw-semibold text-dark" style="font-size:.875rem;">Email</div>
                                <div class="text-muted" style="font-size:.8rem;">hello@jobportal.test</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div style="width:40px;height:40px;font-size:1rem;border-radius:var(--radius-md);background:#d1fae5;color:#059669;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i class="bi bi-geo-alt-fill"></i>
                            </div>
                            <div>
                                <div class="fw-semibold text-dark" style="font-size:.875rem;">Location</div>
                                <div class="text-muted" style="font-size:.8rem;">India (Remote-first)</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Form -->
                <div class="col-md-8">
                    <div class="card border-0 p-4" style="box-shadow:var(--shadow-md);">
                        <h5 class="fw-bold mb-4 text-dark">Send us a Message</h5>
                        <form action="actions/submit-feedback.php" method="post">
                            <div class="mb-3">
                                <label class="form-label fw-semibold" style="font-size:.85rem;color:var(--slate-700);">Your Name <span class="text-danger">*</span></label>
                                <div class="input-icon-wrap">
                                    <i class="bi bi-person input-icon"></i>
                                    <input class="form-control" name="name" placeholder="John Doe" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold" style="font-size:.85rem;color:var(--slate-700);">Email Address <span class="text-danger">*</span></label>
                                <div class="input-icon-wrap">
                                    <i class="bi bi-envelope input-icon"></i>
                                    <input class="form-control" type="email" name="email" placeholder="you@example.com" required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-semibold" style="font-size:.85rem;color:var(--slate-700);">Your Message <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="message" rows="5" placeholder="Tell us how we can help you..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                                <i class="bi bi-send me-1"></i>Submit feedback
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
