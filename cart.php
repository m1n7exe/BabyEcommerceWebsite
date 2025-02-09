<?php
session_start();
require 'db_connection.php';
include_once("header.php");

// Ensure user is logged in before accessing cart
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Initialize cart for each user
if (!isset($_SESSION['cart'][$user_id])) {
    $_SESSION['cart'][$user_id] = [];
    // To Do 5 (Practical 5):
    // Declare an array to store the shopping cart items in session variable 
    $_SESSION["cart"] = array();
    // Store the shopping cart items in session variable as an associate array
    $_SESSION["cart"][] = array(
        "productId" => $row["ProductID"],
        "name" => $row["Name"],
        "price" => $row["Price"],
        "quantity" => $row["Quantity"]
    );
}

// Handle adding items to cart
if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $quantity = intval($_POST['quantity']);

    // Fetch product stock
    $stock_query = "SELECT Quantity FROM Product WHERE ProductID = $product_id";
    $stock_result = mysqli_query($conn, $stock_query);
    $stock = mysqli_fetch_assoc($stock_result)['Quantity'];

    if ($quantity > 0 && $quantity <= $stock) {
        if (!isset($_SESSION['cart'][$user_id][$product_id])) {
            $_SESSION['cart'][$user_id][$product_id] = ['quantity' => $quantity];
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

// Handle quantity update
if (isset($_POST['update'])) {
    foreach ($_POST['quantities'] as $id => $qty) {
        $stock_query = "SELECT Quantity FROM Product WHERE ProductID = $id";
        $stock_result = mysqli_query($conn, $stock_query);
        $stock = mysqli_fetch_assoc($stock_result)['Quantity'];

        if ($qty > 0 && $qty <= $stock) {
            $_SESSION['cart'][$user_id][$id]['quantity'] = $qty;
        } else {
            echo "<script>alert('Cannot update quantity beyond available stock.');</script>";
        }
    }
    header("Location: cart.php");
    exit();
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

// Fetch product details for cart items
$cart_items = [];
$total_price = 0;
$total_items = 0;
$delivery_charge = 5;
if (!empty($_SESSION['cart'][$user_id])) {
    $ids = implode(',', array_keys($_SESSION['cart'][$user_id]));
    $query = "SELECT * FROM Product WHERE ProductID IN ($ids)";
    $result = mysqli_query($conn, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        $id = $row['ProductID'];
        $row['quantity'] = $_SESSION['cart'][$user_id][$id]['quantity'];
        $row['subtotal'] = $row['Price'] * $row['quantity'];
        $cart_items[] = $row;
        $total_price += $row['subtotal'];
        $total_items += $row['quantity'];
    }
}

// Waive delivery charge if subtotal is more than S$200
if ($total_price > 200) {
    $delivery_charge = 0;
}
$total_price += $delivery_charge;
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

        th,
        td {
            padding: 15px;
            text-align: center;
            border: 1px solid #ddd;
        }

        th {
            background: #ff6681;
            color: white;
        }

        button,
        a {
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
            text-align: right;
            font-size: 20px;
            font-weight: bold;
            margin-top: 15px;
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
    </style>
</head>

<body>
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
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th>Action</th>
                    </tr>
                    <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['ProductTitle']) ?></td>
                            <td>S$<?= number_format($item['Price'], 2) ?></td>
                            <td><input type="number" name="quantities[<?= $item['ProductID'] ?>]"
                                    value="<?= $item['quantity'] ?>" min="1"></td>
                            <td>S$<?= number_format($item['subtotal'], 2) ?></td>
                            <td><a href="cart.php?remove=<?= $item['ProductID'] ?>" class="remove-btn">Remove</a></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <p class="total">Subtotal: S$<?= number_format($total_price - $delivery_charge, 2) ?></p>
                <p class="total">Delivery Charge: S$<?= number_format($delivery_charge, 2) ?></p>
                <p class="total">Total Price: S$<?= number_format($total_price, 2) ?></p>
                <button type="submit" name="update" class="update-btn">Update Cart</button>
            </form>

            <form method='post' action="checkoutProcess.php">
                <input type='image' style='float:right;' src='https://www.paypal.com/en_US/i/btn/btn_xpressCheckout.gif'>
            </form>


            <td>
                <form method="post" style="padding-left:20px">
                    <!-- After clicking the submit button, the default dropdown value will be retained -->
                    <!-- Display a label for the dropdown list -->
                    <label for="deliveryMode">Mode of Delivery:</label>
                    <select name="mod" style="width: 200px; height: 30px;">
                        <option value="">Select Mode of Delivery</option>
                        <option value="Normal">Normal Delivery ($5.00)</option>
                        <option value="Express">Express Delivery ($10.00)</option>
                    </select>
                    <input type="submit" name="submit" style="width: 100px; height: 30px;">
                </form>
            </td>

        <?php endif; ?>
    </div>
</body>

</html>