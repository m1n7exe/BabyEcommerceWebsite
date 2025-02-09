<?php
session_start();
require 'db_connection.php';

// Get form inputs
$email = $_POST['email'];
$password = $_POST['password'];

// Prepare SQL statement to get the user by email
$sql = "SELECT * FROM Shopper WHERE Email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

// Check if the user exists
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
    // Compare the plain text password with the stored password
    if ($password == $user['Password']) {
        // Set the session variable using the same key as expected in profile.php
        $_SESSION['ShopperID'] = $user['ShopperID'];
        $_SESSION['email'] = $user['Email'];
        $_SESSION['name'] = $user['Name'];
        
        // Redirect to the homepage (or any landing page)
        header('Location: index.php');
        exit;
    } else {
        echo "Invalid password!";
    }
} else {
    echo "No user found with that email!";
}

// Close statement and connection
$stmt->close();
$conn->close();
?>
