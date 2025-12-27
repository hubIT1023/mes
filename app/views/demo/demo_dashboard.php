<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	 <meta http-equiv="refresh" content="100"> <!-- Refreshes every 120 seconds (2 minutes) -->
    <title>Real-Time Dashboard</title>
    <!-- Use Tailwind CSS for rapid styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Chart.js for data visualization -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Chart.js Annotation Plugin for the threshold line -->
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation@1.0.2"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
            color: #1f2937;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 10vh;
            margin: 0;
            padding: 0;
        }
        .dashboard-card {
            background-color: white;
            padding: 2.5rem;
            border-radius: 1.5rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            max-width: 100rem;
            width: 100%;
            transition: all 0.5s ease-in-out;
            margin: 2rem;
        }
        .asset-box {
            position: relative;
            transition: all 0.5s ease-in-out;
            width: 22rem; /* Fixed width */
            height: auto; /* Corrected to auto for dynamic height */
        }
        .status-indicator {
            position: absolute;
            top: 1rem;
            right: 1rem;
            width: 1rem;
            height: 1rem;
            border-radius: 50%;
            transition: background-color 0.5s ease-in-out, box-shadow 0.5s ease-in-out;
        }
        .status-ok {
            background-color: #22c55e;
            box-shadow: 0 0 10px #86efac;
        }
        .status-alert {
            background-color: #ef4444;
            box-shadow: 0 0 20px #fca5a5;
            animation: pulse-red 1.5s infinite;
        }
        .data-value {
            font-weight: 700;
        }
        .alert-message {
            display: none;
            transition: all 0.5s ease-in-out;
        }
        .alert-shown {
            display: block;
        }
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-badge.IDLE { background-color: #e5e7eb; color: #4b5563; }
        .status-badge.PRODUCTION { background-color: #d1fae5; color: #065f46; }
        .status-badge.FAULT { background-color: #fee2e2; color: #991b1b; }
        .status-badge.MAINTENANCE { background-color: #fef3c7; color: #92400e; }
        .status-badge.QUAL { background-color: #bfdbfe; color: #1e40af; }
        .status-badge.PROD { background-color: #d1fae5; color: #065f46; }
        
        .value-highlight {
            animation: pulse-highlight 1.5s infinite;
        }

        @keyframes pulse-red {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
        }
        
        @keyframes pulse-highlight {
            0%, 100% {
                text-shadow: 0 0 5px rgba(255, 0, 0, 0.5);
            }
            50% {
                text-shadow: 0 0 20px rgba(255, 0, 0, 1);
            }
        }
        
        /* Modal styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease-in-out, visibility 0.3s ease-in-out;
        }

        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .modal-container {
            background-color: white;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1), 0 10px 15px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 400px;
            transform: translateY(-20px);
            transition: transform 0.3s ease-in-out;
            position: relative;
        }

        .modal-overlay.active .modal-container <!-- Sidebar -->
<nav id="accordionSidebar" class="sidebar bg-light accordion" role="navigation" aria-label="Main sidebar">

  <!-- Brand -->
  <div class="sidebar-brand d-flex align-items-center justify-content-center bg-primary py-3">
    <a href="/dashboard" class="sidebar-brand-link d-flex align-items-center">
      <img src="../../assets/img/hubIT_logo-v2.png" 
           alt="HubIT Dashboard" 
           class="img-fluid" 
           style="max-height: 70px;" />
    </a>
  </div>

  <hr class="sidebar-divider my-2">

  <!-- Dashboard -->
  <ul class="navbar-nav">
    <li class="nav-item">
      <a class="nav-link" href="/dashboard">
        <i class="fas fa-fw fa-tachometer-alt"></i>
        <span>Admin</span>
      </a>
    </li>

    <!-- Configure Menu -->
    <li class="nav-item">
      <a class="nav-link collapsed" href="#" 
         data-bs-toggle="collapse" 
         data-bs-target="#collapseConfigure" 
         aria-expanded="false" 
         aria-controls="collapseConfigure">
        <i class="fas fa-fw fa-cog"></i>
        <span>Configure</span>
      </a>
      <div id="collapseConfigure" class="collapse" aria-labelledby="headingConfigure" data-parent="#accordionSidebar">
        <ul class="collapse-inner list-unstyled ms-3">
          <li><a class="collapse-item" href="/meta-data-settings">Column Settings</a></li>
          <li><a class="collapse-item" href="/forms/state_config.php">State Config</a></li>
          <li><a class="collapse-item" href="generateStopcause.php">Generate Stopcause</a></li>
        </ul>
      </div>
    </li>

    <!-- Assets Section -->
    <li class="sidebar-heading mt-3">Assets</li>
    <li class="nav-item">
      <a class="nav-link" href="/assets-list">
        <i class="fas fa-fw fa-list"></i>
        <span>Asset List</span>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="/incoming-maintenance">
        <i class="fas fa-fw fa-wrench"></i>
        <span>Incoming Maintenance</span>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="/completed-work-orders">
        <i class="fas fa-fw fa-check"></i>
        <span>Completed Work Orders</span>
      </a>
    </li>

    <!-- Forms Section -->
    <li class="sidebar-heading mt-3">Forms</li>
    <li class="nav-item">
      <a class="nav-link" href="/manage-checklist-templates">
        <i class="fas fa-fw fa-tasks"></i>
        <span>Create Checklists</span>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="/scheduled-maintenance-form">
        <i class="fas fa-fw fa-calendar"></i>
        <span>Asset Maintenance Schedule</span>
      </a>
    </li>

    <!-- Database Section -->
    <li class="nav-item">
      <a class="nav-link collapsed" href="#" 
         data-bs-toggle="collapse" 
         data-bs-target="#collapseDatabase" 
         aria-expanded="false" 
         aria-controls="collapseDatabase">
        <i class="fas fa-fw fa-table"></i>
        <span>Database</span>
      </a>
      <div id="collapseDatabase" class="collapse" aria-labelledby="headingDatabase" data-parent="#accordionSidebar">
        <ul class="collapse-inner list-unstyled ms-3">
          <li><a class="collapse-item" href="/add-assets">Add Assets</a></li>
          <li><a class="collapse-item" href="/tool-state-log">Tool Status</a></li>
          <li><a class="collapse-item" href="/registered_assets">Assets Maintenance Schedule</a></li>
          <li><a class="collapse-item" href="/incoming-maintenance">Incoming Maintenance</a></li>
        </ul>
      </div>
    </li>

    <!-- Pages Section -->
    <li class="sidebar-heading mt-3">Addons</li>
    <li class="nav-item">
      <a class="nav-link collapsed" href="#" 
         data-bs-toggle="collapse" 
         data-bs-target="#collapsePages" 
         aria-expanded="false" 
         aria-controls="collapsePages">
        <i class="fas fa-fw fa-folder"></i>
        <span>Pages</span>
      </a>
      <div id="collapsePages" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
        <ul class="collapse-inner list-unstyled ms-3">
          <li class="collapse-header">Login Screens</li>
          <li><a class="collapse-item" href="login.html">Login</a></li>
          <li><a class="collapse-item" href="register.html">Register</a></li>
          <li><a class="collapse-item" href="forgot-password.html">Forgot Password</a></li>
          <li class="collapse-divider"></li>
          <li class="collapse-header">Other Pages</li>
          <li><a class="collapse-item" href="404.html">404 Page</a></li>
          <li><a class="collapse-item" href="blank.html">Blank Page</a></li>
        </ul>
      </div>
    </li>

    <!-- Charts -->
    <li class="nav-item">
      <a class="nav-link" href="charts.html">
        <i class="fas fa-fw fa-chart-area"></i>
        <span>Charts</span>
      </a>
    </li>
  </ul>

  <hr class="sidebar-divider d-none d-md-block">

  <!-- Sidebar Toggler -->
  <div class="text-center d-none d-md-inline">
    <button class="rounded-circle border-0" id="sidebarToggle" aria-label="Toggle sidebar"></button>
  </div>

</nav>
<!-- End of Sidebar -->
{
            transform: translateY(0);
        }

           /* Animated cursor styles */
        #demo-cursor {
            position: fixed; /* Changed to fixed for accurate positioning relative to viewport */
            width: 2.5rem;
            height: 2.5rem;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="%23facc15"><path d="M12.5 14.5L10 17 8.5 15.5 12.5 11.5 16.5 15.5 15 17zM12 2C6.486 2 2 6.486 2 12s4.486 10 10 10 10-4.486 10-10S17.514 2 12 2zm0 18c-4.411 0-8-3.589-8-8s3.589-8 8-8 8 3.589 8 8-3.589 8-8 8z"/></svg>');
            background-repeat: no-repeat;
            background-size: contain;
            transform: translate(-9999px, -9999px); /* Start off-screen */
            transition: transform 1.5s ease-in-out;
            z-index: 1001;
            opacity: 0;
        }
        
        #demo-cursor.active {
            opacity: 1;
        }
        
        @keyframes blink-cursor {
            0%, 100% { opacity: 1; }
            50% { opacity: 0; }
        }

        .blinking {
            animation: blink-cursor 0.5s linear 3;
        }

        .chart-container {
            display: none;
            width: 100%;
            height: 12rem; /* Adjusted height for better fit */
        }
        .chart-shown {
            display: block;
        }
    </style>
</head>
<body class="bg-gray-100 flex flex-col items-center min-h-screen p-0">

    <!-- New Navigation Bar -->
    <nav class="w-full bg-white shadow-md p-4 flex items-center justify-between z-50">
        <div class="text-2xl font-bold text-gray-800">
            HUB Dashboard
        </div>
        <div>
		
		<a href="demo_mes"
		   class="button inline-block bg-blue-600 
			      hover:bg-blue-700 text-white font-semibold px-8 py-2 
				  rounded-full shadow-lg transition-colors" >Try Next Demo
	    </a>
        </div>
    </nav>
    
    <div id="demo-cursor"></div>

    <div class="dashboard-card bg-white shadow-xl rounded-3xl p-10 space-y-8 flex-grow">
        <div class="text-center">
            <h1 class="text-3xl font-bold text-gray-800">Business Intelligence Dashboard</h1>
            <p class="text-gray-500 mt-2">Real-Time Data Collection </p>
        </div>

        <!-- Container for all three asset boxes -->
        <!-- Added 'lg:flex-nowrap' to prevent wrapping on large screens and `w-full` for responsiveness-->
        <div class="flex flex-wrap lg:flex-nowrap gap-8 justify-center w-full">
        
            <!-- SPUTTER-01 Monitoring Box -->
            <div id="sputter-asset-box" class="asset-box bg-gray-50 p-6 rounded-2xl border border-gray-200 transition-all duration-500 hover:shadow-lg">
                <div id="sputter-status-indicator" class="status-indicator status-ok"></div>
                <h2 id="sputter-label-btn" class="text-xl font-semibold text-gray-700 cursor-pointer">SPUTTER-01</h2>
                <p class="text-sm text-gray-400">Asset ID: SPUTTER-01</p>
                <div class="mt-2 flex justify-between items-center">
                    <p class="text-sm text-gray-500">WOF</p>
                    <p id="sputter-wof-due-date" class="text-sm text-gray-500">Due: Loading...</p>
                </div>
                <div class="w-full h-2 bg-gray-300 rounded-full overflow-hidden">
                    <div id="sputter-wof-bar" class="h-full rounded-full transition-all duration-500 ease-in-out" style="width: 100%;"></div>
                </div>
                
                <div class="mt-6 grid grid-cols-2 gap-4 text-left">
                    <div>
                        <p class="text-sm text-gray-500">Status</p>
                        <p id="sputter-status" class="text-2xl data-value text-gray-800"><span class="status-badge IDLE">IDLE</span></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Product Count</p>
                        <p id="sputter-product-count" class="text-2xl data-value text-gray-800">0</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Target Life (%)</p>
                        <p id="sputter-target-life" class="text-2xl data-value text-gray-800">100</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Cryo Pumps Life (%)</p>
                        <p id="sputter-cryo-life" class="text-2xl data-value text-gray-800">100</p>
                    </div>
                </div>
                
                <!-- Alert/Graph Location -->
                <div id="sputter-alert-location" class="mt-6">
                    <div id="sputter-alert-message" class="alert-message p-4 rounded-xl bg-red-50 text-red-700 border border-red-300">
                        <p class="font-bold">🚨 AUTOMATED ALERT!</p>
                        <p class="text-sm">Maintenance work order generated for a critical issue.</p>
                    </div>
                    <div id="sputter-chart-container" class="chart-container chart-shown">
                        <canvas id="sputter-chart"></canvas>
                    </div>
                </div>
            </div>

            <!-- CNC Machine 2B Monitoring Box -->
            <div id="cnc-asset-box" class="asset-box bg-gray-50 p-6 rounded-2xl border border-gray-200 transition-all duration-500 hover:shadow-lg">
                <div id="cnc-status-indicator" class="status-indicator status-ok"></div>
                <h2 id="cnc-label-btn" class="text-xl font-semibold text-gray-700 cursor-pointer">ETCH-01</h2>
                <p class="text-sm text-gray-400">Asset ID: ETCH-01</p>
                <div class="mt-2 flex justify-between items-center">
                    <p class="text-sm text-gray-500">WOF</p>
                    <p id="cnc-wof-due-date" class="text-sm text-gray-500">Due: Loading...</p>
                </div>
                <div class="w-full h-2 bg-gray-300 rounded-full overflow-hidden">
                    <div id="cnc-wof-bar" class="h-full rounded-full transition-all duration-500 ease-in-out" style="width: 100%;"></div>
                </div>
                
                <div class="mt-6 grid grid-cols-2 gap-4 text-left">
                    <div>
                        <p class="text-sm text-gray-500">Status</p>
                        <p id="cnc-status-value" class="text-2xl data-value text-gray-800"><span class="status-badge PRODUCTION">PRODUCTION</span></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Temperature (°C)</p>
                        <p id="cnc-temp-value" class="text-2xl data-value text-gray-800">22.5</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Pressure (bar)</p>
                        <p id="cnc-pressure-value" class="text-2xl data-value text-gray-800">650</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Vibration (g)</p>
                        <p id="cnc-vibration-value" class="text-2xl data-value text-gray-800">0.31</p>
                    </div>
                </div>
                
                <!-- Alert/Graph Location -->
                <div id="cnc-alert-location" class="mt-6">
                    <div id="cnc-alert-message" class="alert-message p-4 rounded-xl bg-red-50 text-red-700 border border-red-300">
                        <p class="font-bold">🚨 HIGH VIBRATION DETECTED!</p>
                        <p class="text-sm">Vibration levels are outside the normal range. Work order automatically generated.</p>
                    </div>
                    <div id="cnc-chart-container" class="chart-container chart-shown">
                        <canvas id="cnc-chart"></canvas>
                    </div>
                </div>
            </div>

            <!-- CO2 System Monitoring Box -->
            <div id="co2-asset-box" class="asset-box bg-gray-50 p-6 rounded-2xl border border-gray-200 transition-all duration-500 hover:shadow-lg">
                <div id="co2-status-indicator" class="status-indicator status-ok"></div>
                <h2 id="co2-label-btn" class="text-xl font-semibold text-gray-700 cursor-pointer">CO2 System Pump</h2>
                <p class="text-sm text-gray-400">Asset ID: CO2-PUMP-01</p>
                <div class="mt-2 flex justify-between items-center">
                    <p class="text-sm text-gray-500">WOF</p>
                    <p id="co2-wof-due-date" class="text-sm text-gray-500">Due: Loading...</p>
                </div>
                <div class="w-full h-2 bg-gray-300 rounded-full overflow-hidden">
                    <div id="co2-wof-bar" class="h-full rounded-full transition-all duration-500 ease-in-out" style="width: 100%;"></div>
                </div>

                <div class="mt-6 grid grid-cols-2 gap-4 text-left">
                    <div>
                        <p class="text-sm text-gray-500">Pressure (bar)</p>
                        <p id="co2-pressure-value" class="text-2xl data-value text-gray-800">650</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Tank Contents (%)</p>
                        <p id="co2-contents-value" class="text-2xl data-value text-gray-800">85</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Temperature (°C)</p>
                        <p id="co2-temp-value" class="text-2xl data-value text-gray-800">22.5</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Valve Cycles</p>
                        <p id="co2-valves-value" class="text-2xl data-value text-gray-800">12,500</p>
                    </div>
                </div>
                
                <!-- Alert/Graph Location -->
                <div id="co2-alert-location" class="mt-6">
                    <div id="co2-alert-message" class="alert-message p-4 rounded-xl bg-red-50 text-red-700 border border-red-300">
                        <p class="font-bold">🚨 CRITICAL ALERT!</p>
                        <p class="text-sm">Valve cycles are nearing max limit. Maintenance required immediately.</p>
                    </div>
                    <div id="co2-chart-container" class="chart-container">
                        <canvas id="co2-chart"></canvas>
                    </div>
                </div>
            </div>

            <!-- ENCUBATOR Monitoring Box -->
            <div id="encubator-asset-box" class="asset-box bg-gray-50 p-6 rounded-2xl border border-gray-200 transition-all duration-500 hover:shadow-lg">
                <div id="encubator-status-indicator" class="status-indicator status-ok"></div>
                <h2 id="encubator-label-btn" class="text-xl font-semibold text-gray-700 cursor-pointer">LTA_OVEN-01</h2>
                <p class="text-sm text-gray-400">Asset ID: LTA-01</p>
                <div class="mt-2 flex justify-between items-center">
                    <p class="text-sm text-gray-500">Encubation Period</p>
                    <p id="encubator-wof-due-date" class="text-sm text-gray-500">Due: Loading...</p>
                </div>
                <div class="w-full h-2 bg-gray-300 rounded-full overflow-hidden">
                    <div id="encubator-wof-bar" class="h-full rounded-full transition-all duration-500 ease-in-out" style="width: 100%;"></div>
                </div>
                
                <div class="mt-6 grid grid-cols-2 gap-4 text-left">
                    <div>
                        <p class="text-sm text-gray-500">Temperature (°C)</p>
                        <p id="encubator-temp-value" class="text-2xl data-value text-gray-800">25.0</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Humidity (%)</p>
                        <p id="encubator-humidity-value" class="text-2xl data-value text-gray-800">55</p>
                    </div>
                </div>

                <!-- Alert/Graph Location -->
                <div id="encubator-alert-location" class="mt-6 space-y-4">
                    <div id="encubator-alert-message" class="alert-message p-4 rounded-xl bg-red-50 text-red-700 border border-red-300">
                        <p class="font-bold">🚨 SENSOR ALERT!</p>
                        <p class="text-sm">Temperature or humidity is outside of the normal operating range.</p>
                    </div>
                    <div id="encubator-temp-chart-container" class="chart-container chart-shown">
                        <canvas id="encubator-temp-chart"></canvas>
                    </div>
                    <div id="encubator-humidity-chart-container" class="chart-container chart-shown">
                        <canvas id="encubator-humidity-chart"></canvas>
                    </div>
                </div>
            </div>

        </div>

        <div id="log-container" class="bg-gray-50 p-6 rounded-2xl border border-gray-200 h-64 overflow-y-scroll space-y-2">
            <h3 class="text-lg font-semibold text-gray-700">System Log</h3>
            <ul id="log-list" class="space-y-1 text-sm text-gray-600">
                <!-- Log entries will be added here by JS -->
            </ul>
        </div>
    </div>

    <!-- Modal for Intervention -->
    <div id="intervention-modal-overlay" class="modal-overlay">
        <div id="intervention-modal" class="modal-container">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Select Stoppage Cause</h3>
            <p class="text-gray-600 text-sm mb-4">Please specify the reason for human intervention. This will set the machine's status accordingly.</p>
            
            <div class="mb-4">
                <label for="machine-label-input" class="block text-gray-700 text-sm font-medium mb-2">Machine Label:</label>
                <input type="text" id="machine-label-input" readonly class="w-full p-3 rounded-md border border-gray-300 bg-gray-100 text-gray-700 cursor-not-allowed">
            </div>

            <div class="mb-6">
                <label for="stoppage-cause" class="block text-gray-700 text-sm font-medium mb-2">Cause:</label>
                <select id="stoppage-cause" class="w-full p-3 rounded-md border border-gray-300 focus:outline-none focus:border-indigo-500">
                    <option value="MAINTENANCE">MAINTENANCE</option>
                    <option value="QUAL">PROCESS</option>
                    <option value="PROD">Release to PROD</option>
                </select>
            </div>
            <div class="flex justify-end space-x-4">
                <button id="cancel-btn" class="bg-gray-200 text-gray-800 font-medium py-2 px-4 rounded-xl shadow hover:bg-gray-300 transition-colors">
                    Cancel
                </button>
                <button id="confirm-intervene-btn" class="bg-indigo-600 text-white font-medium py-2 px-4 rounded-xl shadow hover:bg-indigo-700 transition-colors">
                    Confirm
                </button>
            </div>
        </div>
    </div>

    <script>
	
		
		
        // SPUTTER-01 elements
        const sputterStatus = document.getElementById('sputter-status');
        const sputterTargetLife = document.getElementById('sputter-target-life');
        const sputterProductCount = document.getElementById('sputter-product-count');
        const sputterCryoLife = document.getElementById('sputter-cryo-life');
        const sputterStatusIndicator = document.getElementById('sputter-status-indicator');
        const sputterAlertMessage = document.getElementById('sputter-alert-message');
        const sputterAssetBox = document.getElementById('sputter-asset-box');
        const sputterLabelBtn = document.getElementById('sputter-label-btn');
        const sputterWofBar = document.getElementById('sputter-wof-bar');
        const sputterWofDueDate = document.getElementById('sputter-wof-due-date');
        const sputterChartContainer = document.getElementById('sputter-chart-container');
        const sputterChartCanvas = document.getElementById('sputter-chart');

        // CNC Machine 2B elements
        const cncStatusValue = document.getElementById('cnc-status-value');
        const cncTempValue = document.getElementById('cnc-temp-value');
        const cncPressureValue = document.getElementById('cnc-pressure-value');
        const cncVibrationValue = document.getElementById('cnc-vibration-value');
        const cncStatusIndicator = document.getElementById('cnc-status-indicator');
        const cncAlertMessage = document.getElementById('cnc-alert-message');
        const cncAssetBox = document.getElementById('cnc-asset-box');
        const cncLabelBtn = document.getElementById('cnc-label-btn');
        const cncWofBar = document.getElementById('cnc-wof-bar');
        const cncWofDueDate = document.getElementById('cnc-wof-due-date');
        const cncChartContainer = document.getElementById('cnc-chart-container');
        const cncChartCanvas = document.getElementById('cnc-chart');
        
        // CO2 System elements
        const co2PressureValue = document.getElementById('co2-pressure-value');
        const co2ContentsValue = document.getElementById('co2-contents-value');
        const co2TempValue = document.getElementById('co2-temp-value');
        const co2ValvesValue = document.getElementById('co2-valves-value');
        const co2StatusIndicator = document.getElementById('co2-status-indicator');
        const co2AlertMessage = document.getElementById('co2-alert-message');
        const co2AssetBox = document.getElementById('co2-asset-box');
        const co2LabelBtn = document.getElementById('co2-label-btn');
        const co2WofBar = document.getElementById('co2-wof-bar');
        const co2WofDueDate = document.getElementById('co2-wof-due-date');
        const co2ChartContainer = document.getElementById('co2-chart-container');
        const co2ChartCanvas = document.getElementById('co2-chart');
        
        // ENCUBATOR elements
        const encubatorTempValue = document.getElementById('encubator-temp-value');
        const encubatorHumidityValue = document.getElementById('encubator-humidity-value');
        const encubatorStatusIndicator = document.getElementById('encubator-status-indicator');
        const encubatorAlertMessage = document.getElementById('encubator-alert-message');
        const encubatorAssetBox = document.getElementById('encubator-asset-box');
        const encubatorLabelBtn = document.getElementById('encubator-label-btn');
        const encubatorWofBar = document.getElementById('encubator-wof-bar');
        const encubatorWofDueDate = document.getElementById('encubator-wof-due-date');
        const encubatorTempChartCanvas = document.getElementById('encubator-temp-chart');
        const encubatorHumidityChartCanvas = document.getElementById('encubator-humidity-chart');

        // Modal elements
        const interventionModalOverlay = document.getElementById('intervention-modal-overlay');
        const machineLabelInput = document.getElementById('machine-label-input');
        const stoppageCauseSelect = document.getElementById('stoppage-cause');
        const confirmInterveneBtn = document.getElementById('confirm-intervene-btn');
        const cancelBtn = document.getElementById('cancel-btn');

        const logList = document.getElementById('log-list');

        // Demo cursor element
        //const demoCursor = document.getElementById('demo-cursor');

        let cycle = 0;
        const ALERT_FREQUENCY = 5;
        let currentInterventionAsset = null;
        let sputterChart = null;
        let cncChart = null;
        let co2Chart = null; // Variable for the CO2 chart instance
        let encubatorTempChart = null;
        let encubatorHumidityChart = null;

        // SPUTTER-01 simulation variables
        let sputterTargetRemaining = 100;
        let sputterCryoRemaining = 100;
        let sputterProductCountValue = 0;
        let sputterWofDue = new Date("2025-09-15T12:00:00Z"); // Example future date
        let isSputterAlertActive = false;
        let sputterAlertTimeoutId = null; // New variable for the timeout ID
        let sputterIsIntervened = false;
        let sputterDailyProduction = [0, 0, 0, 0, 0];
        
        // CNC Machine simulation variables
        let cncWofDue = new Date("2025-09-05T12:00:00Z"); // Example due date
        let cncIsIntervened = false;
        let cncDailyDowntime = [0, 0, 0, 0, 0];
        
        // CO2 System simulation variables
        let co2WofDue = new Date("2025-08-30T12:00:00Z"); // Example overdue date
        let co2IsIntervened = false;
        const MAX_VALVE_CYCLES = 15000;
        const ORANGE_THRESHOLD = 0.8 * MAX_VALVE_CYCLES;
        const RED_THRESHOLD = 0.9 * MAX_VALVE_CYCLES;
        let co2DailyCycles = [10500, 11200, 11800, 12100, 12500]; // Initial dummy data

        // ENCUBATOR simulation variables
        let encubatorWofDue = new Date("2025-10-10T12:00:00Z");
        let encubatorIsIntervened = false;
        let encubatorHourlyTemp = generateRandomData(24, 23, 27);
        let encubatorHourlyHumidity = generateRandomData(24, 50, 60);
        const TEMP_RANGE = { min: 20, max: 28 };
        const HUMIDITY_RANGE = { min: 40, max: 60 };

        // Function to create and add a log entry
        function addLogEntry(message, type = 'info') {
            const li = document.createElement('li');
            li.textContent = `[${new Date().toLocaleTimeString()}] ${message}`;
            
            if (type === 'alert') {
                li.className = 'text-red-500 font-medium';
            } else {
                li.className = 'text-gray-600';
            }

            logList.prepend(li);
            if (logList.children.length > 10) {
                logList.removeChild(logList.lastChild);
            }
        }
        
        // Helper function to generate a random due date
        function getRandomDueDate() {
            const now = new Date();
            const randomDays = Math.floor(Math.random() * 60); // Random days between 0 and 59
            const randomDate = new Date(now.setDate(now.getDate() + randomDays));
            return randomDate;
        }

        /**
         * Calculates WOF progress and determines the color based on a completion percentage.
         * The percentage is based on the time elapsed since the last WOF, where 0% is
         * just after a WOF and 100% is when the next WOF is due.
         * @param {Date} dueDate The date the next WOF is due.
         * @returns {object} An object containing the completion percentage and color class.
         */
        function calculateWofProgress(dueDate) {
            const now = new Date();
            const totalCycleDays = 180; // Assuming a 6-month WOF cycle
            const startOfCycle = new Date(dueDate);
            startOfCycle.setDate(startOfCycle.getDate() - totalCycleDays);

            const daysSinceStart = Math.floor((now - startOfCycle) / (1000 * 60 * 60 * 24));
            const daysRemaining = Math.floor((dueDate - now) / (1000 * 60 * 60 * 24));

            let completionPercentage = (daysSinceStart / totalCycleDays) * 100;
            if (completionPercentage > 100) completionPercentage = 100;
            if (completionPercentage < 0) completionPercentage = 0;

            let colorClass = 'bg-green-500';
            let alertMessage = '';
            
            // New logic based on days remaining
            if (daysRemaining <= 0) {
                colorClass = 'bg-red-500';
                alertMessage = 'WOF is overdue!';
            } else if (daysRemaining <= 30) {
                colorClass = 'bg-orange-400';
                alertMessage = `WOF is due in less than 30 days.`;
            } else {
                colorClass = 'bg-green-500';
            }
            
            return {
                percentage: completionPercentage,
                colorClass: colorClass,
                alertMessage: alertMessage,
                daysRemaining: daysRemaining
            };
        }

        // Helper function to generate random data for the graphs
        function generateRandomData(count, min, max) {
            const data = [];
            for (let i = 0; i < count; i++) {
                data.push((Math.random() * (max - min + 1)) + min);
            }
            return data;
        }

        // Function to create the SPUTTER-01 production chart
        function createSputterChart() {
            if (sputterChart) {
                sputterChart.destroy();
            }
            sputterDailyProduction = generateRandomData(5, 100, 500);
            
            const backgroundColors = sputterDailyProduction.map(value => {
                const percentage = value / 500;
                if (percentage < 0.5) {
                    return 'rgba(239, 68, 68, 0.8)'; // Red
                } else if (percentage >= 0.5 && percentage < 0.9) {
                    return 'rgba(251, 191, 36, 0.8)'; // Orange
                } else {
                    return 'rgba(34, 197, 94, 0.8)'; // Green
                }
            });

            sputterChart = new Chart(sputterChartCanvas, {
                type: 'bar',
                data: {
                    labels: ['Day 1', 'Day 2', 'Day 3', 'Day 4', 'Day 5'],
                    datasets: [{
                        label: 'Daily Production (counts)',
                        data: sputterDailyProduction,
                        backgroundColor: backgroundColors,
                        borderColor: backgroundColors.map(color => color.replace('0.8', '1')),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 500, // Maximum production count
                            title: {
                                display: true,
                                text: 'Units Produced'
                            },
                        }
                    },
                    plugins: {
                        legend: {
                            display: false,
                        },
                        title: {
                            display: true,
                            text: 'Last 5 Days Production Output'
                        },
                        annotation: {
                            annotations: {
                                line1: {
                                    type: 'line',
                                    yMin: 425, // 85% of 500
                                    yMax: 425,
                                    borderColor: 'rgb(255, 99, 132)',
                                    borderWidth: 2,
                                    borderDash: [5, 5],
                                    label: {
                                        display: true,
                                        content: 'Threshold (85%)',
                                        position: 'start'
                                    }
                                }
                            }
                        }
                    }
                }
            });
        }

        // Function to create the CNC Machine downtime chart
        function createCncChart() {
            if (cncChart) {
                cncChart.destroy();
            }
            
            // Generate data for all three lines
            const cncDailyTotalDowntime = generateRandomData(5, 0, 60); // Random downtime in minutes
            const cncDailyIdleDowntime = generateRandomData(5, 0, 30);
            const cncDailyPmdowntime = generateRandomData(5, 0, 15);
            
            cncChart = new Chart(cncChartCanvas, {
                type: 'line',
                data: {
                    labels: ['Day 1', 'Day 2', 'Day 3', 'Day 4', 'Day 5'],
                    datasets: [{
                        label: 'Total Downtime (minutes)',
                        data: cncDailyTotalDowntime,
                        borderColor: 'rgb(54, 162, 235)',
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        tension: 0.1,
                        fill: true
                    }, {
                        label: 'Idle Downtime (minutes)',
                        data: cncDailyIdleDowntime,
                        borderColor: 'rgb(255, 159, 64)',
                        backgroundColor: 'rgba(255, 159, 64, 0.2)',
                        tension: 0.1,
                        fill: false
                    }, {
                        label: 'PM Downtime (minutes)',
                        data: cncDailyPmdowntime,
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        tension: 0.1,
                        fill: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Downtime (minutes)'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true
                        },
                        title: {
                            display: true,
                            text: 'Last 5 Days Downtime'
                        }
                    }
                }
            });
        }
        
        // Function to create the CO2 System line graph
        function createCo2Chart(data) {
            if (co2Chart) {
                co2Chart.destroy();
            }
            co2Chart = new Chart(co2ChartCanvas, {
                type: 'line',
                data: {
                    labels: ['Day 1', 'Day 2', 'Day 3', 'Day 4', 'Day 5'],
                    datasets: [{
                        label: 'Daily Valve Cycles',
                        data: data,
                        borderColor: 'rgb(54, 162, 235)',
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        fill: false,
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: false,
                            min: 10000,
                            max: MAX_VALVE_CYCLES,
                            title: {
                                display: true,
                                text: 'Valve Cycles'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: 'Last 5 Days Valve Cycles'
                        },
                        annotation: {
                            annotations: {
                                orangeLine: {
                                    type: 'line',
                                    yMin: ORANGE_THRESHOLD,
                                    yMax: ORANGE_THRESHOLD,
                                    borderColor: 'rgb(251, 191, 36)',
                                    borderWidth: 2,
                                    borderDash: [6, 6],
                                    label: {
                                        display: true,
                                        content: '80% Threshold',
                                        position: 'end'
                                    }
                                },
                                redLine: {
                                    type: 'line',
                                    yMin: RED_THRESHOLD,
                                    yMax: RED_THRESHOLD,
                                    borderColor: 'rgb(239, 68, 68)',
                                    borderWidth: 2,
                                    borderDash: [6, 6],
                                    label: {
                                        display: true,
                                        content: '90% Threshold',
                                        position: 'end'
                                    }
                                }
                            }
                        }
                    }
                }
            });
        }
        
        // Function to create the ENCUBATOR line graphs for temperature and humidity
        function createEncubatorCharts() {
            if (encubatorTempChart) {
                encubatorTempChart.destroy();
            }
            if (encubatorHumidityChart) {
                encubatorHumidityChart.destroy();
            }

            const now = new Date();
            const labels = [];
            for (let i = 23; i >= 0; i--) {
                const hour = (now.getHours() - i + 24) % 24;
                labels.push(`${hour}:00`);
            }

            encubatorTempChart = new Chart(encubatorTempChartCanvas, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Temperature (°C)',
                        data: encubatorHourlyTemp,
                        borderColor: '#2563eb', // Blue
                        backgroundColor: 'rgba(37, 99, 235, 0.2)',
                        fill: true,
                        tension: 0.4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: false,
                            min: 15,
                            max: 30,
                            title: {
                                display: true,
                                text: 'Temperature (°C/hr)'
                            },
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: 'Last 24 Hours Temperature'
                        },
                        annotation: {
                            annotations: {
                                upperTempThreshold: {
                                    type: 'line',
                                    yMin: TEMP_RANGE.max,
                                    yMax: TEMP_RANGE.max,
                                    borderColor: 'rgb(239, 68, 68)',
                                    borderWidth: 2,
                                    borderDash: [6, 6],
                                    label: {
                                        display: true,
                                        content: 'Max Threshold',
                                        position: 'end'
                                    }
                                },
                                lowerTempThreshold: {
                                    type: 'line',
                                    yMin: TEMP_RANGE.min,
                                    yMax: TEMP_RANGE.min,
                                    borderColor: 'rgb(239, 68, 68)',
                                    borderWidth: 2,
                                    borderDash: [6, 6],
                                    label: {
                                        display: true,
                                        content: 'Min Threshold',
                                        position: 'end'
                                    }
                                }
                            }
                        }
                    }
                }
            });

            encubatorHumidityChart = new Chart(encubatorHumidityChartCanvas, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Humidity (%)',
                        data: encubatorHourlyHumidity,
                        borderColor: '#10b981', // green-500
                        backgroundColor: 'rgba(16, 185, 129, 0.2)',
                        fill: true,
                        tension: 0.4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: false,
                            min: 35,
                            max: 65,
                            title: {
                                display: true,
                                text: 'Humidity (%/hr)'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: 'Last 24 Hours Humidity'
                        },
                        annotation: {
                            annotations: {
                                upperHumidThreshold: {
                                    type: 'line',
                                    yMin: HUMIDITY_RANGE.max,
                                    yMax: HUMIDITY_RANGE.max,
                                    borderColor: 'rgb(239, 68, 68)',
                                    borderWidth: 2,
                                    borderDash: [6, 6],
                                    label: {
                                        display: true,
                                        content: 'Max Threshold',
                                        position: 'end'
                                    }
                                },
                                lowerHumidThreshold: {
                                    type: 'line',
                                    yMin: HUMIDITY_RANGE.min,
                                    yMax: HUMIDITY_RANGE.min,
                                    borderColor: 'rgb(239, 68, 68)',
                                    borderWidth: 2,
                                    borderDash: [6, 6],
                                    label: {
                                        display: true,
                                        content: 'Min Threshold',
                                        position: 'end'
                                    }
                                }
                            }
                        }
                    }
                }
            });
        }


        // Function to force the SPUTTER-01 alert to clear
        function clearSputterAlert() {
            isSputterAlertActive = false;
            sputterStatus.innerHTML = `<span class="status-badge PRODUCTION">PRODUCTION</span>`;
            sputterStatusIndicator.classList.remove('status-alert');
            sputterStatusIndicator.classList.add('status-ok');
            sputterAlertMessage.classList.remove('alert-shown');
            sputterChartContainer.classList.add('chart-shown');
            sputterAssetBox.classList.remove('border-red-400');
            sputterAssetBox.style.boxShadow = '0 10px 25px rgba(0, 0, 0, 0.1)';
            addLogEntry('SPUTTER-01: Alert condition resolved. Automated systems cleared the fault.', 'info');
            sputterAlertTimeoutId = null; // Clear the timeout ID
        }
        
        // Function to update the CO2 System data and UI
        function updateCo2System() {
            let co2IsAlert = false;
            let co2Contents = (Math.random() * 20 + 80).toFixed(0);
            
            // Generate a random increment for the valve cycles
            let randomIncrement = Math.floor(Math.random() * 50) + 1;
            let lastCycleValue = co2DailyCycles[co2DailyCycles.length - 1];
            let newCycleValue = lastCycleValue + randomIncrement;

            // Ensure the value does not exceed the max
            if (newCycleValue > MAX_VALVE_CYCLES) {
                newCycleValue = MAX_VALVE_CYCLES;
            }

            // Update the daily cycles data array
            co2DailyCycles.shift(); // Remove the oldest value
            co2DailyCycles.push(newCycleValue); // Add the new value

            // Update CO2 System UI values with random data
            co2PressureValue.textContent = (Math.random() * 50 + 600).toFixed(0);
            co2ContentsValue.textContent = co2Contents;
            co2TempValue.textContent = (Math.random() * 3 + 20).toFixed(1);
            co2ValvesValue.textContent = newCycleValue.toLocaleString();

            // Reset color and highlight classes
            co2ValvesValue.classList.remove('text-orange-500', 'text-red-500', 'value-highlight');
            co2AlertMessage.classList.remove('alert-shown');
            co2StatusIndicator.classList.remove('status-alert');
            co2AssetBox.classList.remove('border-red-400');
            co2AssetBox.style.boxShadow = '0 10px 25px rgba(0, 0, 0, 0.1)';
            co2ChartContainer.classList.add('chart-shown'); // Show the chart by default
            
            // Check and apply new colors and highlights based on the thresholds
            if (newCycleValue >= RED_THRESHOLD) {
                co2ValvesValue.classList.add('text-red-500', 'value-highlight');
                co2AlertMessage.classList.add('alert-shown');
                co2ChartContainer.classList.remove('chart-shown');
                co2StatusIndicator.classList.add('status-alert');
                co2AssetBox.classList.add('border-red-400');
                co2AssetBox.style.boxShadow = '0 0 25px rgba(239, 68, 68, 0.4)';
                addLogEntry(`CO2 System: CRITICAL! Valve cycles at ${newCycleValue.toLocaleString()}.`, 'alert');
            } else if (newCycleValue >= ORANGE_THRESHOLD) {
                co2ValvesValue.classList.add('text-orange-500');
                addLogEntry(`CO2 System: WARNING! Valve cycles at ${newCycleValue.toLocaleString()}.`, 'info');
                createCo2Chart(co2DailyCycles); // Update the chart with the new data
            } else {
                createCo2Chart(co2DailyCycles); // Always update chart when no alert is shown
            }
            
            // Update WOF bar and due date
            const co2WofStatus = calculateWofProgress(co2WofDue);
            co2WofBar.style.width = `${co2WofStatus.percentage}%`;
            co2WofBar.className = `h-full rounded-full transition-all duration-500 ease-in-out ${co2WofStatus.colorClass}`;
            co2WofDueDate.textContent = `Due: ${co2WofDue.toLocaleDateString()}`;

            if (co2WofStatus.daysRemaining <= 0) {
                addLogEntry(`CO2 System: WOF is OVERDUE!`, 'alert');
            } else if (co2WofStatus.daysRemaining <= 30) {
                addLogEntry(`CO2 System: WOF expiring soon! Due: ${co2WofDue.toLocaleDateString()}.`, 'alert');
            }
        }

        // Function to update ENCUBATOR data and UI
        function updateEncubatorSystem() {
            let isAlert = false;
            let alertMessage = '';
            
            // Generate mock data
            const currentTemp = (Math.random() * 5 + 23).toFixed(1);
            const currentHumidity = (Math.random() * 10 + 50).toFixed(0);

            // Update UI
            encubatorTempValue.textContent = currentTemp;
            encubatorHumidityValue.textContent = currentHumidity;
            
            // Update the graph data arrays
            encubatorHourlyTemp.shift();
            encubatorHourlyTemp.push(parseFloat(currentTemp));
            encubatorHourlyHumidity.shift();
            encubatorHourlyHumidity.push(parseInt(currentHumidity));
            createEncubatorCharts();

            // Check for alerts
            if (currentTemp < TEMP_RANGE.min || currentTemp > TEMP_RANGE.max) {
                isAlert = true;
                alertMessage = `ENCUBATOR: Temperature is out of range (${currentTemp}°C).`;
            } else if (currentHumidity < HUMIDITY_RANGE.min || currentHumidity > HUMIDITY_RANGE.max) {
                isAlert = true;
                alertMessage = `ENCUBATOR: Humidity is out of range (${currentHumidity}%).`;
            }

            // Update the WOF bar
            const wofStatus = calculateWofProgress(encubatorWofDue);
            encubatorWofBar.style.width = `${wofStatus.percentage}%`;
            encubatorWofBar.className = `h-full rounded-full transition-all duration-500 ease-in-out ${wofStatus.colorClass}`;
            encubatorWofDueDate.textContent = `Due: ${encubatorWofDue.toLocaleDateString()}`;

            if (wofStatus.daysRemaining <= 0) {
                isAlert = true;
                alertMessage = `ENCUBATOR: WOF is OVERDUE!`;
            } else if (wofStatus.daysRemaining <= 30) {
                isAlert = true;
                alertMessage = `ENCUBATOR: WOF expiring soon! Due: ${encubatorWofDue.toLocaleDateString()}.`;
            }
            
            // Final UI state based on alert status
            if (isAlert) {
                encubatorStatusIndicator.classList.remove('status-ok');
                encubatorStatusIndicator.classList.add('status-alert');
                encubatorAlertMessage.classList.add('alert-shown');
                encubatorAssetBox.classList.add('border-red-400');
                encubatorAssetBox.style.boxShadow = '0 0 25px rgba(239, 68, 68, 0.4)';
                addLogEntry(alertMessage, 'alert');
            } else {
                encubatorStatusIndicator.classList.remove('status-alert');
                encubatorStatusIndicator.classList.add('status-ok');
                encubatorAlertMessage.classList.remove('alert-shown');
                encubatorAssetBox.classList.remove('border-red-400');
                encubatorAssetBox.style.boxShadow = '0 10px 25px rgba(0, 0, 0, 0.1)';
            }
        }

        // Main function to simulate data and update the UI
        function updateDashboard() {
            cycle++;
            
            // --- SPUTTER-01 Logic ---
            if (!sputterIsIntervened) {
                let isSputterAlert = false;
                let alertDescription = '';

                // Trigger a new alert only if one isn't already active
                if (!isSputterAlertActive) {
                    const randomState = Math.random();
                    if (randomState < 0.1) {
                        isSputterAlert = true;
                        alertDescription = `SPUTTER-01: System fault detected. Manual inspection required.`;
                    } else if (randomState < 0.2) {
                        isSputterAlert = true;
                        alertDescription = `SPUTTER-01: Scheduled maintenance is incoming. Work order generated.`;
                    }
                }

                // Update values during production
                sputterTargetRemaining = Math.max(0, sputterTargetRemaining - Math.random() * 0.5);
                sputterCryoRemaining = Math.max(0, sputterCryoRemaining - Math.random() * 0.2);
                sputterProductCountValue += Math.floor(Math.random() * 5) + 1;
                
                // Check for other alert conditions
                if (sputterTargetRemaining <= 10 && !isSputterAlertActive) {
                    isSputterAlert = true;
                    alertDescription = `SPUTTER-01: Target life at ${sputterTargetRemaining.toFixed(0)}%. Order replacement target.`;
                } else if (sputterCryoRemaining <= 10 && !isSputterAlertActive) {
                    isSputterAlert = true;
                    alertDescription = `SPUTTER-01: Cryo pumps life at ${sputterCryoRemaining.toFixed(0)}%. Schedule maintenance.`;
                }

                const sputterWofStatus = calculateWofProgress(sputterWofDue);
                if (sputterWofStatus.daysRemaining <= 0 && !isSputterAlertActive) {
                    isSputterAlert = true;
                    alertDescription = `SPUTTER-01: WOF is OVERDUE!`;
                } else if (sputterWofStatus.daysRemaining <= 30 && !isSputterAlertActive) {
                    isSputterAlert = true;
                    alertDescription = `SPUTTER-01: WOF expiring soon! Due: ${sputterWofDue.toLocaleDateString()}.`;
                }

                // Update SPUTTER-01 UI values
                sputterTargetLife.textContent = sputterTargetRemaining.toFixed(0);
                sputterCryoLife.textContent = sputterCryoRemaining.toFixed(0);
                sputterProductCount.textContent = sputterProductCountValue.toLocaleString();
                sputterWofBar.style.width = `${sputterWofStatus.percentage}%`;
                sputterWofBar.className = `h-full rounded-full transition-all duration-500 ease-in-out ${sputterWofStatus.colorClass}`;
                sputterWofDueDate.textContent = `Due: ${sputterWofDue.toLocaleDateString()}`;

                // Final UI state based on alert status
                if (isSputterAlert) {
                    isSputterAlertActive = true;
                    sputterStatus.innerHTML = `<span class="status-badge FAULT">FAULT</span>`;
                    sputterStatusIndicator.classList.remove('status-ok');
                    sputterStatusIndicator.classList.add('status-alert');
                    sputterAlertMessage.classList.add('alert-shown');
                    sputterChartContainer.classList.remove('chart-shown');
                    sputterAssetBox.classList.add('border-red-400');
                    sputterAssetBox.style.boxShadow = '0 0 25px rgba(239, 68, 68, 0.4)';
                    addLogEntry(alertDescription, 'alert');
                    
                    // Set a timeout to clear the alert
                    if (!sputterAlertTimeoutId) {
                        sputterAlertTimeoutId = setTimeout(() => {
                            clearSputterAlert();
                        }, 15000); // 15 seconds
                    }

                } else if (!isSputterAlertActive) {
                    sputterStatus.innerHTML = `<span class="status-badge PRODUCTION">PRODUCTION</span>`;
                    sputterStatusIndicator.classList.remove('status-alert');
                    sputterStatusIndicator.classList.add('status-ok');
                    sputterAlertMessage.classList.remove('alert-shown');
                    sputterChartContainer.classList.add('chart-shown');
                    sputterAssetBox.classList.remove('border-red-400');
                    sputterAssetBox.style.boxShadow = '0 10px 25px rgba(0, 0, 0, 0.1)';
                }
            }
            
            // --- CNC Machine 2B Logic ---
            if (!cncIsIntervened) {
                // Randomly adjust due date
                cncWofDue = getRandomDueDate();
                
                let cncIsAlert = false;
                let cncVibration = (Math.random() * 0.4 + 0.1).toFixed(2);
                let cncStatus = 'PRODUCTION';
                
                if (cycle % ALERT_FREQUENCY === 0) {
                    cncVibration = (Math.random() * 0.5 + 0.8).toFixed(2);
                    cncIsAlert = true;
                    cncStatus = 'FAULT';
                }

                // Update CNC Machine 2B UI values with random data
                cncStatusValue.innerHTML = `<span class="status-badge ${cncStatus}">${cncStatus}</span>`;
                cncTempValue.textContent = (Math.random() * 5 + 80).toFixed(0);
                cncPressureValue.textContent = (Math.random() * 10 + 100).toFixed(0);
                cncVibrationValue.textContent = cncVibration;

                // Update WOF bar and due date
                const cncWofStatus = calculateWofProgress(cncWofDue);
                cncWofBar.style.width = `${cncWofStatus.percentage}%`;

                // Custom logic for the CNC indicator color based on days remaining
                const today = new Date();
                const diffTime = Math.abs(cncWofDue - today);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                
                let indicatorColorClass = 'bg-green-500'; // Default color
                if (diffDays <= 14) {
                    indicatorColorClass = 'bg-yellow-500'; // Due in two weeks
                }
                cncWofBar.className = `h-full rounded-full transition-all duration-500 ease-in-out ${indicatorColorClass}`;
                cncWofDueDate.textContent = `Due: ${cncWofDue.toLocaleDateString()}`;

                if (cncIsAlert) {
                    cncStatusIndicator.classList.remove('status-ok');
                    cncStatusIndicator.classList.add('status-alert');
                    cncAlertMessage.classList.add('alert-shown');
                    cncChartContainer.classList.remove('chart-shown');
                    cncAssetBox.classList.add('border-red-400');
                    cncAssetBox.style.boxShadow = '0 0 25px rgba(239, 68, 68, 0.4)';
                    addLogEntry(`CNC Machine 2B: High vibration detected! Vibration: ${cncVibration} g.`, 'alert');
                } else {
                    cncStatusIndicator.classList.remove('status-alert');
                    cncStatusIndicator.classList.add('status-ok');
                    cncAlertMessage.classList.remove('alert-shown');
                    cncChartContainer.classList.add('chart-shown');
                    cncAssetBox.classList.remove('border-red-400');
                    cncAssetBox.style.boxShadow = '0 10px 25px rgba(0, 0, 0, 0.1)';
                    addLogEntry('CNC Machine 2B: Operating normally.');
                }
            }
            
            // Update the CO2 System
            if (!co2IsIntervened) {
                updateCo2System();
            }

            // Update the ENCUBATOR
            if (!encubatorIsIntervened) {
                updateEncubatorSystem();
            }
        }

        // Function to show the modal and store the asset
        function showInterventionModal(asset, label) {
            currentInterventionAsset = asset;
            machineLabelInput.value = label; // Populate the new input field
            interventionModalOverlay.classList.add('active');
        }

        // Function to hide the modal
        function hideInterventionModal() {
            interventionModalOverlay.classList.remove('active');
        }

        // Function to handle the modal confirmation
        function handleConfirm() {
            const stoppageCause = stoppageCauseSelect.value;
            let assetName = '';
            let statusElement = null;
            let indicatorElement = null;
            let alertElement = null;
            let assetBoxElement = null;
            let wofBarElement = null;
            let chartContainerElement = null;

            // Determine which asset is being intervened
            if (currentInterventionAsset === 'sputter') {
                sputterIsIntervened = true;
                assetName = 'SPUTTER-01';
                statusElement = sputterStatus;
                indicatorElement = sputterStatusIndicator;
                alertElement = sputterAlertMessage;
                assetBoxElement = sputterAssetBox;
                wofBarElement = sputterWofBar;
                chartContainerElement = sputterChartContainer;
                const newWofDue = new Date();
                newWofDue.setDate(newWofDue.getDate() + 180);
                sputterWofDue = newWofDue;
            } else if (currentInterventionAsset === 'cnc') {
                cncIsIntervened = true;
                assetName = 'CNC Machine 2B';
                statusElement = cncStatusValue;
                indicatorElement = cncStatusIndicator;
                alertElement = cncAlertMessage;
                assetBoxElement = cncAssetBox;
                wofBarElement = cncWofBar;
                chartContainerElement = cncChartContainer;
                const newWofDue = new Date();
                newWofDue.setDate(newWofDue.getDate() + 180);
                cncWofDue = newWofDue;
            } else if (currentInterventionAsset === 'co2') {
                co2IsIntervened = true;
                assetName = 'CO2 System';
                indicatorElement = co2StatusIndicator;
                alertElement = co2AlertMessage;
                assetBoxElement = co2AssetBox;
                wofBarElement = co2WofBar;
                chartContainerElement = co2ChartContainer; // Reference the new chart container
                const newWofDue = new Date();
                newWofDue.setDate(newWofDue.getDate() + 180);
                co2WofDue = newWofDue;
            } else if (currentInterventionAsset === 'encubator') {
                encubatorIsIntervened = true;
                assetName = 'ENCUBATOR';
                indicatorElement = encubatorStatusIndicator;
                alertElement = encubatorAlertMessage;
                assetBoxElement = encubatorAssetBox;
                wofBarElement = encubatorWofBar;
                const newWofDue = new Date();
                newWofDue.setDate(newWofDue.getDate() + 180);
                encubatorWofDue = newWofDue;
            }

            // Update UI based on the selected cause
            if (stoppageCause) {
                if (currentInterventionAsset === 'co2') {
                    // For the CO2 system, we'll just set tank contents to 100% and reset valve cycles
                    co2ContentsValue.textContent = '100';
                    co2ValvesValue.textContent = '0';
                    co2ValvesValue.classList.remove('text-orange-500', 'text-red-500', 'value-highlight');
                    co2StatusIndicator.classList.remove('status-alert');
                    co2AssetBox.classList.remove('border-red-400');
                    co2AssetBox.style.boxShadow = '0 10px 25px rgba(0, 0, 0, 0.1)';
                    co2AlertMessage.classList.remove('alert-shown');
                } else {
                    if (statusElement) {
                        statusElement.innerHTML = `<span class="status-badge ${stoppageCause}">${stoppageCause.replace('QUAL', 'Quality').replace('PROD', 'Production')}</span>`;
                    }
                }

                // Update WOF bar and due date
                if (wofBarElement) {
                    const wofStatus = calculateWofProgress(wofBarElement);
                    wofBarElement.style.width = `${wofStatus.percentage}%`;
                    wofBarElement.className = `h-full rounded-full transition-all duration-500 ease-in-out ${wofStatus.colorClass}`;
                }
                
                if (indicatorElement) {
                    indicatorElement.classList.remove('status-alert');
                    indicatorElement.classList.add('status-ok');
                }
                if (alertElement) {
                    alertElement.classList.remove('alert-shown');
                }
                if (assetBoxElement) {
                    assetBoxElement.classList.remove('border-red-400');
                    assetBoxElement.style.boxShadow = '0 10px 25px rgba(0, 0, 0, 0.1)';
                }
                
                addLogEntry(`${assetName}: Human intervention initiated. Stoppage cause: ${stoppageCause}.`, 'info');
                
                // Allow simulation to resume after a delay
                setTimeout(() => {
                    if (currentInterventionAsset === 'sputter') sputterIsIntervened = false;
                    if (currentInterventionAsset === 'cnc') cncIsIntervened = false;
                    if (currentInterventionAsset === 'co2') co2IsIntervened = false;
                    if (currentInterventionAsset === 'encubator') encubatorIsIntervened = false;
                    addLogEntry(`${assetName}: Intervention complete. Resuming automated monitoring.`);
                }, 10000);
            }
            
            hideInterventionModal();
        }

        // Add event listeners for the new buttons
        sputterLabelBtn.addEventListener('click', () => showInterventionModal('sputter', sputterLabelBtn.textContent));
        cncLabelBtn.addEventListener('click', () => showInterventionModal('cnc', cncLabelBtn.textContent));
        co2LabelBtn.addEventListener('click', () => showInterventionModal('co2', co2LabelBtn.textContent));
        encubatorLabelBtn.addEventListener('click', () => showInterventionModal('encubator', encubatorLabelBtn.textContent));

        // Add event listeners for the modal buttons
        confirmInterveneBtn.addEventListener('click', handleConfirm);
        cancelBtn.addEventListener('click', hideInterventionModal);

        // Start the simulation loop
        setInterval(updateDashboard, 3000);

        // Initial update to show something immediately
        updateDashboard();

        // --- DEMO AUTOMATION with CURSOR ---

        // Utility function to create a promise that resolves after a delay
        const delay = ms => new Promise(res => setTimeout(res, ms));

        // Helper function to animate cursor, blink, and then perform a "click"
        async function performClick(element, callback) {
			const rect = element.getBoundingClientRect();
			const cursorRect = demoCursor.getBoundingClientRect();

			// Ensure element is in view
			if (
				rect.top < 0 ||
				rect.left < 0 ||
				rect.bottom > window.innerHeight ||
				rect.right > window.innerWidth
			) {
				element.scrollIntoView({ behavior: 'smooth', block: 'center' });
				await delay(1000);
			}

			// Recalculate after scroll
			const updatedRect = element.getBoundingClientRect();

			// 👉 Target the RIGHT side of the select (where the ^ is)
			const targetX = updatedRect.right - 20; // Slightly left of the far right edge
			const targetY = updatedRect.top + updatedRect.height / 2;

			// Center the cursor on that point
			const x = targetX - cursorRect.width / 2 -960;
			const y = targetY - cursorRect.height / 2;

			// Approach from slightly above and left
			const hoverX = x - 15;
			const hoverY = y - 15;

			demoCursor.classList.add('active');

			// 1. Move near the dropdown arrow (approach)
			demoCursor.style.transform = `translate(${hoverX}px, ${hoverY}px)`;
			await delay(600);

			// 2. Snap to final position near the `^`
			demoCursor.style.transform = `translate(${x}px, ${y}px)`;
			await delay(400); // Hover pause — user is deciding

			// 🔥 ADD THE TOOLTIP HERE 🔥
			const label = document.createElement('div');
			label.textContent = "Clicking ▼";
			label.style.position = 'fixed';
			label.style.left = `${targetX - 100}px`;
			label.style.top = `${targetY -5}px`;
			label.style.fontSize = '12px';
			label.style.color = '#D20103'; 
			label.style.fontWeight = 'bold';
			label.style.pointerEvents = 'none'; // So it doesn't interfere with clicks
			label.style.zIndex = '1002';
			//label.style.textShadow = '0 0 3px black';
			document.body.appendChild(label);
			await delay(800); // Keep label visible for ~0.8s
			document.body.removeChild(label);
			// 🔚 TOOLTIP ENDS HERE 🔚

			// 3. Blink to simulate click
			demoCursor.classList.add('blinking');
			await delay(1500);
			demoCursor.classList.remove('blinking');

			// Execute the callback (e.g., open dropdown)
			if (callback) callback();
		}
        
        async function runDemo() {
			addLogEntry('[DEMO] Starting automated intervention demo with cursor.', 'info');
			await delay(2000);

			addLogEntry('[DEMO] Simulating click on "CNC Machine 2B" label.', 'info');
			await performClick(cncLabelBtn, () => {
				showInterventionModal('cnc', 'CNC Machine 2B');
			});

			// Wait for modal to fully appear and animate in
			await delay(3000);

			const optionsToCycle = ['MAINTENANCE', 'QUAL', 'PROD'];
			for (const option of optionsToCycle) {
				addLogEntry(`[DEMO] Selecting "${option}" from dropdown.`, 'info');

				// Open the dropdown (click on <select>)
				await performClick(stoppageCauseSelect, () => {
					stoppageCauseSelect.value = option;
				});

				// Visual pause to simulate reading options
				await delay(2000);
			}

			addLogEntry('[DEMO] Clicking "Confirm" to complete intervention.', 'info');
			await performClick(confirmInterveneBtn, () => {
				handleConfirm();
			});

			// Reset cursor
			demoCursor.classList.remove('active');
			demoCursor.style.transform = 'translate(-9999px, -9999px)';
		}
        
        // Run the demo after the page loads
        window.onload = function() {
            createSputterChart();
            createCncChart();
            createCo2Chart(co2DailyCycles);
            createEncubatorCharts();
            runDemo();
        };
    </script>
</body>
</html>
