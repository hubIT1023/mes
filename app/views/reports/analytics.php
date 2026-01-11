<?php require __DIR__ . '/../layouts/html/header.php'; ?>

<div class="container mt-4">
    <h2>📊 Reliability Analytics Dashboard</h2>

    <!-- Filters -->
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-4">
            <label class="form-label">Asset ID</label>
            <input type="text" name="asset_id"
                   value="<?= htmlspecialchars($_GET['asset_id'] ?? '') ?>"
                   class="form-control"
                   placeholder="e.g. smt-10267">
        </div>
        <div class="col-md-2 align-self-end">
            <button type="submit" class="btn btn-primary">Filter</button>
        </div>
        <?php if (!empty($_GET['asset_id'])): ?>
            <div class="col-md-2 align-self-end">
                <a href="?" class="btn btn-outline-secondary">Clear</a>
            </div>
        <?php endif; ?>
    </form>

    <?php if (empty($availability)): ?>
        <div class="alert alert-info">
            No complete reliability data available. Ensure the asset has:
            <ul>
                <li>At least two <code>MAINT-COR</code> events (for MTBF)</li>
                <li>Each followed by a <code>PROD</code> event (for MTTR)</li>
            </ul>
        </div>
    <?php else: ?>
        <h4 class="mt-4">📈 Reliability Summary (MTBF, MTTR & Availability)</h4>
        <div class="chart-container" style="height: 400px; margin-bottom: 30px;">
            <canvas id="reliabilityChart"></canvas>
        </div>
    <?php endif; ?>

    <!-- Data Tables (for reference) -->
    <div class="row">
        <div class="col-md-4">
            <h5>⏱ MTBF (hrs)</h5>
            <?php if (empty($mtbf)): ?>
                <p class="text-muted">No data</p>
            <?php else: ?>
                <table class="table table-sm table-bordered">
                    <thead><tr><th>Asset</th><th>Value</th></tr></thead>
                    <tbody>
                        <?php foreach ($mtbf as $row): ?>
                            <tr><td><?= htmlspecialchars($row['asset_id']) ?></td><td><?= $row['mtbf_hours'] ?></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="col-md-4">
            <h5>🛠 MTTR (hrs)</h5>
            <?php if (empty($mttr)): ?>
                <p class="text-muted">No data</p>
            <?php else: ?>
                <table class="table table-sm table-bordered">
                    <thead><tr><th>Asset</th><th>Value</th></tr></thead>
                    <tbody>
                        <?php foreach ($mttr as $row): ?>
                            <tr><td><?= htmlspecialchars($row['asset_id']) ?></td><td><?= $row['mttr_hours'] ?></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="col-md-4">
            <h5>✅ Availability (%)</h5>
            <?php if (empty($availability)): ?>
                <p class="text-muted">No data</p>
            <?php else: ?>
                <table class="table table-sm table-bordered">
                    <thead><tr><th>Asset</th><th>Value</th></tr></thead>
                    <tbody>
                        <?php foreach ($availability as $row): ?>
                            <tr><td><?= htmlspecialchars($row['asset_id']) ?></td><td><?= $row['availability_pct'] ?></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Chart.js v4 -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
// Prepare data safely
const labels = <?= json_encode(array_column($availability, 'asset_id')) ?>;
const mtbfRaw = <?= json_encode($mtbf) ?>;
const mttrRaw = <?= json_encode($mttr) ?>;
const availValues = <?= json_encode(array_column($availability, 'availability_pct')) ?>;

// Build aligned arrays
const mtbfMap = {};
const mttrMap = {};
mtbfRaw.forEach(row => mtbfMap[row.asset_id] = parseFloat(row.mtbf_hours));
mttrRaw.forEach(row => mttrMap[row.asset_id] = parseFloat(row.mttr_hours));

const mtbfValues = labels.map(asset => mtbfMap[asset] || 0);
const mttrValues = labels.map(asset => mttrMap[asset] || 0);

// Render chart only if data exists
if (labels.length > 0) {
    const ctx = document.getElementById('reliabilityChart');
    if (ctx) {
        new Chart(ctx.getContext('2d'), {
            type: 'bar',
             {
                labels: labels,
                datasets: [
                    {
                        type: 'bar',
                        label: 'MTBF (Hours)',
                         mtbfValues,
                        backgroundColor: 'rgba(39, 174, 96, 0.7)',
                        borderColor: '#27ae60',
                        borderWidth: 1
                    },
                    {
                        type: 'bar',
                        label: 'MTTR (Hours)',
                         mttrValues,
                        backgroundColor: 'rgba(243, 156, 18, 0.7)',
                        borderColor: '#f39c12',
                        borderWidth: 1
                    },
                    {
                        type: 'line',
                        label: 'Availability (%)',
                         availValues,
                        backgroundColor: '#3498db',
                        borderColor: '#2980b9',
                        borderWidth: 3,
                        pointBackgroundColor: '#2980b9',
                        pointRadius: 4,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Reliability Metrics by Asset'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Time (Hours)'
                        },
                        beginAtZero: true
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Availability (%)'
                        },
                        min: 0,
                        max: 100,
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                }
            }
        });
    }
}
</script>

<?php require __DIR__ . '/../layouts/html/footer.php'; ?>