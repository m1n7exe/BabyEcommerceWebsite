<?php
// Include the database connection
include 'db_connection.php';
include_once("header.php");

// Initialize variables to store product details
$productTitle = $productDesc = $productPrice = $productImage = "";

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
        body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f5f5f5;
}

.product-container {
    margin: 30px auto;
    background-color: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    width: 80%;
    max-width: 1200px;
    overflow: hidden;
    display: flex;
    justify-content: space-between; /* Keep image and details on opposite sides */
}

.product-image {
    width: 50%;
    padding-left: 100px; /* Add padding to the left to move it to the right */
    padding-top: 20px;

}

.product-image img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.product-details {
    width: 50%;
    padding-left: 30px;
}

.product-details h1 {
    font-size: 32px;
    color: #333;
    margin-bottom: 15px;
}

.product-details p {
    font-size: 16px;
    line-height: 1.6;
    color: #555;
    margin-bottom: 20px;
}

.price {
    font-size: 24px;
    font-weight: bold;
    color: #b12704;
    margin: 20px 0;
}

.product-actions {
    margin-top: 20px;
}

.product-actions button {
    padding: 12px 20px;
    font-size: 16px;
    cursor: pointer;
    border: none;
    border-radius: 5px;
    transition: background-color 0.3s;
    margin-right: 15px;
}

.product-actions .buy-now {
    background-color: #ff9900;
    color: white;
}

.product-actions .add-to-cart {
    background-color: #007600;
    color: white;
}

.product-actions button:hover {
    opacity: 0.9;
}

.product-description {
    background-color: #f1f1f1;
    padding: 20px;
    margin-top: 30px;
    border-radius: 8px;
}

    </style>
</head>
<body>

    <div class="product-container">
        <div class="product-image">
            <img src="assets/ECAD2024Oct_Assignment_1_Input_Files(1)/ECAD2024Oct_Assignment_1_Input_Files/Images/Products/<?php echo $productImage; ?>" alt="<?php echo $productTitle; ?>">
        </div>
        <div class="product-details">
            <h1><?php echo $productTitle; ?></h1>
            <p class="price">$<?php echo $productPrice; ?></p>
            
            <!-- Product Action Buttons -->
            <div class="product-actions">
                <button class="buy-now">Buy Now</button>
                <button class="add-to-cart">Add to Cart</button>
            </div>
            
            <!-- Product Description -->
            <div class="product-description">
                <h3>Product Description:</h3>
                <p><?php echo $productDesc; ?></p>
            </div>
        </div>
    </div>

</body>
</html>
