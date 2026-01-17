<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Monitoring Dashboard Setup Guide</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <style>
        :root {
            --primary: #0d6efd;
            --secondary: #6c757d;
            --success: #198754;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #0dcaf0;
            --light: #f8f9fa;
            --dark: #212529;
            --gray-100: #f8f9fa;
            --gray-200: #e9ecef;
            --gray-300: #dee2e6;
            --gray-400: #ced4da;
            --gray-500: #adb5bd;
            --gray-600: #6c757d;
            --gray-700: #495057;
            --gray-800: #343a40;
            --gray-900: #212529;
        }

        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #212529;
        }

        .dashboard-header {
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
            color: white;
            padding: 2rem 1rem;
            border-bottom: 3px solid rgba(255,255,255,0.2);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .dashboard-header h1 {
            font-weight: 700;
            letter-spacing: -0.5px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }

        .step-card {
            border-left: 5px solid var(--primary);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            background: white;
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .step-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
        }

        .step-number {
            background-color: var(--primary);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.1rem;
            margin-right: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .step-icon {
            color: var(--primary);
            font-size: 1.3rem;
            margin-right: 0.5rem;
        }

        .card-title {
            font-weight: 600;
            font-size: 1.2rem;
            color: var(--dark);
        }

        .btn-outline-primary {
            border-color: var(--primary);
            color: var(--primary);
            transition: all 0.2s ease;
        }

        .btn-outline-primary:hover {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .list-group-item {
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            margin-bottom: 0.5rem;
            transition: background-color 0.2s ease;
        }

        .list-group-item:hover {
            background-color: var(--gray-100);
        }

        .badge {
            font-weight: 500;
            padding: 0.5em 0.75em;
            font-size: 0.85rem;
            border-radius: 0.5rem;
        }

        .badge-success { background-color: #198754; color: white; }
        .badge-secondary { background-color: #6c757d; color: white; }
        .badge-warning { background-color: #ffc107; color: #212529; }
        .badge-danger { background-color: #dc3545; color: white; }
        .badge-info { background-color: #0dcaf0; color: #212529; }

        .state-badge {
            padding: 0.6rem 1.2rem;
            font-weight: 600;
            font-size: 0.95rem;
            border-radius: 0.5rem;
            display: inline-block;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .state-prod { background: linear-gradient(135deg, #198754 0%, #157347 100%); color: white; }
        .state-idle { background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%); color: white; }
        .state-maint-pre { background: linear-gradient(135deg, #ffc107 0%, #ffca28 100%); color: #212529; }
        .state-maint-cor { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; }
        .state-proc { background: linear-gradient(135deg, #0dcaf0 0%, #0aa8d8 100%); color: #212529; }

        .final-callout {
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            margin-top: 2rem;
        }

        .final-callout p {
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }

        .final-callout .btn {
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            font-size: 1rem;
            border-radius: 0.5rem;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .step-number {
                width: 32px;
                height: 32px;
                font-size: 0.9rem;
            }
            .card-title {
                font-size: 1.1rem;
            }
            .badge {
                font-size: 0.75rem;
                padding: 0.4em 0.6em;
            }
            .state-badge {
                font-size: 0.85rem;
                padding: 0.5rem 1rem;
            }
        }

        /* Animation for step cards */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .step-card {
            animation: fadeInUp 0.5s ease-out;
        }

        .step-card:nth-child(1) { animation-delay: 0.1s; }
        .step-card:nth-child(2) { animation-delay: 0.2s; }
        .step-card:nth-child(3) { animation-delay: 0.3s; }
        .step-card:nth-child(4) { animation-delay: 0.4s; }
        .step-card:nth-child(5) { animation-delay: 0.5s; }
    </style>
</head>
<body>

    <div class="container py-4">
        <!-- Header Section -->
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="dashboard-header text-center mb-4">
                    <h1 class="display-5 fw-bold">🚀 Monitoring Dashboard Setup Guide</h1>
                    <p class="lead mb-0">Follow these 5 simple steps to configure your real-time machine monitoring system.</p>
                </div>
            </div>
        </div>

        <!-- Step 1 -->
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="step-card">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start">
                            <span class="step-number">1</span>
                            <div>
                                <h5 class="card-title"><i class="fas fa-microchip step-icon"></i>Register an Asset</h5>
                                <p class="mb-3">
                                    Begin by adding your physical machine or equipment to the system. This creates the foundation for monitoring.
                                </p>
                                <a href="/form_mms/addAsset" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-plus-circle me-1"></i> Add New Asset
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 2 -->
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="step-card">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start">
                            <span class="step-number">2</span>
                            <div>
                                <h5 class="card-title"><i class="fas fa-file-alt step-icon"></i>Create a Page</h5>
                                <p class="mb-3">
                                    A "Page" represents a logical view (e.g., production line, department). Create one to organize your groups.
                                </p>
                                <a href="/create-page" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-file-plus me-1"></i> Create New Page
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 3 -->
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="step-card">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start">
                            <span class="step-number">3</span>
                            <div>
                                <h5 class="card-title"><i class="fas fa-layer-group step-icon"></i>Add a Group</h5>
                                <p class="mb-3">
                                    Groups contain related entities (e.g., all machines in a cell). Add a group to your page.
                                </p>
                                <a href="/create-group" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-object-group me-1"></i> Add Group
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 4 -->
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="step-card">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start">
                            <span class="step-number">4</span>
                            <div>
                                <h5 class="card-title"><i class="fas fa-cube step-icon"></i>Add an Entity</h5>
                                <p class="mb-3">
                                    Link your registered asset(s) as entities inside a group. Each entity will appear on the dashboard.
                                </p>
                                <a href="/add-entity" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-cubes me-1"></i> Add Entity
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 5 -->
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="step-card">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start">
                            <span class="step-number">5</span>
                            <div>
                                <h5 class="card-title"><i class="fas fa-palette step-icon"></i>Configure State/Mode Colors</h5>
                                <p class="mb-3">
                                    Define how each operational state appears visually on the dashboard:
                                </p>
                                <ul class="list-group list-group-flush mt-2">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span><code class="text-success">PROD</code> – Release to Production</span>
                                        <span class="state-badge state-prod">PROD</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span><code class="text-secondary">IDLE</code> – No WIP to run</span>
                                        <span class="state-badge state-idle">IDLE</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span><code class="text-warning">MAINT-PRE</code> – Scheduled Maintenance</span>
                                        <span class="state-badge state-maint-pre">MAINT-PRE</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span><code class="text-danger">MAINT-COR</code> – Fault/Alarm (Repair Needed)</span>
                                        <span class="state-badge state-maint-cor">MAINT-COR</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span><code class="text-info">PROC</code> – Process/Qualification</span>
                                        <span class="state-badge state-proc">PROC</span>
                                    </li>
                                </ul>
                                <a href="/mode-color" class="btn btn-outline-primary btn-sm mt-3">
                                    <i class="fas fa-sliders-h me-1"></i> Configure Mode Colors
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Final Call to Action -->
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="final-callout">
                    <p class="mb-0">✅ Your dashboard is now ready! All machines will auto-update with real-time status.</p>
                    <a href="/mes/dashboard_admin" class="btn btn-light">
                        <i class="fas fa-tachometer-alt me-2"></i> Go to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>