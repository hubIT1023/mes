<!DOCTYPE html>
<html>
<head>
    <title>Scheduler Configuration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<h2>Tenant Scheduler Configuration</h2>

<!-- Tenant Selector -->
<form method="GET" action="/form_mms/scheduler_config" class="mb-4">
    <div class="row g-3 align-items-center">
        <div class="col-auto">
            <label for="tenant" class="col-form-label">Select Tenant:</label>
        </div>
        <div class="col-auto">
            <select name="tenant_id" id="tenant" class="form-select" onchange="this.form.submit()">
                <option value="">-- Select Tenant --</option>
                <?php foreach ($tenants as $t): ?>
                    <option value="<?= htmlspecialchars($t['org_id']) ?>" <?= ($tenantId ?? '') === $t['org_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($t['tenant_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</form>

<?php if (!empty($configs)): ?>
<form method="POST" action="/form_mms/scheduler_config_update">
    <input type="hidden" name="tenant_id" value="<?= htmlspecialchars($tenantId) ?>">

    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>Maintenance Type</th>
                <th>Interval (days)</th>
                <th>Enabled</th>
                <th>Technician</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($configs as $config): ?>
            <tr>
                <td><?= htmlspecialchars($config['maintenance_type']) ?></td>
                <td>
                    <input type="number" name="interval_days[<?= $config['maintenance_type'] ?>]" value="<?= $config['interval_days'] ?>" class="form-control">
                </td>
                <td>
                    <input type="checkbox" name="enabled[<?= $config['maintenance_type'] ?>]" <?= $config['enabled'] ? 'checked' : '' ?>>
                </td>
                <td>
                    <input type="text" name="technician[<?= $config['maintenance_type'] ?>]" value="<?= htmlspecialchars($config['technician_name']) ?>" class="form-control">
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <button type="submit" class="btn btn-primary">Save Configuration</button>
</form>
<?php elseif($tenantId): ?>
    <p>No maintenance configuration found for this tenant.</p>
<?php endif; ?>

</body>
</html>
