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
            --board-blue: #007bff;
            --machine-bg: #ffffff;
            --status-green: #00c851;
            --status-blue: #33b5e5;
            --text-muted: #6c757d;
            --border-color: #e0e0e0;
        }

        body {
            background-color: #f4f7f9;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            padding-bottom: 3rem;
        }

        /* DASHBOARD PREVIEW STYLES (Matching Image) */
        .mock-dashboard {
            background: white;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            border: 1px solid var(--border-color);
        }

        .mock-header {
            background: white;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eee;
        }

        .mock-blue-bar {
            background-color: var(--board-blue);
            color: white;
            padding: 8px 20px;
            font-size: 0.9rem;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .mock-grid {
            display: flex;
            gap: 15px;
            padding: 20px;
            background: #f8f9fa;
            overflow-x: auto;
        }

        .machine-card {
            background: white;
            border: 1px solid #ddd;
            border-radius: 6px;
            min-width: 200px;
            padding: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .card-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .machine-title { font-weight: 700; font-size: 1.1rem; display: flex; align-items: center; gap: 8px; }
        
        .prog-row { margin-bottom: 8px; }
        .prog-labels { display: flex; justify-content: space-between; font-size: 0.7rem; font-weight: 700; margin-bottom: 2px; }
        .prog-bar { height: 6px; background: #eee; border-radius: 3px; overflow: hidden; }
        .prog-fill-green { background: #28a745; height: 100%; width: 100%; }
        .prog-fill-orange { background: #ffa000; height: 100%; width: 70%; }

        .wip-badge {
            background: #e3f2fd;
            color: #1976d2;
            font-size: 0.75rem;
            padding: 2px 8px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin: 8px 0;
        }
        .wip-dot { width: 6px; height: 6px; background: #1976d2; border-radius: 50%; }

        .opt-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 10px; }
        .opt-box { background: #fdfdfd; border: 1px solid #eee; text-align: center; padding: 4px; border-radius: 4px; }
        .opt-val { font-weight: 700; font-size: 0.9rem; }
        .opt-label { font-size: 0.65rem; color: #777; }

        .state-btn {
            width: 100%;
            border: none;
            padding: 8px;
            border-radius: 4px;
            color: white;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.85rem;
        }
        .btn-prod { background: #00c851; }
        .btn-idle { background: #33b5e5; }

        .mini-chart { display: flex; align-items: flex-end; gap: 4px; height: 40px; margin-top: 10px; }
        .chart-bar { width: 15px; border-radius: 2px 2px 0 0; }
        
        /* STEPS STYLING */
        .step-container { margin-top: 40px; }
        .step-card {
            background: white;
            border: none;
            border-left: 4px solid var(--board-blue);
            margin-bottom: 20px;
            border-radius: 0 8px 8px 0;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        .step-num {
            background: var(--board-blue);
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 15px;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="fw-bold text-dark">Monitoring Dashboard Setup</h1>
        <p class="text-muted">Follow these steps to configure your Machine Status Board</p>
    </div>

    <div class="mock-dashboard mb-5">
        <div class="mock-header">
            <span class="fw-bold">Machine Status Board | <span class="text-muted">BUILDING_01</span></span>
            <div>
                <button class="btn btn-sm btn-outline-secondary me-2">BUILDING_01 <i class="fas fa-chevron-down ms-1"></i></button>
                <button class="btn btn-sm btn-primary">+ New Group</button>
            </div>
        </div>
        <div class="mock-blue-bar">
            <span>SMT | TEST_and_PACK | SPUTTER <span class="badge bg-white text-primary ms-2 opacity-75">BAY_1</span></span>
            <div class="opacity-75">
                <i class="fas fa-plus-square me-2"></i>
                <i class="fas fa-edit me-2"></i>
                <i class="fas fa-trash me-2"></i>
                <small>#1</small>
            </div>
        </div>
        <div class="mock-grid">
            <div class="machine-card">
                <div class="card-top">
                    <div class="machine-title"><i class="fas fa-list text-muted small"></i> SMT_01</div>
                    <i class="fas fa-thumbtack text-primary small"></i>
                </div>
                <div class="prog-row">
                    <div class="prog-labels"><span>WOF</span><span class="text-primary">Due: 14 Oct</span></div>
                    <div class="prog-bar"><div class="prog-fill-green"></div></div>
                </div>
                <div class="prog-row">
                    <div class="prog-labels"><span>CAL</span><span class="text-primary">Due: 14 Oct</span></div>
                    <div class="prog-bar"><div class="prog-fill-orange"></div></div>
                </div>
                <div class="wip-badge"><div class="wip-dot"></div> WIP</div>
                <div class="opt-grid">
                    <div class="opt-box"><div class="opt-val">1200</div><div class="opt-label">Actual OPT</div></div>
                    <div class="opt-box"><div class="opt-val">3000</div><div class="opt-label">Target OPT</div></div>
                </div>
                <button class="state-btn btn-prod">PROD</button>
                <div class="mini-chart">
                    <div class="chart-bar" style="height:40%; background:#c8e6c9;"></div>
                    <div class="chart-bar" style="height:70%; background:#ffcdd2;"></div>
                    <div class="chart-bar" style="height:30%; background:#c8e6c9;"></div>
                    <div class="chart-bar" style="height:90%; background:#ffcdd2;"></div>
                </div>
            </div>

            <div class="machine-card">
                <div class="card-top">
                    <div class="machine-title"><i class="fas fa-list text-muted small"></i> ENDURA_01</div>
                    <i class="fas fa-thumbtack text-primary small"></i>
                </div>
                <div class="prog-row">
                    <div class="prog-labels"><span>WOF</span><span class="text-primary">Due: 14 Oct</span></div>
                    <div class="prog-bar"><div class="prog-fill-green"></div></div>
                </div>
                <div class="prog-row">
                    <div class="prog-labels"><span>CAL</span><span class="text-primary">Due: 14 Oct</span></div>
                    <div class="prog-bar"><div class="prog-fill-orange"></div></div>
                </div>
                <div class="wip-badge"><div class="wip-dot"></div> WIP</div>
                <div class="opt-grid">
                    <div class="opt-box"><div class="opt-val">1200</div><div class="opt-label">Actual OPT</div></div>
                    <div class="opt-box"><div class="opt-val">3000</div><div class="opt-label">Target OPT</div></div>
                </div>
                <button class="state-btn btn-idle">IDLE</button>
                <div class="mini-chart">
                    <div class="chart-bar" style="height:40%; background:#c8e6c9;"></div>
                    <div class="chart-bar" style="height:70%; background:#ffcdd2;"></div>
                    <div class="chart-bar" style="height:30%; background:#c8e6c9;"></div>
                    <div class="chart-bar" style="height:90%; background:#ffcdd2;"></div>
                </div>
            </div>

            <div class="machine-card d-flex flex-column align-items-center justify-content-center border-dashed" style="border: 2px dashed #ccc; background: transparent;">
                <i class="fas fa-plus-circle text-light fa-2x mb-2"></i>
                <span class="text-muted small">Add Entity</span>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-10">
            
            <div class="step-card p-3 d-flex align-items-center">
                <div class="step-num">1</div>
                <div class="flex-grow-1">
                    <h5 class="mb-1 fw-bold">Register Assets</h5>
                    <p class="mb-0 text-muted small">Define physical machines (e.g., SMT_01, ENDURA_01) in the system inventory.</p>
                </div>
                <a href="/form_mms/addAsset" class="btn btn-sm btn-primary">Go <i class="fas fa-arrow-right"></i></a>
            </div>

            <div class="step-card p-3 d-flex align-items-center">
                <div class="step-num">2</div>
                <div class="flex-grow-1">
                    <h5 class="mb-1 fw-bold">Create Dashboard Pages</h5>
                    <p class="mb-0 text-muted small">Create main views like "BUILDING_01" to organize high-level locations.</p>
                </div>
                <a href="/mes/dashboard_admin" class="btn btn-sm btn-primary">Go To Dashboard<i class="fas fa-arrow-right"></i></a>
            </div>

            <div class="step-card p-3 d-flex align-items-center">
                <div class="step-num">3</div>
                <div class="flex-grow-1">
                    <h5 class="mb-1 fw-bold">Add Machine Groups</h5>
                    <p class="mb-0 text-muted small">Group machines by process type (e.g., SMT, SPUTTER, TEST_and_PACK).</p>
                </div>
                <a href="/mes/dashboard_admin" class="btn btn-sm btn-primary">Go To Dashboard<i class="fas fa-arrow-right"></i></a>
            </div>

            <div class="step-card p-3 d-flex align-items-center">
                <div class="step-num">4</div>
                <div class="flex-grow-1">
                    <h5 class="mb-1 fw-bold">Map Entities to Groups</h5>
                    <p class="mb-0 text-muted small">Assign registered assets to their respective groups to populate the cards.</p>
                </div>
                <a href="/mes/dashboard_admin" class="btn btn-sm btn-primary">Go To Dashboard<i class="fas fa-arrow-right"></i></a>
            </div>

            <div class="step-card p-3 d-flex align-items-center">
                <div class="step-num">5</div>
                <div class="flex-grow-1">
                    <h5 class="mb-1 fw-bold">Configure Status Colors</h5>
                    <p class="mb-0 text-muted small">Set visual triggers for PROD (Green), IDLE (Blue), and Maintenance (Red).</p>
                </div>
                <a href="/mode-color" class="btn btn-sm btn-primary">Go <i class="fas fa-arrow-right"></i></a>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>