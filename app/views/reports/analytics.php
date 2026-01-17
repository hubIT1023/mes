<?php 
// analytics.php

require __DIR__ . '/../layouts/html/header.php'; 
?>

<style>
    .stat-card { border-left: 4px solid #0d6efd; transition: transform 0.2s; }
    .stat-card:hover { transform: translateY(-3px); }
    .table-v-align td { vertical-align: middle; }
    .btn-group-xs > .btn { padding: .1rem .4rem; font-size: .75rem; }
</style>

<div class="container-fluid px-4 mt-4">
    <nav class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">📊 Reliability Analytics</h2>
            <p class="text-muted small mb-0">Asset Performance and Maintenance Metrics</p>
        </div>
        <a href="/mes/mms_admin" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Home
        </a>
    </nav>

    <!-- Filter Form -->
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-3">
            <label class="form-label fw-bold">Entity</label>
            <select class="form-select" name="entity">
				<option value="">All Entities</option>
				<?php if (!empty($entities)): ?>
					<?php foreach ($entities as $entity): ?>
						<option value="<?= htmlspecialchars($entity ?? '') ?>"
							<?= (($_GET['entity'] ?? '') === $entity) ? 'selected' : '' ?>>
							<?= htmlspecialchars($entity ?? '') ?>
						</option>
					<?php endforeach; ?>
				<?php endif; ?>
			</select>
        </div>

        <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>

        <?php if (!empty($_GET['entity'])): ?>
            <div class="col-md-3 d-flex align-items-end">
                <a href="?" class="btn btn-outline-secondary w-100">Clear</a>
            </div>
        <?php endif; ?>
    </form>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card stat-card shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small fw-bold">Avg MTBF</h6>
                    <h3 class="mb-0 text-primary fw-bold">
                        <?= !empty($mtbf) ? number_format(array_sum(array_column($mtbf, 'mtbf_hours')) / count($mtbf), 1) : '0' ?> <small class="h6">hrs</small>
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card shadow-sm border-danger">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small fw-bold">Avg MTTR</h6>
                    <h3 class="mb-0 text-danger fw-bold">
                        <?= !empty($mttr) ? number_format(array_sum(array_column($mttr, 'mttr_hours')) / count($mttr), 1) : '0' ?> <small class="h6">hrs</small>
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card shadow-sm border-success">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small fw-bold">System Availability</h6>
                    <h3 class="mb-0 text-success fw-bold">
                        <?= !empty($availability) ? number_format(array_sum(array_column($availability, 'availability_pct')) / count($availability), 1) : '0' ?>%
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Trend Chart -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-bottom-0 pt-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0">📈 Reliability Performance Trend</h5>
            <?php if (!empty($reliabilityByDate)): ?>
                <div class="btn-group shadow-sm" role="group">
                    <button type="button" class="btn btn-outline-primary btn-sm active" data-range="7">1W</button>
                    <button type="button" class="btn btn-outline-primary btn-sm" data-range="30">1M</button>
                    <button type="button" class="btn btn-outline-primary btn-sm" data-range="90">3M</button>
                </div>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <?php if (empty($reliabilityByDate)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-graph-up-arrow text-muted opacity-25" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2">No historical data found for the selected entity.</p>
                </div>
            <?php else: ?>
                <div style="height:350px;">
                    <canvas id="timeSeriesChart"></canvas>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Data Tables -->
    <div class="row">
        <?php 
        $tables = [
            ['title' => '⏱ MTBF', 'data' => $mtbf, 'key' => 'mtbf_hours', 'suffix' => ' hrs', 'color' => 'text-primary'],
            ['title' => '🛠 MTTR', 'data' => $mttr, 'key' => 'mttr_hours', 'suffix' => ' hrs', 'color' => 'text-danger'],
            ['title' => '✅ Availability', 'data' => $availability, 'key' => 'availability_pct', 'suffix' => '%', 'color' => 'text-success']
        ];

        foreach ($tables as $table): ?>
            <div class="col-lg-4 mb-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-header bg-white fw-bold"><?= htmlspecialchars($table['title']) ?></div>
                    <div class="card-body p-0 overflow-auto" style="max-height: 400px;">
                        <table class="table table-hover table-v-align mb-0">
                            <thead class="table-light sticky-top">
                                <tr><th class="small">Entity</th><th class="small text-end">Value</th></tr>
                            </thead>
                            <tbody>
                                <?php if (empty($table['data'])): ?>
                                    <tr><td colspan="2" class="text-center text-muted py-3">No data</td></tr>
                                <?php else: ?>
                                    <?php foreach ($table['data'] as $row): ?>
                                        <tr>
                                            <td>
												<span class="badge bg-light text-dark font-monospace">
													<?= htmlspecialchars($row['asset_id'] ?? 'N/A') ?>
												</span>
											</td>
                                            <td class="text-end fw-bold <?= $table['color'] ?>">
                                                <?= number_format($row[$table['key']], 2) ?><?= $table['suffix'] ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Fixed CDN URLs (no trailing spaces) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3"></script>

<script>
const rawData = <?= json_encode($reliabilityByDate ?? [], JSON_NUMERIC_CHECK | JSON_HEX_TAG | JSON_HEX_AMP) ?>;

if (Array.isArray(rawData) && rawData.length > 0) {
    const data = rawData.map(r => ({
        x: new Date(r.date + 'T00:00:00'),
        mtbf: r.mtbf_hours,
        mttr: r.mttr_hours,
        avail: r.availability_pct
    }));

    const maxDate = Math.max(...data.map(d => d.x.getTime()));
    const ctx = document.getElementById('timeSeriesChart');

    const chart = new Chart(ctx, {
        type: 'bar',
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
                x: { 
                    type: 'time', 
                    time: { unit: 'day' },
                    min: maxDate - (7 * 24 * 60 * 60 * 1000),
                    max: maxDate
                },
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
            const newMin = maxDate - (days * 24 * 60 * 60 * 1000);
            chart.options.scales.x.min = newMin;
            chart.options.scales.x.max = maxDate;
            chart.update();
        });
    });
}
</script>

<?php require __DIR__ . '/../layouts/html/footer.php'; ?>