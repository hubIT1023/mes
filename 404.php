<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Building Something Awesome</title>
    <style>
        :root {
            --primary-color: #f39c12; /* Construction Orange */
            --dark-blue: #2c3e50;
            --light-gray: #ecf0f1;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--light-gray);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            overflow: hidden;
            color: var(--dark-blue);
        }

        .error-container {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            max-width: 600px;
            width: 90%;
            position: relative;
        }

        /* The "Floating" Animation */
        .illustration {
            font-size: 80px;
            margin-bottom: 20px;
            display: inline-block;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
            100% { transform: translateY(0px) rotate(0deg); }
        }

        h1 {
            font-size: 80px;
            margin: 0;
            line-height: 1;
            color: var(--primary-color);
        }

        h2 {
            margin-top: 0;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        p {
            font-size: 1.1rem;
            line-height: 1.6;
            color: #636e72;
        }

        /* Animated Construction Bar */
        .progress-bar {
            width: 100%;
            height: 10px;
            background: #dfe6e9;
            border-radius: 5px;
            margin: 25px 0;
            overflow: hidden;
            position: relative;
        }

        .progress-fill {
            width: 60%;
            height: 100%;
            background: repeating-linear-gradient(
                45deg,
                var(--primary-color),
                var(--primary-color) 10px,
                #2d3436 10px,
                #2d3436 20px
            );
            animation: slide 2s linear infinite;
        }

        @keyframes slide {
            from { background-position: 0 0; }
            to { background-position: 40px 0; }
        }

        .btn {
            display: inline-block;
            padding: 15px 35px;
            background-color: var(--dark-blue);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(44, 62, 80, 0.3);
        }

        .btn:hover {
            background-color: var(--primary-color);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(243, 156, 18, 0.4);
        }

        /* Subtle background decorations */
        .bg-icon {
            position: absolute;
            color: rgba(0,0,0,0.03);
            z-index: -1;
            user-select: none;
        }
    </style>
</head>
<body>

    <div class="bg-icon" style="top: 10%; left: 10%; font-size: 150px;">🛠️</div>
    <div class="bg-icon" style="bottom: 10%; right: 10%; font-size: 150px;">🏗️</div>

    <div class="error-container">
        <div class="illustration">🤖</div>
        <h1>404</h1>
        <h2>Under Construction</h2>
        <p>Our digital robot is currently laying bricks for this page. It's not quite ready for visitors yet, but it's going to be spectacular!</p>
        
        <div class="progress-bar">
            <div class="progress-fill"></div>
        </div>

        <a href="/mes/hub_portal" class="btn">Take Me Home</a>
    </div>

</body>
</html>