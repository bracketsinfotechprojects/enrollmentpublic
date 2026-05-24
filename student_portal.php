<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['user_id'] === ''){
    header('Location: student_login.php');
    exit;
}
if(empty($_SESSION['user_type']) || $_SESSION['user_type'] !== 'student'){
    header('Location: student_login.php');
    exit;
}
header('Location: student_enquiry_form.php');
exit;
