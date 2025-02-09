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
        // Start a session for the user
        $_SESSION['user_id'] = $user['ShopperID'];  // Store user ID or other session data
        $_SESSION['email'] = $user['Email'];  // Store the email of the logged-in user
        $_SESSION['name'] = $user['Name'];  // Store the name of the logged-in user
        header('Location: index.php');  // Redirect to the homepage after login
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
