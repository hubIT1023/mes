export function setupEntityHandlers() {
    const entityForm = document.getElementById('entityForm');
    const entityModal = new bootstrap.Modal(document.getElementById('entityModal'));

    window.openEntityModal = function (groupCode) {
        document.getElementById('group_code').value = groupCode;
        entityForm.reset();
        entityModal.show();
    };

    entityForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const data = Object.fromEntries(new FormData(entityForm).entries());

        try {
            const res = await fetch('../handler/add_groupEntity_handler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const result = await res.json();

            if (res.ok && result.success) {
                alert('? Entity added successfully.');
                entityModal.hide();
                location.reload();
            } else {
                alert(result.message || result.error || '?? Something went wrong.');
            }
        } catch (err) {
            alert('? Unexpected error.');
        }
    });
}
