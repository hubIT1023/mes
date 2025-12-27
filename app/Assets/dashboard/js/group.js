export function setupGroupHandlers() {
    window.createGroup = function () {
        const locationName = prompt("Enter new location name (e.g., BAY_1):");
        if (!locationName?.trim()) return alert("Location name is required.");

        const groupName = prompt("Enter new group name (e.g., SPUTTER):");
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
}
