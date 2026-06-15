<?php
session_start();

if(!isset($_SESSION['admin_id']) || !isset($_SESSION['professor_id']) || !isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}
?>