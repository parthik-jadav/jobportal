import os
import sys
import time
from playwright.sync_api import sync_playwright

BASE_URL = "http://localhost/jobportal"
CHROME_PATH = r"C:\Program Files\Google\Chrome\Application\chrome.exe"

def run_interactive():
    print("\n=======================================================")
    print("   LAUNCHING INTERACTIVE PLAYWRIGHT BROWSER SESSION")
    print("=======================================================\n")
    print("Opening visible browser window on your desktop...")

    with sync_playwright() as p:
        browser = p.chromium.launch(
            executable_path=CHROME_PATH,
            headless=False,
            slow_mo=1000,
            args=["--start-maximized"]
        )
        context = browser.new_context(no_viewport=True)
        page = context.new_page()

        # Step 1: Homepage
        print("[1/8] Navigating to Homepage...")
        page.goto(f"{BASE_URL}/index.php", wait_until="networkidle")
        page.evaluate("window.scrollBy(0, 500)")
        time.sleep(1.5)
        page.evaluate("window.scrollBy(0, 500)")
        time.sleep(1.5)
        page.evaluate("window.scrollTo(0, 0)")
        time.sleep(1)

        # Step 2: Student Login
        print("[2/8] Logging in as Demo Student...")
        page.goto(f"{BASE_URL}/login.php", wait_until="networkidle")
        page.fill('input[name="email"]', "demo@demo.com")
        time.sleep(0.5)
        page.fill('input[name="password"]', "student123")
        time.sleep(0.5)
        page.click('button:has-text("Log in"), button[type="submit"]')
        page.wait_for_load_state("networkidle")
        print("  -> Logged in. Viewing Student Dashboard.")
        time.sleep(2)

        # Step 3: Browse Jobs & Filters
        print("[3/8] Navigating to Job Search & Filtering...")
        page.goto(f"{BASE_URL}/student/jobs.php", wait_until="networkidle")
        page.select_option('select[name="type"]', "Internship")
        page.click('button:has-text("Filter Jobs")')
        page.wait_for_load_state("networkidle")
        print("  -> Filtered by Internship.")
        time.sleep(1.5)

        # Step 4: Check Applications Tracker
        print("[4/8] Viewing Student Applications Tracker...")
        page.goto(f"{BASE_URL}/student/applications.php", wait_until="networkidle")
        time.sleep(1.5)
        # Open details modal of first application
        details_btn = page.locator('button:has-text("Details")').first
        if details_btn.count() > 0:
            details_btn.click()
            time.sleep(2)
            page.click('.modal.show button:has-text("Close")')
            time.sleep(1)

        # Step 5: Notifications Center
        print("[5/8] Checking Notifications Center...")
        page.goto(f"{BASE_URL}/student/notifications.php", wait_until="networkidle")
        time.sleep(2)

        # Step 6: Employer Login
        print("[6/8] Logging in as Employer Recruiter (Acme Corp)...")
        page.goto(f"{BASE_URL}/login.php", wait_until="networkidle")
        page.fill('input[name="email"]', "employer@jobportal.test")
        time.sleep(0.5)
        page.fill('input[name="password"]', "employer123")
        time.sleep(0.5)
        page.click('button:has-text("Log in"), button[type="submit"]')
        page.wait_for_load_state("networkidle")
        print("  -> Employer Dashboard loaded.")
        time.sleep(2)

        # Step 7: Candidate Pipeline
        print("[7/8] Reviewing Candidate Pipeline & Resumes...")
        page.goto(f"{BASE_URL}/employer/applicants.php", wait_until="networkidle")
        page.evaluate("window.scrollBy(0, 400)")
        time.sleep(2.5)

        # Step 8: Admin Overview
        print("[8/8] Logging into Administrator Area...")
        page.goto(f"{BASE_URL}/admin/login.php", wait_until="networkidle")
        page.fill('input[name="email"]', "admin@jobportal.test")
        time.sleep(0.5)
        page.fill('input[name="password"]', "admin123")
        time.sleep(0.5)
        page.click('button:has-text("Log in as admin"), button[type="submit"]')
        page.wait_for_load_state("networkidle")
        page.goto(f"{BASE_URL}/admin/reports.php", wait_until="networkidle")
        print("  -> Admin Analytics & Reports loaded.")
        time.sleep(3)

        print("\n[SUCCESS] Interactive Playwright browser session completed successfully!")
        browser.close()

if __name__ == "__main__":
    run_interactive()
