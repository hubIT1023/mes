<?php
// hub_portal.php — Tenant Dashboard

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ Check if tenant session exists
if (!isset($_SESSION['tenant']) || empty($_SESSION['tenant'])) {
    header("Location: /mes/signin?error=Please+log+in+first");
    exit;
}

$tenant = $_SESSION['tenant'];


?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($tenant['org_name'] ?? 'HubIT.online') ?> - Enterprise Portal</title>

  <link rel="icon" type="image/png" href="/../Assets/images/favicon.png">

  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="../assets/css/hub_portal.css" rel="stylesheet">
</head>

<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col">

  <!-- ✅ Header -->
  <header class="w-full bg-white dark:bg-gray-800 shadow-xl py-4 sticky top-0 z-10">
    <div class="max-w-6xl mx-auto flex items-center justify-between px-6 sm:px-8">
      <h1 class="text-2xl font-bold text-blue-600 dark:text-blue-400"> 
        <?= htmlspecialchars($tenant['org_name']) ?> Dashboard
      </h1>
      <div class="flex items-center gap-4">
        <a href="/mes/logout" 
           class="bg-red-500 hover:bg-red-600 text-white font-semibold px-4 py-2 rounded-lg shadow transition">
           <i class="fas fa-sign-out-alt mr-2"></i> Logout
        </a>
      </div>
    </div>
  </header>

  <!-- ✅ Main Content -->
  <main class="flex-grow flex items-center justify-center">
    <section id="management-systems" class="py-16 w-full">
      <div class="max-w-6xl mx-auto px-6 sm:px-8">
        <h2 class="text-4xl font-bold mb-14 text-center text-gray-800 dark:text-white">
          Enterprise Module Access
        </h2>

        <div class="flex flex-wrap gap-8 justify-center">

          <!-- HubIT Insights -->
          <a href="/mes/dashboard_admin" class="block">
            <div class="system-card bg-white dark:bg-gray-800 rounded-3xl shadow-xl p-8 w-80 text-center border-4 border-transparent hover:border-blue-600 dark:hover:border-blue-400">
              <i class="fas fa-chart-line text-6xl text-blue-600 dark:text-blue-400 mb-6"></i>
              <h3 class="text-2xl font-bold mb-3 text-slate-800 dark:text-white">HubIT Insights</h3>
              <p class="text-slate-600 dark:text-gray-300 text-sm">
                Monitor tool status, usage, and performance with real-time dashboards.
              </p>
            </div>
          </a>

          <!-- HubIT Maintain -->
          <a href="/mes/mms_admin" class="block">
            <div class="system-card bg-white dark:bg-gray-800 rounded-3xl shadow-xl p-8 w-80 text-center border-4 border-transparent hover:border-green-600 dark:hover:border-green-400">
              <i class="fas fa-wrench text-6xl text-green-600 dark:text-green-400 mb-6"></i>
              <h3 class="text-2xl font-bold mb-3 text-slate-800 dark:text-white">HubIT Maintain</h3>
              <p class="text-slate-600 dark:text-gray-300 text-sm">
                Plan, manage, and track preventive and corrective maintenance activities.
              </p>
            </div>
          </a>

          <!-- HubIT Sense -->
          <a href="/mes/demo3" class="block">
            <div class="system-card bg-white dark:bg-gray-800 rounded-3xl shadow-xl p-8 w-80 text-center border-4 border-transparent hover:border-yellow-500 dark:hover:border-yellow-300">
              <i class="fas fa-microchip text-6xl text-yellow-500 dark:text-yellow-300 mb-6"></i>
              <h3 class="text-2xl font-bold mb-3 text-slate-800 dark:text-white">HubIT Sense</h3>
              <p class="text-slate-600 dark:text-gray-300 text-sm">
                Connect, monitor, and analyze IoT sensor data across all assets.
              </p>
            </div>
          </a>

        </div>
      </div>
    </section>
  </main>

  <!-- ✅ Footer -->
  <footer class="bg-gray-200 dark:bg-gray-800 py-6 border-t border-gray-300 dark:border-gray-700">
    <div class="max-w-6xl mx-auto px-6 text-center text-gray-600 dark:text-gray-400 text-sm">
      © <span id="year"></span> HubIT.online — All rights reserved.
    </div>
  </footer>

  <script>
    document.getElementById("year").textContent = new Date().getFullYear();
  </script>
</body>
</html>
