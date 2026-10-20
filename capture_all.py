import os
import time
from playwright.sync_api import sync_playwright

BASE_URL = "http://localhost/jobportal"
CHROME_PATH = r"C:\Program Files\Google\Chrome\Application\chrome.exe"
OUTPUT_DIR = os.path.join(os.path.dirname(os.path.abspath(__file__)), "screenshots")

os.makedirs(OUTPUT_DIR, exist_ok=True)

PAGES_PUBLIC = [
    ("01_home.png", f"{BASE_URL}/index.php"),
    ("02_about.png", f"{BASE_URL}/about.php"),
    ("03_features.png", f"{BASE_URL}/features.php"),
    ("04_contact.png", f"{BASE_URL}/contact.php"),
    ("05_login.png", f"{BASE_URL}/login.php"),
    ("06_register_student_employer.png", f"{BASE_URL}/register.php"),
    ("07_admin_login.png", f"{BASE_URL}/admin/login.php"),
]

PAGES_STUDENT = [
    ("08_student_dashboard.png", f"{BASE_URL}/student/dashboard.php"),
    ("09_student_jobs_search_filters.png", f"{BASE_URL}/student/jobs.php"),
    ("10_student_applications_tracking.png", f"{BASE_URL}/student/applications.php"),
    ("11_student_saved_jobs.png", f"{BASE_URL}/student/saved-jobs.php"),
    ("12_student_profile_resume_upload.png", f"{BASE_URL}/student/profile.php"),
    ("13_student_notifications.png", f"{BASE_URL}/student/notifications.php"),
]

PAGES_EMPLOYER = [
    ("14_employer_dashboard.png", f"{BASE_URL}/employer/dashboard.php"),
    ("15_employer_post_job.png", f"{BASE_URL}/employer/post-job.php"),
    ("16_employer_manage_jobs.png", f"{BASE_URL}/employer/manage-jobs.php"),
    ("17_employer_applicants_pipeline.png", f"{BASE_URL}/employer/applicants.php"),
    ("18_employer_profile.png", f"{BASE_URL}/employer/profile.php"),
]

PAGES_ADMIN = [
    ("19_admin_dashboard.png", f"{BASE_URL}/admin/dashboard.php"),
    ("20_admin_jobs_moderation.png", f"{BASE_URL}/admin/jobs.php"),
    ("21_admin_users_management.png", f"{BASE_URL}/admin/users.php"),
    ("22_admin_feedback.png", f"{BASE_URL}/admin/feedback.php"),
    ("23_admin_reports_analytics.png", f"{BASE_URL}/admin/reports.php"),
]

def capture_page(page, url, filename):
    print(f"Navigating to {url}...")
    page.goto(url, wait_until="networkidle")
    page.wait_for_timeout(600)
    filepath = os.path.join(OUTPUT_DIR, filename)
    page.screenshot(path=filepath, full_page=True)
    print(f"  [SAVED] {filename} ({os.path.getsize(filepath):,} bytes)")

def run():
    with sync_playwright() as p:
        browser = p.chromium.launch(executable_path=CHROME_PATH, headless=True)

        # 1. Public Pages
        print("\n--- Capturing Public Pages ---")
        context_public = browser.new_context(viewport={"width": 1920, "height": 1080})
        page = context_public.new_page()
        for filename, url in PAGES_PUBLIC:
            capture_page(page, url, filename)
        context_public.close()

        # 2. Student Pages
        print("\n--- Capturing Student Pages ---")
        context_student = browser.new_context(viewport={"width": 1920, "height": 1080})
        page = context_student.new_page()
        print("Logging in as Student (demo@demo.com)...")
        page.goto(f"{BASE_URL}/login.php", wait_until="networkidle")
        page.fill('input[name="email"]', "demo@demo.com")
        page.fill('input[name="password"]', "student123")
        page.click('button:has-text("Log in"), button[type="submit"]')
        page.wait_for_load_state("networkidle")
        page.wait_for_timeout(600)

        for filename, url in PAGES_STUDENT:
            capture_page(page, url, filename)
        context_student.close()

        # 3. Employer Pages
        print("\n--- Capturing Employer Pages ---")
        context_employer = browser.new_context(viewport={"width": 1920, "height": 1080})
        page = context_employer.new_page()
        print("Logging in as Employer (employer@jobportal.test)...")
        page.goto(f"{BASE_URL}/login.php", wait_until="networkidle")
        page.fill('input[name="email"]', "employer@jobportal.test")
        page.fill('input[name="password"]', "employer123")
        page.click('button:has-text("Log in"), button[type="submit"]')
        page.wait_for_load_state("networkidle")
        page.wait_for_timeout(600)

        for filename, url in PAGES_EMPLOYER:
            capture_page(page, url, filename)
        context_employer.close()

        # 4. Admin Pages
        print("\n--- Capturing Admin Pages ---")
        context_admin = browser.new_context(viewport={"width": 1920, "height": 1080})
        page = context_admin.new_page()
        print("Logging in as Admin (admin@jobportal.test)...")
        page.goto(f"{BASE_URL}/admin/login.php", wait_until="networkidle")
        page.fill('input[name="email"]', "admin@jobportal.test")
        page.fill('input[name="password"]', "admin123")
        page.click('button:has-text("Log in as admin"), button[type="submit"]')
        page.wait_for_load_state("networkidle")
        page.wait_for_timeout(600)

        for filename, url in PAGES_ADMIN:
            capture_page(page, url, filename)
        context_admin.close()

        browser.close()
        print(f"\nAll 23 screenshots captured successfully into:\n{OUTPUT_DIR}\n")

if __name__ == "__main__":
    run()
