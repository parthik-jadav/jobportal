CREATE DATABASE IF NOT EXISTS jobportal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE jobportal;

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('student','admin') NOT NULL DEFAULT 'student',
  phone VARCHAR(30) DEFAULT '',
  education VARCHAR(150) DEFAULT '',
  skills TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE jobs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  company VARCHAR(150) NOT NULL,
  location VARCHAR(150) NOT NULL,
  description TEXT NOT NULL,
  requirements TEXT NOT NULL,
  salary VARCHAR(100) DEFAULT 'Not specified',
  job_type ENUM('Internship','Full-time','Part-time','Remote') NOT NULL DEFAULT 'Internship',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE saved_jobs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  job_id INT UNSIGNED NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_saved_job(user_id,job_id),
  CONSTRAINT saved_user_fk FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT saved_job_fk FOREIGN KEY(job_id) REFERENCES jobs(id) ON DELETE CASCADE
);
CREATE TABLE feedback (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL,
  message TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default administrator login: admin@jobportal.test / admin123
INSERT INTO users(name,email,password,role) VALUES
('Portal Administrator','admin@jobportal.test','$2y$10$dg.0cHSGUkF1HlIBfp7Fve7iic/JnLU8Uu93bYnbjHQI9/sDMdp1y','admin');
INSERT INTO jobs(title,company,location,description,requirements,salary,job_type) VALUES
('Frontend Developer Intern','PixelCraft Studio','Mumbai, India','Help build responsive web interfaces and work with a friendly product team.','HTML, CSS, JavaScript, basic Git','₹12,000/month','Internship'),
('Software Engineer Trainee','TechNova Solutions','Bengaluru, India','Join our graduate training program and contribute to real web applications.','Programming fundamentals, problem solving, SQL basics','₹4.5 LPA','Full-time'),
('Marketing Intern','GrowthSpark','Remote','Support social media campaigns, content planning, and performance reporting.','Good communication, social media familiarity, MS Office','₹8,000/month','Remote');
