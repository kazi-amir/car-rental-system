<?php
require_once 'session_manager.php';
startSecureSession();

$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

session_destroy();

session_regenerate_id(true);

header("Location: ../login.php");
exit();
?>
