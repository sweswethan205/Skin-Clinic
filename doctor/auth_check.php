<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['doctor_token']) || $_SESSION['doctor_token'] !== 'authenticated_success_token') {
    header('Location: ../auth/doctor_login.php');
    exit;
}
