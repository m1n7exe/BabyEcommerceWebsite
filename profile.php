<?php
session_start();
require 'db_connection.php';

// Check if the user is logged in; if not, redirect to login page.
if (!isset($_SESSION['ShopperID'])) {
    header("Location: login.php");
    exit;
}

$shopperID = $_SESSION['ShopperID'];

// Fetch the current shopper data
$stmt = $conn->prepare("SELECT * FROM Shopper WHERE ShopperID = ?");
$stmt->bind_param("i", $shopperID);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    // Invalid user; redirect to login.
    header("Location: login.php");
    exit;
}
$currentData = $result->fetch_assoc();

// Remove the (65) prefix for display if present.
$displayPhone = $currentData['Phone'];
if (strpos($displayPhone, "(65)") === 0) {
    $displayPhone = trim(str_replace("(65)", "", $displayPhone));
}

$errorMessage = "";
$successMessage = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect updated data. For password, if the field is left empty, keep the old password.
    $name       = trim($_POST['name']);
    $email      = trim($_POST['email']);
    $newPassword= trim($_POST['password']);
    $password   = empty($newPassword) ? $currentData['Password'] : $newPassword;
    $address    = trim($_POST['address']);
    $phone      = trim($_POST['phone']);
    $birthDate  = trim($_POST['birthDate']);
    $country    = trim($_POST['country']);
    $pwdQuestion= trim($_POST['pwdQuestion']);
    $pwdAnswer  = trim($_POST['pwdAnswer']);

    // Basic validation for required fields (excluding password since it is optional to change)
    if (empty($name) || empty($email) || empty($address) || empty($phone) || empty($pwdAnswer)) {
        $errorMessage = "All required fields must be filled!";
    } else {
        // If the email has changed, check that it is not already used by another account.
        if ($email != $currentData['Email']) {
            $stmt = $conn->prepare("SELECT * FROM Shopper WHERE Email = ? AND ShopperID <> ?");
            $stmt->bind_param("si", $email, $shopperID);
            $stmt->execute();
            $resultEmail = $stmt->get_result();
            if ($resultEmail->num_rows > 0) {
                $errorMessage = "Email is already registered by another user!";
            }
        }

        if (empty($errorMessage)) {
            // Always prepend (65) to the phone number if not already present.
            if (strpos($phone, "(65)") !== 0) {
                $phone = "(65) " . $phone;
            }
            // Update the Shopper record with the new data.
            $stmt = $conn->prepare("UPDATE Shopper SET Name = ?, Email = ?, Password = ?, Address = ?, Phone = ?, BirthDate = ?, Country = ?, PwdQuestion = ?, PwdAnswer = ? WHERE ShopperID = ?");
            $stmt->bind_param("sssssssssi", $name, $email, $password, $address, $phone, $birthDate, $country, $pwdQuestion, $pwdAnswer, $shopperID);
            if ($stmt->execute()) {
                $successMessage = "Profile updated successfully!";
                // Refresh the current data so that the form reflects the latest changes.
                $stmt = $conn->prepare("SELECT * FROM Shopper WHERE ShopperID = ?");
                $stmt->bind_param("i", $shopperID);
                $stmt->execute();
                $result = $stmt->get_result();
                $currentData = $result->fetch_assoc();
                // Also update the display phone (remove the (65) prefix for display)
                $displayPhone = $currentData['Phone'];
                if (strpos($displayPhone, "(65)") === 0) {
                    $displayPhone = trim(str_replace("(65)", "", $displayPhone));
                }
            } else {
                $errorMessage = "Error updating profile. Please try again.";
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
  <title>My Profile</title>
  <link rel="stylesheet" href="styles.css"> <!-- Your existing CSS -->
  <style>
    /* Two-column compact layout for profile */
    body {
      font-family: Arial, sans-serif;
      background-color: #fbb6c9;
      margin: 0;
      padding: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
    }
    .profile-container {
      background: #ffffff;
      border-radius: 8px;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
      padding: 20px;
      max-width: 700px;
      width: 90%;
      margin: 20px;
      position: relative;
    }
    /* Updated Back Button with container styling */
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
      font-weight: bold;
    }
    /* Background for displayed details */
    p.detail {
      background-color: #f0f0f0;
      padding: 8px;
      border-radius: 4px;
      margin: 0 0 10px 0;
      font-size: 14px;
      color: #333;
    }
    /* Form styling (same as before) */
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
    /* Modal styles (for messages) */
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
    /* Edit mode toggle styles */
    #editProfile {
      display: none;
    }
  </style>
</head>
<body>
  <!-- Back Button -->
  <a href="index.php" class="back-button">&larr; Back</a>

  <div class="profile-container">
      <!-- View Mode: Display profile details -->
      <div id="viewProfile">
          <h2>My Profile</h2>
          <div class="row">
            <div class="column">
              <label>Name:</label>
              <p class="detail"><?php echo htmlspecialchars($currentData['Name']); ?></p>
            </div>
            <div class="column">
              <label>Phone:</label>
              <p class="detail"><?php echo htmlspecialchars($displayPhone); ?></p>
            </div>
          </div>
          <div class="row full">
            <label>Email:</label>
            <p class="detail"><?php echo htmlspecialchars($currentData['Email']); ?></p>
          </div>
          <div class="row full">
            <label>Address:</label>
            <p class="detail"><?php echo htmlspecialchars($currentData['Address']); ?></p>
          </div>
          <div class="row">
            <div class="column">
              <label>Birth Date:</label>
              <p class="detail"><?php echo htmlspecialchars($currentData['BirthDate']); ?></p>
            </div>
            <div class="column">
              <label>Country:</label>
              <p class="detail"><?php echo htmlspecialchars($currentData['Country']); ?></p>
            </div>
          </div>
          <div class="row full">
            <label>Security Question:</label>
            <p class="detail"><?php echo htmlspecialchars($currentData['PwdQuestion']); ?></p>
          </div>
          <div class="row full">
            <label>Security Answer:</label>
            <p class="detail"><?php echo htmlspecialchars($currentData['PwdAnswer']); ?></p>
          </div>
          <button id="editBtn" type="button">Edit Profile</button>
      </div>

      <!-- Edit Mode: Form for editing profile details -->
      <div id="editProfile">
          <h2>Edit Profile</h2>
          <form action="profile.php" method="POST">
              <!-- Row 1: Name and Phone -->
              <div class="row">
                <div class="column">
                  <label for="name">Name:</label>
                  <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($currentData['Name']); ?>" required>
                </div>
                <div class="column">
                  <label for="phone">Phone:</label>
                  <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($displayPhone); ?>" placeholder="e.g. 81238971" required>
                </div>
              </div>
              <!-- Row 2: Email and (Optional) New Password -->
              <div class="row">
                <div class="column">
                  <label for="email">Email:</label>
                  <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($currentData['Email']); ?>" required>
                </div>
                <div class="column">
                  <label for="password">New Password (leave blank to keep current):</label>
                  <input type="password" id="password" name="password" placeholder="Enter new password if desired">
                </div>
              </div>
              <!-- Row 3: Address (full width) -->
              <div class="row full">
                <label for="address">Address:</label>
                <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($currentData['Address']); ?>" required>
              </div>
              <!-- Row 4: Birth Date and Country -->
              <div class="row">
                <div class="column">
                  <label for="birthDate">Birth Date:</label>
                  <input type="date" id="birthDate" name="birthDate" value="<?php echo htmlspecialchars($currentData['BirthDate']); ?>">
                </div>
                <div class="column">
                  <label for="country">Country:</label>
                  <input type="text" id="country" name="country" value="<?php echo htmlspecialchars($currentData['Country']); ?>" required>
                </div>
              </div>
              <!-- Row 5: Security Question (full width) -->
              <div class="row full">
                <label for="pwdQuestion">Security Question:</label>
                <select id="pwdQuestion" name="pwdQuestion" required>
                    <option value="">-- Select a question --</option>
                    <option value="What is your mother's maiden name?" <?php if($currentData['PwdQuestion']=="What is your mother's maiden name?") echo "selected"; ?>>What is your mother's maiden name?</option>
                    <option value="What was the name of your first pet?" <?php if($currentData['PwdQuestion']=="What was the name of your first pet?") echo "selected"; ?>>What was the name of your first pet?</option>
                    <option value="How many siblings do you have?" <?php if($currentData['PwdQuestion']=="How many siblings do you have?") echo "selected"; ?>>How many siblings do you have?</option>
                    <option value="What is your favorite book?" <?php if($currentData['PwdQuestion']=="What is your favorite book?") echo "selected"; ?>>What is your favorite book?</option>
                </select>
              </div>
              <!-- Row 6: Security Answer (full width) -->
              <div class="row full">
                <label for="pwdAnswer">Answer:</label>
                <input type="text" id="pwdAnswer" name="pwdAnswer" value="<?php echo htmlspecialchars($currentData['PwdAnswer']); ?>" required>
              </div>
              <button type="submit">Save Changes</button>
              <button type="button" id="cancelEdit">Cancel</button>
          </form>
      </div>
  </div>

  <!-- Modal Structure -->
  <div id="modal" class="modal">
    <div class="modal-content">
      <span id="modal-close" class="close">&times;</span>
      <p id="modal-message"></p>
    </div>
  </div>

  <script>
    // Toggle between view mode and edit mode
    document.getElementById("editBtn").addEventListener("click", function(){
        document.getElementById("viewProfile").style.display = "none";
        document.getElementById("editProfile").style.display = "block";
    });

    document.getElementById("cancelEdit").addEventListener("click", function(){
        document.getElementById("editProfile").style.display = "none";
        document.getElementById("viewProfile").style.display = "block";
    });

    // Modal functionality for error/success messages
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
    <?php } ?>
  </script>
</body>
</html>
