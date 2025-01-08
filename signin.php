<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
            box-sizing: border-box;
            text-align: center;
        }

        h2 {
            color: #333;
        }

        .button-container {
            margin-top: 20px;
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
            margin-bottom: 10px;
        }

        button:hover {
            background-color: #555;
        }

        .error-message {
            color: red;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Welcome</h2>
        <p>Choose an option below:</p>

        <div class="button-container">
            <!-- Sign Up Button (You can link this to your sign-up page later) -->
            <button onclick="window.location.href='signup.php';">Sign Up</button>
            
            <!-- Log In Button -->
            <button onclick="window.location.href='login.php';">Log In</button>
        </div>
    </div>

</body>
</html>
