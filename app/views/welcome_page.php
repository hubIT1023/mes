<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HubIT.online</title>

    <link rel="icon" href="/app/Assets/img/favicon.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        .gradient-bg {
            background: linear-gradient(
                to bottom right,
                rgba(15, 23, 42, 0.9),
                rgba(30, 41, 59, 0.9)
            );
        }

        .card-hover:hover {
            transform: translateY(-5px);
            transition: all 0.3s ease;
        }

        /* Global background image overlay */
        body::before {
            content: "";
            position: fixed;
            inset: 0;
            background: url('/app/Assets/img/hub.png') no-repeat center center;
            background-size: cover;
            opacity: 0.15;
            z-index: -1;
            pointer-events: none;
        }
    </style>
</head>

<body class="font-sans text-slate-900 relative min-h-screen">

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

<!-- HERO -->
<header class="gradient-bg text-white py-24">
    <div class="container mx-auto px-6 text-center">
        <h1 class="text-4xl md:text-6xl font-extrabold mb-6">
            HubIT.online<br class="hidden md:block">
            Smarter Tech. Simpler Operations.
        </h1>
        <p class="text-xl text-slate-300 max-w-3xl mx-auto">
            A unified platform for maintenance, asset intelligence, and IoT monitoring —
            built for factories, utilities, and modern facilities.
        </p>
    </div>
</header>

<!-- SCREENS -->
<section class="py-12 bg-white/90 backdrop-blur">
    <div class="container mx-auto px-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 -mt-24">

            <!-- Card -->
            <div class="bg-slate-800 rounded-xl shadow-2xl overflow-hidden border-4 border-slate-700 group">
                <div class="h-[300px] overflow-hidden">
                    <img src="../Assets/img/dashboard_overview.JPG"
                         class="w-full h-full object-cover opacity-70 group-hover:scale-105 transition duration-500">
                </div>
                <div class="p-3 text-center text-xs text-slate-400 bg-slate-900 uppercase">Dashboard Overview</div>
            </div>

            <div class="bg-slate-800 rounded-xl shadow-2xl overflow-hidden border-4 border-slate-700 group">
                <div class="h-[300px] overflow-hidden">
                    <img src="/app/Assets/img/work_order_details.JPG"
                         class="w-full h-full object-cover opacity-70 group-hover:scale-105 transition duration-500">
                </div>
                <div class="p-3 text-center text-xs text-slate-400 bg-slate-900 uppercase">Work Orders</div>
            </div>

            <div class="bg-slate-800 rounded-xl shadow-2xl overflow-hidden border-4 border-slate-700 group">
                <div class="h-[300px] overflow-hidden">
                    <img src="/app/Assets/img/prod_machineries.JPG"
                         class="w-full h-full object-cover opacity-70 group-hover:scale-105 transition duration-500">
                </div>
                <div class="p-3 text-center text-xs text-slate-400 bg-slate-900 uppercase">Assets</div>
            </div>

            <div class="bg-slate-800 rounded-xl shadow-2xl overflow-hidden border-4 border-slate-700 group">
                <div class="h-[300px] overflow-hidden">
                    <img src="/app/Assets/img/condition_monitonig.JPG"
                         class="w-full h-full object-cover opacity-70 group-hover:scale-105 transition duration-500">
                </div>
                <div class="p-3 text-center text-xs text-slate-400 bg-slate-900 uppercase">Condition Monitoring</div>
            </div>

        </div>
    </div>
</section>

<!-- DEMO -->
<section id="demo" class="py-20">
    <div class="container mx-auto px-6">
        <div class="max-w-4xl mx-auto bg-slate-800 border border-slate-700 rounded-2xl shadow-2xl text-white">
            <div class="p-10 text-center">
                <h2 class="text-3xl font-bold mb-8">Explore HubIT.online</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <button class="bg-blue-600 hover:bg-blue-500 py-3 rounded font-bold">▶ Maintenance</button>
                    <button class="bg-slate-700 hover:bg-slate-600 py-3 rounded font-bold">▶ Assets</button>

                    <a href="demo/demo_dashboard"
                       class="bg-slate-700 hover:bg-slate-600 py-3 rounded font-bold block">
                        ▶ Dashboards
                    </a>

                    <button class="bg-indigo-600 hover:bg-indigo-500 py-3 rounded font-bold">▶ IoT Sensors</button>
                </div>
            </div>
        </div>
    </div>
</section>

</body>
</html>
