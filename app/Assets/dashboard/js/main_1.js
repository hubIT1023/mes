document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".entity-list").forEach(list => {
    new Sortable(list, {
      group: 'shared',
      animation: 150,
      onEnd: function (evt) {
        const entityId = evt.item.dataset.id;
        const newGroupId = evt.to.closest(".group").dataset.groupId;

        fetch('edit_entity.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: entityId, group_id: newGroupId })
        });
      }
    });
  });
});

function createGroup() {
  const entity = prompt("Group name:");
  if (!entity?.trim()) return;

  fetch('groups.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ entity })
  }).then(() => location.reload());
}

function editGroup(id, oldName) {
  const entity = prompt("Edit group name:", oldName);
  if (!entity?.trim()) return;

  fetch('groups.php', {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id, entity }) 
  }).then(() => location.reload());
}

function deleteGroup(id) {
  if (!confirm("Delete this group and all its entities?")) return;

  fetch(`groups.php?id=${id}`, {
    method: 'DELETE'
  }).then(() => location.reload());
}

function addEntity(group_id) {
  const entity = prompt("Entity name:");
  if (!entity?.trim()) return;

  fetch('add_entity.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ entity, group_id })
  }).then(() => location.reload());
}

function editEntity(id, oldName) {
  const entity = prompt("Edit entity name:", oldName);
  if (!entity?.trim()) return;

  fetch('edit_entity.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id, entity })
  }).then(() => location.reload());
}

function deleteEntity(id) {
  if (!confirm("Delete this entity?")) return;

  fetch(`delete_entity.php?id=${id}`, {
    method: 'GET' // Or just remove the method line
  }).then(() => location.reload());
}
