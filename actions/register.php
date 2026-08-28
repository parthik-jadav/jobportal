<?php require '../config/database.php'; require '../includes/auth.php';
$name=trim($_POST['name']??''); $email=filter_var($_POST['email']??'',FILTER_VALIDATE_EMAIL); $password=$_POST['password']??'';
if(!$name||!$email||strlen($password)<6){ flash('error','Please enter valid registration details.'); header('Location: ../register.php'); exit; }
try { $stmt=$pdo->prepare('INSERT INTO users(name,email,password,role) VALUES(?,?,?,"student")'); $stmt->execute([$name,$email,password_hash($password,PASSWORD_DEFAULT)]); flash('success','Account created. Please log in.'); } catch(PDOException $e){ flash('error','That email address is already registered.'); }
header('Location: ../login.php');
