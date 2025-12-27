<?php 
// configModeColor.php

if (!isset($items)) {
    $items = [];
}
//include __DIR__ . '/../layouts/html/header.php'; 
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Metadata Customization - HubIT</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom Styles -->
    <style>
        body { font-family: 'Inter', sans-serif; }
        .sidebar-sticky {
            position: sticky;
            top: 56px;
            height: calc(100vh - 56px);
            overflow-y: auto;
        }
    </style>
</head>

<body class="bg-white text-slate-900 leading-normal">
<header class="sticky top-0 z-50 bg-white bg-opacity-90 backdrop-blur-sm border-b border-slate-200">
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
        <a href="#" class="text-2xl font-bold text-brand-600">HubIT.online</a>
        <div class="hidden md:flex space-x-6">
            <a href="/mes/dashboard_admin" class="text-slate-600 hover:text-brand-600 transition-colors">Admin</a>
            <a href="/mes/signin" class="text-slate-600 hover:text-brand-600 transition-colors">Log-out</a>
        </div>
    </nav>
</header>

<div class="container-fluid p-0">
    <div class="row g-0 min-vh-100">
        <!-- Sidebar Column -->
        <div class="col-md-3 col-lg-2 bg-light sidebar-sticky">
            <?php include __DIR__ . '/../layouts/html/sidebar_2.php'; ?>
        </div>
		<!-- Main Content Column -->
        <div class="col-md-9 col-lg-10">
            <h2 class="text-3xl font-bold text-center my-4">Mode Color Configuration</h2>

            <div id="wrapper">
                <div id="content-wrapper" class="d-flex flex-column bg-white">
                    <div class="container-fluid">
                        <!-- Alerts -->
			
			<?php if (isset($_SESSION['success'])): ?>
				<div class="alert alert-success alert-dismissible fade show" role="alert">
					<?= htmlspecialchars($_SESSION['success']) ?>
					<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
				</div>
				<?php unset($_SESSION['success']); ?>
			<?php endif; ?>

			<?php if (isset($_SESSION['error'])): ?>
				<div class="alert alert-danger alert-dismissible fade show" role="alert">
					<?= htmlspecialchars($_SESSION['error']) ?>
					<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
				</div>
				<?php unset($_SESSION['error']); ?>
			<?php endif; ?>

			<!-- Add New Mode Button -->
			<button class="btn btn-primary mb-3" onclick="showCreateRow()">
				+ Add New Mode
			</button>

			<div class="table-responsive">
				<table class="table table-bordered" id="modeColorTable">
					<thead class="table-light">
						<tr>
							<th>Mode Key</th>
							<th>Label</th>
							<th>CSS Class</th>
							<th>Preview</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody>
						<!-- Blank Create Row (Hidden by default) -->
						<tr id="createRow" class="d-none">
							<form method="POST" action="/mes/mode-color" style="display: contents;">
								<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
								<td><input type="text" name="mode_key" class="form-control form-control-sm" required placeholder="e.g., IDLE"></td>
								<td><input type="text" name="label" class="form-control form-control-sm" required placeholder="e.g., Idle"></td>
								<td><input type="text" name="tailwind_class" class="form-control form-control-sm" required placeholder="e.g., bg-blue-500"></td>
								<td><span class="text-white px-2 py-1 rounded">Preview</span></td>
								<td>
									<button type="submit" class="btn btn-sm btn-success me-1">Create</button>
									<button type="button" class="btn btn-sm btn-secondary" onclick="hideCreateRow()">Cancel</button>
								</td>
							</form>
						</tr>

						<!-- Existing Items -->
						<?php if (empty($items)): ?>
							<tr>
								<td colspan="5" class="text-center text-muted">No mode colors configured yet.</td>
							</tr>
						<?php else: ?>
							<?php foreach ($items as $item): ?>
							<tr>
								<form method="POST" action="/mes/mode-color/update" style="display: contents;">
									<input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
									<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
									<td><input type="text" name="mode_key" class="form-control form-control-sm" value="<?= htmlspecialchars($item['mode_key']) ?>" required></td>
									<td><input type="text" name="label" class="form-control form-control-sm" value="<?= htmlspecialchars($item['label']) ?>" required></td>
									<td><input type="text" name="tailwind_class" class="form-control form-control-sm" value="<?= htmlspecialchars($item['tailwind_class']) ?>" required></td>
									<td><span class="<?= htmlspecialchars($item['tailwind_class']) ?> text-white px-2 py-1 rounded"><?= htmlspecialchars($item['label']) ?></span></td>
									<td>
										<button type="submit" class="btn btn-sm btn-success me-1">Save</button>
										<form method="POST" action="/mes/mode-color/delete" style="display:inline;" onsubmit="return confirm('Delete?')">
											<input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
											<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
											<button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
										</form>
									</td>
								</form>
							</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
		      </div>
                </div>
            </div>
  </div>
</div>

<?php include __DIR__ . '/../layouts/html/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


<script>
function showCreateRow() {
    document.getElementById('createRow').classList.remove('d-none');
}
function hideCreateRow() {
    document.getElementById('createRow').classList.add('d-none');
}
</script>
</body>
</html>