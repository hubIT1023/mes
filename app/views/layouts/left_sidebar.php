<!-- /app/views/layouts/html/sidebar_2  -->


<ul class="navbar-nav sidebar accordion bg-light text-dark" id="accordionSidebar">

  <!-- Sidebar - Brand -->
  <header class="bg-primary text-center py-3">
    <a class="sidebar-brand d-block" href="/dashboard">
      <img src="/../assets/img/hubIT_logo-v2.png" alt="hubIT Logo" class="img-fluid" style="max-height: 70px;" />
    </a>
  </header>

  <!-- Divider -->
  <hr class="sidebar-divider my-2">

  <!-- Dashboard -->
  <li class="nav-item active">
    <a class="nav-link" href="/mes/dashboard_admin">
      <i class="fas fa-fw fa-tachometer-alt"></i>
      <span>Admin</span>
    </a>
  </li>

  
   <!-- Asset List -->
  <li class="nav-item">
    <a class="nav-link" href="/assets-list">
      <i class="fas fa-fw fa-boxes"></i>
      <span>Asset List</span>
    </a>
  </li>

  <hr class="sidebar-divider d-none d-md-block">
  

  <!-- Assets Management -->
  <li class="nav-item">
    <a class="nav-link" href="/add-assets">
      <i class="fas fa-fw fa-plus-square"></i>
      <span>Add Assets</span>
    </a>
  </li>

  <li class="nav-item">
    <a class="nav-link" href="/manage-checklist-templates">
      <i class="fas fa-fw fa-tasks"></i>
      <span>Create Checklists</span>
    </a>
  </li>



  <hr class="sidebar-divider">

  <li class="nav-item">
    <a class="nav-link" href="/registered_assets">
      <i class="fas fa-fw fa-calendar-check"></i>
      <span>Maintenance Schedule</span>
    </a>
  </li>

  <!-- Maintenance -->
  <li class="nav-item">
    <a class="nav-link" href="/incoming-maintenance">
      <i class="fas fa-fw fa-tools"></i>
      <span>Incoming Maintenance</span>
    </a>
  </li>

  <li class="nav-item">
    <a class="nav-link" href="/completed-work-orders">
      <i class="fas fa-fw fa-check-circle"></i>
      <span>Completed Work Orders</span>
    </a>
  </li>

  <hr class="sidebar-divider">


  <!-- Interface Heading -->
  <div class="sidebar-heading">Interface</div>

  <!-- Configure -->
  <li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo"
       aria-expanded="false" aria-controls="collapseTwo">
      <i class="fas fa-fw fa-cogs"></i>
      <span>Configure</span>
    </a>
    <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
      <div class="bg-white py-2 collapse-inner rounded">
        <h6 class="collapse-header">Custom Components:</h6>
        <a class="collapse-item" href="/meta-data-settings">Column Settings</a>
        <a class="collapse-item" href="/forms/state_config.php">Edit State Config</a>
        <a class="collapse-item" href="generateStopcause.php">Generate Stopcause</a>
      </div>
    </div>
  </li>

  <!-- Database -->
  <li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseDatabase"
       aria-expanded="false" aria-controls="collapseDatabase">
      <i class="fas fa-fw fa-database"></i>
      <span>Database</span>
    </a>
    <div id="collapseDatabase" class="collapse" aria-labelledby="headingDatabase" data-parent="#accordionSidebar">
      <div class="bg-white py-2 collapse-inner rounded">
        <h6 class="collapse-header">Tables:</h6>
        <a class="collapse-item" href="/add-assets">Add Assets</a>
        <a class="collapse-item" href="/tool-state-log">Tool Status</a>
        <a class="collapse-item" href="/registered_assets">Assets Maintenance Schedule</a>
        <a class="collapse-item" href="/incoming-maintenance">Incoming Maintenance</a>
      </div>
    </div>
  </li>

  <!-- Forms -->
  <li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseForms"
       aria-expanded="false" aria-controls="collapseForms">
      <i class="fas fa-fw fa-file-alt"></i>
      <span>Forms</span>
    </a>
    <div id="collapseForms" class="collapse" aria-labelledby="headingForms" data-parent="#accordionSidebar">
      <div class="bg-white py-2 collapse-inner rounded">
        <h6 class="collapse-header">Forms:</h6>
        <a class="collapse-item" href="/manage-checklist-templates">Create Checklists</a>
        <a class="collapse-item" href="/completed-work-orders">Completed Work Orders</a>
        <a class="collapse-item" href="/scheduled-maintenance-form">Asset Schedule Maintenance</a>
      </div>
    </div>
  </li>

  <hr class="sidebar-divider">

  <!-- Addons -->
  <div class="sidebar-heading">Addons</div>

  <!-- Pages -->
  <li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePages"
       aria-expanded="false" aria-controls="collapsePages">
      <i class="fas fa-fw fa-folder-open"></i>
      <span>Pages</span>
    </a>
    <div id="collapsePages" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
      <div class="bg-white py-2 collapse-inner rounded">
        <h6 class="collapse-header">Login Screens:</h6>
        <a class="collapse-item" href="login.html">Login</a>
        <a class="collapse-item" href="register.html">Register</a>
        <a class="collapse-item" href="forgot-password.html">Forgot Password</a>
        <div class="collapse-divider"></div>
        <h6 class="collapse-header">Other Pages:</h6>
        <a class="collapse-item" href="404.html">404 Page</a>
        <a class="collapse-item" href="blank.html">Blank Page</a>
      </div>
    </div>
  </li>

  <!-- Charts -->
  <li class="nav-item">
    <a class="nav-link" href="charts.html">
      <i class="fas fa-fw fa-chart-line"></i>
      <span>Charts</span>
    </a>
  </li>

  <!-- Sidebar Toggler -->
  <div class="text-center d-none d-md-inline">
    <button class="rounded-circle border-0" id="sidebarToggle"></button>
  </div>

</ul>
<!-- End of Sidebar -->

<!-- Extra CSS -->
<style>
  .sidebar .nav-link {
    color: #343a40; /* Darker text for readability */
    font-weight: 500;
  }
  .sidebar .nav-link:hover {
    color: #0d6efd; /* Bootstrap primary blue on hover */
  }
  .sidebar-heading {
    font-size: 0.85rem;
    font-weight: 700;
    text-transform: uppercase;
    color: #6c757d; /* Muted gray */
    padding-left: 1rem;
    margin-top: 1rem;
  }

  .sidebar-divider {
  border-top: 1px solid #495057; /* dark gray */
}
</style>

