<?php
require_once 'session_manager.php';
startSecureSession();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate password match
    if ($password !== $confirm_password) {
        header("Location: ../register.php?error=password_mismatch");
        exit();
    }

    try {
        // Check if username exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            header("Location: ../register.php?error=username_taken");
            exit();
        }

        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            header("Location: ../register.php?error=email_taken");
            exit();
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) 
                               VALUES (:username, :email, :password, 'customer')");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->execute();

        // Registration successful
        header("Location: ../login.php?success=registered");
        exit();

    } catch(PDOException $e) {
        // Database error
        header("Location: ../register.php?error=database_error");
        exit();
    }
} else {
    // Invalid request method
    header("Location: ../register.php");
    exit();
}
?>
