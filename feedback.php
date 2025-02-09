<?php
session_start();
require 'db_connection.php';

// Check if the user is logged in; if not, redirect to login page.
$loggedIn = isset($_SESSION['ShopperID']);

$feedbackError = "";
$feedbackSuccess = "";

// Process form submission only if the user is logged in.
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $loggedIn) {
    $rank = trim($_POST['rank']);      // Expected value between 1 and 5.
    $subject = trim($_POST['subject']);  // Optional
    $content = trim($_POST['content']);  // Required
    $shopperID = $_SESSION['ShopperID'];

    // Basic validation: ensure feedback content is provided.
    if (empty($content)) {
        $feedbackError = "Please provide your feedback.";
    } else {
        // Insert the feedback into the Feedback table.
        $stmt = $conn->prepare("INSERT INTO Feedback (ShopperID, Subject, Content, Rank) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("issi", $shopperID, $subject, $content, $rank);
        if ($stmt->execute()) {
            $feedbackSuccess = "Thank you for your feedback!";
        } else {
            $feedbackError = "Error submitting feedback. Please try again later.";
        }
    }
}

// Retrieve all feedback entries (displayed to everybody) along with the user's name.
$query = "SELECT f.*, s.Name 
          FROM Feedback f 
          LEFT JOIN Shopper s ON f.ShopperID = s.ShopperID 
          ORDER BY f.DateTimeCreated DESC";
$resultFeedback = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Feedback</title>
  <link rel="stylesheet" href="styles.css"> <!-- Link to your site CSS if available -->
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #fbb6c9;
      margin: 0;
      padding: 20px;
      position: relative; /* Needed for positioning the back button */
    }
    .feedback-container {
      max-width: 700px;
      margin: 0 auto;
    }
    .feedback-form {
      background: #fff;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
      margin-bottom: 20px;
    }
    .feedback-form h2 {
      text-align: center;
      margin-bottom: 20px;
      color: #343a40;
    }
    .feedback-form label {
      display: block;
      margin-bottom: 5px;
      color: #495057;
      font-size: 14px;
    }
    .feedback-form input[type="text"],
    .feedback-form select,
    .feedback-form textarea {
      width: 100%;
      padding: 8px;
      margin-bottom: 10px;
      border: 1px solid #ced4da;
      border-radius: 4px;
      font-size: 14px;
      box-sizing: border-box;
    }
    .feedback-form button {
      display: block;
      width: 100%;
      padding: 10px;
      background-color: #007bff;
      color: #fff;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 16px;
    }
    .feedback-form button:hover {
      background-color: #0056b3;
    }
    .message {
      text-align: center;
      margin-bottom: 10px;
      font-size: 14px;
      color: red;
    }
    .success {
      color: green;
    }
    .feedback-list {
      background: #fff;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .feedback-list h2 {
      text-align: center;
      color: #343a40;
      margin-bottom: 20px;
    }
    .feedback-item {
      border-bottom: 1px solid #ccc;
      padding: 10px 0;
    }
    .feedback-item:last-child {
      border-bottom: none;
    }
    .feedback-item .subject {
      font-weight: bold;
      margin-bottom: 5px;
    }
    .feedback-item .author {
      color: #555;
      font-size: 14px;
    }
    .feedback-item .date {
      font-size: 12px;
      color: #888;
    }
    .feedback-item .content {
      margin-top: 5px;
      font-size: 14px;
      white-space: pre-wrap;
    }
    /* Back Button Styling */
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
  </style>
</head>
<body>
  <!-- Back Button -->
  <a href="index.php" class="back-button">&larr; Back</a>

  <div class="feedback-container">
    <div class="feedback-form">
      <h2>Post Your Feedback</h2>
      <?php if (!$loggedIn): ?>
        <p style="text-align: center;">Please <a href="login.php">login</a> to post feedback.</p>
      <?php else: ?>
        <?php if (!empty($feedbackError)): ?>
          <p class="message"><?php echo htmlspecialchars($feedbackError); ?></p>
        <?php elseif (!empty($feedbackSuccess)): ?>
          <p class="message success"><?php echo htmlspecialchars($feedbackSuccess); ?></p>
        <?php endif; ?>
        <form action="feedback.php" method="POST">
          <label for="rank">Rating (1 - 5):</label>
          <select name="rank" id="rank" required>
            <option value="1">1 - Poor</option>
            <option value="2">2 - Fair</option>
            <option value="3" selected>3 - Average</option>
            <option value="4">4 - Good</option>
            <option value="5">5 - Excellent</option>
          </select>
          <label for="subject">Subject (Optional):</label>
          <input type="text" id="subject" name="subject" placeholder="Enter a subject">
          <label for="content">Feedback:</label>
          <textarea id="content" name="content" rows="5" placeholder="Enter your feedback here..." required></textarea>
          <button type="submit">Submit Feedback</button>
        </form>
      <?php endif; ?>
    </div>

    <div class="feedback-list">
      <h2>Feedback from Our Customers</h2>
      <?php
      // Retrieve all feedback entries along with the member's name.
      $query = "SELECT f.*, s.Name 
                FROM Feedback f 
                LEFT JOIN Shopper s ON f.ShopperID = s.ShopperID 
                ORDER BY f.DateTimeCreated DESC";
      $resultAll = $conn->query($query);
      ?>
      <?php if ($resultAll->num_rows > 0): ?>
        <?php while ($row = $resultAll->fetch_assoc()): ?>
          <div class="feedback-item">
            <?php if (!empty($row['Subject'])): ?>
              <div class="subject"><?php echo htmlspecialchars($row['Subject']); ?></div>
            <?php endif; ?>
            <div class="author">
              By: <?php echo htmlspecialchars($row['Name']); ?> | Rating: <?php echo htmlspecialchars($row['Rank']); ?>/5
            </div>
            <div class="date"><?php echo htmlspecialchars($row['DateTimeCreated']); ?></div>
            <div class="content"><?php echo nl2br(htmlspecialchars($row['Content'])); ?></div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p style="text-align: center;">No feedback yet. Be the first to post!</p>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
