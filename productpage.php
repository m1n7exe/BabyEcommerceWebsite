<?php
session_start();
require 'db_connection.php';
include_once("header.php");

// Ensure user is logged in before adding to cart
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Initialize variables to store product details
$productTitle = $productDesc = $productPrice = $productImage = "";
$productQuantity = 0;

// Check if ProductID is provided in the URL
if (isset($_GET['ProductID'])) {
    $productID = $_GET['ProductID'];
    if (filter_var($productID, FILTER_VALIDATE_INT)) {
        $query = "SELECT * FROM product WHERE ProductID = $productID";
        $result = mysqli_query($conn, $query);
        if ($result) {
            $product = mysqli_fetch_assoc($result);
            if ($product) {
                $productTitle = htmlspecialchars($product['ProductTitle']);
                $productDesc = htmlspecialchars($product['ProductDesc']);
                $productPrice = htmlspecialchars($product['Price']);
                $productImage = htmlspecialchars($product['ProductImage']);
                $productQuantity = intval($product['Quantity']);
            } else {
                echo "Product not found.";
                exit;
            }
        } else {
            echo "Error executing query: " . mysqli_error($conn);
            exit;
        }
    } else {
        echo "Invalid Product ID.";
        exit;
    }
} else {
    echo "No product ID provided.";
    exit;
}

// Handle adding to cart
if (isset($_POST['add_to_cart'])) {
    $quantity = intval($_POST['quantity']);
    if ($quantity > 0 && $quantity <= $productQuantity) {
        if (!isset($_SESSION['cart'][$user_id])) {
            $_SESSION['cart'][$user_id] = [];
        }
        if (!isset($_SESSION['cart'][$user_id][$productID])) {
            $_SESSION['cart'][$user_id][$productID] = ['quantity' => $quantity, 'price' => $productPrice];
        } else {
            $_SESSION['cart'][$user_id][$productID]['quantity'] += $quantity;
        }
        header("Location: cart.php");
        exit();
    } else {
        echo "<script>alert('Invalid quantity. Please select a valid amount.');</script>";
    }
}

// Handle Buy Now
if (isset($_POST['buy_now'])) {
    $quantity = intval($_POST['quantity']);
    if ($quantity > 0 && $quantity <= $productQuantity) {
        $_SESSION['cart'][$user_id] = [$productID => ['quantity' => $quantity, 'price' => $productPrice]];
        header("Location: checkout.php");
        exit();
    } else {
        echo "<script>alert('Invalid quantity. Please select a valid amount.');</script>";
    }
}

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
        <div class="product-image">
            <img src="assets/ECAD2024Oct_Assignment_1_Input_Files(1)/ECAD2024Oct_Assignment_1_Input_Files/Images/Products/<?php echo $productImage; ?>" alt="<?php echo $productTitle; ?>">
        </div>
        <div class="product-details">
            <h1 class="product-title"><?php echo $productTitle; ?></h1>
            <?php
            // Determine if the product has a discount
            $discountedPrice = (!empty($product['OfferedPrice']) && $product['OfferedPrice'] > 0) ? $product['OfferedPrice'] : $product['Price'];
            ?>
            <p class="price">
                <?php if (!empty($product['OfferedPrice']) && $product['OfferedPrice'] > 0): ?>
                    <s style="color: gray;">$<?php echo number_format($product['Price'], 2); ?></s>
                    <span style="color: #b12704; font-weight: bold;">$<?php echo number_format($product['OfferedPrice'], 2); ?></span>
                <?php else: ?>
                    $<?php echo number_format($product['Price'], 2); ?>
                <?php endif; ?>
            </p>
            <div class="stock-status">
                <?php if ($productQuantity > 0): ?>
                    <p class="in-stock">In Stock</p>
                    <p>Quantity Available: <?php echo $productQuantity; ?></p>
                <?php else: ?>
                    <p class="out-of-stock">Out of Stock</p>
                <?php endif; ?>
            </div>
            <div class="product-description">
                <h3>About this item</h3>
                <p><?php echo $productDesc; ?></p>
            </div>
            <form method="post">
                <label for="quantity">Quantity:</label>
                <input type="number" name="quantity" min="1" max="<?php echo $productQuantity; ?>" value="1" required>
                <div class="actions">
                    <button type="submit" name="buy_now" class="buy-now" <?php echo $productQuantity > 0 ? '' : 'disabled'; ?>>Buy Now</button>
                    <button type="submit" name="add_to_cart" class="add-to-cart" <?php echo $productQuantity > 0 ? '' : 'disabled'; ?>>Add to Cart</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
