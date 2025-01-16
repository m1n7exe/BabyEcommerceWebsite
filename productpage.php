<?php
// Include the database connection
include 'db_connection.php';
include_once("header.php");

// Initialize variables to store product details
$productTitle = $productDesc = $productPrice = $productImage = "";
$productQuantity = 0; // Initialize quantity variable

// Check if ProductID is provided in the URL
if (isset($_GET['ProductID'])) {
    $productID = $_GET['ProductID'];

    // Validate and sanitize the input to prevent SQL injection
    if (filter_var($productID, FILTER_VALIDATE_INT)) {

        // Create a query to fetch the product details
        $query = "SELECT * FROM product WHERE ProductID = $productID";
        
        // Execute the query using MySQLi
        $result = mysqli_query($conn, $query);

        // Check if the query was successful
        if ($result) {
            // Fetch the product details
            $product = mysqli_fetch_assoc($result);

            // Check if the product exists
            if ($product) {
                // Populate product details
                $productTitle = htmlspecialchars($product['ProductTitle']);
                $productDesc = htmlspecialchars($product['ProductDesc']);
                $productPrice = htmlspecialchars($product['Price']);
                $productImage = htmlspecialchars($product['ProductImage']);
                $productQuantity = intval($product['Quantity']); // Get quantity level
            } else {
                // If no product is found, show an error message
                echo "Product not found.";
                exit;
            }
        } else {
            // If the query fails, show an error message
            echo "Error executing query: " . mysqli_error($conn);
            exit;
        }
    } else {
        // If ProductID is not valid, show an error message
        echo "Invalid Product ID.";
        exit;
    }
} else {
    // If no ProductID is provided, show an error message
    echo "No product ID provided.";
    exit;
}

// Close the database connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Page</title>
    <style>
        /* General Reset */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f7f7f7;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 20px auto;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            display: flex;
            align-items: flex-start;
            gap: 20px;
            padding: 20px;
        }

        .product-image {
            flex: 1;
            max-width: 40%;
            display: flex;
            align-items: flex-start;
            justify-content: center;
        }

        .product-image img {
            max-width: 100%;
            height: auto;
            display: block;
        }

        .product-details {
            flex: 2;
            padding: 20px;
        }

        .product-title {
            font-size: 24px;
            font-weight: bold;
            color: #222;
            margin-bottom: 10px;
        }

        .price {
            font-size: 22px;
            color: #b12704;
            margin-bottom: 10px;
        }

        .stock-status {
            margin-bottom: 15px;
        }

        .in-stock {
            color: #007600;
            font-weight: bold;
        }

        .out-of-stock {
            color: #b12704;
            font-weight: bold;
        }

        .product-description {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .actions button {
            padding: 10px 20px;
            font-size: 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .buy-now {
            background-color: #ffa41c;
            color: white;
            font-weight: bold;
            flex: 1;
        }

        .buy-now:hover {
            background-color: #cc8500;
        }

        .buy-now:disabled {
            background-color: #f0f0f0;
            color: #999;
            cursor: not-allowed;
        }

        .add-to-cart {
            background-color: #f0c14b;
            border: 1px solid #a88734;
            flex: 1;
        }

        .add-to-cart:hover {
            background-color: #e2b230;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }

            .product-image {
                max-width: 100%;
                margin-bottom: 20px;
                border-right: none;
                border-bottom: 1px solid #ddd;
                padding-bottom: 20px;
            }

            .actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>

    <div class="container">
        <!-- Product Image -->
        <div class="product-image">
            <img src="assets/ECAD2024Oct_Assignment_1_Input_Files(1)/ECAD2024Oct_Assignment_1_Input_Files/Images/Products/<?php echo $productImage; ?>" alt="<?php echo $productTitle; ?>">
        </div>

        <!-- Product Details -->
        <div class="product-details">
            <h1 class="product-title"><?php echo $productTitle; ?></h1>
            <p class="price">$<?php echo number_format($productPrice, 2); ?></p>

            <!-- Stock Status -->
            <div class="stock-status">
                <?php if ($productQuantity > 0): ?>
                    <p class="in-stock">In Stock</p>
                    <p>Quantity Available: <?php echo $productQuantity; ?></p>
                <?php else: ?>
                    <p class="out-of-stock">Out of Stock</p>
                <?php endif; ?>
            </div>

            <!-- Description -->
            <div class="product-description">
                <h3>About this item</h3>
                <p><?php echo $productDesc; ?></p>
            </div>

            <!-- Action Buttons -->
            <div class="actions">
                <button class="buy-now" <?php echo $productQuantity > 0 ? '' : 'disabled'; ?>>Buy Now</button>
                <button class="add-to-cart">Add to Cart</button>
            </div>
        </div>
    </div>

</body>
</html>
