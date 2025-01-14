<?php
session_start();
require 'db_connection.php';
include_once("header.php");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop by Category</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #ffe4e1; /* Baby Pink */
            margin: 0;
            padding: 0;
        }

        header {
    background-color: #ff69b4; /* Hot Pink */
    color: white;
    padding: 8px 0;  /* Reduced padding for a smaller header */
    text-align: center;
    font-size: 1.2rem;  /* Adjusted font size to be smaller */
    margin-bottom: 10px;
}

        .container {
            max-width: 900px; /* Reduced container width */
            margin: 30px auto;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); /* Adjusted grid layout */
            gap: 20px; /* Reduced the gap between cards */
            padding: 0 15px;
            margin-top: 50px;
        }

        .category-card {
    background-color: white;
    border: 1px solid #ddd;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    text-align: center;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    justify-content: space-between; /* Ensures the content is spaced evenly */
    height: 100%; /* Makes the card stretch to fill available space */
    transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
}


        .category-card:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 12px rgba(0, 0, 0, 0.2);
        }

        .category-card img {
            width: 100%;
            height: 180px; /* Adjusted image height for less zoomed-in effect */
            object-fit: contain; /* Changed object-fit to 'contain' for better image fit */
        }

        .category-card h2 {
            padding: 15px;
            font-size: 1.4rem;
            background-color: #ff69b4; /* Hot Pink */
            color: white;
            margin: 0;
            text-transform: uppercase;
        }

        .category-card p {
            padding: 15px;
            font-size: 1rem;
            color: #555;
        }

        .category-card a {
            display: inline-block;
            padding: 8px 16px; /* Adjusted padding for a smaller button */
            font-size: 1rem; /* Smaller font size for the button */
            color: white;
            background-color: #ff69b4; /* Hot Pink */
            text-decoration: none;
            text-transform: uppercase;
            border-radius: 5px; /* Rounded corners for the button */
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .category-card a:hover {
            background-color: #ff1493; /* Deep Pink */
            transform: scale(1.1); /* Slight scale-up effect on hover */
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
                    <img src="assets/ECAD2024Oct_Assignment_1_Input_Files(1)/ECAD2024Oct_Assignment_1_Input_Files/Images/Category/<?php echo htmlspecialchars($categoryImage); ?>" alt="Category Image">
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
