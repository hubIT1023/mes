<?php include __DIR__ . '/../layouts/html/header.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container-lg mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Live Data: <?= htmlspecialchars($device['device_name']) ?></h2>
            <p class="text-muted">Device Key: <?= substr($device['device_key'], 0, 8) ?>...</p>
        </div>
        <a href="/device" class="btn btn-secondary">← Back to Devices</a>
    </div>

    <!-- Live Chart -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Real-Time Trend (Last 60 Data Points)</h5>
        </div>
        <div class="card-body">
            <canvas id="liveChart" height="120"></canvas>
        </div>
    </div>

    <!-- History Table -->
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0">Last 24 Hours</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead class="table-light">
                    <tr>
                        <th>Time</th>
                        <th>Parameter</th>
                        <th>Value</th>
                        <th>Unit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($history)): ?>
                        <tr><td colspan="4" class="text-center text-muted">No data in the last 24 hours</td></tr>
                    <?php else: ?>
                        <?php foreach ($history as $row): ?>
                            <tr>
                                <td><?= date('M j, H:i:s', strtotime($row['recorded_at'])) ?></td>
                                <td><?= htmlspecialchars($row['parameter_name']) ?></td>
                                <td><?= number_format((float)$row['parameter_value'], 2) ?></td>
                                <td><?= htmlspecialchars($row['unit'] ?? '—') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
const ctx = document.getElementById('liveChart').getContext('2d');
const liveChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_map(fn($d) => date('H:i:s', strtotime($d['recorded_at'])), $recentData)) ?>,
        datasets: [{
            label: 'Value',
            data: <?= json_encode(array_column($recentData, 'parameter_value')) ?>,
            borderColor: '#3498db',
            backgroundColor: 'rgba(52, 152, 219, 0.1)',
            borderWidth: 2,
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            x: { grid: { display: false } },
            y: { beginAtZero: false }
        },
        plugins: {
            legend: { display: false }
        }
    }
});

// Optional: Auto-refresh every 15 seconds
// setInterval(() => location.reload(), 15000);
</script>

<?php include __DIR__ . '/../layouts/html/footer.php'; ?>