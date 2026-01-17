<?php require __DIR__ . '/../layouts/html/header.php'; ?>

<div class="container mt-4">
    <nav class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">📊 Reliability Analytics Dashboard</h2>
        <a href="/mes/mms_admin" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-house"></i> Home
        </a>
    </nav>

    <!-- FILTER CARD -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">

                <!-- Asset -->
                <div class="col-md-3">
                    <label class="form-label fw-bold">Asset ID</label>
                    <input type="text" name="asset_id"
                           value="<?= htmlspecialchars($_GET['asset_id'] ?? '') ?>"
                           class="form-control" placeholder="e.g. smt-10267">
                </div>

                <!-- Entity -->
                <div class="col-md-3">
                    <label class="form-label fw-bold">Entity</label>
                    <select name="entity" class="form-select">
                        <option value="">All Entities</option>
                        <?php foreach ($entities as $e): ?>
                            <option value="<?= htmlspecialchars($e) ?>"
                                <?= ($_GET['entity'] ?? '') === $e ? 'selected' : '' ?>>
                                <?= htmlspecialchars($e) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <button class="btn btn-primary w-100">Filter</button>
                </div>

                <?php if (!empty($_GET['asset_id']) || !empty($_GET['entity'])): ?>
                    <div class="col-md-3">
                        <a href="?" class="btn btn-outline-secondary w-100">Clear</a>
                    </div>
                <?php endif; ?>

            </form>
        </div>
    </div>

    <!-- CHART -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">📈 Reliability Over Time</h4>
            <?php if (!empty($reliabilityByDate)): ?>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary active" data-range="1">1D</button>
                    <button class="btn btn-outline-primary" data-range="7">1W</button>
                    <button class="btn btn-outline-primary" data-range="30">1M</button>
                </div>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <?php if (empty($reliabilityByDate)): ?>
                <div class="alert alert-info">No data available</div>
            <?php else: ?>
                <div style="height:400px">
                    <canvas id="timeSeriesChart"></canvas>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- TABLES (unchanged) -->
    <?php require __DIR__ . '/partials/reliability_tables.php'; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3"></script>

<script>
const rawData = <?= json_encode($reliabilityByDate ?? [], JSON_NUMERIC_CHECK) ?>;
if (rawData.length) {
    const data = rawData.map(r => ({
        x: new Date(r.date),
        mtbf: r.mtbf_hours,
        mttr: r.mttr_hours,
        avail: r.availability_pct
    }));

    const maxDate = Math.max(...data.map(d => d.x));

    const chart = new Chart(document.getElementById('timeSeriesChart'), {
        data: {
            datasets: [
                { type:'bar', label:'MTBF', data:data.map(d=>({x:d.x,y:d.mtbf})) },
                { type:'bar', label:'MTTR', data:data.map(d=>({x:d.x,y:d.mttr})) },
                { type:'line', label:'Availability %', yAxisID:'y1',
                  data:data.map(d=>({x:d.x,y:d.avail})) }
            ]
        },
        options: {
            responsive:true,
            scales:{
                x:{type:'time'},
                y:{beginAtZero:true},
                y1:{position:'right',min:0,max:100,grid:{drawOnChartArea:false}}
            }
        }
    });

    document.querySelectorAll('[data-range]').forEach(btn=>{
        btn.onclick=()=>{
            document.querySelectorAll('[data-range]').forEach(b=>b.classList.remove('active'));
            btn.classList.add('active');
            chart.options.scales.x.min = maxDate - btn.dataset.range*86400000;
            chart.options.scales.x.max = maxDate;
            chart.update();
        };
    });
}
</script>

<?php require __DIR__ . '/../layouts/html/footer.php'; ?>
