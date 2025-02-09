<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <style>
    /* Reset and basic styles */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    body {
      font-family: Arial, sans-serif;
      /* Hide scrollbars since the carousel covers the viewport */
      overflow: hidden;
    }
    /* Carousel container (background) */
    .carousel-container {
            width: 100vw;
            height: 100vh;
            overflow: hidden;
            position: relative;
        }

        .carousel-images {
            display: flex;
            width: 100%; /* 5 images */
            height: 100%;
            animation: slide 10s infinite;
        }

        .carousel-images img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            flex-shrink: 0;
        }
        .center-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            color: white;
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.7);

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
    /* Login form styling */
    form {
      background-color: rgba(255, 255, 255, 0.9); /* Slight transparency */
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      max-width: 400px;
      width: 90%;
      box-sizing: border-box;
      display: flex;
      flex-direction: column;
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      z-index: 1;
    }
    h2 {
      text-align: center;
      color: #333;
      margin-bottom: 20px;
    }
    label {
      font-size: 16px;
      color: #555;
      margin-bottom: 8px;
    }
    input[type="email"],
    input[type="password"] {
      width: 100%;
      padding: 10px;
      margin-bottom: 20px;
      border: 1px solid #ccc;
      border-radius: 4px;
      font-size: 16px;
      box-sizing: border-box;
    }
    button {
      width: 100%;
      padding: 10px;
      background-color: #333;
      color: #fff;
      font-size: 16px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }
    button:hover {
      background-color: #555;
    }
    /* Forgot Password link styling */
    .forgot-password {
      text-align: right;
      margin-top: -10px;
      margin-bottom: 15px;
    }
    .forgot-password a {
      font-size: 13px;
      color: #007bff;
      text-decoration: none;
    }
    .forgot-password a:hover {
      text-decoration: underline;
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

  <!-- Login Form -->
  <form action="authenticate.php" method="POST">
    <h2>Login</h2>
    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required>

    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required>

    <div class="forgot-password">
      <a href="forgot_password.php">Forgot Password?</a>
    </div>

    <button type="submit">Login</button>
  </form>
</body>
</html>
