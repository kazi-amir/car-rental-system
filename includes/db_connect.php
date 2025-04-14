<?php
$host = 'localhost';
$dbname = 'car_rental';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    file_put_contents('debug.log', "DB Connection Error: " . $e->getMessage() . "\n", FILE_APPEND);
    die("Database connection failed. Please try again later.");
}
?>
