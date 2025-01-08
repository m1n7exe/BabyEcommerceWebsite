<?php
// Database connection parameters
$servername = "localhost";  // or your server IP
$username = "root";         // Your MySQL username (default for XAMPP is 'root')
$password = "";             // Your MySQL password (default for XAMPP is an empty string)
$dbname = "babyecommerce"; // The name of your database

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Optional: Set the character set for the connection (useful for non-ASCII characters)
$conn->set_charset("utf8");
?>
