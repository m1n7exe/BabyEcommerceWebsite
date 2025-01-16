<?php
// Include the existing database connection file
include 'db_connection.php';
include_once("header.php");

// Query to fetch all categories
$categoryQuery = "SELECT CategoryID, CatName FROM category";
$categoryResult = $conn->query($categoryQuery);

// Check if categories were fetched
if ($categoryResult === false) {
    echo "Error fetching categories: " . $conn->error;
    exit();
}

// Fetch categories as an associative array
$categories = $categoryResult->fetch_all(MYSQLI_ASSOC);

// Get user inputs from the query string (GET)
$searchQuery = isset($_GET['search']) ? '%' . $conn->real_escape_string($_GET['search']) . '%' : '%';
$minPrice = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$maxPrice = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 99999;
$Offered = isset($_GET['on_offer']) && $_GET['on_offer'] == '1' ? 1 : 0;
$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;  // Get selected category

// Updated query for advanced filters (note: ? placeholders)
$sql = "SELECT p.ProductID, p.ProductTitle, p.ProductDesc, p.ProductImage, p.Price
        FROM product AS p
        INNER JOIN catproduct AS cp ON p.ProductID = cp.ProductID
        WHERE (p.ProductTitle LIKE ? OR p.ProductDesc LIKE ?)
        AND p.Price BETWEEN ? AND ?
        AND (? = 0 OR p.Offered = ?)
        AND (? = 0 OR cp.CategoryID = ?)";

// Prepare the statement
$stmt = $conn->prepare($sql);

// Check for preparation error
if ($stmt === false) {
    echo "Error preparing statement: " . $conn->error;
    exit();
}

// Bind the parameters (ssddii stands for 2 strings, 2 doubles, and 2 integers)
// Bind the parameters (including category filter)
$category = isset($_GET['category']) ? (int)$_GET['category'] : 0; // Default to 0 if no category selected

$stmt->bind_param('ssddiiii', 
    $searchQuery,   // ProductTitle LIKE
    $searchQuery,   // ProductDesc LIKE
    $minPrice,      // minPrice
    $maxPrice,      // maxPrice
    $Offered,       // Offered filter
    $Offered,       // Offered filter again
    $category,      // Category filter (0 for All Categories)
    $category       // Category filter (same value passed twice)
);


// Execute the query
$stmt->execute();

// Get the result
$result = $stmt->get_result();

// Fetch all the products
$products = $result->fetch_all(MYSQLI_ASSOC);

// Close the prepared statement
$stmt->close();


?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Listing</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #fbb6c9;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 20px auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 0 20px;
        }

        /* Product Grid */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            padding: 0 20px;
        }

        .product-card {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }

        .product-card:hover {
            transform: translateY(-5px);
        }

        .product-image img {
            width: 100%;
            height: 200px;
            object-fit: contain;
        }

        .product-details {
            padding: 15px;
        }

        .product-title {
            font-size: 16px;
            font-weight: bold;
            margin: 0 0 10px;
        }

        .product-description {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
            height: 50px;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .product-price {
            font-size: 18px;
            color: #ff7043;
            font-weight: bold;
        }

        /* Search Bar and Category Dropdown */
        .search-bar {
            margin: 30px 0;
            text-align: center;
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px; /* Space between the input, dropdown, and button */
        }

        .search-bar input {
            width: 580px;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .search-bar button {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #ff7043;  /* Orange color */
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .search-bar button:hover {
            background-color: #ff5722;  /* Darker orange color on hover */
            transform: scale(1.05);  /* Slight scale effect on hover */
        }

        /* Category Dropdown */
        #category {
            padding: 10px;
            font-size: 16px;
            background-color: #fff;
            color: #333;
            border: 1px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
            margin-left: 20px;
            margin-bottom: 20px;
        }

        #category:hover {
            background-color: #ff7043;  /* Orange background on hover */
            color: white;  /* Change text color when hovered */
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }

        .modal-content {
            background: white;
            margin: 10% auto;
            padding: 20px;
            width: 400px;
            border-radius: 8px;
            text-align: left;
        }

        .modal-header {
            font-size: 20px;
            margin-bottom: 10px;
        }

        .modal-footer {
            text-align: right;
        }

        .close-modal {
            background-color: red;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        /* Modal Styles */



        .modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1000;
}

.modal-content {
    background: white;
    margin: 10% auto;
    padding: 20px;
    width: 400px;
    border-radius: 8px;
    text-align: left;
}

.modal-header {
    font-size: 20px;
    margin-bottom: 10px;
}

.modal-footer {
    text-align: right;
}

.close-modal {
    background-color: red;
    color: white;
    border: none;
    padding: 5px 10px;
    border-radius: 5px;
    cursor: pointer;
}


    </style>
</head>

<body>
    <div class="search-bar">
        <form method="GET" action="" class="search-form">
            <input type="text" name="search" placeholder="Search products..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <button type="submit">Search</button>
            <button type="button" class="advanced-search-btn" id="openAdvancedSearchModal">+</button>
        </form>
    </div>

    <div class="category-container">
        <form method="GET" action="" class="category-form">
            <select name="category" id="category" onchange="this.form.submit()">
                <option value="0">All Categories</option>
                <?php foreach ($categories as $category) : ?>
                    <option value="<?php echo $category['CategoryID']; ?>" 
                        <?php echo isset($_GET['category']) && $_GET['category'] == $category['CategoryID'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category['CatName']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <div class="product-grid">
    <?php if (!empty($products)) : ?>
        <?php foreach ($products as $product) : ?>
            <div class="product-card" onclick="window.location.href='productpage.php?ProductID=<?php echo $product['ProductID']; ?>'">
                <div class="product-image">
                    <img src="assets/ECAD2024Oct_Assignment_1_Input_Files(1)/ECAD2024Oct_Assignment_1_Input_Files/Images/Products/<?php echo htmlspecialchars($product['ProductImage']); ?>" alt="<?php echo htmlspecialchars($product['ProductTitle']); ?>">
                </div>
                <div class="product-details">
                    <div class="product-title"><?php echo htmlspecialchars($product['ProductTitle']); ?></div>
                    <div class="product-description"><?php echo htmlspecialchars($product['ProductDesc']); ?></div>
                    <div class="product-price">$<?php echo htmlspecialchars($product['Price']); ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else : ?>
        <p>No products found.</p>
    <?php endif; ?>
    </div>


    <!-- Modal for Advanced Search -->
    <div id="advancedSearchModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Advanced Search</h3>
            </div>
            <div class="modal-body">
                <form method="GET" action="">
                    <label for="priceRange">Price Range:</label>
                    <input type="text" name="minPrice" placeholder="Min Price">
                    <input type="text" name="maxPrice" placeholder="Max Price">

                    <label for="keywords">Keywords:</label>
                    <input type="text" name="keywords" placeholder="Enter keywords">

                    <button type="submit">Apply Filters</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="close-modal" id="closeAdvancedSearchModal">Close</button>
            </div>
        </div>
    </div>

    <script>
        const closeProductModalBtn = document.getElementById('closeProductModal');
        const advancedSearchModal = document.getElementById('advancedSearchModal');
        const openAdvancedSearchModalBtn = document.getElementById('openAdvancedSearchModal');
        const closeAdvancedSearchModalBtn = document.getElementById('closeAdvancedSearchModal');

        function openProductModal(productId) {
            const products = <?php echo json_encode($products); ?>;
            const product = products.find(p => p.ProductID == productId);

            if (product) {
                document.getElementById('modalTitle').innerText = product.ProductTitle;
                document.getElementById('modalDesc').innerText = product.ProductDesc;
                document.getElementById('modalPrice').innerText = `Price: $${product.Price}`;
                document.getElementById('modalImage').src = `assets/ECAD2024Oct_Assignment_1_Input_Files(1)/ECAD2024Oct_Assignment_1_Input_Files/Images/Products/${product.ProductImage}`;
                productModal.style.display = 'flex';
            }
        }

        openAdvancedSearchModalBtn.addEventListener('click', () => {
            advancedSearchModal.style.display = 'flex';
        });

        closeAdvancedSearchModalBtn.addEventListener('click', () => {
            advancedSearchModal.style.display = 'none';
        });

        window.onclick = (event) => {
            if (event.target === productModal) {
                productModal.style.display = 'none';
            } else if (event.target === advancedSearchModal) {
                advancedSearchModal.style.display = 'none';
            }
        };
    </script>
</body>
</html>
