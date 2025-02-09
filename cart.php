<?php
session_start();
require 'db_connection.php';
include_once("header.php");

// Ensure user is logged in before accessing cart
if (!isset($_SESSION['ShopperID'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['ShopperID'];

// Initialize cart for each user
if (!isset($_SESSION['cart'][$user_id])) {
    $_SESSION['cart'][$user_id] = [];
}

// Handle adding items to cart
if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $quantity = intval($_POST['quantity']);
    
    // Fetch product stock and discounted price
    $stock_query = "SELECT Quantity, Price, OfferedPrice FROM Product WHERE ProductID = $product_id";
    $stock_result = mysqli_query($conn, $stock_query);
    $product = mysqli_fetch_assoc($stock_result);
    
    if ($product) {
        $stock = $product['Quantity'];
        $originalPrice = $product['Price'];
        $discountedPrice = (!empty($product['OfferedPrice']) && $product['OfferedPrice'] > 0) ? $product['OfferedPrice'] : $originalPrice;

        if ($quantity > 0 && $quantity <= $stock) {
            if (!isset($_SESSION['cart'][$user_id][$product_id])) {
                $_SESSION['cart'][$user_id][$product_id] = ['quantity' => $quantity, 'price' => $discountedPrice];
            } else {
                $new_quantity = $_SESSION['cart'][$user_id][$product_id]['quantity'] + $quantity;
                if ($new_quantity <= $stock) {
                    $_SESSION['cart'][$user_id][$product_id]['quantity'] = $new_quantity;
                } else {
                    echo "<script>alert('Cannot add more than available stock.');</script>";
                }
            }
            header("Location: cart.php");
            exit();
        } else {
            echo "<script>alert('Invalid quantity.');</script>";
        }
    }
}

// Handle quantity update
$validation_errors = []; // Store validation messages

if (isset($_POST['update'])) {
    foreach ($_POST['quantities'] as $id => $qty) {
        $stock_query = "SELECT Quantity FROM Product WHERE ProductID = $id";
        $stock_result = mysqli_query($conn, $stock_query);
        $stock = mysqli_fetch_assoc($stock_result)['Quantity'];

        if ($qty > 0 && $qty <= $stock) {
            $_SESSION['cart'][$user_id][$id]['quantity'] = $qty;
        } else {
            // Add validation message
            $validation_errors[$id] = "Stock available: $stock, but you entered: $qty.";
        }
    }
    // Reload the page only if there are no errors
    if (empty($validation_errors)) {
        header("Location: cart.php");
        exit();
    }
}

// Handle item removal from cart
if (isset($_GET['remove'])) {
    $product_id = $_GET['remove'];
    if (isset($_SESSION['cart'][$user_id][$product_id])) {
        unset($_SESSION['cart'][$user_id][$product_id]);
    }
    header("Location: cart.php");
    exit();
}

// Fetch current GST rate
$tax_rate = 0;
$today = date('Y-m-d');
$tax_query = "SELECT TaxRate FROM gst WHERE EffectiveDate <= '$today' ORDER BY EffectiveDate DESC LIMIT 1";
$tax_result = mysqli_query($conn, $tax_query);
if ($tax_row = mysqli_fetch_assoc($tax_result)) {
    $tax_rate = $tax_row['TaxRate'];
}

// Fetch product details for cart items
$subtotal = 0; // Only the sum of products
$total_price = 0;
$total_items = 0;
$delivery_charge = isset($_POST['delivery_mode']) && $_POST['delivery_mode'] === 'Normal' ? 5 : 10;
$cart_items = [];

if (!empty($_SESSION['cart'][$user_id])) {
    $ids = implode(',', array_keys($_SESSION['cart'][$user_id]));
    $query = "SELECT ProductID, ProductTitle, ProductImage, Price, OfferedPrice FROM Product WHERE ProductID IN ($ids)";
    $result = mysqli_query($conn, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        $id = $row['ProductID'];
        $row['quantity'] = $_SESSION['cart'][$user_id][$id]['quantity'];

        // Use discounted price if available
        $price = (!empty($row['OfferedPrice']) && $row['OfferedPrice'] > 0) ? $row['OfferedPrice'] : $row['Price'];
        
        $row['subtotal'] = $price * $row['quantity'];
        $cart_items[] = $row;
        $subtotal += $row['subtotal'];
        $total_items += $row['quantity'];
    }
}

// Waive delivery charge if subtotal is more than S$200
if ($subtotal > 200) {
    $delivery_charge = 0;
}

// Calculate tax based on subtotal (not including delivery charge)
$tax_amount = ($subtotal * $tax_rate) / 100;

// Total price should now be: subtotal + delivery + GST
$total_price = $subtotal + $delivery_charge + $tax_amount;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Shopping Cart</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f7f7f7;
            color: #333;
        }
        .cart-container {
            max-width: 900px;
            margin: 50px auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        th, td {
            padding: 15px;
            text-align: center;
            border: 1px solid #ddd;
        }
        th {
            background: #ff6681;
            color: white;
        }
        button, a {
            padding: 12px 15px;
            margin-top: 10px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            border-radius: 5px;
        }
        .update-btn {
            background: #ffcc00;
            color: black;
        }
        .checkout-btn {
            background: #ff6681;
            color: white;
        }
        .remove-btn {
            background: #dc3545;
            color: white;
        }
        .total {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-top: 10px;
            padding: 5px;
            border-radius: 5px;
            text-align: right;
            display: block;
        }
        .shop-now {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 20px;
            background: #ff6681;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 18px;
        }
        .delivery-form {
            text-align: right; /* Aligns the form with total price */
            margin-top: 10px; /* Adds space between total price and form */
            font-size: 16px; /* Matches the total price font size */
        }
        .delivery-form p {
            font-weight: bold;
        }
        .delivery-form label {
            display: block; /* Ensures radio buttons appear in a vertical layout */
            margin: 5px 0;
        }
        .total-summary {
            text-align: right; /* Aligns with the delivery form */
            margin-top: 15px; /* Adds space */
            font-size: 16px; /* Default size for subtotals */
        }
        .total-summary p {
            margin: 10px 0;
            font-weight: bold; /* Ensures normal weight for smaller values */
        }
        .total-summary .total-price {
            font-size: 18px; /* Makes the total price the biggest */
            font-weight: bold; /* Makes it stand out */
            color: red; /* Highlights it in red */
        }
    </style>
</head>
<div class="cart-container">
    <h2>Shopping Cart</h2>
    <p>Total Items in Cart: <?= $total_items ?></p>
    <?php if (empty($cart_items)): ?>
        <p>Your shopping cart is empty.</p>
        <a href="productListing.php" class="shop-now">Go to Shopping Now</a>
    <?php else: ?>
        <form method="post">
            <table>
                <tr>
                    <th>Product</th>
                    <th>Image</th> <!-- New Column for Image -->
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                    <th>Action</th>
                </tr>
                <?php foreach ($cart_items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['ProductTitle']) ?></td>
                        <td>
                            <img src="assets/ECAD2024Oct_Assignment_1_Input_Files(1)/ECAD2024Oct_Assignment_1_Input_Files/Images/Products/<?= htmlspecialchars($item['ProductImage']) ?>" 
                            alt="<?= htmlspecialchars($item['ProductTitle']) ?>" style="width: 80px; height: auto;">
                        </td>
                        <td>
                            <?php if (!empty($item['OfferedPrice']) && $item['OfferedPrice'] > 0 && $item['OfferedPrice'] < $item['Price']) : ?>
                                <s>S$<?= number_format($item['Price'], 2) ?></s> 
                                <span style="color: #d63384; font-weight: bold;">S$<?= number_format($item['OfferedPrice'], 2) ?></span>
                            <?php else : ?>
                                S$<?= number_format($item['Price'], 2) ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <input type="number" name="quantities[<?= $item['ProductID'] ?>]" value="<?= $item['quantity'] ?>" min="1">
                            <?php if (isset($validation_errors[$item['ProductID']])): ?>
                                <p style="color: red; font-size: 12px;"><?= $validation_errors[$item['ProductID']] ?></p>
                            <?php endif; ?>
                        </td>
                        <td>S$<?= number_format($item['subtotal'], 2) ?></td>
                        <td><a href="cart.php?remove=<?= $item['ProductID'] ?>" class="remove-btn">Remove</a></td>
                    </tr>
                <?php endforeach; ?>
            </table>   
            <div class="delivery-form">
                <p>Please select a delivery mode:</p>
                <label>
                    <input type="radio" name="delivery_mode" value="Normal" 
                        <?= isset($_POST['delivery_mode']) && $_POST['delivery_mode'] === 'Normal' ? 'checked' : '' ?> 
                        onchange="this.form.submit()"> 
                    Normal Delivery (S$5.00)
                </label>
                <label>
                    <input type="radio" name="delivery_mode" value="Express" 
                        <?= isset($_POST['delivery_mode']) && $_POST['delivery_mode'] === 'Express' ? 'checked' : '' ?> 
                        onchange="this.form.submit()"> 
                    Express Delivery (S$10.00)
                </label>
            </div>
            <div class="total-summary">
                <p>Subtotal: S$<?= number_format($subtotal, 2) ?></p>
                <p>Delivery Charge: S$<?= number_format($delivery_charge, 2) ?></p>
                <p>GST (<?= $tax_rate ?>%): S$<?= number_format($tax_amount, 2) ?></p>
                <p class="total-price">Total Price: S$<?= number_format($total_price, 2) ?></p>
            </div>
            
            <button type="submit" name="update" class="update-btn">Update Cart</button>
            <a href="checkoutProcess.php" class="checkout-btn">Proceed to Checkout</a>        
        </form>
    <?php endif; ?>
</div>
</html>
