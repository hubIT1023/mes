
<?php
// mms_admin.php — Maintenance Management System (Tenant Admin Dashboard)

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
  <title><?= htmlspecialchars($tenant['org_name']) ?> — Maintenance Management</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
    body {
      font-family: 'Inter', sans-serif;
    }
  </style>
</head>

<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 flex h-screen">

  <!-- ✅ Sidebar -->
  <aside class="w-64 bg-white dark:bg-gray-800 shadow-lg flex flex-col">
    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
      <h2 class="text-xl font-bold text-blue-600 dark:text-blue-400">
        <?= htmlspecialchars($tenant['org_alias'] ?? $tenant['org_name']) ?> MMS
      </h2>
    </div>

    <nav class="flex-grow p-4 space-y-2">
      <a href="#" class="flex items-center p-3 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900">
        <i class="fas fa-boxes mr-3 text-blue-600"></i> Assets
      </a>
      <a href="http://localhost/mes/app/views/forms_mms/config_maint_form.php" class="flex items-center p-3 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900">
        <i class="fas fa-clipboard-check mr-3 text-green-600"></i> Configuration
      </a>
      <a href="#" class="flex items-center p-3 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900">
        <i class="fas fa-calendar-alt mr-3 text-purple-600"></i> Schedule Maintenance
      </a>
      <a href="#" class="flex items-center p-3 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900">
        <i class="fas fa-bell mr-3 text-yellow-500"></i> Upcoming Maintenance
      </a>
      <a href="#" class="flex items-center p-3 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900">
        <i class="fas fa-chart-line mr-3 text-red-500"></i> Reports & Analytics
      </a>
      <a href="#" class="flex items-center p-3 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900">
        <i class="fas fa-tools mr-3 text-gray-500"></i> Machine Parts
      </a>
    </nav>

    <div class="p-4 border-t border-gray-200 dark:border-gray-700">
      <a href="/mes/logout" class="flex items-center p-3 rounded-lg hover:bg-red-100 dark:hover:bg-red-900 text-red-600">
        <i class="fas fa-sign-out-alt mr-3"></i> Logout
      </a>
    </div>
  </aside>

  <!-- ✅ Main Content -->
  <div class="flex-grow flex flex-col">
    
    <!-- Header -->
    <header class="w-full bg-white dark:bg-gray-800 shadow-md py-4 px-6 flex items-center justify-between">
      <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
        Maintenance Management System
      </h1>
      <div class="flex items-center gap-4">
		<a href="/mes/hub_portal" class="text-gray-700 dark:text-gray-300 hover:text-blue-500 font-medium">
		  Hub Portal
		</a>
        <button class="text-gray-600 dark:text-gray-300 hover:text-blue-500" aria-label="Notifications">
          <i class="fas fa-bell text-xl"></i>
        </button>
        <button class="text-gray-600 dark:text-gray-300 hover:text-blue-500" aria-label="Profile">
          <i class="fas fa-user-circle text-2xl"></i>
        </button>
      </div>
    </header>

    <!-- ✅ Dashboard Content -->
    <main class="flex-grow p-6 overflow-y-auto">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">

        <!-- Register Assets -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow hover:shadow-lg transition">
          <i class="fas fa-boxes text-4xl text-blue-600 mb-4"></i>
          <h3 class="text-lg font-semibold mb-2">Register Assets</h3>
          <p class="text-gray-600 dark:text-gray-300 mb-4">
            Add and manage equipment, machines, and company assets.
          </p>
          <a href="mes/form_mms/addAsset" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 inline-block">Go</a>
        </div>
		
		<!-- Maintenance Checklist -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow hover:shadow-lg transition">
          <i class="fas fa-clipboard-check text-4xl text-green-600 mb-4"></i>
          <h3 class="text-lg font-semibold mb-2">Checklists</h3>
          <p class="text-gray-600 dark:text-gray-300 mb-4">
            Define maintenance steps and inspections for each asset type.
          </p>
			  <div class="d-flex align-items-center gap-3">
				  <a href="/mes/form_mms/checklist_template" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 inline-block">Create Checklist</a>
				  <a href="/mes/form_mms/checklists" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 inline-block">List of Checklist</a>
			  </div>
        </div>

         <!-- Schedule Maintenance -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow hover:shadow-lg transition">
          <i class="fas fa-calendar-alt text-4xl text-purple-600 mb-4"></i>
          <h3 class="text-lg font-semibold mb-2">Work Orders</h3>
          <p class="text-gray-600 dark:text-gray-300 mb-4">
            Plan calibration, preventive or corrective maintenance for your assets.
          </p>
			  <div class="d-flex align-items-center gap-3">
				  <a href="mes/form_mms/routine_maintenance" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 inline-block">Create WO</a>
				  <a href="#" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 inline-block">List of Work Order </a>
			  </div>
        </div>
		

        <!-- Monitor Upcoming -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow hover:shadow-lg transition">
          <i class="fas fa-bell text-4xl text-yellow-500 mb-4"></i>
          <h3 class="text-lg font-semibold mb-2">Maintenance – Scheduled & In Progress</h3>
          <p class="text-gray-600 dark:text-gray-300 mb-4">
            Track upcoming and ongoing maintenance tasks and receive alerts.
          </p>
		  	<div class="d-flex align-items-center gap-3">
				<a href="/mes/dashboard_upcoming_maint" class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 inline-block">Work Orders</a>
				<a href="/mes/completed_work_orders" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 inline-block">Completed WO</a>
		    </div>
        </div>

        <!-- Reports -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow hover:shadow-lg transition">
          <i class="fas fa-chart-line text-4xl text-red-500 mb-4"></i>
          <h3 class="text-lg font-semibold mb-2">Reports & Analytics</h3>
          <p class="text-gray-600 dark:text-gray-300 mb-4">
            Generate reports on downtime, cost, and performance.
          </p>
          <a href="#" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 inline-block">Go</a>
        </div>

        <!-- Machine Parts -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow hover:shadow-lg transition">
          <i class="fas fa-tools text-4xl text-gray-500 mb-4"></i>
          <h3 class="text-lg font-semibold mb-2">Machine Parts</h3>
          <p class="text-gray-600 dark:text-gray-300 mb-4">
            Search and manage parts associated with each machine.
          </p>
          <a href="/mes/parts-list'" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 inline-block">Go</a>
        </div>

      </div>
    </main>
  </div>

</body>
</html>
