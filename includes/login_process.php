<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        if ($stmt->rowCount() == 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (password_verify($password, $user['password'])) {

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                
                if ($user['role'] == 'admin') {
                    header("Location: ../admin/dashboard.php");
                } else {
                    header("Location: ../customer/dashboard.php");
                }
                exit();
            } else {
                
                header("Location: ../login.php?error=invalid_credentials");
                exit();
            }
        } else {

            header("Location: ../login.php?error=invalid_credentials");
            exit();
        }
    } catch(PDOException $e) {
        
        header("Location: ../login.php?error=database_error");
        exit();
    }
} else {
    
    header("Location: ../login.php");
    exit();
}
?>
