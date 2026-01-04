

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hub It Dashboard - Live Demo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet"  />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .asset-card { transition: all 0.2s ease-in-out; }
        .asset-card:hover { box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); transform: translateY(-2px); }
    </style>
</head>


<body class="bg-white text-slate-900 leading-normal">

<header class="sticky top-0 z-50 bg-white bg-opacity-90 backdrop-blur-sm border-b border-slate-200">
<?php //load_view('topbar'); ?>

    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
        <a href="#" class="text-2xl font-bold text-brand-600">HubIT.online</a>
        <div class="hidden md:flex space-x-6">
            <a href="/admin_dashboard" class="text-slate-600 hover:text-brand-600 transition-colors">Admin</a>
            <a href="#login" class="text-slate-600 hover:text-brand-600 transition-colors">Log-in</a>
        </div>
    </nav>
</header>

<div class="container-fluid">
<h2 class="text-3xl font-bold text-center">Business Intelligence Dashboard</h2>

<div id="wrapper">



        <div id="content-wrapper" class="d-flex flex-column bg-white">
        
              

            <div class="container-fluid  ">

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($_SESSION['success']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['success']); // Clear after showing ?>
                <?php endif; ?>

                <!-- Dashboard Header -->
                <?php if (!empty($orgId)): ?>
                    
                        <!-- New Group Button -->
                <div class="d-sm-flex align-items-center justify-content-between  mb-3">
                    <!--div class="alert alert-info btn- sm mb-0">
                       Org_ID: <?= htmlspecialchars($orgId, ENT_QUOTES, 'UTF-8') ?>
                    </div-->

                    <!--button class="btn btn-xxl btn-outline-primary" data-bs-toggle="modal" data-bs-target="#createGroupModal">
                        + New Group
                    </button-->
                    </div>

                    <!---------- Divider -------->
                     <hr class="divider  my-3">
                    <!---------- Divider -------->

                <?php else: ?>
                    <div class="alert alert-warning">Session org_id is not set.</div>
                    <?php 
                    load_view('footer'); 
                    exit; 
                    ?>
                <?php endif; ?>

               

                <!-- Groups List -->
               
                <?php if (empty($groups)): ?>
                    <p>No groups created yet.</p>
                <?php else: ?>
                    <?php foreach ($groups as $g): ?>
                        <div class="card mt-2">
                            <!-- Group Header -->
                            <div class="d-flex align-items-center justify-content-between p-3" style="background-color:#426ff5; color:#fcfdff;">
                                <div>
                                    <small>
                                        GC: <?= (int)$g['group_code'] ?> | 
                                        LC: <?= (int)$g['location_code'] ?> | 
                                        Location: <?= htmlspecialchars($g['location_name']) ?>
                                    </small>
                                    <h5 class="mb-0"><?= htmlspecialchars($g['group_name']) ?></h5>
                                </div>

                                <div class="text-end">
                                    <!--button class="btn btn-sm btn-light text-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-light text-danger" title="Delete">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    <button class="btn btn-sm btn-success" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#addEntityModal_<?= (int)$g['group_code'] ?>">
                                        <i class="fas fa-plus"></i>
                                    </button-->
                                </div>
                            </div>

                            <!--  Entities / Tool State Cards -->
                            <div class="card-body " >

                            <!--  fit 9  entity_toolState_card inside this card-body -->  
                            
                                    <?php 
                                    $group = $g;
                                    $group_location_name = $g['location_name'] ?? 'Unknown Location';
                                    include __DIR__ . '/Assets/tool_card/entity_toolState_card.php';   // inserted cards
                                    ?>
                                
                            </div>
                        </div>

                        <?php include __DIR__ . '/forms/createEntity_modal.php'; ?>
                    <?php endforeach; ?>
                <?php endif; ?>

            </div> <!-- /.container-fluid -->

        </div> <!-- /.content-wrapper -->


        <!-- ✅ Modals -->
        <?php include __DIR__ . '/forms/createGroup_modal.php'; ?>
        <?php include __DIR__ . '/forms/setToolState_modal.php'; ?>
        <?php include __DIR__ . '/forms/setToolAccessories_modal.php'; ?>

    </div> <!-- /#content-wrapper -->
</div> <!-- /#wrapper -->

<?php load_view('footer'); // Includes JS ?>
</body>
</html>