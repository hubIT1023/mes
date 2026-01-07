<!-- public/header.php -->

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta http-equiv="refresh" content="500">
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <title>hubIT.online</title>

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
  <!-- Your other styles -->
  <link href="../Assets/css/sb-admin-2.min.css" rel="stylesheet" />
  <link href="../Assets/css/custom-colors.css" rel="stylesheet"  />
  <link href="../Assets/css/custom-style.css" rel="stylesheet"  />
  <link href="../Assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet" />
  <style>
        /* Keeping only custom specific logic that Bootstrap doesn't have */
        body { background-color: #f8fafc; padding: 40px; }
        .custom-card { width: 400px; border-radius: 20px; transition: transform 0.2s; }
        .custom-card:hover { transform: translateY(-5px); }
        
        /* Pulse animation logic */
        .pulse-dot {
            width: 8px; height: 8px; border-radius: 50%; position: relative;
        }
        .pulse-dot::after {
            content: ''; position: absolute; width: 100%; height: 100%;
            background: inherit; border-radius: 50%; animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); opacity: 0.8; }
            70% { transform: scale(2.5); opacity: 0; }
            100% { transform: scale(1); opacity: 0; }
        }
    </style>
</head>
<body id="page-top bg-white">

