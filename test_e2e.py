import os
import sys
import time
from playwright.sync_api import sync_playwright, expect

BASE_URL = "http://localhost/jobportal"
CHROME_PATH = r"C:\Program Files\Google\Chrome\Application\chrome.exe"
TEST_RESUME_PATH = os.path.join(os.path.dirname(os.path.abspath(__file__)), "uploads", "resumes", "sample_student_resume.pdf")

passed_tests = 0
failed_tests = 0

def log_test(name, success, detail=""):
    global passed_tests, failed_tests
    if success:
        passed_tests += 1
        print(f"  [PASS] {name} {detail}")
    else:
        failed_tests += 1
        print(f"  [FAIL] {name} - {detail}")

def run_tests():
    global passed_tests, failed_tests
    print("\n=======================================================")
    print("   STARTING END-TO-END AUTOMATED PLAYWRIGHT TEST SUITE")
    print("=======================================================\n")

    with sync_playwright() as p:
        browser = p.chromium.launch(executable_path=CHROME_PATH, headless=True)

        # -------------------------------------------------------------
        # SUITE 1: PUBLIC DISCOVERY & FEEDBACK
        # -------------------------------------------------------------
        print(">>> SUITE 1: Public Pages & Feedback Submission")
        context = browser.new_context(viewport={"width": 1920, "height": 1080})
        page = context.new_page()

        # 1.1 Homepage
        try:
            page.goto(f"{BASE_URL}/index.php", wait_until="networkidle")
            title_ok = "JobPortal" in page.title()
            log_test("Homepage loads with correct title", title_ok, page.title())

            stats_visible = page.locator(".h2.fw-bold").count() >= 3
            log_test("Homepage displays live KPI statistics counters", stats_visible)

            featured_count = page.locator(".job-card").count()
            log_test("Featured jobs grid is populated", featured_count > 0, f"({featured_count} cards found)")
        except Exception as e:
            log_test("Homepage verification", False, str(e))

        # 1.2 Feedback Submission
        try:
            page.goto(f"{BASE_URL}/contact.php", wait_until="networkidle")
            page.fill('input[name="name"]', "Automated E2E Bot")
            page.fill('input[name="email"]', "bot@test.com")
            page.fill('textarea[name="message"]', "Automated end-to-end feedback verification message.")
            page.click('button:has-text("Submit feedback")')
            page.wait_for_load_state("networkidle")

            alert_text = page.locator(".alert-success").inner_text()
            feedback_ok = "Thank you" in alert_text or "submitted" in alert_text
            log_test("Contact feedback submission", feedback_ok, alert_text.strip())
        except Exception as e:
            log_test("Contact feedback submission", False, str(e))

        context.close()

        # -------------------------------------------------------------
        # SUITE 2: STUDENT JOURNEY
        # -------------------------------------------------------------
        print("\n>>> SUITE 2: Student Experience & Application Flow")
        context_student = browser.new_context(viewport={"width": 1920, "height": 1080})
        page = context_student.new_page()

        # 2.1 Login
        try:
            page.goto(f"{BASE_URL}/login.php", wait_until="networkidle")
            page.fill('input[name="email"]', "demo@demo.com")
            page.fill('input[name="password"]', "student123")
            page.click('button:has-text("Log in"), button[type="submit"]')
            page.wait_for_load_state("networkidle")

            dash_ok = "student/dashboard.php" in page.url
            log_test("Student login redirects to student dashboard", dash_ok, page.url)

            welcome_ok = page.locator("h1:has-text('Welcome back')").count() > 0
            log_test("Student dashboard displays personalized welcome", welcome_ok)
        except Exception as e:
            log_test("Student login", False, str(e))

        # 2.2 Profile & Resume Upload
        try:
            page.goto(f"{BASE_URL}/student/profile.php", wait_until="networkidle")
            page.fill('input[name="phone"]', "+91 98111 22334")
            page.fill('input[name="education"]', "B.Tech Computer Science & AI (2025)")
            page.fill('textarea[name="skills"]', "PHP, MySQL, JavaScript, React, Python, Playwright")
            if os.path.exists(TEST_RESUME_PATH):
                page.set_input_files('input[name="resume"]', TEST_RESUME_PATH)
            page.click('button:has-text("Save Changes")')
            page.wait_for_load_state("networkidle")

            profile_ok = page.locator(".alert-success").count() > 0
            log_test("Student profile and resume update", profile_ok)
        except Exception as e:
            log_test("Student profile update", False, str(e))

        # 2.3 Job Search, Filters & Bookmarking
        try:
            page.goto(f"{BASE_URL}/student/jobs.php", wait_until="networkidle")
            jobs_count_initial = page.locator(".job-card").count()
            log_test("Student job listings view loaded", jobs_count_initial > 0, f"({jobs_count_initial} jobs)")

            # Test filter by Type
            page.select_option('select[name="type"]', "Internship")
            page.click('button:has-text("Filter Jobs")')
            page.wait_for_load_state("networkidle")

            filtered_count = page.locator(".job-card").count()
            log_test("Filter by job type 'Internship' applied", filtered_count > 0, f"({filtered_count} internships)")

            # Test Bookmark / Save
            page.goto(f"{BASE_URL}/student/jobs.php", wait_until="networkidle")
            save_form_btn = page.locator('form[action*="save-job.php"] button').first
            if save_form_btn.count() > 0:
                save_form_btn.click()
                page.wait_for_load_state("networkidle")
                log_test("Job bookmarking (Save button clicked)", True)
            else:
                log_test("Job bookmarking", True, "(All visible jobs already bookmarked)")

            page.goto(f"{BASE_URL}/student/saved-jobs.php", wait_until="networkidle")
            saved_count = page.locator(".job-card").count()
            log_test("Saved jobs list displays bookmarked openings", saved_count > 0, f"({saved_count} saved)")
        except Exception as e:
            log_test("Job search & bookmarking", False, str(e))

        # 2.4 One-Click Application with Modal
        try:
            page.goto(f"{BASE_URL}/student/jobs.php", wait_until="networkidle")
            apply_btn = page.locator('button:has-text("Apply Now")').first
            if apply_btn.count() > 0:
                apply_btn.click()
                page.wait_for_selector(".modal.show", state="visible")
                log_test("Job application modal opens on click", True)

                # Fill cover note and submit
                modal = page.locator(".modal.show")
                cover_note = modal.locator('textarea[name="cover_letter"]')
                if cover_note.count() > 0:
                    cover_note.fill("Automated E2E application: Excited to apply for this opening.")
                modal.locator('button:has-text("Submit Application")').click()
                page.wait_for_load_state("networkidle")

                app_redirect = "student/applications.php" in page.url
                log_test("Application submitted and redirected to tracker", app_redirect, page.url)
            else:
                log_test("Job application modal", True, "(All listed jobs already applied)")

            page.goto(f"{BASE_URL}/student/applications.php", wait_until="networkidle")
            total_apps = page.locator("tbody tr").count()
            log_test("Student My Applications table is populated", total_apps > 0, f"({total_apps} applications)")
        except Exception as e:
            log_test("Application submission", False, str(e))

        context_student.close()

        # -------------------------------------------------------------
        # SUITE 3: EMPLOYER & RECRUITER PORTAL
        # -------------------------------------------------------------
        print("\n>>> SUITE 3: Employer & Recruiter Portal")
        context_employer = browser.new_context(viewport={"width": 1920, "height": 1080})
        page = context_employer.new_page()

        # 3.1 Employer Login
        try:
            page.goto(f"{BASE_URL}/login.php", wait_until="networkidle")
            page.fill('input[name="email"]', "employer@jobportal.test")
            page.fill('input[name="password"]', "employer123")
            page.click('button:has-text("Log in"), button[type="submit"]')
            page.wait_for_load_state("networkidle")

            emp_dash_ok = "employer/dashboard.php" in page.url
            log_test("Employer login redirects to employer dashboard", emp_dash_ok, page.url)

            emp_header_ok = page.locator("h1:has-text('Acme Corporation')").count() > 0 or page.locator("h1:has-text('Acme Recruiter')").count() > 0
            log_test("Employer dashboard displays company brand header", emp_header_ok)
        except Exception as e:
            log_test("Employer login", False, str(e))

        # 3.2 Post New Job
        new_job_title = f"QA Automation Engineer {int(time.time())}"
        try:
            page.goto(f"{BASE_URL}/employer/post-job.php", wait_until="networkidle")
            page.fill('input[name="title"]', new_job_title)
            page.select_option('select[name="category"]', "Technology & Engineering")
            page.fill('input[name="location"]', "Hybrid - Bengaluru, India")
            page.select_option('select[name="job_type"]', "Full-time")
            page.select_option('select[name="experience_level"]', "1-2 years")
            page.fill('input[name="salary"]', "₹8 - ₹10 LPA")
            page.check('input[name="is_featured"]')
            page.fill('textarea[name="description"]', "Build end-to-end automated test suites using Playwright, CI/CD pipelines, and Python.")
            page.fill('textarea[name="requirements"]', "Python, Playwright, API Testing, Git, Linux basics.")
            page.click('button:has-text("Publish Job Opening")')
            page.wait_for_load_state("networkidle")

            post_ok = "employer/manage-jobs.php" in page.url and page.locator(f"text={new_job_title}").count() > 0
            log_test("Post new job opening and display in listings", post_ok, new_job_title)
        except Exception as e:
            log_test("Post new job", False, str(e))

        # 3.3 Status Toggle (Active / Closed)
        try:
            page.goto(f"{BASE_URL}/employer/manage-jobs.php", wait_until="networkidle")
            close_btn = page.locator('button:has-text("Close")').first
            if close_btn.count() > 0:
                close_btn.click()
                page.wait_for_load_state("networkidle")
                closed_badge = page.locator('.badge:has-text("Closed")').count() > 0
                log_test("Toggle job status from Active to Closed", closed_badge)

                # Reopen
                reopen_btn = page.locator('button:has-text("Reopen")').first
                reopen_btn.click()
                page.wait_for_load_state("networkidle")
                active_badge = page.locator('.badge:has-text("Active")').count() > 0
                log_test("Toggle job status from Closed back to Active", active_badge)
            else:
                log_test("Toggle job status", True, "(No active jobs to close)")
        except Exception as e:
            log_test("Toggle job status", False, str(e))

        # 3.4 Candidate Pipeline & Shortlisting
        try:
            page.goto(f"{BASE_URL}/employer/applicants.php", wait_until="networkidle")
            applicants_count = page.locator(".card").count()
            log_test("Candidate pipeline view loaded", applicants_count > 0, f"({applicants_count} candidate cards)")

            # Inspect resume download button
            resume_btn = page.locator('a:has-text("Download / View Resume")').first
            log_test("Candidate resume preview/download button exists", resume_btn.count() > 0)

            # Update status to Shortlisted
            status_select = page.locator('select[name="status"]').first
            status_select.select_option("shortlisted")
            notes_field = page.locator('textarea[name="recruiter_notes"]').first
            notes_field.fill("Candidate meets all core criteria. Scheduled technical round.")
            page.locator('button:has-text("Save & Notify Candidate")').first.click()
            page.wait_for_load_state("networkidle")

            shortlist_badge = page.locator('.badge:has-text("Shortlisted")').count() > 0
            log_test("Update candidate status to Shortlisted with notes", shortlist_badge)
        except Exception as e:
            log_test("Candidate pipeline & shortlisting", False, str(e))

        context_employer.close()

        # -------------------------------------------------------------
        # SUITE 4: NOTIFICATION LOOP VERIFICATION
        # -------------------------------------------------------------
        print("\n>>> SUITE 4: Notification Loop Verification")
        context_student2 = browser.new_context(viewport={"width": 1920, "height": 1080})
        page = context_student2.new_page()

        try:
            # Login as student again
            page.goto(f"{BASE_URL}/login.php", wait_until="networkidle")
            page.fill('input[name="email"]', "demo@demo.com")
            page.fill('input[name="password"]', "student123")
            page.click('button:has-text("Log in"), button[type="submit"]')
            page.wait_for_load_state("networkidle")

            # Check notification badge in navbar
            bell_badge = page.locator(".bi-bell + .badge.bg-danger")
            badge_present = bell_badge.count() > 0
            log_test("Navbar notification bell displays unread count badge", badge_present)

            # Open notifications page
            page.goto(f"{BASE_URL}/student/notifications.php", wait_until="networkidle")
            notif_items = page.locator(".list-group-item").count()
            log_test("Notification center displays status change alerts", notif_items > 0, f"({notif_items} notifications)")

            # Check applications page status badge
            page.goto(f"{BASE_URL}/student/applications.php", wait_until="networkidle")
            status_shortlisted = page.locator('span.badge:has-text("Shortlisted")').count() > 0
            log_test("Student applications table reflects 'Shortlisted' status badge", status_shortlisted)
        except Exception as e:
            log_test("Notification loop verification", False, str(e))

        context_student2.close()

        # -------------------------------------------------------------
        # SUITE 5: ADMINISTRATOR GOVERNANCE & EXPORTS
        # -------------------------------------------------------------
        print("\n>>> SUITE 5: Administrator Governance & Data Exports")
        context_admin = browser.new_context(viewport={"width": 1920, "height": 1080})
        page = context_admin.new_page()

        # 5.1 Admin Login & KPIs
        try:
            page.goto(f"{BASE_URL}/admin/login.php", wait_until="networkidle")
            page.fill('input[name="email"]', "admin@jobportal.test")
            page.fill('input[name="password"]', "admin123")
            page.click('button:has-text("Log in as admin"), button[type="submit"]')
            page.wait_for_load_state("networkidle")

            admin_dash_ok = "admin/dashboard.php" in page.url
            log_test("Admin login redirects to admin dashboard", admin_dash_ok, page.url)

            kpi_count = page.locator(".card .h3.fw-bold").count()
            log_test("Admin dashboard displays system KPI counters", kpi_count >= 5, f"({kpi_count} counters)")
        except Exception as e:
            log_test("Admin login", False, str(e))

        # 5.2 Job Moderation
        try:
            page.goto(f"{BASE_URL}/admin/jobs.php", wait_until="networkidle")
            admin_jobs = page.locator("tbody tr").count()
            log_test("Admin job moderation table is populated", admin_jobs > 0, f"({admin_jobs} jobs listed)")

            # Toggle featured star
            star_btn = page.locator('button[title="Toggle Featured status"]').first
            star_btn.click()
            page.wait_for_load_state("networkidle")
            alert_star = page.locator(".alert-success").count() > 0
            log_test("Admin toggle job Featured status", alert_star)
        except Exception as e:
            log_test("Admin job moderation", False, str(e))

        # 5.3 User Account Moderation
        try:
            page.goto(f"{BASE_URL}/admin/users.php", wait_until="networkidle")
            users_count = page.locator("tbody tr").count()
            log_test("Admin user accounts table is populated", users_count > 0, f"({users_count} users listed)")

            # Test role filter tab
            page.click('a:has-text("Employers")')
            page.wait_for_load_state("networkidle")
            emp_users = page.locator("tbody tr").count()
            log_test("Admin user role filtering by 'Employers'", emp_users > 0, f"({emp_users} employers)")

            # Test account suspend / activate toggle
            suspend_btn = page.locator('button:has-text("Suspend")').first
            if suspend_btn.count() > 0:
                suspend_btn.click()
                page.wait_for_load_state("networkidle")
                has_suspended = page.locator('.badge:has-text("Suspended")').count() > 0
                log_test("Admin suspend user account", has_suspended)

                activate_btn = page.locator('button:has-text("Activate")').first
                activate_btn.click()
                page.wait_for_load_state("networkidle")
                has_active = page.locator('.badge:has-text("Active")').count() > 0
                log_test("Admin reactivate user account", has_active)
            else:
                log_test("Admin user suspension toggle", True, "(No suspendable users found)")
        except Exception as e:
            log_test("Admin user moderation", False, str(e))

        # 5.4 CSV Data Exports
        try:
            page.goto(f"{BASE_URL}/admin/reports.php", wait_until="networkidle")
            funnel_ok = page.locator(".progress-bar").count() >= 5
            log_test("Admin reports displays hiring pipeline funnel breakdown", funnel_ok)

            # Test Applications CSV export
            with page.expect_download() as download_info:
                page.click('a:has-text("Export Applications")')
            download = download_info.value
            csv_path = download.path()
            csv_valid = os.path.exists(csv_path) and os.path.getsize(csv_path) > 50
            log_test("Export Applications CSV file download", csv_valid, f"({os.path.getsize(csv_path)} bytes)")

            # Test Users CSV export
            with page.expect_download() as download_info_users:
                page.click('a:has-text("Export Users")')
            download_users = download_info_users.value
            users_csv_path = download_users.path()
            users_csv_valid = os.path.exists(users_csv_path) and os.path.getsize(users_csv_path) > 50
            log_test("Export Users CSV file download", users_csv_valid, f"({os.path.getsize(users_csv_path)} bytes)")
        except Exception as e:
            log_test("CSV data exports", False, str(e))

        context_admin.close()
        browser.close()

    print("\n=======================================================")
    print(f"   E2E PLAYWRIGHT TEST SUMMARY: {passed_tests} PASSED | {failed_tests} FAILED")
    print("=======================================================\n")

    return failed_tests == 0

if __name__ == "__main__":
    success = run_tests()
    sys.exit(0 if success else 1)
