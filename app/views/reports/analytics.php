<?php require __DIR__ . '/../partials/header.php'; ?>

<div class="container mt-4">
    <h2>📊 Reliability Analytics</h2>

    <!-- Filters -->
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-4">
            <label class="form-label">Asset ID</label>
            <input type="text" name="asset_id"
                   value="<?= htmlspecialchars($_GET['asset_id'] ?? '') ?>"
                   class="form-control">
        </div>
        <div class="col-md-2 align-self-end">
            <button class="btn btn-primary">Filter</button>
        </div>
    </form>

    <!-- MTBF -->
    <h4>⏱ MTBF (Hours)</h4>
    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th>Asset</th>
                <th>MTBF (hrs)</th>
            </tr>
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

    <!-- MTTR -->
    <h4 class="mt-4">🛠 MTTR (Hours)</h4>
    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th>Asset</th>
                <th>MTTR (hrs)</th>
            </tr>
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
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
