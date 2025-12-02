<?php
session_start();
$_SESSION['user_id'] = 1; // Assuming ID 1 is a student (mathuja) based on debug output
$_SESSION['role'] = 'student';
$_SESSION['username'] = 'mathuja';
header('Location: student_dashboard.php');
?>
