<?php
// Database configuration template
$host = 'localhost';
$port = '3306';
$dbname = 'artado_docs';
$username = 'root';
$password = '';

try {
    // Initial connection to create DB if needed
    $pdo_temp = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $username, $password);
    $pdo_temp->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo_temp->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    // Main connection
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed. Please check your configuration in includes/db.php");
}
?>
