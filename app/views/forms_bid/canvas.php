<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Canvas Pagination</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #f8f9fa;
        }
        .canvas-container {
            width: 100%;
            max-width: 800px;
            height: 400px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            background: white;
            display: flex;
            justify-content: center;
            align-items: center;
        }
    </style>
</head>
<body>
    <div class="canvas-container">
        <svg width="100%" height="100%" viewBox="0 0 200 200" preserveAspectRatio="xMidYMid meet">
            <!-- Dashed square -->
            <rect 
                x="50" 
                y="50" 
                width="100" 
                height="100" 
                fill="none" 
                stroke="#4a90e2" 
                stroke-width="2"
                stroke-dasharray="5,5"
                rx="4"
            />
            
            <!-- Plus sign -->
            <line 
                x1="100" 
                y1="80" 
                x2="100" 
                y2="120" 
                stroke="#4a90e2" 
                stroke-width="3"
                stroke-linecap="round"
            />
            <line 
                x1="80" 
                y1="100" 
                x2="120" 
                y2="100" 
                stroke="#4a90e2" 
                stroke-width="3"
                stroke-linecap="round"
            />
        </svg>
    </div>
</body>
</html>