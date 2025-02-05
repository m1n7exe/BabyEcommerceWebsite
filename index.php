<?php
session_start();
require 'db_connection.php';
include_once("header.php");

// Fetch products on offer
$query = "SELECT * FROM Product WHERE Offered = 1";
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
            background-color: #fce4ec;
        }
        .header {
            background-color: #f8bbd0;
            color: #4a148c;
            padding: 20px;
            text-align: center;
        }
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 15px;
        }
        .category-list, .product-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }
        .category-item, .product-item {
            background-color: #e1bee7;
            border: 1px solid #ce93d8;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            width: 250px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .category-item img, .product-item img {
            max-width: 100%;
            border-radius: 5px;
        }
        .category-item h3, .product-item h3 {
            margin: 10px 0;
            color: #4a148c;
        }
        .category-item a, .product-item a {
            display: inline-block;
            padding: 10px 15px;
            background-color: #ba68c8;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
        }
        .category-item a:hover, .product-item a:hover {
            background-color: #ab47bc;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Welcome to Baby E-Commerce</h1>
        <p>Your one-stop shop for baby essentials</p>
    </div>

    <div class="container">
        <h2>Explore Categories</h2>
        <div class="category-list">
            <?php
            // Fetch categories from the database
            $sql = "SELECT * FROM Category ORDER BY CatName ASC";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "
                        <div class='category-item'>
                            <img src='data:image/jpeg;base64," . base64_encode($row['CatImage']) . "' alt='" . htmlspecialchars($row['CatName']) . "'>
                            <h3>" . htmlspecialchars($row['CatName']) . "</h3>
                            <p>" . htmlspecialchars($row['CatDesc']) . "</p>
                            <a href='products.php?category_id=" . $row['CategoryID'] . "'>Shop Now</a>
                        </div>
                    ";
                }
            } else {
                echo "<p>No categories available.</p>";
            }
            ?>
        </div>

        <h2>Products on Offer</h2>
        <div class="product-container">
            <?php if (!empty($products)) : ?>
                <?php foreach ($products as $product) : ?>
                    <div class="product-item">
                        <img src="assets/ECAD2024Oct_Assignment_1_Input_Files(1)/ECAD2024Oct_Assignment_1_Input_Files/Images/Products/<?php echo htmlspecialchars($product['ProductImage']); ?>" alt="<?php echo htmlspecialchars($product['ProductTitle']); ?>">
                        <h3><?php echo htmlspecialchars($product['ProductTitle']); ?></h3>
                        <p><s>$<?php echo number_format($product['Price'], 2); ?></s> $<?php echo number_format($product['OfferedPrice'], 2); ?></p>
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

