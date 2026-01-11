<?php require __DIR__ . '/../layouts/html/header.php'; ?>

<div class="container mt-4">
    <h2>📘 Machine Event Log</h2>

    <!-- Filters -->
    <form method="GET" class="row g-3 mb-3">
        <div class="col-md-3">
            <input class="form-control" name="asset_id" placeholder="Asset ID"
                   value="<?= htmlspecialchars($_GET['asset_id'] ?? '') ?>">
        </div>
		
		<div class="col-md-3">
            <input class="form-control" name="asset_id" placeholder="Asset ID"
                   value="<?= htmlspecialchars($_GET['entity'] ?? '') ?>">
        </div>

        <!--div class="col-md-2">
            <select name="stopcause" class="form-select">
                <option value="">All States</option>
                <?php// foreach (['FAIL','IDLE','PROD'] as $s): ?>
                    <option value="<?//= $s ?>"
                        <?//= ($_GET['stopcause'] ?? '') === $s ? 'selected' : '' ?>>
                        <?//= $s ?>
                    </option>
                <?php //endforeach; ?>
            </select>
        </div-->

        <div class="col-md-2">
            <input type="datetime-local" name="from" class="form-control"
                   value="<?= htmlspecialchars($_GET['from'] ?? '') ?>">
        </div>

        <div class="col-md-2">
            <input type="datetime-local" name="to" class="form-control"
                   value="<?= htmlspecialchars($_GET['to'] ?? '') ?>">
        </div>

        <div class="col-md-2">
            <button class="btn btn-primary w-100">Filter</button>
        </div>
    </form>

    <!-- Log Table -->
    <table class="table table-sm table-bordered table-striped">
        <thead class="table-dark">
        <tr>
            <th>Time</th>
            <!--th>Group</th-->
            <!--th>Location</th-->
            <th>Asset</th>
            <th>Entity</th>
            <th>State</th>
            <th>Reason</th>
            <th>Action</th>
            <th>Status</th>
            <th>Done By</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($logs as $log): ?>
            <tr>
                <td><?= htmlspecialchars($log['event_time']) ?></td>
                <!--td><?//= htmlspecialchars($log['group_code']) ?></td>
                <td><?//= htmlspecialchars($log['location_code']) ?></td-->
                <td><?= htmlspecialchars($log['asset_id']) ?></td>
                <td><?= htmlspecialchars($log['entity']) ?></td>
                <td><?= htmlspecialchars($log['stopcause_start']) ?></td>
                <td><?= htmlspecialchars($log['reason']) ?></td>
                <td><?= htmlspecialchars($log['action']) ?></td>
                <td><?= htmlspecialchars($log['status']) ?></td>
                <td><?= htmlspecialchars($log['reported_by']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../layouts/html/footer.php'; ?>
