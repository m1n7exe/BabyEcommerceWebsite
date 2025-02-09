<?php
require 'db_connection.php';

$step = 1; // Controls the step of the process (1: enter email, 2: answer security question, 3: show password)
$message = "";
$passwordDisplay = "";
$securityQuestion = "";
$email = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Step 1: User submits email
    if (isset($_POST['email']) && !isset($_POST['security_answer'])) {
        $email = trim($_POST['email']);
        if (empty($email)) {
            $message = "Please enter your email.";
        } else {
            // Query the database for this email.
            $stmt = $conn->prepare("SELECT PwdQuestion FROM Shopper WHERE Email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows == 0) {
                $message = "Email not found.";
            } else {
                $row = $result->fetch_assoc();
                $securityQuestion = $row['PwdQuestion'];
                $step = 2;
            }
        }
    }
    // Step 2: User submits answer to security question
    elseif (isset($_POST['email']) && isset($_POST['security_answer'])) {
        $email = trim($_POST['email']);
        $securityAnswerInput = trim($_POST['security_answer']);
        if (empty($securityAnswerInput)) {
            $message = "Please provide your answer.";
            $step = 2;
            // Re-fetch the security question to display.
            $stmt = $conn->prepare("SELECT PwdQuestion FROM Shopper WHERE Email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $securityQuestion = $row['PwdQuestion'];
            }
        } else {
            // Check if the answer is correct.
            $stmt = $conn->prepare("SELECT Password, PwdAnswer, PwdQuestion FROM Shopper WHERE Email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows == 0) {
                $message = "Email not found.";
                $step = 1;
            } else {
                $row = $result->fetch_assoc();
                $correctAnswer = $row['PwdAnswer'];
                if ($securityAnswerInput === $correctAnswer) {
                    $passwordDisplay = "Your password is: " . $row['Password'];
                    $step = 3;
                } else {
                    $message = "Incorrect answer. Please try again.";
                    $step = 2;
                    $securityQuestion = $row['PwdQuestion'];
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
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Forgot Password</title>
  <link rel="stylesheet" href="styles.css">
  <style>
    /* Ensure html and body take full viewport size */
    html, body {
      height: 100%;
      width: 100%;
      margin: 0;
      padding: 0;
    }
    body {
      font-family: Arial, sans-serif;
      background-color: #f8f9fa;
      /* Center the container using flexbox */
      display: flex;
      justify-content: center;
      align-items: center;
    }
    .forgot-container {
      background: #ffffff;
      border-radius: 8px;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
      padding: 20px;
      max-width: 400px;
      width: 100%;
    }
    h2 {
      text-align: center;
      margin-bottom: 20px;
      color: #343a40;
    }
    form {
      display: flex;
      flex-direction: column;
    }
    label {
      margin-bottom: 5px;
      color: #495057;
      font-size: 14px;
    }
    input {
      padding: 8px;
      margin-bottom: 10px;
      border: 1px solid #ced4da;
      border-radius: 4px;
      font-size: 14px;
    }
    button {
      padding: 10px;
      background-color: #007bff;
      border: none;
      border-radius: 4px;
      color: white;
      font-size: 16px;
      cursor: pointer;
    }
    button:hover {
      background-color: #0056b3;
    }
    .message {
      color: red;
      text-align: center;
      margin-bottom: 10px;
    }
    .back-link {
      display: block;
      text-align: center;
      margin-top: 10px;
      text-decoration: none;
      color: #007bff;
    }
    .back-link:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="forgot-container">
    <h2>Forgot Password</h2>
    <?php if (!empty($message)) { ?>
      <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <?php } ?>
    
    <?php if ($step == 1) { ?>
      <!-- Step 1: Enter email -->
      <form action="forgot_password.php" method="POST">
        <label for="email">Enter your registered email:</label>
        <input type="email" id="email" name="email" required>
        <button type="submit">Submit</button>
      </form>
    <?php } elseif ($step == 2) { ?>
      <!-- Step 2: Answer security question -->
      <form action="forgot_password.php" method="POST">
        <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
        <p>Your security question:</p>
        <p><strong><?php echo htmlspecialchars($securityQuestion); ?></strong></p>
        <label for="security_answer">Your Answer:</label>
        <input type="text" id="security_answer" name="security_answer" required>
        <button type="submit">Submit</button>
      </form>
    <?php } elseif ($step == 3) { ?>
      <!-- Step 3: Display password -->
      <p style="text-align: center; font-size: 16px;"><?php echo htmlspecialchars($passwordDisplay); ?></p>
      <a href="login.php" class="back-link">Return to Login</a>
    <?php } ?>
  </div>
</body>
</html>
