<?php
session_start();
require 'db_connection.php';
include_once("header.php");

// Ensure user is logged in
if (!isset($_SESSION['ShopperID'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['ShopperID'];

// Fetch user name if not already stored in session
if (!isset($_SESSION['Name'])) {
    $user_query = "SELECT Name FROM Shopper WHERE ShopperID = ?";
    $stmt = $conn->prepare($user_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($user = $result->fetch_assoc()) {
        $_SESSION['Name'] = $user['Name']; // Store name in session
    }
    $stmt->close();
}

// Get the user's name from the session
$shopper_name = $_SESSION['Name'] ?? 'Guest';

// Fetch products on offer with valid offer dates
$query = "SELECT * FROM Product WHERE Offered = 1 AND OfferStartDate <= CURDATE() AND OfferEndDate >= CURDATE()";
$result = mysqli_query($conn, $query);
$products = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Ensure session cart is initialized as an array
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
if (!isset($_SESSION['cart'][$user_id]) || !is_array($_SESSION['cart'][$user_id])) {
    $_SESSION['cart'][$user_id] = [];
}

// Count total items in the cart
$total_items_in_cart = 0;
if (!empty($_SESSION['cart'][$user_id])) {
    foreach ($_SESSION['cart'][$user_id] as $item) {
        if (is_array($item) && isset($item['quantity'])) {
            $total_items_in_cart += $item['quantity'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Baby E-Commerce</title>
    <style>
        .header-container {
            text-align: center;
            font-size: 22px; /* Increase font size */
            font-weight: bold;
            padding: 15px;
            color: black;
            margin-bottom: 20px;
        }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #ffdee9;
            color: #333;
        }
        h2 {
            text-align: center;
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 15px;
        }
        .product-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            align-items: stretch;
        }
        .product-item {
            background-color: white;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            width: 250px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 420px;
        }
        .product-item:hover {
            transform: translateY(-5px);
        }
        .product-item img {
            max-width: 100%;
            border-radius: 5px;
        }
        .product-item h3 {
            margin: 10px 0;
            color: #d63384;
            font-size: 18px;
            font-weight: bold;
        }
        .product-item p {
            font-size: 14px;
            color: #666;
        }
        .offer-dates {
            font-size: 12px;
            color: #888;
            background: #ffe4e1;
            padding: 5px;
            border-radius: 5px;
            margin: auto 0 10px 0;
        }
        .product-item a {
            display: inline-block;
            padding: 10px 15px;
            background-color: #ff85a2;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin-top: 10px;
        }
        .product-item a:hover {
            background-color: #ff5277;
        }
        .cart-info {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-top: 10px;
        }
        .cart-info span {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Welcome Message -->
    <div class="header-container">
    Welcome, <span><?php echo htmlspecialchars($shopper_name); ?></span>!
    </div>
    <div class="cart-info">
    You have <span><?php echo $total_items_in_cart; ?></span> item(s) in your cart.
    </div>
    <div class="container">
        <h2>Products on Offer</h2>
        <div class="product-container">
            <?php if (!empty($products)) : ?>
                <?php foreach ($products as $product) : ?>
                    <div class="product-item">
                        <img src="assets/ECAD2024Oct_Assignment_1_Input_Files(1)/ECAD2024Oct_Assignment_1_Input_Files/Images/Products/<?php echo htmlspecialchars($product['ProductImage']); ?>" alt="<?php echo htmlspecialchars($product['ProductTitle']); ?>">
                        <h3><?php echo htmlspecialchars($product['ProductTitle']); ?></h3>
                        <p><s>$<?php echo number_format($product['Price'], 2); ?></s> <span style="color: #d63384; font-weight: bold;">$<?php echo number_format($product['OfferedPrice'], 2); ?></span></p>
                        <p class="offer-dates">Offer valid from <?php echo date("M d, Y", strtotime($product['OfferStartDate'])); ?> to <?php echo date("M d, Y", strtotime($product['OfferEndDate'])); ?></p>
                        <a href="productpage.php?ProductID=<?php echo urlencode($product['ProductID']); ?>" class="btn">View Details</a>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <p>No products are on offer at the moment.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>