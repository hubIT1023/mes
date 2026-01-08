<?php include __DIR__ . '/../layouts/html/header.php'; ?>
<!-- Load Chart.js from CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container-lg mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Live Data: <?= htmlspecialchars($device['device_name']) ?></h2>
            <p class="text-muted mb-0">
                <small>Device Key: <?= substr(htmlspecialchars($device['device_key']), 0, 8) ?>…</small>
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="/device" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <!-- Live Chart Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white py-2">
            <h5 class="mb-0">
                <i class="fas fa-chart-line me-2"></i> Real-Time Trend (Last 60 Data Points)
            </h5>
        </div>
        <div class="card-body">
            <div style="height: 250px;">
                <canvas id="liveChart"></canvas>
            </div>
        </div>
    </div>

    <!-- History Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-light py-2">
            <h5 class="mb-0">
                <i class="fas fa-history me-2"></i> Last 24 Hours
            </h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
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
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 opacity-50"></i><br>
                                No data received in the last 24 hours
                            </td>
                        </tr>
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

<!-- Initialize Chart -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('liveChart').getContext('2d');
    
    // Prepare initial data
    const initialLabels = <?= json_encode(array_map(fn($d) => date('H:i:s', strtotime($d['recorded_at'])), $recentData)) ?>;
    const initialData = <?= json_encode(array_column($recentData, 'parameter_value')) ?>;

    const liveChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: initialLabels,
            datasets: [{
                label: 'Live Value',
                data: initialData,
                borderColor: '#3498db',
                backgroundColor: 'rgba(52, 152, 219, 0.1)',
                borderWidth: 2,
                tension: 0.3,
                fill: true,
                pointRadius: 0,
                pointHoverRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                duration: 300
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        maxRotation: 0,
                        autoSkip: true
                    }
                },
                y: {
                    beginAtZero: false,
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            }
        }
    });

    // 🔥 Connect to SSE endpoint
    const deviceKey = '<?= addslashes($device['device_key']) ?>';
    const eventSource = new EventSource('/device/stream?device_key=' + encodeURIComponent(deviceKey));

    // Handle real-time updates
    eventSource.onmessage = function(e) {
        const newData = JSON.parse(e.data);
        const time = new Date(newData.recorded_at).toLocaleTimeString([], { 
            hour12: false, 
            hour: '2-digit', 
            minute: '2-digit', 
            second: '2-digit' 
        });
        const value = parseFloat(newData.parameter_value);

        // Update chart
        const labels = liveChart.data.labels;
        const data = liveChart.data.datasets[0].data;

        labels.push(time);
        data.push(value);

        // Keep only last 60 points
        if (labels.length > 60) {
            labels.shift();
            data.shift();
        }

        liveChart.update('none'); // Fast, non-animated update
    };

    // Handle errors
    eventSource.onerror = function(err) {
        console.warn('SSE connection lost. Reconnecting...');
        setTimeout(() => {
            eventSource.close();
            // Optional: auto-reconnect logic here
        }, 5000);
    };

    // Clean up on page unload
    window.addEventListener('beforeunload', () => {
        eventSource.close();
    });
});
</script>

<?php include __DIR__ . '/../layouts/html/footer.php'; ?>