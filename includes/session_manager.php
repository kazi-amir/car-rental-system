<?php
function startSecureSession() {
    if (!headers_sent() && session_status() === PHP_SESSION_NONE) {
        session_start([
            'cookie_lifetime' => 86400,
            'cookie_secure' => true,
            'cookie_httponly' => true,
            'use_strict_mode' => true,
            'gc_maxlifetime' => 1440
        ]);
        return true;
    }
    return false;
}
?>
