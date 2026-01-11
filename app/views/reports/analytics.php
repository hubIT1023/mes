<?php require __DIR__ . '/../layouts/html/header.php'; ?>

<div class="container mt-4">
    <h2>📊 Reliability Analytics Dashboard</h2>

    <!-- Filters -->
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-4">
            <label class="form-label">Asset ID</label>
            <input type="text"
                   name="asset_id"
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

    <!-- Time-Series Chart -->
    <h4 class="mt-4">📈 Reliability Over Time</h4>

    <?php if (empty($reliabilityByDate)): ?>
        <div class="alert alert-info">
            No time-series reliability data available.
        </div>
    <?php else: ?>

        <!-- Time range selector -->
        <div class="btn-group mb-3" role="group">
            <button class="btn btn-outline-primary active" data-range="1">1 Day</button>
            <button class="btn btn-outline-primary" data-range="7">1 Week</button>
            <button class="btn btn-outline-primary" data-range="30">1 Month</button>
        </div>

        <div style="height:400px; margin-bottom:30px;">
            <canvas id="timeSeriesChart"></canvas>
        </div>

    <?php endif; ?>

    <!-- Per-Asset Tables -->
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
                        <tr>
                            <td><?= htmlspecialchars($row['asset_id']) ?></td>
                            <td><?= $row['mtbf_hours'] ?></td>
                        </tr>
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
                        <tr>
                            <td><?= htmlspecialchars($row['asset_id']) ?></td>
                            <td><?= $row['mttr_hours'] ?></td>
                        </tr>
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
                        <tr>
                            <td><?= htmlspecialchars($row['asset_id']) ?></td>
                            <td><?= $row['availability_pct'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Chart.js + Time Adapter -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3"></script>

<script>
const rawData = <?= json_encode($reliabilityByDate, JSON_NUMERIC_CHECK) ?>;

if (Array.isArray(rawData) && rawData.length > 0) {

    // Convert SQL DATE → JS Date
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
                    backgroundColor: 'rgba(255,182,193,0.7)'
                },
                {
                    type: 'bar',
                    label: 'MTTR (hrs)',
                    data: data.map(d => ({ x: d.x, y: d.mttr })),
                    backgroundColor: 'rgba(135,206,235,0.7)'
                },
                {
                    type: 'line',
                    label: 'Availability (%)',
                    yAxisID: 'y1',
                    data: data.map(d => ({ x: d.x, y: d.avail })),
                    borderColor: '#00bfa5',
                    borderWidth: 3,
                    tension: 0.3
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            parsing: false,
            scales: {
                x: {
                    type: 'time',
                    time: { unit: 'day' },
                    title: { display: true, text: 'Date' }
                },
                y: {
                    beginAtZero: true,
                    title: { display: true, text: 'Hours' }
                },
                y1: {
                    position: 'right',
                    min: 0,
                    max: 100,
                    grid: { drawOnChartArea: false },
                    title: { display: true, text: 'Availability (%)' }
                }
            }
        }
    });

    // Time-range selector logic
    document.querySelectorAll('[data-range]').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('[data-range]')
                .forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const days = parseInt(btn.dataset.range, 10);
            const minDate = maxDate - (days * 24 * 60 * 60 * 1000);

            chart.options.scales.x.min = minDate;
            chart.options.scales.x.max = maxDate;
            chart.update();
        });
    });
}
</script>

<?php require __DIR__ . '/../layouts/html/footer.php'; ?>
