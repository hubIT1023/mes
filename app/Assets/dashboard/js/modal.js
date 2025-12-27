export function setupModalHandlers() {
    document.querySelectorAll('.open-second-modal').forEach(btn => {
        btn.addEventListener('click', function () {
            const header = this.getAttribute('data-header') || 'Entity Details';
            document.querySelector('#secondModal .modal-title').textContent = header;
        });
    });

    window.delete_groupEntity = function (groupCode) {
        document.getElementById('group_code').value = groupCode;
        document.getElementById('entity').value = '';
        document.getElementById('location_code').value = '';

        const modal = new bootstrap.Modal(document.getElementById('deleteEntityModal'));
        modal.show();
    };

    document.getElementById('deleteEntityForm')?.addEventListener('submit', async function (e) {
        e.preventDefault();
        const data = Object.fromEntries(new FormData(e.target).entries());

        try {
            const res = await fetch('../handler/delete_groupEntity_handler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const result = await res.json();

            if (res.ok && result.success) {
                alert('? Entity deleted successfully.');
                bootstrap.Modal.getInstance(document.getElementById('deleteEntityModal')).hide();
                location.reload();
            } else {
                alert(result.message || result.error || '?? Could not delete entity.');
            }
        } catch (err) {
            alert('? Error occurred while deleting the entity.');
        }
    });
}
