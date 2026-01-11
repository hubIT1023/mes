
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
    body { font-family: 'Inter', sans-serif; }
  </style>
</head>

<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 flex flex-col min-h-screen">

  <!-- Top Navigation Bar -->
  <header class="bg-white dark:bg-gray-800 shadow-md py-4 px-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 sm:gap-0">
    <div class="flex items-center gap-3">
      <h1 class="text-xl font-bold text-blue-600 dark:text-blue-400">
        <?= htmlspecialchars($tenant['org_alias'] ?? $tenant['org_name']) ?> MMS
      </h1>
      <span class="hidden sm:inline text-gray-600 dark:text-gray-300"></span>
    </div>

    <!-- Navigation Links -->
    <!--nav class="flex flex-wrap gap-2 sm:gap-4">
      <a href="#" class="flex items-center px-3 py-2 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900 transition">
        <i class="fas fa-boxes mr-2 text-blue-600"></i> Assets
      </a>
      <a href="http://localhost/mes/app/views/forms_mms/config_maint_form.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900 transition">
        <i class="fas fa-clipboard-check mr-2 text-green-600"></i> Configuration
      </a>
      <a href="#" class="flex items-center px-3 py-2 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900 transition">
        <i class="fas fa-calendar-alt mr-2 text-purple-600"></i> Schedule Maintenance
      </a>
      <a href="#" class="flex items-center px-3 py-2 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900 transition">
        <i class="fas fa-bell mr-2 text-yellow-500"></i> Upcoming Maintenance
      </a>
      <a href="#" class="flex items-center px-3 py-2 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900 transition">
        <i class="fas fa-chart-line mr-2 text-red-500"></i> Reports
      </a>
      <a href="#" class="flex items-center px-3 py-2 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900 transition">
        <i class="fas fa-tools mr-2 text-gray-500"></i> Machine Parts
      </a-->

		<nav class="flex flex-wrap gap-2 sm:gap-4 space-y-1">
		  <a href="/mes/hub_portal" class="flex items-center px-3 py-2 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-blue-900/30 hover:text-blue-600 transition group">
			<i class="fas fa-th-large mr-3 text-gray-400 group-hover:text-blue-500"></i> 
			<span class="font-medium">Hub Portal</span>
		  </a>

		  <a href="/mes/signout" class="flex items-center px-3 py-2 rounded-lg text-red-600 hover:bg-red-50 dark:hover:bg-red-900/40 transition group">
			<i class="fas fa-power-off mr-3 opacity-80 group-hover:scale-110 transition-transform"></i> 
			<span class="font-medium">Logout</span>
		  </a>
		</nav>
		
  </header>

  <!-- Dashboard Content -->
  <main class="flex-grow p-6 overflow-y-auto">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">

      <!-- Register Assets -->
      <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow hover:shadow-lg transition flex flex-col">
        <i class="fas fa-boxes text-4xl text-blue-600 mb-4"></i>
        <h3 class="text-lg font-semibold mb-2">Register Assets</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4 flex-grow">
          Add and manage equipment, machines, and company assets.
        </p>
        <a href="mes/form_mms/addAsset" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-center inline-block">Go</a>
      </div>

      <!-- Maintenance Checklist -->
      <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow hover:shadow-lg transition flex flex-col">
        <i class="fas fa-clipboard-check text-4xl text-green-600 mb-4"></i>
        <h3 class="text-lg font-semibold mb-2">Checklists</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4 flex-grow">
          Define maintenance steps and inspections for each asset type.
        </p>
        <div class="flex flex-wrap gap-3">
          <a href="/mes/form_mms/checklist_template" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 inline-block text-center">Create Checklist</a>
          <a href="/mes/form_mms/checklists" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 inline-block text-center">List of Checklist</a>
        </div>
      </div>

      <!-- Schedule Maintenance -->
		<div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow hover:shadow-lg transition flex flex-col">
		  <i class="fas fa-calendar-alt text-4xl text-purple-600 mb-4"></i>
		  <h3 class="text-lg font-semibold mb-2">Work Orders</h3>
		  <p class="text-gray-600 dark:text-gray-300 mb-4 flex-grow">
			Plan calibration, preventive or corrective maintenance for your assets.
		  </p>
		  <div class="flex flex-wrap gap-3">
			<!-- ✅ FIXED: Added leading slash -->
			<a href="/mes/form_mms/routine_maintenance" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 inline-block text-center">Create WO</a>
			<a href="#" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 inline-block text-center">List of Work Order</a>
		  </div>
		</div>

		<!-- Monitor Upcoming -->
		<div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow hover:shadow-lg transition flex flex-col">
		  <i class="fas fa-bell text-4xl text-yellow-500 mb-4"></i>
		  <h3 class="text-lg font-semibold mb-2">Maintenance – Scheduled & In Progress</h3>
		  <p class="text-gray-600 dark:text-gray-300 mb-4 flex-grow">
			Track upcoming and ongoing maintenance tasks and receive alerts.
		  </p>
		  <div class="flex flex-wrap gap-3">
			<!-- ✅ Already correct -->
			<a href="/mes/dashboard_upcoming_maint" class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 inline-block text-center">Work Orders</a>
			<!-- ✅ Already correct -->
			<a href="/mes/completed_work_orders" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 inline-block text-center">Completed WO</a>
		  </div>
		</div>

      <!-- Reports -->
      <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow hover:shadow-lg transition flex flex-col">
        <i class="fas fa-chart-line text-4xl text-red-500 mb-4"></i>
        <h3 class="text-lg font-semibold mb-2">Reports & Analytics</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4 flex-grow">
          Generate reports on downtime, cost, and performance.
        </p>
		  <div class="flex flex-wrap gap-3">
			<a href="/mes/reports/machine-log" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 inline-block text-center">Maintenance Logs</a>
            <a href="/mes/reports/analytics" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 inline-block text-center">Analytics</a>
		  </div>
	  </div>

      <!-- Machine Parts -->
      <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow hover:shadow-lg transition flex flex-col">
        <i class="fas fa-tools text-4xl text-gray-500 mb-4"></i>
        <h3 class="text-lg font-semibold mb-2">Machine Parts</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4 flex-grow">
          Search and manage parts associated with each machine.
        </p>
        <a href="/mes/parts-list" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 inline-block text-center">Go</a>
      </div>

    </div>
  </main>
</body>
</html>

