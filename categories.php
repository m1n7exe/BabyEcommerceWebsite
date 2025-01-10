<?php
session_start();
require 'db_connection.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #4CAF50;
            color: white;
            padding: 10px 0;
            text-align: center;
        }

        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }

        .category-card {
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s ease-in-out;
        }

        .category-card:hover {
            transform: scale(1.05);
        }

        .category-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .category-card h2 {
            padding: 15px;
            font-size: 1.5em;
            background-color: #4CAF50;
            color: white;
            margin: 0;
        }

        .category-card a {
            display: block;
            padding: 15px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            font-size: 1.2em;
            text-transform: uppercase;
            transition: background-color 0.3s ease;
        }

        .category-card a:hover {
            background-color: #45a049;
        }

    </style>
</head>
<body>
    <header>
        <h1>Shop by Category</h1>
    </header>

    <div class="container">
        <?php
        // Fetch categories from the database
        $sql = "SELECT * FROM Category ORDER BY CatName ASC";
        $result = $conn->query($sql);

        // Loop through the categories and display them
        while ($row = $result->fetch_assoc()) {
            $categoryId = $row['CategoryID'];
            $categoryName = $row['CatName'];
            $categoryDesc = $row['CatDesc'];
            $categoryImage = $row['CatImage'];
            ?>

            <div class="category-card">
                <?php if ($categoryImage) { ?>
                    <img src="uploads/<?php echo $categoryImage; ?>" alt="Category Image">
                <?php } else { ?>
                    <img src="https://via.placeholder.com/300x200?text=No+Image" alt="Category Image">
                <?php } ?>
                <h2><?php echo htmlspecialchars($categoryName); ?></h2>
                <p><?php echo htmlspecialchars($categoryDesc); ?></p>
                <a href="products.php?category_id=<?php echo $categoryId; ?>">Browse Products</a>
            </div>

            <?php
        }

        // Close connection
        $conn->close();
        ?>
    </div>
</body>
</html>
