# Job Portal

A modern PHP & MySQL job and internship portal with role-based access control for **Students**, **Employers / Recruiters**, and **Administrators**.

---

## 🌟 Key Features

### 🎓 For Students
- **Registration & Profile**: Complete profile with education, phone, skills, and completeness score.
- **Resume Management**: Upload, store, and preview `.pdf`, `.doc`, and `.docx` resumes.
- **One-Click Applications**: Apply instantly using your profile resume or upload tailored resumes with custom cover notes.
- **Application Tracker**: Real-time hiring pipeline tracking (`Applied`, `Reviewing`, `Shortlisted 🎉`, `Hired 🏆`, `Not Selected`).
- **Discovery & Search**: Multi-filter by keyword, job type, category, experience level, and sort options.
- **Saved Opportunities**: Bookmark openings to review and apply later.
- **Notification Center**: In-app notifications when recruiters update application statuses.

### 🏢 For Employers & Recruiters
- **Employer Registration**: Sign up with company details, website, and industry description.
- **Recruiter Dashboard**: Quick stats on active postings, total applicants, shortlisted candidates, and hires.
- **Job Posting & Management**: Publish, edit, close, feature, and delete listings with deadlines and salary info.
- **Candidate Pipeline**: Filter candidates by opening and status, preview/download resumes, review cover letters, and leave recruiter notes.
- **Automated Candidate Alerts**: Changing candidate status automatically delivers notifications to candidates.

### 🛡️ For Administrators
- **Executive Dashboard**: System-wide KPIs across students, employers, jobs, applications, and feedback.
- **Job Moderation**: Oversight of all postings across companies with featured badges and status controls.
- **User Account Management**: Filter users by role, activate or suspend accounts, and manage registrations.
- **Reporting & Data Export**: One-click CSV exports for applications, users, and feedback with candidate funnel analytics.

---

## 🔑 Default Accounts

| Role | Email | Password | Access Area |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin@jobportal.test` | `admin123` | [`admin/dashboard.php`](file:///D:/laragon/www/jobportal/admin/dashboard.php) |
| **Employer** | `employer@jobportal.test` | `employer123` | [`employer/dashboard.php`](file:///D:/laragon/www/jobportal/employer/dashboard.php) |
| **Student** | `demo@demo.com` | `student123` | [`student/dashboard.php`](file:///D:/laragon/www/jobportal/student/dashboard.php) |

---

## 📁 Project Structure

```text
jobportal/
├── actions/                  # Form handlers & business logic
│   ├── apply-job.php         # Application submission & notifications
│   ├── delete-job.php        # Job deletion
│   ├── delete-user.php       # User moderation
│   ├── edit-job.php          # Job updates
│   ├── export-csv.php        # One-click CSV exporter
│   ├── login.php             # Unified 3-role authentication
│   ├── mark-notifications.php# In-app notification read toggles
│   ├── post-job.php          # Job publishing
│   ├── register.php          # Student & Employer registration
│   ├── remove-job.php        # Bookmark removal
│   ├── save-job.php          # Opportunity bookmarking
│   ├── toggle-featured.php   # Admin featured badge toggle
│   ├── toggle-job-status.php # Open / Close job status
│   ├── toggle-user-status.php# Activate / Suspend user accounts
│   ├── update-applicant-status.php # Status pipeline & student alerts
│   ├── update-employer-profile.php # Company profile updates
│   ├── update-profile.php    # Student profile & resume upload
│   └── submit-feedback.php   # Contact feedback
├── admin/                    # Admin portal
│   ├── dashboard.php         # Metric KPIs & recent platform activity
│   ├── feedback.php          # Feedback inquiry inbox
│   ├── jobs.php              # Full listing moderation
│   ├── login.php             # Admin sign-in
│   ├── logout.php            # Session termination
│   ├── reports.php           # Funnel analytics & data export
│   └── users.php             # User moderation & role filters
├── config/                   # Configuration files
│   └── database.php          # PDO database connection
├── database/                 # Schema & migrations
│   ├── jobportal.sql         # Complete production schema & seeds
│   ├── migrate_v2.php        # Migration script
│   └── seed_sample_data.php  # Sample applicant & resume seeder
├── employer/                 # Employer & recruiter portal
│   ├── applicants.php        # Candidate review & hiring pipeline
│   ├── dashboard.php         # Recruiter metrics & recent candidates
│   ├── edit-job.php          # Edit job openings
│   ├── manage-jobs.php       # Listing management & status toggles
│   ├── post-job.php          # New opening submission
│   └── profile.php           # Company profile & details
├── includes/                 # Shared templates & authentication
│   ├── auth.php              # Role guards, CSRF, flash, notifications
│   ├── footer.php            # Shared footer & scripts
│   └── header.php            # Dynamic navbar with role-aware items
├── screenshots/              # Automated full-page screenshot gallery
├── student/                  # Student portal
│   ├── applications.php      # Live application tracking
│   ├── dashboard.php         # Student overview & recommendations
│   ├── jobs.php              # Search, multi-filters & apply modal
│   ├── logout.php            # Student sign-out
│   ├── notifications.php     # In-app notification center
│   ├── profile.php           # Profile & resume upload
│   └── saved-jobs.php        # Bookmarked jobs
├── uploads/                  # Uploaded files
│   └── resumes/              # Candidate PDF/DOCX resumes
├── capture_all.py            # Automated Playwright screenshot script
├── FEATURE_ENHANCEMENT_PLAN.md# Architectural enhancement plan
├── index.php                 # Home landing page with live stats
├── login.php                 # Unified login
└── register.php              # Dual-role registration
```

---

## 🚀 Running Screenshots Automation

To capture full-page screenshots of all 23 views into [`screenshots/`](file:///D:/laragon/www/jobportal/screenshots):

```bash
python capture_all.py
```
