CREATE DATABASE IF NOT EXISTS jobportal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE jobportal;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('student','employer','admin') NOT NULL DEFAULT 'student',
  company_name VARCHAR(150) DEFAULT NULL,
  company_website VARCHAR(200) DEFAULT NULL,
  company_about TEXT DEFAULT NULL,
  phone VARCHAR(30) DEFAULT '',
  education VARCHAR(150) DEFAULT '',
  skills TEXT DEFAULT NULL,
  resume_path VARCHAR(255) DEFAULT NULL,
  avatar_path VARCHAR(255) DEFAULT NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS jobs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employer_id INT UNSIGNED DEFAULT NULL,
  title VARCHAR(150) NOT NULL,
  company VARCHAR(150) NOT NULL,
  category VARCHAR(100) NOT NULL DEFAULT 'Technology',
  location VARCHAR(150) NOT NULL,
  description TEXT NOT NULL,
  requirements TEXT NOT NULL,
  salary VARCHAR(100) DEFAULT 'Not specified',
  job_type ENUM('Internship','Full-time','Part-time','Remote') NOT NULL DEFAULT 'Internship',
  experience_level ENUM('Fresher','1-2 years','3-5 years','5+ years') NOT NULL DEFAULT 'Fresher',
  deadline DATE DEFAULT NULL,
  status ENUM('active','closed') NOT NULL DEFAULT 'active',
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT jobs_employer_fk FOREIGN KEY(employer_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS saved_jobs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  job_id INT UNSIGNED NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_saved_job(user_id,job_id),
  CONSTRAINT saved_user_fk FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT saved_job_fk FOREIGN KEY(job_id) REFERENCES jobs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS applications (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  job_id INT UNSIGNED NOT NULL,
  resume_path VARCHAR(255) NOT NULL,
  cover_letter TEXT DEFAULT NULL,
  status ENUM('applied','reviewing','shortlisted','rejected','hired') NOT NULL DEFAULT 'applied',
  recruiter_notes TEXT DEFAULT NULL,
  applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY unique_app_user_job(user_id,job_id),
  CONSTRAINT app_user_fk FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT app_job_fk FOREIGN KEY(job_id) REFERENCES jobs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS feedback (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL,
  message TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS notifications (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  title VARCHAR(200) NOT NULL,
  message TEXT NOT NULL,
  link VARCHAR(255) DEFAULT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT notif_user_fk FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default accounts
-- Admin: admin@jobportal.test / admin123
INSERT INTO users(name,email,password,role,status) VALUES
('Portal Administrator','admin@jobportal.test','$2y$10$dg.0cHSGUkF1HlIBfp7Fve7iic/JnLU8Uu93bYnbjHQI9/sDMdp1y','admin','active')
ON DUPLICATE KEY UPDATE name=name;

-- Demo Employer: employer@jobportal.test / employer123
INSERT INTO users(name,email,password,role,company_name,company_website,company_about,phone,status) VALUES
('Acme Recruiter','employer@jobportal.test','$2y$10$f6B0v6/gG2uWzWp5z6B7heGkWk1b8E1R9G2yC4S5g7Y9d0H1e2J3K','employer','Acme Corporation','https://acme.example.com','A premier technology engineering and digital solutions company.','+91 98765 43210','active')
ON DUPLICATE KEY UPDATE name=name;

-- Demo Student: demo@demo.com / student123
INSERT INTO users(name,email,password,role,phone,education,skills,status) VALUES
('Demo Student','demo@demo.com','$2y$10$wE99X0Yx8gPz/oV7d/jX7.3dZ8b8vD9fNl1/Q2k7/p9O6V9y5n0iK','student','+91 98111 22334','B.Tech Computer Science (2025)','PHP, JavaScript, HTML, CSS, MySQL, Git','active')
ON DUPLICATE KEY UPDATE name=name;

-- Sample Jobs
INSERT INTO jobs(title,company,category,location,description,requirements,salary,job_type,experience_level,deadline,status,is_featured) VALUES
('Frontend Developer Intern','PixelCraft Studio','Design & Creative','Mumbai, India','Help build responsive web interfaces and work with a friendly product team.','HTML, CSS, JavaScript, basic Git','₹12,000/month','Internship','Fresher',DATE_ADD(CURDATE(), INTERVAL 30 DAY),'active',1),
('Software Engineer Trainee','TechNova Solutions','Engineering','Bengaluru, India','Join our graduate training program and contribute to real web applications.','Programming fundamentals, problem solving, SQL basics','₹4.5 LPA','Full-time','Fresher',DATE_ADD(CURDATE(), INTERVAL 45 DAY),'active',1),
('Marketing Intern','GrowthSpark','Marketing','Remote','Support social media campaigns, content planning, and performance reporting.','Good communication, social media familiarity, MS Office','₹8,000/month','Remote','Fresher',DATE_ADD(CURDATE(), INTERVAL 20 DAY),'active',0),
('Backend PHP Developer','CloudScale Systems','Engineering','Pune, India','Develop robust backend APIs and maintain scalable microservices.','PHP 8+, MySQL, REST APIs, Git','₹6 LPA','Full-time','1-2 years',DATE_ADD(CURDATE(), INTERVAL 40 DAY),'active',1);
