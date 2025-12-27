


<!-- Footer HTML ... -->
 
    <footer class="sticky-footer bg-white">
      <div class="container my-auto">
        <div class="copyright text-center my-auto">
          <span>Copyright &copy; hubIT.online</span>
        </div>
      </div>
    </footer>
    <!-- End of Footer -->
<!-- Scripts -->

<!-- jQuery (optional if needed for other plugins) -->
<script src="/Assets/vendor/jquery/jquery.min.js"></script>

<!-- Bootstrap 5 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Other plugin scripts -->
<script src="/Assets/vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="/Assets/js/sb-admin-2.min.js"></script>
<script src="/Assets/vendor/chart.js/Chart.min.js"></script>
<script src="/Assets/js/demo/chart-area-demo.js"></script>
<script src="/Assets/js/demo/chart-pie-demo.js"></script>

<!-- /public/Assets/html/footer.php -->
<script src="/Assets/vendor/jquery/jquery.min.js"></script>
<script src="/Assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    function populateModal(event) {
        const trigger = event.relatedTarget;
        if (!trigger) return;

        const assetId = trigger.getAttribute('data-asset-id');
        const entity = trigger.getAttribute('data-header');
        const groupCode = trigger.getAttribute('data-group-code');
        const locationCode = trigger.getAttribute('data-location-code');
        const locationName = trigger.getAttribute('data-location-name') || 'Unknown';
        const date = trigger.getAttribute('data-date');

        console.log('populateModal triggered:', {
            modal: event.target.id,
            assetId, entity, groupCode, locationCode, locationName, date
        });

        // Determine prefix based on modal
        const prefix = event.target.id === 'toolStateModal' ? 'ts_' : 'acc_';

        const fields = {
            [prefix + 'modal_asset_id']: assetId,
            [prefix + 'modal_asset_id_display']: assetId,
            [prefix + 'ipt_entity']: entity,
            [prefix + 'modal_group_code']: groupCode,
            [prefix + 'modal_location_code']: locationCode,
            [prefix + 'modal_date_time']: date,
            [prefix + 'modal_start_time']: date,
            [prefix + 'modal_location']: locationName,
            [prefix + 'modal_group']: groupCode,
            [prefix + 'dbg_location']: locationName,
            [prefix + 'dbg_group']: groupCode
        };

        Object.entries(fields).forEach(([id, value]) => {
            const el = document.getElementById(id);
            if (el) {
                if (['INPUT', 'SELECT', 'TEXTAREA'].includes(el.tagName)) {
                    el.value = value;
                } else {
                    el.textContent = value;
                }
                console.log(`Set #${id} = ${value}`);
            } else {
                console.warn(`#${id} not found`);
            }
        });

        // Show debug
        const debug = document.getElementById(prefix + 'debugOutput');
        if (debug) debug.classList.remove('d-none');

        // Update modal title
        const modalTitle = document.querySelector(`#${event.target.id}Label`);
        if (modalTitle) {
            modalTitle.textContent = 
                event.target.id === 'entityAccessoriesModal'
                    ? `Add Accessories: ${entity}`
                    : `Tool State: ${entity}`;
        }
    }

    const toolStateModal = document.getElementById('toolStateModal');
    const accessoriesModal = document.getElementById('entityAccessoriesModal');

    console.log('Modals found:', {
        toolStateModal: !!toolStateModal,
        accessoriesModal: !!accessoriesModal
    });

    if (toolStateModal) {
        toolStateModal.addEventListener('show.bs.modal', populateModal);
    } else {
        console.error('#toolStateModal not found');
    }

    if (accessoriesModal) {
        accessoriesModal.addEventListener('show.bs.modal', populateModal);
    } else {
        console.error('#entityAccessoriesModal not found — CHECK INCLUDE');
    }

    window.handleStopCauseChange = function (value) {
        const container = document.getElementById('customInputContainer');
        if (container) {
            container.style.display = value === 'CUSTOM' ? 'block' : 'none';
        }
    };
});
</script>
<script>
// Auto-dismiss the alert after 5 seconds (5000 ms)
document.addEventListener('DOMContentLoaded', function () {
    const alert = document.getElementById('autoDismissAlert');
    if (alert) {
        setTimeout(() => {
            bootstrap.Alert.getOrCreateInstance(alert).close();
        }, 5000); // Adjust time as needed (in milliseconds)
    }
});
</script>