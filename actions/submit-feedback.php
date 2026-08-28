<?php require '../config/database.php'; require '../includes/auth.php';
$name=trim($_POST['name']??'');$email=filter_var($_POST['email']??'',FILTER_VALIDATE_EMAIL);$message=trim($_POST['message']??'');
if($name&&$email&&$message){$pdo->prepare('INSERT INTO feedback(name,email,message) VALUES(?,?,?)')->execute([$name,$email,$message]);flash('success','Thank you—your feedback has been submitted.');}else flash('error','Please complete every field.'); header('Location: ../contact.php');
