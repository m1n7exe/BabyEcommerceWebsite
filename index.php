<?php
session_start();
require 'db_connection.php';
include_once("header.php");

// Fetch products on offer with valid offer dates
$query = "SELECT * FROM Product WHERE Offered = 1 AND OfferStartDate <= CURDATE() AND OfferEndDate >= CURDATE()";
$result = mysqli_query($conn, $query);
$products = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Baby E-Commerce</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #ffdee9;
            color: #333;
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
    </style>
</head>
<body>
    <div class="header">
        <h1>Welcome to Baby E-Commerce</h1>
        <p>Your one-stop shop for baby essentials</p>
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
