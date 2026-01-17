

<?php require __DIR__ . '/../layouts/html/header.php'; ?>


<div class="container mt-4">
    <nav class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">📊 Reliability Analytics Dashboard</h2>
        <a href="/mes/mms_admin" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-house"></i> Home
        </a>
    </nav>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Asset ID</label>
                    <input type="text" name="asset_id" 
                           value="<?= htmlspecialchars($_GET['asset_id'] ?? '') ?>" 
                           class="form-control" placeholder="e.g. smt-10267">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
                <?php if (!empty($_GET['asset_id'])): ?>
                    <div class="col-md-3">
                        <a href="?" class="btn btn-outline-secondary w-100">Clear</a>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">📈 Reliability Over Time</h4>
            <?php if (!empty($reliabilityByDate)): ?>
                <div class="btn-group btn-group-sm" role="group">
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
                <div style="height:400px;">
                    <canvas id="timeSeriesChart"></canvas>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-light"><h5 class="mb-0">⏱ MTBF (hrs)</h5></div>
                <div class="card-body p-0">
                    <?php if (empty($mtbf)): ?>
                        <p class="p-3 text-muted">No data</p>
                    <?php else: ?>
                        <table class="table table-hover mb-0">
                            <thead class="table-light"><tr><th>Asset</th><th>Value</th></tr></thead>
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

        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-light"><h5 class="mb-0">🛠 MTTR (hrs)</h5></div>
                <div class="card-body p-0">
                    <?php if (empty($mttr)): ?>
                        <p class="p-3 text-muted">No data</p>
                    <?php else: ?>
                        <table class="table table-hover mb-0">
                            <thead class="table-light"><tr><th>Asset</th><th>Value</th></tr></thead>
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

        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-light"><h5 class="mb-0">✅ Availability (%)</h5></div>
                <div class="card-body p-0">
                    <?php if (empty($availability)): ?>
                        <p class="p-3 text-muted">No data</p>
                    <?php else: ?>
                        <table class="table table-hover mb-0">
                            <thead class="table-light"><tr><th>Asset</th><th>Value</th></tr></thead>
                            <tbody>
                                <?php foreach ($availability as $row): ?>
                                    <tr>
                                        <td><code><?= htmlspecialchars($row['asset_id']) ?></code></td>
                                        <td class="fw-bold text-success"><?= number_format($row['availability_pct'], 1) ?>%</td>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3"></script>

<script>
const rawData = <?= json_encode($reliabilityByDate ?? [], JSON_NUMERIC_CHECK) ?>;

if (Array.isArray(rawData) && rawData.length > 0) {
    const data = rawData.map(r => ({
        x: new Date(r.date + 'T00:00:00'),
        mtbf: r.mtbf_hours,
        mttr: r.mttr_hours,
        avail: r.availability_pct
    }));

    const maxDate = Math.max(...data.map(d => d.x));
    const ctx = document.getElementById('timeSeriesChart');

    const chart = new Chart(ctx, {
        data: {
            datasets: [
                {
                    type: 'bar',
                    label: 'MTBF (hrs)',
                    data: data.map(d => ({ x: d.x, y: d.mtbf })),
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    yAxisID: 'y'
                },
                {
                    type: 'bar',
                    label: 'MTTR (hrs)',
                    data: data.map(d => ({ x: d.x, y: d.mttr })),
                    backgroundColor: 'rgba(255, 99, 132, 0.6)',
                    yAxisID: 'y'
                },
                {
                    type: 'line',
                    label: 'Availability (%)',
                    data: data.map(d => ({ x: d.x, y: d.avail })),
                    borderColor: '#20c997',
                    borderWidth: 3,
                    pointRadius: 4,
                    tension: 0.3,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { type: 'time', time: { unit: 'day' } },
                y: { beginAtZero: true, title: { display: true, text: 'Hours' } },
                y1: { 
                    position: 'right', 
                    min: 0, max: 100, 
                    title: { display: true, text: 'Availability %' },
                    grid: { drawOnChartArea: false }
                }
            }
        }
    });

    // Time-range selector logic
    document.querySelectorAll('[data-range]').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('[data-range]').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const days = parseInt(btn.dataset.range, 10);
            chart.options.scales.x.min = maxDate - (days * 24 * 60 * 60 * 1000);
            chart.options.scales.x.max = maxDate;
            chart.update();
        });
    });
}
</script>

<?php require __DIR__ . '/../layouts/html/footer.php'; ?>