<?php
// Database connection
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'ecommerce_payment';

$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Start the session
session_start();
?>
