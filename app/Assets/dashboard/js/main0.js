document.addEventListener('DOMContentLoaded', function () {

    // ? Create Group
    window.createGroup = function () {
        const locationName = prompt("Enter new location name (e.g., BAY_1, Production):");
        if (!locationName?.trim()) return alert("Location name is required.");

        const groupName = prompt("Enter new group name (e.g., SPUTTER, SORTER):");
        if (!groupName?.trim()) return alert("Group name is required.");

        fetch('../handler/create_groups.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                group_name: groupName.trim(),
                location_name: locationName.trim()
            })
        })
            .then(res => res.json())
            .then(data => {
                if (data.error) alert("Error: " + data.error);
                else location.reload();
            })
            .catch(() => alert('? Request failed'));
    };

    // ? Open Entity Modal
    const entityForm = document.getElementById('entityForm');
    const entityModal = new bootstrap.Modal(document.getElementById('entityModal'));

    window.openEntityModal = function (groupCode) {
        document.getElementById('group_code').value = groupCode;
        entityForm.reset();
        entityModal.show();
    };

    // ? Add Entity
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
            console.error(err);
            alert('? Unexpected error.');
        }
    });

    // ? Delete Entity
    window.delete_groupEntity = function (groupCode) {
        document.getElementById('group_code').value = groupCode;
        document.getElementById('entity').value = '';
        document.getElementById('location_code').value = '';

        const modal = new bootstrap.Modal(document.getElementById('deleteEntityModal'));
        modal.show();
    };

    document.getElementById('deleteEntityForm')?.addEventListener('submit', async (e) => {
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
            console.error(err);
            alert('? Error occurred while deleting the entity.');
        }
    });

    // ? Entity state modal (demo modal)
    const demoForm = document.getElementById('demoForm');
    const demoModal = new bootstrap.Modal(document.getElementById('demoModal'));

    document.querySelectorAll('.demo-header').forEach(header => {
        header.addEventListener('click', () => {
            const entityName = header.getAttribute('data-entity');
            document.getElementById('demoEntityName').textContent = entityName;
            document.getElementById('demoEntityInput').value = entityName;
            demoForm.reset();
            demoModal.show();
        });
    });

    demoForm?.addEventListener('submit', async function (e) {
        e.preventDefault();
        const data = Object.fromEntries(new FormData(demoForm).entries());

        try {
            const response = await fetch('./handler/submit_demo_form.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const result = await response.json();
            if (result.success) {
                alert("? Demo submitted successfully!");
                demoModal.hide();
            } else {
                alert(result.error || "? Failed to submit demo.");
            }
        } catch (err) {
            alert("? Submission error.");
        }
    });

    // ? Second Modal Header Setup
    document.querySelectorAll('.open-second-modal').forEach(btn => {
        btn.addEventListener('click', function () {
            const header = this.getAttribute('data-header') || 'Entity Details';
            document.querySelector('#secondModal .modal-title').textContent = header;
        });
    });

});
