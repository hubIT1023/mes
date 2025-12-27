<?php //completed_work_order_details.php ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Completed Work Order Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1, h2 {
            color: #0056b3;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #0056b3;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .info-table th, .info-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .info-table th {
            background-color: #f8f9fa;
            width: 30%;
        }
        .tasks-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .tasks-table th, .tasks-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .tasks-table th {
            background-color: #0056b3;
            color: white;
        }
        .btn {
            background-color: #0056b3;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }
        .btn:hover {
            background-color: #003d7a;
        }
        @media print {
            .no-print { display: none; }
            body { background-color: white; }
            .container { box-shadow: none; }
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Completed Work Order Details</h1>
    
    <!-- Work Order Information -->
    <table class="info-table">
        <tr>
            <th>Maintenance Checklist ID</th>
            <td><?= htmlspecialchars($work_order['maintenance_checklist_id'] ?? '') ?></td>
        </tr>
        <tr>
            <th>Work Order Reference</th>
            <td><?= htmlspecialchars($work_order['work_order_ref'] ?? '') ?></td>
        </tr>
        <tr>
            <th>Asset ID</th>
            <td><?= htmlspecialchars($work_order['asset_id'] ?? '') ?></td>
        </tr>
        <tr>
            <th>Asset Name</th>
            <td><?= htmlspecialchars($work_order['asset_name'] ?? '') ?></td>
        </tr>
        <tr>
            <th>Checklist ID</th>
            <td><?= htmlspecialchars($work_order['checklist_id'] ?? '') ?></td>
        </tr>
        <tr>
            <th>Location</th>
            <td>
                <?= htmlspecialchars(
                    trim(
                        ($work_order['location_id_1'] ?? '') . ' ' .
                        ($work_order['location_id_2'] ?? '') . ' ' .
                        ($work_order['location_id_3'] ?? '')
                    )
                ) ?>
            </td>
        </tr>
        <tr>
            <th>Technician</th>
            <td><?= htmlspecialchars($work_order['technician_name'] ?? '') ?></td>
        </tr>
        <tr>
            <th>Date Started</th>
            <td><?= $work_order['date_started'] ? date('Y-m-d H:i', strtotime($work_order['date_started'])) : 'N/A' ?></td>
        </tr>
        <tr>
            <th>Date Completed</th>
            <td><?= $work_order['date_completed'] ? date('Y-m-d H:i', strtotime($work_order['date_completed'])) : 'N/A' ?></td>
        </tr>
        <tr>
            <th>Archived At</th>
            <td><?= $work_order['created_at'] ? date('Y-m-d H:i', strtotime($work_order['created_at'])) : 'N/A' ?></td>
        </tr>
        <tr>
            <th>Archived By</th>
            <td><?= htmlspecialchars($work_order['created_by'] ?? '') ?></td>
        </tr>
    </table>
    
    <!-- Tasks -->
    <h2>Completed Tasks</h2>
    <table class="tasks-table">
        <thead>
            <tr>
                <th>Order</th>
                <th>Task Description</th>
                <th>Result</th>
                <th>Remarks</th>
                <th>Completed At</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($tasks)): ?>
                <?php foreach ($tasks as $task): ?>
                <tr>
                    <td><?= htmlspecialchars($task['task_order'] ?? '') ?></td>
                    <td><?= htmlspecialchars($task['task_text'] ?? '') ?></td>
                    <td><?= htmlspecialchars($task['result_value'] ?? '') ?></td>
                    <td><?= htmlspecialchars($task['result_notes'] ?? '') ?></td>
                    <td><?= $task['completed_at'] ? date('Y-m-d H:i', strtotime($task['completed_at'])) : 'N/A' ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center;">No tasks found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <a href="/mes/completed_work_orders" class="btn no-print">← Back to List</a>
    <button onclick="window.print()" class="btn no-print" style="margin-left: 10px;">🖨️ Print</button>
</div>
</body>
</html>