# End-to-End Automated Browser Agent Testing Plan

This document serves as an exhaustive, step-by-step testing blueprint designed for automated browser agents (e.g. Playwright, Puppeteer, Selenium, or AI Browser Subagents) to systematically validate every workflow and role across **JobPortal**.

---

## 1. Test Environment Setup & Credentials

- **Base URL**: `http://localhost/jobportal` (or `http://jobportal.test`)
- **Supported Viewports**:
  - Desktop: `1920 x 1080` (Primary)
  - Tablet/Mobile: `375 x 812` (Responsive checks)

### Test Accounts Reference Table

| Role | Email | Password | Primary Landing Page |
| :--- | :--- | :--- | :--- |
| **Student** | `demo@demo.com` | `student123` | `http://localhost/jobportal/student/dashboard.php` |
| **Employer** | `employer@jobportal.test` | `employer123` | `http://localhost/jobportal/employer/dashboard.php` |
| **Administrator** | `admin@jobportal.test` | `admin123` | `http://localhost/jobportal/admin/dashboard.php` |

---

## 2. Test Execution Workflow Map

```mermaid
flowchart TD
    subgraph Flow 1: Public Discovery
        P1[Navigate Home] --> P2[Test Search & Links]
        P2 --> P3[Check Features & Contact]
        P3 --> P4[Submit Feedback]
    end

    subgraph Flow 2: Student Journey
        S1[Register New Student or Login] --> S2[Upload Resume in Profile]
        S2 --> S3[Search & Filter Jobs]
        S3 --> S4[Bookmark/Save a Job]
        S4 --> S5[Apply with Resume + Cover Letter]
        S5 --> S6[Verify in My Applications]
    end

    subgraph Flow 3: Employer Journey
        E1[Login as Employer] --> E2[Post a New Job Opening]
        E2 --> E3[Manage Listings & Status Toggle]
        E3 --> E4[Open Candidate Pipeline]
        E4 --> E5[Download Resume & Shortlist Candidate]
    end

    subgraph Flow 4: Status Notification Loop
        N1[Re-login as Student] --> N2[Check Notification Bell Badge]
        N2 --> N3[Verify Live Status Updated to Shortlisted]
    end

    subgraph Flow 5: Admin Governance
        A1[Login as Admin] --> A2[Verify KPI Dashboard]
        A2 --> A3[Moderate Listings & Feature Toggle]
        A3 --> A4[Moderate Users & Suspend/Activate]
        A4 --> A5[Export Applications & Users CSV]
    end

    Flow 1 --> Flow 2
    Flow 2 --> Flow 3
    Flow 3 --> Flow 4
    Flow 4 --> Flow 5
```

---

## 3. Detailed Step-by-Step Test Scenarios

---

### Test Suite 1: Public Pages & Feedback Submission

#### Scenario 1.1: Homepage Discovery & Navigation
1. **Action**: Navigate to `http://localhost/jobportal/index.php`.
2. **Assertions**:
   - Page title contains `JobPortal`.
   - Header contains logo and public links: *Home*, *Features*, *About*, *Contact*, *Login*, *Register*.
   - Hero banner and live statistics counters (Active Openings, Hiring Companies, Registered Students) are visible with numeric values > 0.
   - Featured opportunities grid is present.
3. **Action**: In the hero search form, type `Engineer` into input `input[name="q"]` and click `button:has-text("Search Jobs")`.
4. **Assertion**: Redirects to `http://localhost/jobportal/student/jobs.php` or `login.php` (if protected).

#### Scenario 1.2: Contact & Feedback Form
1. **Action**: Navigate to `http://localhost/jobportal/contact.php`.
2. **Action**: Fill form:
   - Name: `input[name="name"]` $\rightarrow$ `Automated Tester`
   - Email: `input[name="email"]` $\rightarrow$ `tester@example.com`
   - Message: `textarea[name="message"]` $\rightarrow$ `E2E automated test inquiry message.`
3. **Action**: Click `button:has-text("Submit feedback")`.
4. **Assertions**:
   - Success flash message appears: *"Thank you—your feedback has been submitted."*
   - Database record is created in table `feedback`.

---

### Test Suite 2: Student Experience & Application Flow

#### Scenario 2.1: Student Authentication
1. **Action**: Navigate to `http://localhost/jobportal/login.php`.
2. **Action**: Fill credentials:
   - `input[name="email"]` $\rightarrow$ `demo@demo.com`
   - `input[name="password"]` $\rightarrow$ `student123`
3. **Action**: Click `button:has-text("Log in")`.
4. **Assertions**:
   - URL redirects to `http://localhost/jobportal/student/dashboard.php`.
   - Navbar updates to student navigation: *Dashboard*, *Find Jobs*, *Applications*, *Saved*, *Profile*, notification bell, and *Logout*.
   - Welcome banner displays candidate's name.

#### Scenario 2.2: Profile & Resume Upload
1. **Action**: Navigate to `http://localhost/jobportal/student/profile.php`.
2. **Assertions**:
   - Profile completeness progress bar is visible.
   - Student info fields (Name, Education, Phone, Skills) are populated.
3. **Action**: Update profile details:
   - Education: `input[name="education"]` $\rightarrow$ `B.Tech Computer Science & AI (2025)`
   - Skills: `textarea[name="skills"]` $\rightarrow$ `PHP, MySQL, JavaScript, React, Python, Docker`
4. **Action**: Attach a test PDF file to `input[name="resume"]`.
5. **Action**: Click `button:has-text("Save Changes")`.
6. **Assertions**:
   - Success alert appears: *"Profile updated successfully."*
   - Resume preview link appears with badge *"Uploaded"*.

#### Scenario 2.3: Job Search, Multi-Filter & Bookmark
1. **Action**: Navigate to `http://localhost/jobportal/student/jobs.php`.
2. **Assertions**:
   - Multi-filter bar is visible (Keywords, Job Type, Category, Experience, Sort).
   - List of active job cards is displayed.
3. **Action**: Test search filters:
   - Select `select[name="type"]` $\rightarrow$ `Internship`
   - Click `button:has-text("Filter Jobs")`
4. **Assertion**: All visible cards have badge `Internship`.
5. **Action**: On any un-saved job card, click `button:has-text("Save")`.
6. **Assertion**: Button changes to `button:has-text("Saved")` with outline-danger style.
7. **Action**: Navigate to `http://localhost/jobportal/student/saved-jobs.php`.
8. **Assertion**: The bookmarked job appears in the saved list.

#### Scenario 2.4: One-Click Job Application
1. **Action**: Return to `http://localhost/jobportal/student/jobs.php`.
2. **Action**: Find an opening without an "Applied" badge and click `button:has-text("Apply Now")`.
3. **Assertion**: Modal `#applyModal{id}` opens.
4. **Action**: In the modal:
   - Verify radio `Use Profile Resume` is checked by default.
   - Fill Cover Note: `textarea[name="cover_letter"]` $\rightarrow$ `I am eager to contribute my full-stack skills to this role.`
   - Click modal submit button: `button:has-text("Submit Application")`.
5. **Assertions**:
   - Redirects to `http://localhost/jobportal/student/applications.php`.
   - Success flash alert appears: *"Application submitted successfully!"*
   - Newly applied job appears in the table with status badge `Applied`.

---

### Test Suite 3: Employer & Recruiter Portal

#### Scenario 3.1: Employer Login & Metrics
1. **Action**: Click `Logout` or navigate to `http://localhost/jobportal/student/logout.php`.
2. **Action**: Navigate to `http://localhost/jobportal/login.php`.
3. **Action**: Fill employer credentials:
   - `input[name="email"]` $\rightarrow$ `employer@jobportal.test`
   - `input[name="password"]` $\rightarrow$ `employer123`
4. **Action**: Click `button:has-text("Log in")`.
5. **Assertions**:
   - Redirects to `http://localhost/jobportal/employer/dashboard.php`.
   - Recruiter header displays company name (*Acme Corporation*).
   - Metric cards visible: *Active Postings*, *Total Applicants*, *Shortlisted*, *Offers Made*.
   - Navbar displays: *Dashboard*, *My Jobs*, *Applicants*, *Company*, *Post a Job*, *Logout*.

#### Scenario 3.2: Post a New Job Opening
1. **Action**: Click `a:has-text("Post a Job")` or navigate to `http://localhost/jobportal/employer/post-job.php`.
2. **Action**: Fill form:
   - Title: `input[name="title"]` $\rightarrow$ `Senior QA Automation Engineer`
   - Category: `select[name="category"]` $\rightarrow$ `Technology & Engineering`
   - Location: `input[name="location"]` $\rightarrow$ `Hybrid - Bengaluru, India`
   - Employment Type: `select[name="job_type"]` $\rightarrow$ `Full-time`
   - Experience Level: `select[name="experience_level"]` $\rightarrow$ `3-5 years`
   - Salary: `input[name="salary"]` $\rightarrow$ `₹12 - ₹15 LPA`
   - Checkbox `input[name="is_featured"]` $\rightarrow$ check
   - Description: `textarea[name="description"]` $\rightarrow$ `Responsible for end-to-end automated test suites using Playwright and Selenium.`
   - Requirements: `textarea[name="requirements"]` $\rightarrow$ `Python, Playwright, CI/CD, SQL, API testing.`
3. **Action**: Click `button:has-text("Publish Job Opening")`.
4. **Assertions**:
   - Redirects to `http://localhost/jobportal/employer/manage-jobs.php`.
   - Success alert appears: *"Job posting ... published successfully!"*
   - New listing `Senior QA Automation Engineer` appears in the jobs table with status badge `Active` and `0 Candidates`.

#### Scenario 3.3: Manage Postings & Status Toggle
1. **Action**: On `http://localhost/jobportal/employer/manage-jobs.php`, locate the newly created job.
2. **Action**: Click `button:has-text("Close")` in its row.
3. **Assertions**:
   - Status badge changes from `Active` to `Closed`.
   - Button text toggles to `Reopen`.
4. **Action**: Click `button:has-text("Reopen")`.
5. **Assertion**: Status badge toggles back to `Active`.

#### Scenario 3.4: Candidate Review Pipeline & Shortlisting
1. **Action**: Navigate to `http://localhost/jobportal/employer/applicants.php`.
2. **Assertions**:
   - Candidate filter dropdowns (Job Opening, Hiring Stage) are visible.
   - List of student applications is displayed.
3. **Action**: Locate the application from `Demo Student`.
4. **Assertions**:
   - Candidate contact information, education, and skills are visible.
   - `Download / View Resume` button is present and links to valid file.
   - Candidate's cover note is visible.
5. **Action**: Update Candidate Status form:
   - Select `select[name="status"]` $\rightarrow$ `Shortlisted (Schedule Interview)`
   - Recruiter Notes: `textarea[name="recruiter_notes"]` $\rightarrow$ `Candidate passed screening. Round 1 interview scheduled for 11 AM Monday.`
   - Click `button:has-text("Save & Notify Candidate")`.
6. **Assertions**:
   - Success alert appears: *"Candidate status updated to Shortlisted and candidate notified."*
   - Status badge updates to `Shortlisted 🎉`.

---

### Test Suite 4: Notification Loop Verification

#### Scenario 4.1: Student In-App Notification Check
1. **Action**: Log out as employer and log in as Student (`demo@demo.com` / `student123`).
2. **Assertions**:
   - Navbar notification bell displays an unread count badge (e.g. `1` or higher).
3. **Action**: Click the notification bell or navigate to `http://localhost/jobportal/student/notifications.php`.
4. **Assertions**:
   - Unread notification is listed with title: *"Application Status Updated: Shortlisted"*.
   - Notification message contains employer company name and job title.
5. **Action**: Navigate to `http://localhost/jobportal/student/applications.php`.
6. **Assertions**:
   - The application status badge now displays `Shortlisted` with warning/orange styling.
   - Click `button:has-text("Details")` on that application.
   - Recruiter note *"Candidate passed screening. Round 1 interview scheduled..."* is visible in the modal.

---

### Test Suite 5: Administrator Governance & Data Exports

#### Scenario 5.1: Admin Authentication & System KPIs
1. **Action**: Log out as student.
2. **Action**: Navigate to `http://localhost/jobportal/admin/login.php`.
3. **Action**: Fill admin credentials:
   - `input[name="email"]` $\rightarrow$ `admin@jobportal.test`
   - `input[name="password"]` $\rightarrow$ `admin123`
4. **Action**: Click `button:has-text("Log in as admin")`.
5. **Assertions**:
   - Redirects to `http://localhost/jobportal/admin/dashboard.php`.
   - KPI cards display numbers for: *Students*, *Employers*, *Total Jobs*, *Applications*, *Feedback*.
   - Recent system applications and recent job listings tables are populated.

#### Scenario 5.2: Admin Job Moderation
1. **Action**: Navigate to `http://localhost/jobportal/admin/jobs.php`.
2. **Assertions**:
   - Table lists all jobs across all employers with applicant counts.
   - Moderation actions: Featured star toggle, Close/Reopen toggle, Delete button.
3. **Action**: Click the star icon `button[title="Toggle Featured status"]` on any job.
4. **Assertion**: Success alert appears: *"Featured status updated."* Star icon becomes filled (`bi-star-fill`).

#### Scenario 5.3: User Account Moderation
1. **Action**: Navigate to `http://localhost/jobportal/admin/users.php`.
2. **Assertions**:
   - Tabs: *All Users*, *Students*, *Employers*, *Admins*.
   - Filter clicks (e.g. `Employers`) correctly filter table rows by role.
3. **Action**: Locate a non-admin user (e.g. employer) and click `button:has-text("Suspend")`.
4. **Assertions**:
   - User status badge changes to `Suspended`.
   - Button text toggles to `Activate`.
5. **Action**: Click `button:has-text("Activate")`.
6. **Assertion**: User status badge restores to `Active`.

#### Scenario 5.4: CSV Data Exports
1. **Action**: Navigate to `http://localhost/jobportal/admin/reports.php`.
2. **Assertions**:
   - Platform Totals table and Candidate Hiring Pipeline Funnel progress bars are displayed.
   - Export buttons are present: *Export Applications*, *Export Users*, *Export Feedback*.
3. **Action**: Trigger HTTP GET request to `http://localhost/jobportal/actions/export-csv.php?type=applications`.
4. **Assertions**:
   - Response status is `200 OK`.
   - Response header `Content-Type` contains `text/csv`.
   - CSV body includes columns: `Application ID`, `Job Title`, `Company`, `Candidate Name`, `Status`.
5. **Action**: Trigger HTTP GET request to `http://localhost/jobportal/actions/export-csv.php?type=users`.
6. **Assertions**:
   - Response status is `200 OK`.
   - CSV body contains registered user rows.

---

## 4. Automated Agent Error Recovery & Edge Cases

When scripting an automated browser agent, ensure it handles these edge cases:

1. **CSRF Token Validation**: Ensure form submissions include hidden field `input[name="csrf_token"]`.
2. **File Upload Selectors**: When attaching a resume, use standard file input element:
   ```javascript
   await page.setInputFiles('input[type="file"][name="resume"]', 'path/to/test_resume.pdf');
   ```
3. **Modal Backdrop Interactivity**: When submitting modals (`#applyModal`), wait for `networkidle` or modal element to hide before clicking the next element:
   ```javascript
   await page.waitForSelector('.modal-backdrop', { state: 'detached' });
   ```
4. **Session Clearing between Roles**: Always clear cookies or use separate browser contexts (`browser.new_context()`) when switching between Student, Employer, and Admin roles to prevent session contamination.

---

## 5. Verification Checklist

- [ ] Public landing page loads with real stats.
- [ ] Contact feedback persists to database.
- [ ] Student can register, log in, and view dashboard.
- [ ] Student can upload resume and edit skills.
- [ ] Student can filter jobs by type/category/experience.
- [ ] Student can save/bookmark a job.
- [ ] Student can submit application with resume and cover note.
- [ ] Employer can register, log in, and access recruiter hub.
- [ ] Employer can publish new jobs with custom deadlines and salaries.
- [ ] Employer can view applicants and download resumes.
- [ ] Employer can change candidate status to Shortlisted with notes.
- [ ] In-app notification bell notifies student of status change.
- [ ] Admin dashboard displays accurate platform counts.
- [ ] Admin can moderate jobs and users.
- [ ] Admin can download CSV exports for applications and users.
