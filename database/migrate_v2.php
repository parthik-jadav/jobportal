<?php
require __DIR__ . '/../config/database.php';

try {
    echo "Running migrations...\n";

    // 1. Upgrade users table
    $pdo->exec("
        ALTER TABLE users 
        MODIFY COLUMN role ENUM('student','employer','admin') NOT NULL DEFAULT 'student'
    ");
    
    // Check and add columns to users
    $userCols = $pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('company_name', $userCols)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN company_name VARCHAR(150) NULL AFTER role");
    }
    if (!in_array('company_website', $userCols)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN company_website VARCHAR(200) NULL AFTER company_name");
    }
    if (!in_array('company_about', $userCols)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN company_about TEXT NULL AFTER company_website");
    }
    if (!in_array('resume_path', $userCols)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN resume_path VARCHAR(255) NULL AFTER skills");
    }
    if (!in_array('avatar_path', $userCols)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN avatar_path VARCHAR(255) NULL AFTER resume_path");
    }
    if (!in_array('status', $userCols)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN status ENUM('active','inactive') NOT NULL DEFAULT 'active' AFTER avatar_path");
    }
    echo "Users table updated.\n";

    // 2. Upgrade jobs table
    $jobCols = $pdo->query("SHOW COLUMNS FROM jobs")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('employer_id', $jobCols)) {
        $pdo->exec("ALTER TABLE jobs ADD COLUMN employer_id INT UNSIGNED NULL AFTER id");
    }
    if (!in_array('category', $jobCols)) {
        $pdo->exec("ALTER TABLE jobs ADD COLUMN category VARCHAR(100) NOT NULL DEFAULT 'Technology' AFTER company");
    }
    if (!in_array('experience_level', $jobCols)) {
        $pdo->exec("ALTER TABLE jobs ADD COLUMN experience_level ENUM('Fresher','1-2 years','3-5 years','5+ years') NOT NULL DEFAULT 'Fresher' AFTER job_type");
    }
    if (!in_array('deadline', $jobCols)) {
        $pdo->exec("ALTER TABLE jobs ADD COLUMN deadline DATE NULL AFTER salary");
    }
    if (!in_array('status', $jobCols)) {
        $pdo->exec("ALTER TABLE jobs ADD COLUMN status ENUM('active','closed') NOT NULL DEFAULT 'active' AFTER deadline");
    }
    if (!in_array('is_featured', $jobCols)) {
        $pdo->exec("ALTER TABLE jobs ADD COLUMN is_featured TINYINT(1) NOT NULL DEFAULT 0 AFTER status");
    }
    echo "Jobs table updated.\n";

    // 3. Create applications table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS applications (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            job_id INT UNSIGNED NOT NULL,
            resume_path VARCHAR(255) NOT NULL,
            cover_letter TEXT NULL,
            status ENUM('applied','reviewing','shortlisted','rejected','hired') NOT NULL DEFAULT 'applied',
            recruiter_notes TEXT NULL,
            applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_user_job (user_id, job_id),
            CONSTRAINT app_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            CONSTRAINT app_job_fk FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Applications table created/verified.\n";

    // 4. Create notifications table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS notifications (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            title VARCHAR(200) NOT NULL,
            message TEXT NOT NULL,
            link VARCHAR(255) NULL,
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT notif_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Notifications table created/verified.\n";

    // 5. Seed default employer if not exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute(['employer@jobportal.test']);
    $employer = $stmt->fetch();
    $employerId = null;

    if (!$employer) {
        $employerPass = password_hash('employer123', PASSWORD_DEFAULT);
        $insertEmp = $pdo->prepare("
            INSERT INTO users (name, email, password, role, company_name, company_website, company_about, phone, status)
            VALUES (?, ?, ?, 'employer', ?, ?, ?, ?, 'active')
        ");
        $insertEmp->execute([
            'Acme Recruiter',
            'employer@jobportal.test',
            $employerPass,
            'Acme Corporation',
            'https://acmecorp.example.com',
            'Leading innovator in web technologies, software solutions, and talent development.',
            '+91 98765 43210'
        ]);
        $employerId = $pdo->lastInsertId();
        echo "Default employer created (employer@jobportal.test / employer123).\n";
    } else {
        $employerId = $employer['id'];
        echo "Default employer already exists.\n";
    }

    // Link existing jobs without employer to this employer and enrich metadata
    if ($employerId) {
        $pdo->prepare("UPDATE jobs SET employer_id = ? WHERE employer_id IS NULL")->execute([$employerId]);
        $pdo->exec("UPDATE jobs SET category = 'Engineering', experience_level = 'Fresher', deadline = DATE_ADD(CURDATE(), INTERVAL 30 DAY) WHERE category = 'General' OR category = 'Technology'");
    }

    echo "Migration completed successfully!\n";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
