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

    <!-- Time-Series Combo Chart -->
    <h4 class="mt-4">📈 Time-Scale Reliability (Last 7 Days)</h4>
    <?php if (empty($reliabilityByDate)): ?>
        <div class="alert alert-info">
            No time-series reliability data available. Ensure there are MAINT-COR events in the last 7 days.
        </div>
    <?php else: ?>
        <div class="chart-container" style="height: 400px; margin-bottom: 30px;">
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
// Prepare time-series data
const timeData = <?= json_encode($reliabilityByDate) ?>;

if (timeData.length > 0) {
    const labels = timeData.map(row => row.date); // e.g., "2026-01-10"
    const mtbfValues = timeData.map(row => parseFloat(row.mtbf_hours));
    const mttrValues = timeData.map(row => parseFloat(row.mttr_hours));
    const availValues = timeData.map(row => parseFloat(row.availability_pct));

    const ctx = document.getElementById('timeSeriesChart');
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
                        backgroundColor: 'rgba(255, 182, 193, 0.7)', // pink
                        borderColor: '#ff99a8',
                        borderWidth: 1
                    },
                    {
                        type: 'bar',
                        label: 'MTTR (Hours)',
                         mttrValues,
                        backgroundColor: 'rgba(135, 206, 235, 0.7)', // light blue
                        borderColor: '#5fa8d3',
                        borderWidth: 1
                    },
                    {
                        type: 'line',
                        label: 'Availability (%)',
                         availValues,
                        backgroundColor: 'rgba(0, 191, 165, 0.7)', // teal
                        borderColor: '#00bfa5',
                        borderWidth: 3,
                        pointBackgroundColor: '#00bfa5',
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
                        text: 'Reliability Over Time (Last 7 Days)'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    },
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            unit: 'day',
                            displayFormats: {
                                day: 'MMM D'
                            }
                        },
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    },
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