<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $siteTitle; ?></title>
  <link rel="stylesheet" href="styles.css"> <!-- Link to your CSS file -->
  <style>

.footer {
    background-color: #ff85a2; /* Soft pink background */
    color: white;
    padding: 20px 0;
    text-align: center;
    margin-top: 40px;
}

html, body {
    height: 100%;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
}

.main-content {
    flex: 1; /* Pushes the footer down */
}

.footer-container {
    display: flex;
    justify-content: space-around;
    align-items: center;
    flex-wrap: wrap;
    max-width: 1200px;
    margin: 0 auto;
}

.footer-section {
    flex: 1;
    min-width: 200px;
    padding: 10px;
}

.footer-section h3 {
    font-size: 18px;
    margin-bottom: 10px;
}

.footer-section ul {
    list-style: none;
    padding: 0;
}

.footer-section ul li {
    margin: 5px 0;
}

.footer-section ul li a {
    color: white;
    text-decoration: none;
    transition: color 0.3s ease-in-out;
}

.footer-section ul li a:hover {
    color: #ffe6ea;
}

.social-icons a {
    color: white;
    font-size: 24px;
    margin: 0 10px;
    text-decoration: none;
}

.social-icons a:hover {
    color: #ffe6ea;
}

.footer-bottom {
    margin-top: 10px;
    font-size: 14px;
}
</style>
</head>
<body>


<footer class="footer">
    <div class="footer-container">
        <div class="footer-section">
            <h3>BabyBoo E-Commerce</h3>
            <p>Your one-stop shop for baby essentials.</p>
        </div>
        <div class="footer-section">
            <h3>Helpdesk</h3>
            <p>Need help? Contact us at:</p>
            <p><a href="mailto:support@babyboo.com" style="color: white; text-decoration: none;">support@babyboo.com</a></p>
        </div>
        <div class="footer-section">
            <h3>Follow Us</h3>
            <div class="social-icons">
                <a href="#"><i class="fa fa-facebook"></i></a>
                <a href="#"><i class="fa fa-instagram"></i></a>
                <a href="#"><i class="fa fa-twitter"></i></a>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?= date("Y") ?> BabyBoo E-commerce. All Rights Reserved.</p>
    </div>
</footer>