<?php require '../config/database.php'; require '../includes/auth.php'; require_student(); $job_id=(int)($_POST['job_id']??0);
if($job_id){$pdo->prepare('INSERT IGNORE INTO saved_jobs(user_id,job_id) VALUES(?,?)')->execute([$_SESSION['user']['id'],$job_id]);flash('success','Job saved to your list.');} header('Location: ../student/jobs.php');
