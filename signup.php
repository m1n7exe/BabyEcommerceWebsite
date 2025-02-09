<?php
require 'db_connection.php';

$errorMessage = "";
$successMessage = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify reCAPTCHA first
    $recaptchaResponse = $_POST['g-recaptcha-response'];
    $secretKey = "6Led0skqAAAAACCu9oJidz8cAzJjGM32m0pki1cw";  // Your secret key
    $remoteip = $_SERVER['REMOTE_ADDR'];
    $verifyURL = "https://www.google.com/recaptcha/api/siteverify?secret=$secretKey&response=$recaptchaResponse&remoteip=$remoteip";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $verifyURL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    $responseData = json_decode($response);

    if (!$responseData->success) {
        $errorMessage = "Please complete the reCAPTCHA.";
    } else {
        // Collect form data
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $address = trim($_POST['address']);
        $phone = trim($_POST['phone']);
        $birthDate = trim($_POST['birthDate']);
        $country = trim($_POST['country']);
        $pwdQuestion = trim($_POST['pwdQuestion']);
        $pwdAnswer = trim($_POST['pwdAnswer']);

        if (empty($name) || empty($email) || empty($password) || empty($address) || empty($phone) || empty($pwdAnswer)) {
            $errorMessage = "All required fields must be filled!";
        } else {
            // --- New Phone Validation Start ---
            // Use the raw phone number as entered by the user.
            $rawPhone = $phone;
            // If the phone number starts with "(65)", remove it for validation.
            if (strpos($rawPhone, "(65)") === 0) {
                $numberPart = trim(substr($rawPhone, 4));
            } else {
                $numberPart = $rawPhone;
            }
            // Check that the first digit of the number part is either 6, 8, or 9.
            if (!in_array(substr($numberPart, 0, 1), ['6', '8', '9'])) {
                $errorMessage = "Phone number must start with 6, 8, or 9.";
            }
            // If phone validation passed, check for duplicate phone number.
            if (empty($errorMessage)) {
                // Ensure the phone number is stored with the "(65)" prefix.
                if (strpos($rawPhone, "(65)") !== 0) {
                    $phoneToCheck = "(65) " . $rawPhone;
                } else {
                    $phoneToCheck = $rawPhone;
                }
                $checkPhoneSql = "SELECT * FROM Shopper WHERE Phone = ?";
                $stmtPhone = $conn->prepare($checkPhoneSql);
                $stmtPhone->bind_param("s", $phoneToCheck);
                $stmtPhone->execute();
                $resultPhone = $stmtPhone->get_result();
                if ($resultPhone->num_rows > 0) {
                    $errorMessage = "Phone number is already registered!";
                }
            }
            // --- New Phone Validation End ---
            
            // If no error so far, check if email already exists.
            if (empty($errorMessage)) {
                $check_sql = "SELECT * FROM Shopper WHERE Email = ?";
                $stmt = $conn->prepare($check_sql);
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $errorMessage = "Email is already registered!";
                } else {
                    // Prepend (65) if not already present.
                    if (strpos($rawPhone, "(65)") !== 0) {
                        $phone = "(65) " . $rawPhone;
                    } else {
                        $phone = $rawPhone;
                    }
                    $insert_sql = "INSERT INTO Shopper (Name, Email, Password, Address, Phone, BirthDate, Country, PwdQuestion, PwdAnswer, ActiveStatus) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)";
                    $stmt = $conn->prepare($insert_sql);
                    $stmt->bind_param("sssssssss", $name, $email, $password, $address, $phone, $birthDate, $country, $pwdQuestion, $pwdAnswer);

                    if ($stmt->execute()) {
                        $successMessage = "Registration successful! Redirecting to login...";
                    } else {
                        $errorMessage = "Error: Could not register. Please try again later.";
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Shopper Registration</title>
  <link rel="stylesheet" href="styles.css">
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
  <style>
    /* Reset and basic styles */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    body {
      font-family: Arial, sans-serif;
      overflow: hidden;
    }
    /* Carousel background styles (same as login) */
    .carousel-container {
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      overflow: hidden;
      z-index: -1;
    }
    .carousel-images {
      display: flex;
      width: 100%;
      height: 100%;
      animation: slide 10s infinite;
    }
    .carousel-images img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      flex-shrink: 0;
    }
    @keyframes slide {
      0% { transform: translateX(0); }
      20% { transform: translateX(0); }
      25% { transform: translateX(-100%); }
      45% { transform: translateX(-100%); }
      50% { transform: translateX(-200%); }
      70% { transform: translateX(-200%); }
      75% { transform: translateX(-300%); }
      95% { transform: translateX(-300%); }
      100% { transform: translateX(-400%); }
    }
    /* Registration form styling */
    .registration-container {
      background-color: rgba(255,255,255,0.9);
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
      max-width: 700px;
      width: 90%;
      margin: 100px auto;
      position: relative;
      z-index: 1;
    }
    /* Updated Back Button styling with background */
    .back-button {
      position: absolute;
      top: 15px;
      left: 15px;
      text-decoration: none;
      font-size: 16px;
      color: #007bff;
      font-weight: bold;
      background-color: #fff;
      padding: 5px 10px;
      border: 1px solid #007bff;
      border-radius: 5px;
    }
    .back-button:hover {
      background-color: #007bff;
      color: #fff;
    }
    h2 {
      text-align: center;
      color: #343a40;
      margin-bottom: 20px;
      font-size: 24px;
    }
    .row {
      display: flex;
      gap: 20px;
      margin-bottom: 15px;
    }
    .row.full {
      flex-direction: column;
    }
    .column {
      flex: 1;
      display: flex;
      flex-direction: column;
    }
    label {
      margin-bottom: 5px;
      color: #495057;
      font-size: 14px;
    }
    input, select {
      padding: 8px;
      margin-bottom: 10px;
      border: 1px solid #ced4da;
      border-radius: 4px;
      font-size: 14px;
    }
    button {
      background-color: #007bff;
      color: #fff;
      border: none;
      padding: 10px;
      border-radius: 4px;
      cursor: pointer;
      font-size: 16px;
      margin-top: 10px;
    }
    button:hover {
      background-color: #0056b3;
    }
    /* Center the reCAPTCHA widget */
    .g-recaptcha {
      margin: 15px auto;
      display: block;
      transform: scale(0.9);
      transform-origin: center;
    }
    /* Modal styles */
    .modal {
      display: none;
      position: fixed;
      z-index: 100;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background-color: rgba(0,0,0,0.4);
    }
    .modal-content {
      background-color: #fefefe;
      margin: 15% auto;
      padding: 15px;
      border: 1px solid #888;
      width: 80%;
      max-width: 300px;
      text-align: center;
      border-radius: 8px;
      font-size: 14px;
    }
    .close {
      color: #aaa;
      float: right;
      font-size: 24px;
      font-weight: bold;
      cursor: pointer;
    }
    .close:hover,
    .close:focus {
      color: black;
      text-decoration: none;
    }
  </style>
</head>
<body>
  <!-- Carousel Background -->
  <div class="carousel-container">
    <div class="carousel-images">
      <img src="assets/homepagephotos/hp1.jpg" alt="Image 1">
      <img src="assets/homepagephotos/hp2.jpg" alt="Image 2">
      <img src="assets/homepagephotos/hp3.jpg" alt="Image 3">
      <img src="assets/homepagephotos/hp4.jpg" alt="Image 4">
      <img src="assets/homepagephotos/hp1.jpg" alt="Image 5">
    </div>
  </div>

  <!-- Back Button -->
  <a href="signin.php" class="back-button">&larr; Back</a>

  <!-- Registration Form -->
  <div class="registration-container">
      <h2>Register as a Shopper</h2>
      <form action="signup.php" method="POST">
          <!-- Row 1: Name and Phone -->
          <div class="row">
            <div class="column">
              <label for="name">Name:</label>
              <input type="text" id="name" name="name" required>
            </div>
            <div class="column">
              <label for="phone">Phone:</label>
              <input type="text" id="phone" name="phone" placeholder="e.g. 81238971" required>
            </div>
          </div>
          <!-- Row 2: Email and Password -->
          <div class="row">
            <div class="column">
              <label for="email">Email:</label>
              <input type="email" id="email" name="email" required>
            </div>
            <div class="column">
              <label for="password">Password:</label>
              <input type="password" id="password" name="password" required>
            </div>
          </div>
          <!-- Row 3: Address (full width) -->
          <div class="row full">
            <label for="address">Address:</label>
            <input type="text" id="address" name="address" required>
          </div>
          <!-- Row 4: Birth Date and Country -->
          <div class="row">
            <div class="column">
              <label for="birthDate">Birth Date:</label>
              <input type="date" id="birthDate" name="birthDate">
            </div>
            <div class="column">
              <label for="country">Country:</label>
              <input type="text" id="country" name="country" required>
            </div>
          </div>
          <!-- Row 5: Security Question (full width) -->
          <div class="row full">
            <label for="pwdQuestion">Security Question:</label>
            <select id="pwdQuestion" name="pwdQuestion" required>
                <option value="">-- Select a question --</option>
                <option value="What is your mother's maiden name?">What is your mother's maiden name?</option>
                <option value="What was the name of your first pet?">What was the name of your first pet?</option>
                <option value="How many siblings do you have?">How many siblings do you have?</option>
                <option value="What is your favorite book?">What is your favorite book?</option>
            </select>
          </div>
          <!-- Row 6: Security Answer (full width) -->
          <div class="row full">
            <label for="pwdAnswer">Answer:</label>
            <input type="text" id="pwdAnswer" name="pwdAnswer" required>
          </div>
          <!-- reCAPTCHA and Submit Button -->
          <div class="g-recaptcha" data-sitekey="6Led0skqAAAAAGYGip8-6I8QlJwaWfBw-P3Lz3V6"></div>
          <button type="submit">Register</button>
      </form>
  </div>

  <!-- Modal Structure -->
  <div id="modal" class="modal">
    <div class="modal-content">
      <span id="modal-close" class="close">&times;</span>
      <p id="modal-message"></p>
    </div>
  </div>

  <script>
    var modal = document.getElementById("modal");
    var modalMessage = document.getElementById("modal-message");
    var modalClose = document.getElementById("modal-close");

    modalClose.onclick = function() {
      modal.style.display = "none";
    };

    function showModal(message) {
      modalMessage.textContent = message;
      modal.style.display = "block";
    }

    <?php if (!empty($errorMessage)) { ?>
      showModal("<?php echo addslashes($errorMessage); ?>");
    <?php } elseif (!empty($successMessage)) { ?>
      showModal("<?php echo addslashes($successMessage); ?>");
      setTimeout(function() {
        window.location.href = "login.php";
      }, 3000);
    <?php } ?>
  </script>
</body>
</html>
