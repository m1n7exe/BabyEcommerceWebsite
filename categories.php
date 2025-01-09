<!DOCTYPE html>
<html>
<head>
    <title>Product Categories</title>
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
        .category-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            padding: 20px;
        }
        .category-card {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            width: 300px;
            margin: 10px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .category-card:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }
        .category-card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-bottom: 1px solid #ddd;
        }
        .category-card h2 {
            font-size: 20px;
            color: #333;
            margin: 15px 0;
        }
        .category-card p {
            font-size: 14px;
            color: #666;
            margin: 10px 15px;
        }
        .category-card a {
            display: inline-block;
            background-color: #4CAF50;
            color: #fff;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            transition: background-color 0.3s ease;
        }
        .category-card a:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <h1>Product Categories</h1>
    <div class="category-container">
        <?php
        require 'db_connection.php';

        $sql = "SELECT * FROM Categories ORDER BY name ASC";
        $result = $conn->query($sql);

        while ($row = $result->fetch_assoc()) {
            echo "
                <div class='category-card'>
                    <img src='images/placeholder.jpg' alt='Image for " . htmlspecialchars($row['name']) . "'>
                    <h2>" . htmlspecialchars($row['name']) . "</h2>
                    <p>" . htmlspecialchars($row['description']) . "</p>
                    <a href='products.php?category_id={$row['id']}'>View Products</a>
                </div>
            ";
        }

        $conn->close();
        ?>
    </div>
</body>
</html>
