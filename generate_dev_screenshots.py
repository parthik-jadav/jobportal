import os
import shutil
import time
from playwright.sync_api import sync_playwright

BASE_URL = "http://localhost/jobportal"
CHROME_PATH = r"C:\Program Files\Google\Chrome\Application\chrome.exe"
OUTPUT_DIR = os.path.join(os.path.dirname(os.path.abspath(__file__)), "screenshots")

# Ensure clean directory
if os.path.exists(OUTPUT_DIR):
    for f in os.listdir(OUTPUT_DIR):
        file_path = os.path.join(OUTPUT_DIR, f)
        if os.path.isfile(file_path):
            os.unlink(file_path)
else:
    os.makedirs(OUTPUT_DIR, exist_ok=True)

def capture(page, filename, description, full_page=True):
    filepath = os.path.join(OUTPUT_DIR, filename)
    page.screenshot(path=filepath, full_page=full_page)
    size_kb = os.path.getsize(filepath) / 1024
    print(f"  [SAVED] {filename} ({size_kb:.1f} KB) - {description}")

def run():
    print("\n=======================================================")
    print("   GENERATING COMPLETE DEVELOPER SCREENSHOT CATALOG")
    print("=======================================================\n")

    with sync_playwright() as p:
        browser = p.chromium.launch(executable_path=CHROME_PATH, headless=True)

        # -------------------------------------------------------------
        # 1. PUBLIC PAGES
        # -------------------------------------------------------------
        print(">>> 1. Capturing Public Pages...")
        ctx_pub = browser.new_context(viewport={"width": 1920, "height": 1080})
        page = ctx_pub.new_page()

        page.goto(f"{BASE_URL}/index.php", wait_until="networkidle")
        time.sleep(0.5)
        capture(page, "01_public_home.png", "Homepage Landing Page with Live Stats & Featured Openings")

        page.goto(f"{BASE_URL}/features.php", wait_until="networkidle")
        time.sleep(0.5)
        capture(page, "02_public_features.png", "Platform Features Breakdown for Students, Employers & Admin")

        page.goto(f"{BASE_URL}/about.php", wait_until="networkidle")
        time.sleep(0.5)
        capture(page, "03_public_about.png", "About Page")

        page.goto(f"{BASE_URL}/contact.php", wait_until="networkidle")
        time.sleep(0.5)
        capture(page, "04_public_contact.png", "Contact & Feedback Form")

        page.goto(f"{BASE_URL}/login.php", wait_until="networkidle")
        time.sleep(0.5)
        capture(page, "05_public_login.png", "Unified Login (Student, Employer, Admin)")

        page.goto(f"{BASE_URL}/register.php", wait_until="networkidle")
        time.sleep(0.5)
        capture(page, "06_public_register_student_employer.png", "Dual-Role Registration (Student & Employer)")

        page.goto(f"{BASE_URL}/admin/login.php", wait_until="networkidle")
        time.sleep(0.5)
        capture(page, "07_public_admin_login.png", "Administrator Sign-in Form")

        ctx_pub.close()

        # -------------------------------------------------------------
        # 2. STUDENT PORTAL
        # -------------------------------------------------------------
        print("\n>>> 2. Capturing Student Portal Pages...")
        ctx_stu = browser.new_context(viewport={"width": 1920, "height": 1080})
        page = ctx_stu.new_page()

        # Login as student
        page.goto(f"{BASE_URL}/login.php", wait_until="networkidle")
        page.fill('input[name="email"]', "demo@demo.com")
        page.fill('input[name="password"]', "student123")
        page.click('button:has-text("Log in"), button[type="submit"]')
        page.wait_for_load_state("networkidle")
        time.sleep(0.5)

        capture(page, "08_student_dashboard.png", "Student Dashboard (Metrics, Recent Apps, Recommendations)")

        page.goto(f"{BASE_URL}/student/jobs.php", wait_until="networkidle")
        time.sleep(0.5)
        capture(page, "09_student_jobs_catalog.png", "Job Search & Multi-Faceted Filters (Type, Category, Exp, Sort)")

        # Open Apply Modal
        apply_btn = page.locator('button:has-text("Apply Now")').first
        if apply_btn.count() > 0:
            apply_btn.click()
            time.sleep(0.6)
            capture(page, "10_student_job_apply_modal.png", "Job Application Modal (Resume Selection & Cover Note)", full_page=False)
            close_btn = page.locator('.modal.show button:has-text("Cancel"), .modal.show .btn-close').first
            if close_btn.count() > 0:
                close_btn.click()
                time.sleep(0.4)

        page.goto(f"{BASE_URL}/student/applications.php", wait_until="networkidle")
        time.sleep(0.5)
        capture(page, "11_student_applications.png", "Live Student Application Tracker with Status Badges")

        # Open Details Modal
        det_btn = page.locator('button:has-text("Details")').first
        if det_btn.count() > 0:
            det_btn.click()
            time.sleep(0.6)
            capture(page, "12_student_application_details_modal.png", "Application Status Details & Recruiter Notes Modal", full_page=False)
            close_modal = page.locator('.modal.show button:has-text("Close"), .modal.show .btn-close').first
            if close_modal.count() > 0:
                close_modal.click()
                time.sleep(0.4)

        page.goto(f"{BASE_URL}/student/saved-jobs.php", wait_until="networkidle")
        time.sleep(0.5)
        capture(page, "13_student_saved_jobs.png", "Saved Opportunities (Bookmarks & One-Click Apply)")

        page.goto(f"{BASE_URL}/student/profile.php", wait_until="networkidle")
        time.sleep(0.5)
        capture(page, "14_student_profile_resume.png", "Student Profile, Skills & Active PDF Resume on File")

        page.goto(f"{BASE_URL}/student/notifications.php", wait_until="networkidle")
        time.sleep(0.5)
        capture(page, "15_student_notifications.png", "Real-Time Candidate Notification Center")

        ctx_stu.close()

        # -------------------------------------------------------------
        # 3. EMPLOYER PORTAL
        # -------------------------------------------------------------
        print("\n>>> 3. Capturing Employer Portal Pages...")
        ctx_emp = browser.new_context(viewport={"width": 1920, "height": 1080})
        page = ctx_emp.new_page()

        # Login as employer
        page.goto(f"{BASE_URL}/login.php", wait_until="networkidle")
        page.fill('input[name="email"]', "employer@jobportal.test")
        page.fill('input[name="password"]', "employer123")
        page.click('button:has-text("Log in"), button[type="submit"]')
        page.wait_for_load_state("networkidle")
        time.sleep(0.5)

        capture(page, "16_employer_dashboard.png", "Employer Recruiter Dashboard (KPIs, Activity, Pipeline)")

        page.goto(f"{BASE_URL}/employer/post-job.php", wait_until="networkidle")
        time.sleep(0.5)
        capture(page, "17_employer_post_job.png", "Job Opening Creation Form (Category, Salary, Deadline, Featured)")

        page.goto(f"{BASE_URL}/employer/manage-jobs.php", wait_until="networkidle")
        time.sleep(0.5)
        capture(page, "18_employer_manage_jobs.png", "Manage Job Listings (Status Toggles, Applicant Counts)")

        # Edit job
        edit_link = page.locator('a[title="Edit Job"]').first
        if edit_link.count() > 0:
            edit_link.click()
            page.wait_for_load_state("networkidle")
            time.sleep(0.5)
            capture(page, "19_employer_edit_job.png", "Edit Job Opening Details Form")

        page.goto(f"{BASE_URL}/employer/applicants.php", wait_until="networkidle")
        time.sleep(0.5)
        capture(page, "20_employer_applicants_pipeline.png", "Candidate Review Pipeline (Resume Download, Status Update & Notes)")

        page.goto(f"{BASE_URL}/employer/profile.php", wait_until="networkidle")
        time.sleep(0.5)
        capture(page, "21_employer_profile.png", "Company Profile & Recruiter Contact Management")

        ctx_emp.close()

        # -------------------------------------------------------------
        # 4. ADMINISTRATOR PORTAL
        # -------------------------------------------------------------
        print("\n>>> 4. Capturing Administrator Portal Pages...")
        ctx_adm = browser.new_context(viewport={"width": 1920, "height": 1080})
        page = ctx_adm.new_page()

        # Login as admin
        page.goto(f"{BASE_URL}/admin/login.php", wait_until="networkidle")
        page.fill('input[name="email"]', "admin@jobportal.test")
        page.fill('input[name="password"]', "admin123")
        page.click('button:has-text("Log in as admin"), button[type="submit"]')
        page.wait_for_load_state("networkidle")
        time.sleep(0.5)

        capture(page, "22_admin_dashboard.png", "Executive Administrator Dashboard (KPIs & Global Activity)")

        page.goto(f"{BASE_URL}/admin/jobs.php", wait_until="networkidle")
        time.sleep(0.5)
        capture(page, "23_admin_jobs_moderation.png", "Global Job Moderation (Featured Badges, Open/Close, Delete)")

        # Open Add Job Modal
        add_job_btn = page.locator('button[data-bs-target="#adminAddJobModal"]').first
        if add_job_btn.count() > 0:
            add_job_btn.click()
            time.sleep(0.6)
            capture(page, "24_admin_add_job_modal.png", "Admin Add New Job Modal", full_page=False)
            close_adm_modal = page.locator('#adminAddJobModal button[data-bs-dismiss="modal"]').first
            if close_adm_modal.count() > 0:
                close_adm_modal.click()
                time.sleep(0.4)

        page.goto(f"{BASE_URL}/admin/users.php", wait_until="networkidle")
        time.sleep(0.5)
        capture(page, "25_admin_users_management.png", "User Account Governance (Role Filters, Suspend & Activate)")

        page.goto(f"{BASE_URL}/admin/feedback.php", wait_until="networkidle")
        time.sleep(0.5)
        capture(page, "26_admin_feedback_inbox.png", "User Feedback & Inquiry Inbox")

        page.goto(f"{BASE_URL}/admin/reports.php", wait_until="networkidle")
        time.sleep(0.5)
        capture(page, "27_admin_reports_analytics.png", "Analytics Reports, Candidate Funnel & CSV Data Exports")

        ctx_adm.close()
        browser.close()

    print("\n=======================================================")
    print(f"   ALL 27 SCREENSHOTS CAPTURED INTO: {OUTPUT_DIR}")
    print("=======================================================\n")

if __name__ == "__main__":
    run()
