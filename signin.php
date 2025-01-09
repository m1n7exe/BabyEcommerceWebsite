<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Full-Screen Photo Carousel</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            overflow: hidden;
        }

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

        .center-content h1 {
            font-size: 4rem;
            margin-bottom: 20px;
            
        }

        .center-content p {
            font-size: 1.5rem;
            margin-bottom: 20px;
        }

        .center-content button {
            font-size: 1.2rem;
            padding: 10px 20px;
            margin: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            background-color: #f08c35;
            color: white;
            transition: background-color 0.3s;
        }

        .center-content button:hover {
            background-color: #d4732b;
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
    </style>
</head>
<body>
    <div class="carousel-container">
        <div class="carousel-images">
            <img src="assets/homepagephotos/hp1.jpg" alt="Image 1">
            <img src="assets/homepagephotos/hp2.jpg" alt="Image 2">
            <img src="assets/homepagephotos/hp3.jpg" alt="Image 3">
            <img src="assets/homepagephotos/hp4.jpg" alt="Image 4">
            
            <img src="assets/homepagephotos/hp1.jpg" alt="Image 5">
        </div>

        <div class="center-content">
            <h1>Welcome to BabyBoo</h1>
            <p>Your one-stop destination for baby care!</p>
            <button onclick="window.location.href='login.php';">Log In</button>

            <button onclick="alert('Sign Up functionality coming soon!')">Sign Up</button>
        </div>
    </div>
</body>
</html>
