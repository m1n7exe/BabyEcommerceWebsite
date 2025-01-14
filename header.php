<?php
// Start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define site-wide variables
$siteTitle = "Sustainability & Lifestyle Dashboard";
$logoURL = "logo.png"; // Replace with the path to your logo file
$navItems = [
    "Home" => "index.php",
    "Products" => "dashboard.php",
    "Categories" => "settings.php",
    "ShoppingCart" => "shoppingCart.php" // Shopping cart icon placement
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $siteTitle; ?></title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to your CSS file -->
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #ff85a2; /* Soft pink background */
            color: white;
            padding: 15px 30px;
        }

        .header img {
            height: 40px;
        }

        .nav {
            display: flex;
            gap: 20px;
        }

        .nav a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            padding: 8px 12px; /* Add padding for spacing */
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .nav a:hover {
            background-color: #ffb3c6; /* Lighter pink on hover */
        }

        .logout-button {
            color: white;
            background-color: #ff4d6d; /* Distinctive red-pink for logout */
            border: none;
            padding: 8px 15px;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .logout-button:hover {
            background-color: #ff1f45; /* Darker shade for hover */
        }
    </style>
</head>
<body>
<div class="header">
    <div class="logo">
        <img src="<?php echo $logoURL; ?>" alt="Site Logo">
    </div>
    <nav class="nav">
        <?php foreach ($navItems as $name => $url): ?>
            <a href="<?php echo $url; ?>"><?php echo $name; ?></a>
        <?php endforeach; ?>
        <form action="logout.php" method="POST" style="margin: 0;">
            <button type="submit" class="logout-button">Logout</button>
        </form>
    </nav>
</div>
</body>
</html>
