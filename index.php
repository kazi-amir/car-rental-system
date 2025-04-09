<?php
session_start();
require_once 'includes/db_connect.php';

// Redirect to appropriate dashboard based on role
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: customer/dashboard.php");
    }
    exit();
} else {
    header("Location: login.php");
    exit();
}
?>
