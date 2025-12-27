<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Maintenance Checklist - <?= htmlspecialchars($checklist['checklist_id'] ?? '') ?></title>
    <style>
        /* Screen styles */
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            background-color: #f4f4f4; 
        }
        .container { 
            max-width: 1000px; 
            margin: auto; 
            background-color: white; 
            padding: 20px; 
            border: 1px solid #ccc; 
            box-shadow: 0 0 10px rgba(0,0,0,0.1); 
        }
        h1, h2 { 
            color: #333; 
            border-bottom: 2px solid #0056b3; 
            padding-bottom: 5px; 
            margin-top: 20px; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 15px; 
        }
        th, td { 
            border: 1px solid #ddd; 
            padding: 8px; 
            text-align: left; 
        }
        th { 
            background-color: #0056b3; 
            color: white; 
        }
        .task-input { 
            width: 95%; 
            padding: 4px; 
            font-size: 0.9em; 
        }
        .btn { 
            padding: 10px 15px; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
            font-size: 1.1em; 
            margin: 5px;
        }
        .btn-save { 
            background-color: #28a745; 
            color: white; 
        }
        .btn-complete { 
            background-color: #007bff; 
            color: white; 
        }
        .btn-print { 
            background-color: #6c757d; 
            color: white; 
        }
        .btn-row { 
            margin-top: 20px; 
            text-align: center;
        }
        .alert { 
            padding: 10px; 
            margin-bottom: 15px; 
            border-radius: 4px; 
        }
        .alert-error { 
            background-color: #f8d7da; 
            color: #721c24; 
        }
        
        /* Print styles - hide buttons and non-essential elements */
        @media print {
            body { 
                background-color: white; 
                margin: 0; 
            }
            .container { 
                box-shadow: none; 
                border: none; 
                padding: 10px; 
            }
            .btn-row, .alert, .no-print { 
                display: none !important; 
            }
            .task-input { 
                border: none; 
                background: none; 
                box-shadow: none; 
                width: 100%; 
                padding: 0; 
                font-size: 12pt;
            }
            table { 
                font-size: 12pt; 
            }
            th, td { 
                border: 1px solid #000; 
                padding: 6px;
            }
            h1, h2 { 
                border-bottom: 2px solid #000; 
                color: #000;
            }
            /* Ensure all content fits on page */
            page-break-inside: avoid;
        }
    </style>
</head>
<body>
<div class="container">

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-error">
            <?= htmlspecialchars($_SESSION['flash_error']) ?>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <h1>Maintenance Checklist Form</h1>

    <!-- Asset & Checklist Info -->
    <table>
        <!--tr><th>Maintenance Checklist ID</th><td><?= htmlspecialchars($checklist['maintenance_checklist_id'] ?? '') ?></td></tr-->
		<tr><th>Date Started</th><td><?= htmlspecialchars($checklist['date_started'] ?? '') ?></td></tr>
        <tr><th>Asset ID</th><td><?= htmlspecialchars($checklist['asset_id'] ?? '') ?></td></tr>
        <tr><th>Asset Name</th><td><?= htmlspecialchars($checklist['asset_name'] ?? '') ?></td></tr>
        <tr><th>Location</th><td><?= htmlspecialchars($checklist['location_id_3'] ?? '') ?></td></tr>
        <tr><th>Status</th><td><?= htmlspecialchars($checklist['status'] ?? '') ?></td></tr>
        <tr><th>Work Order</th><td><?= htmlspecialchars($checklist['work_order_ref'] ?? '') ?></td></tr>
        <tr><th>Maintenance Task</th><td><?= htmlspecialchars($checklist['checklist_id'] ?? '') ?></td></tr>
		 <tr><th>Person In-charge</th><td><?= htmlspecialchars($checklist['technician_name'] ?? '') ?></td></tr>
    </table>

    <!-- Editable Tasks Form -->
    <form method="POST" action="/mes/maintenance_checklist/update">
        <!-- Pass the maintenance checklist ID (required for ownership & update) -->
        <input type="hidden" name="maintenance_checklist_id" 
               value="<?= htmlspecialchars($checklist['maintenance_checklist_id'] ?? '') ?>">

        <h2>Checklist Tasks</h2>
        <table>
            <thead>
                <tr>
                    <th style="width: 80px;">Order</th>
                    <th style="width: 50%;">Task Description</th>
                    <th style="width: 120px;">Result</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($tasks)): ?>
                    <?php foreach ($tasks as $i => $t): ?>
                    <tr>
                        <td><?= htmlspecialchars($t['task_order'] ?? '') ?></td>
                        <td><?= htmlspecialchars($t['task_text'] ?? '') ?></td>
                        <td>
                            <input type="text" 
                                   name="tasks[<?= $i ?>][status]" 
                                   value="<?= htmlspecialchars($t['result_value'] ?? '') ?>" 
                                   class="task-input"
                                   placeholder="Enter result...">
                        </td>
                        <td>
                            <input type="text" name="tasks[<?= $i ?>][remarks]" 
                                   value="<?= htmlspecialchars($t['result_notes'] ?? '') ?>" 
                                   class="task-input">
                        </td>
                        <input type="hidden" name="tasks[<?= $i ?>][task_id]" 
                               value="<?= htmlspecialchars($t['task_id'] ?? '') ?>">
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" style="text-align: center;">No tasks found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

     

		<div class="btn-row">
			<button type="button" onclick="window.print()" class="btn btn-print">🖨️ Print Checklist</button>
			<button type="submit" name="action" value="save" class="btn btn-save">Save Progress</button>
			<!-- ✅ Changed value to "archive" for clarity -->
			<button type="submit" name="action" value="archive" class="btn btn-complete">Mark as Completed</button>
		</div>
    </form>

</div>

<!-- Optional: Add print dialog instruction for first-time users -->
<script>
// Optional: Show print tip on first visit (cookies needed for persistence)
document.addEventListener('DOMContentLoaded', function() {
    const printBtn = document.querySelector('.btn-print');
    if (printBtn) {
        printBtn.addEventListener('click', function() {
            // The window.print() will automatically show the print dialog
            // No additional code needed
        });
    }
});
</script>
</body>
</html>