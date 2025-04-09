<?php
// Common head section for all pages
require_once 'session_manager.php';
startSecureSession();
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $pageTitle ?? 'Car Rental System'; ?></title>
<link rel="stylesheet" href="/assets/css/main.css">
<script src="/assets/js/main.js" defer></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
