<?php
// Start the session if not already started.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define site-wide variables.
$siteTitle = "Sustainability & Lifestyle Dashboard";
$logoURL = "assets/homepagephotos/baby.png"; // Replace with the path to your logo file

// Updated navigation items include a Feedback button.
$navItems = [
    "Home"         => "index.php",
    "Products"     => "productListing.php",
    "Categories"   => "categories.php",
    "Feedback"     => "feedback.php", // New Feedback link.
    "Shopping Cart" => "cart.php",
    "Profile"      => "profile.php"
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
    .header .logo img {
      height: 100px; /* Increased logo size */
    }
    .nav {
      display: flex;
      gap: 20px;
    }
    .nav a {
      color: white;
      text-decoration: none;
      font-weight: bold;
      padding: 8px 12px; /* Padding for spacing */
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
    .logo {
      display: flex;
      align-items: center;
    }
    .site-title {
      font-size: 24px;
      font-weight: bold;
      margin-left: 15px; /* Space between logo and text */
      color: white;
    }
  </style>
</head>
<body>
<div class="header">
    <div class="logo">
      <a href="index.php">
        <img src="<?php echo $logoURL; ?>" alt="Site Logo">
      </a>
      <span class="site-title">BabyBoo E-commerce</span> <!-- Added Here -->
    </div>
    <nav class="nav">
      <?php foreach ($navItems as $name => $url): ?>
        <a href="<?php echo $url; ?>"><?php echo $name; ?></a>
      <?php endforeach; ?>
      <form action="signin.php" method="POST" style="margin: 0;">
        <button type="submit" class="logout-button">Logout</button>
      </form>
    </nav>
  </div>
