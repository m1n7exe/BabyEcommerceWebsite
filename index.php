<?php
session_start();

// If the user is not logged in, redirect to the login page
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            text-align: center;
            padding: 50px;
        }
        .welcome-message {
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            border-radius: 5px;
            display: inline-block;
            font-size: 24px;
        }
        .logout-btn {
            display: inline-block;
            margin-top: 20px;
            background-color: #f44336;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
        }
        .logout-btn:hover {
            background-color: #d32f2f;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="welcome-message">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['email']); ?>!</h2>
            <p>You have successfully logged in.</p>
        </div>
        <br>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>

</body>
</html>
