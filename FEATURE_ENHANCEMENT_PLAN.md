# Job Portal - Feature Enhancement & Architecture Roadmap

This document outlines a phased development plan to transform the existing lightweight prototype into a robust, full-featured Job & Internship Portal.

---

## 1. Current State vs. Target State Analysis

| Domain | Current Implementation | Planned Target Capability |
| :--- | :--- | :--- |
| **User Roles** | `student`, `admin` | `student`, `employer` / `recruiter`, `admin` |
| **Job Applications** | None (only "Save job" bookmarking) | Full application workflow: resume upload, cover letter, application status tracking |
| **Employer Experience** | Non-existent (admin posts directly) | Dedicated Employer Dashboard: post jobs, manage listings, review applicants, update statuses |
| **Resume Handling** | Plain text skills field in database | PDF/DOCX resume file upload, storage, preview, and download |
| **Job Search & Filters** | Simple client-side text filtering | Multi-faceted search: category, job type, location, experience level, salary range, pagination |
| **Admin Controls** | View stats, feedback, basic report list | Full CRUD for jobs & users, company verification, job approvals, CSV export, analytics charts |
| **Notifications** | None | In-app notification bell + email alerts on status changes |
| **Security & Forms** | Basic PDO prepared statements | CSRF token protection, strict file upload MIME validation, input sanitization |

---

## 2. Target Database Schema Enhancements

```mermaid
erDiagram
    USERS ||--o{ JOBS : "posts (as employer)"
    USERS ||--o{ APPLICATIONS : "submits (as student)"
    USERS ||--o{ SAVED_JOBS : "bookmarks"
    USERS ||--o{ NOTIFICATIONS : "receives"
    JOBS ||--o{ APPLICATIONS : "receives"
    JOBS ||--o{ SAVED_JOBS : "bookmarked in"
    CATEGORIES ||--o{ JOBS : "classifies"

    USERS {
        int id PK
        string name
        string email UK
        string password
        enum role "student, employer, admin"
        string company_name "null for student"
        string company_website
        string phone
        string education
        text skills
        string resume_path
        string avatar_path
        timestamp created_at
    }

    CATEGORIES {
        int id PK
        string name UK
        string slug UK
        string icon
    }

    JOBS {
        int id PK
        int employer_id FK
        int category_id FK
        string title
        string location
        enum job_type "Full-time, Part-time, Internship, Remote"
        enum experience_level "Fresher, 1-2 years, 3+ years"
        string salary
        text description
        text requirements
        date deadline
        enum status "draft, active, closed"
        boolean is_featured
        timestamp created_at
    }

    APPLICATIONS {
        int id PK
        int user_id FK
        int job_id FK
        string resume_path
        text cover_letter
        enum status "applied, reviewing, shortlisted, rejected, hired"
        text recruiter_notes
        timestamp applied_at
        timestamp updated_at
    }

    NOTIFICATIONS {
        int id PK
        int user_id FK
        string title
        string message
        string link
        boolean is_read
        timestamp created_at
    }
```

---

## 3. Phased Implementation Roadmap

### Phase 1: Core Job Application System (Priority: Critical)
> **Goal**: Turn the portal from a read-only job catalog into an active hiring platform where students can actually apply.

- [ ] **Database Migration**:
  - Create `applications` table with foreign keys to `users(id)` and `jobs(id)`.
  - Add `resume_path` and `avatar_path` columns to `users`.
- [ ] **Resume Upload & Storage**:
  - Secure upload handler in `student/profile.php` supporting `.pdf` and `.docx` (max 5MB).
  - Save files to `uploads/resumes/` with uniquely generated filenames.
- [ ] **Job Application Modal / Page**:
  - Add "Apply Now" button to job cards and detail view.
  - Option to apply with profile resume or upload a custom tailored resume.
  - Optional cover letter input field.
  - Prevent duplicate applications to the same job.
- [ ] **Student "My Applications" Dashboard**:
  - New page `student/applications.php` displaying applied jobs, date applied, and current status badges (`Pending`, `Reviewing`, `Shortlisted`, `Rejected`, `Hired`).

---

### Phase 2: Employer & Recruiter Portal (Priority: High)
> **Goal**: Allow companies to sign up, manage job openings, and review applicants.

- [ ] **Employer Authentication & Profile**:
  - Add `role = 'employer'` support during registration.
  - Employer profile setup: company name, company description, website URL, company logo.
- [ ] **Employer Dashboard (`employer/dashboard.php`)**:
  - Metrics: active jobs, total applications received, shortlisted candidates.
- [ ] **Job Posting & Management (`employer/manage-jobs.php`)**:
  - Form to create/edit jobs with deadline, experience level, salary range, and category.
  - Actions to edit, close, or delete listings.
- [ ] **Candidate Review Pipeline (`employer/applicants.php`)**:
  - Filter applicants by job listing.
  - View applicant profile, cover letter, and one-click resume download/preview.
  - Quick action buttons to update status: `Shortlist`, `Reject`, `Schedule Interview`, `Hire`.
  - Add private recruiter notes per candidate.

---

### Phase 3: Discovery, Multi-Faceted Filters & UI Upgrade (Priority: High)
> **Goal**: Help candidates easily locate relevant opportunities.

- [ ] **Job Details View (`job-detail.php`)**:
  - Dedicated page for each job with full job specification, company info, and social share links.
- [ ] **Advanced Search & Filtering**:
  - Server-side search filtering by keyword, location, job type, salary range, and experience.
  - Category badges (Technology, Marketing, Design, Finance, Healthcare).
  - Sorting: "Newest First", "Salary (High to Low)", "Expiring Soon".
- [ ] **Pagination**:
  - Standard pagination (9 or 12 cards per page) replacing single unpaginated lists.
- [ ] **Featured Jobs & Badges**:
  - Visual highlight for urgent or high-priority listings on the home page.

---

### Phase 4: Administrative Governance & Analytics (Priority: Medium)
> **Goal**: Provide administrators with complete oversight, reporting, and moderation controls.

- [ ] **User & Company Moderation (`admin/users.php`)**:
  - List all users with filter by role (`student`, `employer`, `admin`).
  - Activate, deactivate, or delete user accounts.
  - Verify new employer accounts.
- [ ] **Job Moderation (`admin/jobs.php`)**:
  - Moderate job postings before they go live (Optional approval toggle).
  - Feature / pin jobs to the homepage.
- [ ] **Analytics & Data Export (`admin/reports.php`)**:
  - Visual charts using Chart.js: Applications per month, top job categories, active student vs employer ratios.
  - One-click CSV export for applications, registered users, and feedback submissions.

---

### Phase 5: Communication, Alerts & Polish (Priority: Medium)
> **Goal**: Engagement retention, feedback loops, and production-grade security.

- [ ] **In-App Notifications (`student/notifications.php` / header bell icon)**:
  - Trigger notification when an application status updates.
  - Mark notifications as read / unread.
- [ ] **Email Alerts (Optional)**:
  - Transactional emails for account registration, application submitted, and status change alerts.
- [ ] **Security Hardening**:
  - CSRF tokens embedded in all forms (`actions/*.php`).
  - Strict file MIME-type validation using PHP `finfo` (prevent executing uploaded malicious files).
  - Rate limiting on login attempts.

---

## 4. Suggested Implementation Schedule

```mermaid
gantt
    title Job Portal Enhancement Roadmap
    dateFormat  YYYY-MM-DD
    section Phase 1: Applications
    Database Schema & Migrations       :p1_1, 2026-10-21, 2d
    Resume Upload & File Management   :p1_2, after p1_1, 2d
    Apply Flow & Student Applications :p1_3, after p1_2, 3d
    section Phase 2: Employer Portal
    Employer Auth & Profile           :p2_1, after p1_3, 2d
    Job Creation & Management         :p2_2, after p2_1, 3d
    Applicant Pipeline & Statuses     :p2_3, after p2_2, 3d
    section Phase 3: Search & Discovery
    Job Detail View & Categories      :p3_1, after p2_3, 2d
    Multi-Filter & Pagination         :p3_2, after p3_1, 3d
    section Phase 4: Admin Controls
    User/Job Moderation & Exports     :p4_1, after p3_2, 3d
    Analytics & Chart.js Integration  :p4_2, after p4_1, 2d
    section Phase 5: Alerts & Security
    In-App Notifications & CSRF       :p5_1, after p4_2, 3d
```

---

## 5. Next Immediate Action Steps

1. **Review and approve this plan**: Confirm if you want to include all 3 roles (`student`, `employer`, `admin`) or keep it between `student` and `admin`.
2. **Execute Phase 1 first**: Run database migration for `applications` and build the resume upload + apply functionality.
