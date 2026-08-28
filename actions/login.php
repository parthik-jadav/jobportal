<?php require '../config/database.php'; require '../includes/auth.php';
$stmt=$pdo->prepare('SELECT * FROM users WHERE email=? LIMIT 1'); $stmt->execute([trim($_POST['email']??'')]); $user=$stmt->fetch();
if($user && password_verify($_POST['password']??'', $user['password'])){ session_regenerate_id(true); $_SESSION['user']=['id'=>$user['id'],'name'=>$user['name'],'email'=>$user['email'],'role'=>$user['role']]; header('Location: ../'.($user['role']==='admin'?'admin/dashboard.php':'student/dashboard.php')); exit; }
flash('error','Invalid email or password.'); header('Location: ../login.php');
