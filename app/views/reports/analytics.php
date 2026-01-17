<?php
// analytics.php
require __DIR__ . '/../layouts/html/header.php';
?>

<div class="container mt-4">

    <nav class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">📊 Reliability Analytics Dashboard</h2>
        <a href="/mes/mms_admin" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-house"></i> Home
        </a>
    </nav>

    <!-- FILTERS -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">

                <div class="col-md-3">
                    <label class="form-label fw-bold">Asset ID</label>
                    <input type="text"
                           name="asset_id"
                           class="form-control"
                           value="<?= htmlspecialchars($_GET['asset_id'] ?? '') ?>"
                           placeholder="e.g. smt-10267">
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">Entity Name</label>
                    <select class="form-select" name="entity">
                        <option value="">All Entities</option>
                        <?php foreach ($entities as $entity): ?>
                            <option value="<?= htmlspecialchars($entity) ?>"
                                <?= ($_GET['entity'] ?? '') === $entity ? 'selected' : '' ?>>
                                <?= htmlspecialchars($entity) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>

                <?php if (!empty($_GET['asset_id']) || !empty($_GET['entity'])): ?>
                    <div class="col-md-3">
                        <a href="?" class="btn btn-outline-secondary w-100">Clear</a>
                    </div>
                <?php endif; ?>

            </form>
        </div>
    </div>

    <!-- TIME SERIES -->
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
                <div class="alert alert-info mb-0">No time-series reliability data available.</div>
            <?php else: ?>
                <div style="height:400px">
                    <canvas id="timeSeriesChart"></canvas>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- KPI TABLES -->
    <div class="row">

        <!-- MTBF -->
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-light"><h5 class="mb-0">⏱ MTBF (hrs)</h5></div>
                <div class="card-body p-0">
                    <?php if (empty($mtbf)): ?>
                        <p class="p-3 text-muted">No data</p>
                    <?php else: ?>
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr><th>Asset</th><th>Value</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($mtbf as $row): ?>
                                    <tr>
                                        <td><code><?= htmlspecialchars($row['asset_id']) ?></code></td>
                                        <td class="fw-bold"><?= number_format($row['mtbf_hours'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- MTTR -->
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-light"><h5 class="mb-0">🛠 MTTR (hrs)</h5></div>
                <div class="card-body p-0">
                    <?php if (empty($mttr)): ?>
                        <p class="p-3 text-muted">No data</p>
                    <?php else: ?>
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr><th>Asset</th><th>Value</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($mttr as $row): ?>
                                    <tr>
                                        <td><code><?= htmlspecialchars($row['asset_id']) ?></code></td>
                                        <td class="fw-bold text-danger"><?= number_format($row['mttr_hours'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- AVAILABILITY -->
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-light"><h5 class="mb-0">✅ Availability (%)</h5></div>
                <div class="card-body p-0">
                    <?php if (empty($availability)): ?>
                        <p class="p-3 text-muted">No data</p>
                    <?php else: ?>
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr><th>Asset</th><th>Value</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($availability as $row): ?>
                                    <tr>
                                        <td><code><?= htmlspecialchars($row['asset_id']) ?></code></td>
                                        <td class="fw-bold text-success">
                                            <?= number_format($row['availability_pct'], 1) ?>%
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- CHART -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3"></script>

<script>
const rawData = <?= json_encode($reliabilityByDate ?? [], JSON_NUMERIC_CHECK) ?>;

if (rawData.length) {
    const parsed = rawData.map(r => ({
        x: new Date(r.date),
        mtbf: r.mtbf_hours,
        mttr: r.mttr_hours,
        avail: r.availability_pct
    }));

    const maxDate = Math.max(...parsed.map(d => d.x));
    const ctx = document.getElementById('timeSeriesChart');

    const chart = new Chart(ctx, {
        data: {
            datasets: [
                { type: 'bar', label: 'MTBF (hrs)', data: parsed.map(d => ({ x: d.x, y: d.mtbf })), yAxisID: 'y' },
                { type: 'bar', label: 'MTTR (hrs)', data: parsed.map(d => ({ x: d.x, y: d.mttr })), yAxisID: 'y' },
                { type: 'line', label: 'Availability (%)', data: parsed.map(d => ({ x: d.x, y: d.avail })), yAxisID: 'y1' }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { type: 'time' },
                y: { beginAtZero: true },
                y1: { position: 'right', min: 0, max: 100, grid: { drawOnChartArea: false } }
            }
        }
    });

    document.querySelectorAll('[data-range]').forEach(btn => {
        btn.onclick = () => {
            document.querySelectorAll('[data-range]').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const days = +btn.dataset.range;
            chart.options.scales.x.min = maxDate - days * 86400000;
            chart.options.scales.x.max = maxDate;
            chart.update();
        };
    });
}
</script>

<?php require __DIR__ . '/../layouts/html/footer.php'; ?>
