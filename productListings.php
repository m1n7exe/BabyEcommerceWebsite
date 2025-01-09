<!DOCTYPE html>
<html>
<head>
    <title>Product Listing</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }
        h1 {
            text-align: center;
            color: #4CAF50;
            margin: 20px 0;
        }
        .product-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            padding: 20px;
        }
        .product-card {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            width: 250px;
            margin: 10px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .product-card:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }
        .product-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-bottom: 1px solid #ddd;
        }
        .product-card h2 {
            font-size: 18px;
            color: #333;
            margin: 15px 0;
        }
        .product-card p {
            font-size: 14px;
            color: #666;
            margin: 10px 15px;
        }
        .product-card .price {
            font-size: 16px;
            font-weight: bold;
            color: #4CAF50;
            margin: 10px 0;
        }
        .product-card a {
            display: inline-block;
            background-color: #4CAF50;
            color: #fff;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            transition: background-color 0.3s ease;
        }
        .product-card a:hover {
            background-color: #45a049;
        }
        .product-card .out-of-stock {
            color: #ff0000;
            font-size: 16px;
            font-weight: bold;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <?php
    require 'db_connection.php';

    $category_id = $_GET['category_id'];

    // Get category name
    $category_query = "SELECT name FROM Categories WHERE id = ?";
    $stmt = $conn->prepare($category_query);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $category_result = $stmt->get_result();
    $category = $category_result->fetch_assoc();

    echo "<h1>Products in Category: " . htmlspecialchars($category['name']) . "</h1>";

    // Get products
    $sql = "SELECT * FROM Products WHERE category_id = ? ORDER BY name ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();

    echo "<div class='product-container'>";
    while ($row = $result->fetch_assoc()) {
        echo "
            <div class='product-card'>
                <img src='images/{$row['image']}' alt='" . htmlspecialchars($row['name']) . "'>
                <h2>" . htmlspecialchars($row['name']) . "</h2>
                <p>" . htmlspecialchars($row['description']) . "</p>
                <p class='price'>Price: \$" . number_format($row['price'], 2) . "</p>
                " . 
                ($row['stock'] > 0 
                ? "<a href='product_details.php?product_id={$row['id']}'>View Details</a>" 
                : "<div class='out-of-stock'>Out of Stock</div>") . "
            </div>
        ";
    }
    echo "</div>";

    $conn->close();
    ?>
</body>
</html>
