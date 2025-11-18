<div class="task-card priority-<?= $task['priority'] ?> <?= ($task['due_date'] && $task['due_date'] < date('Y-m-d') && $task['status'] !== 'completed') ? 'overdue' : '' ?>"
     data-task-id="<?= $task['id'] ?>">

    <div class="task-title">
        <?= htmlspecialchars($task['title']) ?>

        <?php if ($task['due_date'] && $task['due_date'] < date('Y-m-d') && $task['status'] !== 'completed'): ?>
            <i class="fas fa-exclamation-circle text-danger" title="En retard"></i>
        <?php endif; ?>
    </div>

    <?php if ($task['description']): ?>
    <div class="task-description">
        <?= htmlspecialchars($task['description']) ?>
    </div>
    <?php endif; ?>

    <div class="task-meta">
        <!-- Priorité -->
        <span class="badge <?= $task['priority'] === 'urgent' ? 'badge-danger' : ($task['priority'] === 'high' ? 'badge-warning' : ($task['priority'] === 'medium' ? 'badge-primary' : 'badge-secondary')) ?>">
            <?= ucfirst($task['priority']) ?>
        </span>

        <!-- Date d'échéance -->
        <?php if ($task['due_date']): ?>
        <span class="badge badge-light">
            <i class="fas fa-calendar"></i>
            <?= date('d/m/Y', strtotime($task['due_date'])) ?>
        </span>
        <?php endif; ?>

        <!-- Assigné à -->
        <?php if ($task['assigned_to_name']): ?>
        <span class="badge badge-light">
            <i class="fas fa-user"></i>
            <?= htmlspecialchars($task['assigned_to_name']) ?>
        </span>
        <?php endif; ?>
    </div>

    <div class="task-actions">
        <a href="/tasks/edit/<?= $task['id'] ?>" class="btn btn-sm btn-secondary">
            <i class="fas fa-edit"></i>
        </a>

        <?php if ($task['status'] !== 'completed'): ?>
        <button class="btn btn-sm btn-success" onclick="completeTask(<?= $task['id'] ?>)">
            <i class="fas fa-check"></i> Terminer
        </button>
        <?php endif; ?>
    </div>
</div>

<script>
function completeTask(taskId) {
    if (confirm('Marquer cette tâche comme terminée ?')) {
        fetch(`/tasks/complete/${taskId}`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        })
        .catch(error => console.error('Error:', error));
    }
}
</script>
