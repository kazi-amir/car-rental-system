<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        // Check if user exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        if ($stmt->rowCount() == 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                
                // Redirect based on role
                if ($user['role'] == 'admin') {
                    header("Location: ../admin/dashboard.php");
                } else {
                    header("Location: ../customer/dashboard.php");
                }
                exit();
            } else {
                // Invalid password
                header("Location: ../login.php?error=invalid_credentials");
                exit();
            }
        } else {
            // User not found
            header("Location: ../login.php?error=invalid_credentials");
            exit();
        }
    } catch(PDOException $e) {
        // Database error
        header("Location: ../login.php?error=database_error");
        exit();
    }
} else {
    // Invalid request method
    header("Location: ../login.php");
    exit();
}
?>
