<?php
$host = 'localhost';
$dbname = 'kopeladar_db';
$username = 'root'; // Change if your MySQL user differs
$password = ''; // Change if your MySQL password differs

session_start();

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // Set PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    if ($e->getCode() == 1049) {
        die("<strong>Database error:</strong> The database 'kopeladar_db' does not exist. Please import the database.sql file into phpMyAdmin.");
    } else {
        die("Connection failed: " . $e->getMessage());
    }
}

function isLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}
?>
