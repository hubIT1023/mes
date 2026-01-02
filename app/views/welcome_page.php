<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HubIT.online</title>
	<!--title>HubIT | Maintenance, Assets, and Machine Data Platform</title-->
	<link rel="icon" href="../Assets/img/favicon.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
    .gradient-bg { 
        background: 
    }
    .card-hover:hover { 
        transform: translateY(-5px); 
        transition: all 0.3s ease; 
    }
    .demo-badge { 
        background: #fee2e2; 
        color: #991b1b; 
        padding: 2px 8px; 
        border-radius: 4px; 
        font-size: 0.75rem; 
        font-weight: bold; 
    }
    .img-overlay { 
        background: url('/app/Assets/img/hub.png') no-repeat center center;
        background-size: cover;
    }

    /* ✅ Simple background overlay for local testing */
    body::before {
        content: "";
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: url('/app/Assets/img/hub.png') no-repeat center center;
        background-size: cover;
        z-index: -1;
        pointer-events: none;
    }

    /* Optional: Make sure body has no background color so overlay shows */
    body {
        background: transparent !important;
    }
</style>
</head>
<body class="font-sans text-slate-900 bg-slate-50">

    <!-- NAV -->
<nav class="sticky top-0 z-50 bg-white/90 backdrop-blur border-b border-slate-200 py-4">
    <div class="container mx-auto px-6 flex justify-between items-center">
        <img src="/app/Assets/img/hubIT_logo-v5.png" alt="HubIT.ONLINE" class="h-12 w-auto">

        <div class="flex gap-3">
            <a href="/mes/register" class="bg-blue-600 text-white px-6 py-3 rounded-lg font-bold hover:bg-blue-700">Register</a>
            <a href="/mes/signin" class="bg-slate-800 text-white px-6 py-3 rounded-lg font-bold hover:bg-slate-700">Sign In</a>
        </div>
    </div>
</nav>

    <header class="gradient-bg text-white py-20">
        <div class="container mx-auto px-6 text-center">
            <h1 class="text-4xl md:text-6xl font-extrabold mb-6 leading-tight">
                HubIT.online</h1> <h5><br class="hidden md:block"> Smarter Tech. Simpler Solutions. 
            </h5>
            <p class="text-xl text-slate-300 max-w-3xl mx-auto mb-10">
               HubIT.online brings maintenance, asset management, and 
			   IoT monitoring together in one real-time platform—built for factories, utilities, and modern facilities.
            </p>
            <!--div class="flex flex-col md:flex-row justify-center items-center gap-4">
                <a href="#demo" class="w-full md:w-auto bg-blue-600 text-white px-8 py-4 rounded-lg text-lg font-bold hover:bg-blue-700">Let's Go!</a>
                <a href="#pricing" class="w-full md:w-auto bg-transparent border border-slate-500 
				text-white px-8 py-4 rounded-lg text-lg font-bold hover:bg-slate-800">View Pricing</a>
            </div>
            <p class="mt-6 text-sm text-slate-400 italic">
                No signup required · Real interface · Demo data resets daily
            </p-->
        </div>
    </header>

   <section class="py-12 bg-white">
		<div class="container mx-auto px-6">
			<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 -mt-24">
				<!-- Dashboard Overview -->
				<div class="bg-slate-800 rounded-xl shadow-2xl overflow-hidden border-4 border-slate-700 group">
					<div class="relative overflow-hidden h-[300px]"> <!-- Fixed height -->
						<img src="/app/Assets/img/dashboard_overview.jpg" 
							 alt="Dashboard Overview" 
							 class="w-full h-full object-cover opacity-70 group-hover:scale-105 transition duration-500">
					</div>
					<div class="p-3 text-center text-[10px] text-slate-400 font-mono uppercase tracking-widest bg-slate-900">Dashboard Overview</div>
				</div>

				<!-- Work Order Details -->
				<div class="bg-slate-800 rounded-xl shadow-2xl overflow-hidden border-4 border-slate-700 group">
					<div class="relative overflow-hidden h-[300px]"> <!-- Fixed height -->
						<img src="/app/Assets/img/work_order_details.jpg" 
							 alt="Work Order Details" 
							 class="w-full h-full object-cover opacity-70 group-hover:scale-105 transition duration-500">
					</div>
					<div class="p-3 text-center text-[10px] text-slate-400 font-mono uppercase tracking-widest bg-slate-900">Work Order Details</div>
				</div>

				<!-- Asset Management -->
				<div class="bg-slate-800 rounded-xl shadow-2xl overflow-hidden border-4 border-slate-700 group">
					<div class="relative overflow-hidden h-[300px]"> <!-- Fixed height -->
						<img src="/app/Assets/img/prod_machineries.jpg" 
							 alt="Asset Management" 
							 class="w-full h-full object-cover opacity-70 group-hover:scale-105 transition duration-500">
					</div>
					<div class="p-3 text-center text-[10px] text-slate-400 font-mono uppercase tracking-widest bg-slate-900">Asset Management</div>
				</div>

				<!-- Condition Monitoring -->
				<div class="bg-slate-800 rounded-xl shadow-2xl overflow-hidden border-4 border-slate-700 group">
					<div class="relative overflow-hidden h-[300px]"> <!-- Fixed height -->
						<img src="/app/Assets/img/condition_monitonig.jpg" 
							 alt="Condition Monitoring" 
							 class="w-full h-full object-cover opacity-70 group-hover:scale-105 transition duration-500">
					</div>
					<div class="p-3 text-center text-[10px] text-slate-400 font-mono uppercase tracking-widest bg-slate-900">Condition Monitoring</div>
				</div>
			</div>
			<!--p class="text-center mt-8 text-slate-500 font-medium italic">
				<i class="fas fa-check-circle text-green-500 mr-2"></i> This is the real HubIT interface — not mockups.
			</p-->
		</div>
		<div class="py-20">
			<div class="container mx-auto px-20">
				<h2 class="text-3xl font-bold text-center mb-16 uppercase tracking-wider text-slate-500">Built for Every Industrial Team</h2>
				<div class="grid grid-cols-1 md:grid-cols-4 gap-8">
					<div class="bg-white p-8 rounded-xl border border-slate-200 card-hover shadow-sm">
						<div class="text-3xl mb-4">🧰</div>
						<h3 class="font-bold text-xl mb-4 text-blue-900">Small Workshops</h3>
						<ul class="text-slate-600 space-y-2 text-sm">
							<li>• Replace Excel and paper</li>
							<li>• Track equipment & repairs</li>
							<li>• Get organized in minutes</li>
						</ul>
					</div>
					<div class="bg-white p-8 rounded-xl border border-slate-200 card-hover shadow-sm">
						<div class="text-3xl mb-4">🏭</div>
						<h3 class="font-bold text-xl mb-4 text-blue-900">Factories</h3>
						<ul class="text-slate-600 space-y-2 text-sm">
							<li>• Preventive maintenance</li>
							<li>• Full asset history</li>
							<li>• Reduce downtime</li>
						</ul>
					</div>
					<!--div class="bg-white p-8 rounded-xl border border-slate-200 card-hover shadow-sm">
						<div class="text-3xl mb-4">🛠️</div>
						<h3 class="font-bold text-xl mb-4 text-blue-900">Service Companies</h3>
						<ul class="text-slate-600 space-y-2 text-sm">
							<li>• Manage multiple customers</li>
							<li>• Technician workflows</li>
							<li>• Professional reporting</li>
						</ul>
					</div-->
					<div class="bg-white p-8 rounded-xl border border-slate-200 card-hover shadow-sm">
						<div class="text-3xl mb-4">🧩</div>
						<h3 class="font-bold text-xl mb-4 text-blue-900">Integrators & OEMs</h3>
						<ul class="text-slate-600 space-y-2 text-sm">
							<li>• White-label platform</li>
							<li>• API access</li>
							<li>• Faster project delivery</li>
						</ul>
					</div>
					
					<div class="bg-white p-8 rounded-xl border border-slate-200 card-hover shadow-sm">
					  <div class="text-3xl mb-4">🐔</div>
					  <h3 class="font-bold text-xl mb-4 text-amber-700">Poultry & Livestock</h3>
					  <ul class="text-slate-600 space-y-2 text-sm">
						<li>• Control climate, feed & water automation systems</li>
						<li>• Maintain HVAC, fans, and heating equipment</li>
						<li>• Ensure biosecurity with audit-ready maintenance logs</li>
					  </ul>
					</div>
				</div>
			</div>
		</div>
    </section>


    <section id="demo" class="py-20 text-white">
        <div class="container mx-auto px-6">
            <div class="max-w-4xl mx-auto bg-slate-800 border border-slate-700 rounded-2xl overflow-hidden shadow-2xl">
                <div class="bg-slate-700 px-6 py-3 flex items-center justify-between">
                    <div class="flex space-x-2">
                        <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                        <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                    </div>
                    <!--span class="text-xs font-mono text-slate-400">DEMO MODE ACTIVE</span-->
                </div>
                <div class="p-10 text-center">
                    <h2 class="text-3xl font-bold mb-4">Explore HubIT.online </h2>
                    <!--p class="text-slate-400 mb-10">Real Assets: CNC Machine A, Air Compressor, Conveyor Line 1</p-->
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                        <button onclick="alert('Opening Maintenance Demo...')" class="bg-blue-600 hover:bg-blue-500 py-3 rounded text-sm font-bold transition">▶ Maintenance</button>
                        <button onclick="alert('Opening Asset Demo...')" class="bg-slate-700 hover:bg-slate-600 py-3 rounded text-sm font-bold transition">▶ Assets</button>
                       <a href="demo/demo_dashboard"
						  class="inline-block bg-slate-700 hover:bg-slate-600 py-3 px-4 rounded text-sm font-bold transition">
						  ▶ Dashboards
					  </a>
                        <button onclick="alert('Opening Sensor Data Demo...')" class="bg-indigo-600 hover:bg-indigo-500 py-3 rounded text-sm font-bold transition">▶ IoT Sensors</button>
                    </div>
                    
                    <!--p class="text-xs text-slate-500 mb-6">Demo data is reset daily at midnight. Changes made now will be cleared.</p-->
                    <!--a href="#" class="text-blue-400 font-bold hover:underline">Want your own workspace? Create a free account &rarr;</a-->
                </div>
            </div>
        </div>
    </section>

    </body>
</html>