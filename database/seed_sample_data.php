<?php
require __DIR__ . '/../config/database.php';

// 1. Create a minimal PDF resume
$pdf = "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n3 0 obj<</Type/Page/MediaBox[0 0 612 792]/Parent 2 0 R/Resources<<>>/Contents 4 0 R>>endobj\n4 0 obj<</Length 44>>stream\nBT /F1 18 Tf 50 700 Td (Demo Student Resume) Tj ET\nendstream\nendobj\nxref\n0 5\n0000000000 65535 f\n0000000009 00000 n\n0000000058 00000 n\n0000000115 00000 n\n0000000216 00000 n\ntrailer<</Size 5/Root 1 0 R>>\nstartxref\n310\n%%EOF";
$resumeFile = __DIR__ . '/../uploads/resumes/sample_student_resume.pdf';
file_put_contents($resumeFile, $pdf);
echo "Created sample resume: $resumeFile\n";

// 2. Update demo student
$student = $pdo->query("SELECT id FROM users WHERE email = 'demo@demo.com'")->fetch();
if ($student) {
    $pdo->prepare("
        UPDATE users 
        SET resume_path = 'uploads/resumes/sample_student_resume.pdf',
            education = 'B.Tech in Computer Science & Engineering (2025)',
            skills = 'PHP, MySQL, JavaScript, HTML5, CSS3, Git, Bootstrap',
            phone = '+91 98111 22334',
            status = 'active'
        WHERE id = ?
    ")->execute([$student['id']]);
    echo "Updated demo student profile.\n";

    // 3. Insert sample application if none exists
    $job = $pdo->query("SELECT id FROM jobs LIMIT 1")->fetch();
    if ($job) {
        $checkApp = $pdo->prepare("SELECT id FROM applications WHERE user_id = ? AND job_id = ?");
        $checkApp->execute([$student['id'], $job['id']]);
        if (!$checkApp->fetch()) {
            $pdo->prepare("
                INSERT INTO applications (user_id, job_id, resume_path, cover_letter, status, recruiter_notes)
                VALUES (?, ?, 'uploads/resumes/sample_student_resume.pdf', 'I am passionate about full-stack web development and excited about the opportunity to contribute to real-world software projects.', 'shortlisted', 'Strong technical fundamentals and enthusiastic attitude. Scheduled interview.')
            ")->execute([$student['id'], $job['id']]);
            echo "Seeded sample application (Shortlisted).\n";

            // Add notification
            $pdo->prepare("
                INSERT INTO notifications (user_id, title, message, link)
                VALUES (?, 'Application Status: Shortlisted! 🎉', 'Congratulations! PixelCraft Studio has shortlisted your application for Frontend Developer Intern.', 'student/applications.php')
            ")->execute([$student['id']]);
            echo "Seeded sample notification for student.\n";
        }
    }
}
echo "Sample seeding complete!\n";
