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

    <!-- MTBF Section -->
    <h4>⏱ MTBF (Mean Time Between Failures)</h4>
    <?php if (empty($mtbf)): ?>
        <div class="alert alert-info">
            No consecutive failure events found. MTBF requires at least two 'FAIL' records per asset.
        </div>
    <?php else: ?>
        <div class="chart-container" style="height: 250px; margin-bottom: 20px;">
            <canvas id="mtbfChart"></canvas>
        </div>
        <table class="table table-bordered">
            <thead class="table-light">
                <tr><th>Asset</th><th>MTBF (hrs)</th></tr>
            </thead>
            <tbody>
                <?php foreach ($mtbf as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['asset_id']) ?></td>
                        <td><?= htmlspecialchars($row['mtbf_hours']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- MTTR Section -->
    <h4 class="mt-4">🛠 MTTR (Mean Time To Repair)</h4>
    <?php if (empty($mttr)): ?>
        <div class="alert alert-info">
            No completed repairs detected. MTTR requires a 'PROD' event after each 'FAIL'.
        </div>
    <?php else: ?>
        <div class="chart-container" style="height: 250px; margin-bottom: 20px;">
            <canvas id="mttrChart"></canvas>
        </div>
        <table class="table table-bordered">
            <thead class="table-light">
                <tr><th>Asset</th><th>MTTR (hrs)</th></tr>
            </thead>
            <tbody>
                <?php foreach ($mttr as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['asset_id']) ?></td>
                        <td><?= htmlspecialchars($row['mttr_hours']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- Availability Section -->
    <h4 class="mt-4">✅ System Availability (%)</h4>
    <?php if (empty($availability)): ?>
        <div class="alert alert-info">
            Availability cannot be calculated. Requires both MTBF and MTTR data for the same asset(s).
        </div>
    <?php else: ?>
        <div class="chart-container" style="height: 250px; margin-bottom: 20px;">
            <canvas id="availabilityChart"></canvas>
        </div>
        <table class="table table-bordered">
            <thead class="table-light">
                <tr><th>Asset</th><th>Availability (%)</th></tr>
            </thead>
            <tbody>
                <?php foreach ($availability as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['asset_id']) ?></td>
                        <td><?= htmlspecialchars($row['availability_pct']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Helper: safely convert PHP arrays to JS
const mtbfLabels = <?= json_encode(array_column($mtbf, 'asset_id')) ?>;
const mtbfValues = <?= json_encode(array_column($mtbf, 'mtbf_hours')) ?>;

const mttrLabels = <?= json_encode(array_column($mttr, 'asset_id')) ?>;
const mttrValues = <?= json_encode(array_column($mttr, 'mttr_hours')) ?>;

const availLabels = <?= json_encode(array_column($availability, 'asset_id')) ?>;
const availValues = <?= json_encode(array_column($availability, 'availability_pct')) ?>;

// MTBF Chart
if (mtbfLabels.length > 0) {
    const mtbfCtx = document.getElementById('mtbfChart').getContext('2d');
    new Chart(mtbfCtx, {
        type: 'bar',
        data: {
            labels: mtbfLabels,
            datasets: [{
                label: 'MTBF (Hours)',
                data: mtbfValues,
                backgroundColor: '#27ae60',
                borderColor: '#219653',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: { display: true, text: 'MTBF by Asset' }
            },
            scales: {
                y: { beginAtZero: true, title: { display: true, text: 'Hours' } }
            }
        }
    });
}

// MTTR Chart
if (mttrLabels.length > 0) {
    const mttrCtx = document.getElementById('mttrChart').getContext('2d');
    new Chart(mttrCtx, {
        type: 'bar',
        data: {
            labels: mttrLabels,
            datasets: [{
                label: 'MTTR (Hours)',
                data: mttrValues,
                backgroundColor: '#f39c12',
                borderColor: '#d68910',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: { display: true, text: 'MTTR by Asset' }
            },
            scales: {
                y: { beginAtZero: true, title: { display: true, text: 'Hours' } }
            }
        }
    });
}

// Availability Chart
if (availLabels.length > 0) {
    const availCtx = document.getElementById('availabilityChart').getContext('2d');
    new Chart(availCtx, {
        type: 'bar',
        data: {
            labels: availLabels,
            datasets: [{
                label: 'Availability (%)',
                data: availValues,
                backgroundColor: '#3498db',
                borderColor: '#2980b9',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: { display: true, text: 'System Availability by Asset' }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    title: { display: true, text: 'Percentage (%)' }
                }
            }
        }
    });
}
</script>

<?php require __DIR__ . '/../layouts/html/footer.php'; ?>