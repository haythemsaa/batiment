<div class="page-header">
    <div>
        <h1>
            <i class="fas fa-tasks"></i>
            Tâches - <?= htmlspecialchars($chantier['name']) ?>
        </h1>
        <nav class="breadcrumb">
            <a href="/chantiers">Chantiers</a>
            <span>/</span>
            <a href="/chantiers/view/<?= $chantier['id'] ?>"><?= htmlspecialchars($chantier['name']) ?></a>
            <span>/</span>
            <span>Tâches</span>
        </nav>
    </div>
    <a href="/tasks/create?chantier_id=<?= $chantier['id'] ?>" class="btn btn-primary">
        <i class="fas fa-plus"></i>
        Nouvelle tâche
    </a>
</div>

<!-- Statistiques -->
<div class="stats-grid mb-4">
    <div class="stat-card">
        <div class="stat-icon" style="background: #dbeafe;">
            <i class="fas fa-list-ul" style="color: #2563eb;"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= $stats['total'] ?></div>
            <div class="stat-label">Total tâches</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: #fef3c7;">
            <i class="fas fa-clock" style="color: #f59e0b;"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= $stats['todo'] ?></div>
            <div class="stat-label">À faire</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: #ddd6fe;">
            <i class="fas fa-spinner" style="color: #8b5cf6;"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= $stats['in_progress'] ?></div>
            <div class="stat-label">En cours</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: #d1fae5;">
            <i class="fas fa-check-circle" style="color: #10b981;"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= $stats['completed'] ?></div>
            <div class="stat-label">Terminées</div>
        </div>
    </div>

    <?php if ($stats['overdue'] > 0): ?>
    <div class="stat-card">
        <div class="stat-icon" style="background: #fee2e2;">
            <i class="fas fa-exclamation-triangle" style="color: #ef4444;"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= $stats['overdue'] ?></div>
            <div class="stat-label">En retard</div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Tableau Kanban -->
<div class="kanban-board">
    <!-- Colonne À faire -->
    <div class="kanban-column">
        <div class="kanban-header" style="background: #fef3c7;">
            <h3><i class="fas fa-circle" style="color: #f59e0b;"></i> À faire (<?= $stats['todo'] ?>)</h3>
        </div>
        <div class="kanban-tasks" data-status="todo">
            <?php
            $todoTasks = array_filter($tasks, fn($t) => $t['status'] === 'todo');
            foreach ($todoTasks as $task):
            ?>
            <?php include '_task_card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Colonne En cours -->
    <div class="kanban-column">
        <div class="kanban-header" style="background: #ddd6fe;">
            <h3><i class="fas fa-circle" style="color: #8b5cf6;"></i> En cours (<?= $stats['in_progress'] ?>)</h3>
        </div>
        <div class="kanban-tasks" data-status="in_progress">
            <?php
            $inProgressTasks = array_filter($tasks, fn($t) => $t['status'] === 'in_progress');
            foreach ($inProgressTasks as $task):
            ?>
            <?php include '_task_card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Colonne Terminées -->
    <div class="kanban-column">
        <div class="kanban-header" style="background: #d1fae5;">
            <h3><i class="fas fa-circle" style="color: #10b981;"></i> Terminées (<?= $stats['completed'] ?>)</h3>
        </div>
        <div class="kanban-tasks" data-status="completed">
            <?php
            $completedTasks = array_filter($tasks, fn($t) => $t['status'] === 'completed');
            foreach ($completedTasks as $task):
            ?>
            <?php include '_task_card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<style>
.kanban-board {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-top: 2rem;
}

.kanban-column {
    background: #f8fafc;
    border-radius: 8px;
    overflow: hidden;
}

.kanban-header {
    padding: 1rem;
    border-bottom: 2px solid rgba(0, 0, 0, 0.1);
}

.kanban-header h3 {
    margin: 0;
    font-size: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.kanban-tasks {
    padding: 1rem;
    min-height: 200px;
    max-height: 600px;
    overflow-y: auto;
}

.task-card {
    background: white;
    border-radius: 6px;
    padding: 1rem;
    margin-bottom: 0.75rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    cursor: pointer;
    transition: all 0.2s;
    border-left: 3px solid;
}

.task-card:hover {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.15);
    transform: translateY(-2px);
}

.task-card.priority-low {
    border-left-color: #64748b;
}

.task-card.priority-medium {
    border-left-color: #2563eb;
}

.task-card.priority-high {
    border-left-color: #f59e0b;
}

.task-card.priority-urgent {
    border-left-color: #ef4444;
}

.task-card.overdue {
    background: #fef2f2;
}

.task-title {
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #1e293b;
}

.task-description {
    font-size: 0.875rem;
    color: #64748b;
    margin-bottom: 0.75rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.task-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    font-size: 0.75rem;
}

.task-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 0.75rem;
    padding-top: 0.75rem;
    border-top: 1px solid #e2e8f0;
}

.task-actions button {
    flex: 1;
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
}
</style>

<script>
// Drag & Drop pour le tableau Kanban
document.addEventListener('DOMContentLoaded', function() {
    const taskCards = document.querySelectorAll('.task-card');
    const columns = document.querySelectorAll('.kanban-tasks');

    taskCards.forEach(card => {
        card.draggable = true;

        card.addEventListener('dragstart', function(e) {
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/html', this.innerHTML);
            this.classList.add('dragging');
        });

        card.addEventListener('dragend', function() {
            this.classList.remove('dragging');
        });
    });

    columns.forEach(column => {
        column.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
        });

        column.addEventListener('drop', function(e) {
            e.preventDefault();
            const dragging = document.querySelector('.dragging');
            if (dragging) {
                const newStatus = this.dataset.status;
                const taskId = dragging.dataset.taskId;

                // Mettre à jour le statut via AJAX
                updateTaskStatus(taskId, newStatus);

                this.appendChild(dragging);
            }
        });
    });
});

function updateTaskStatus(taskId, status) {
    fetch(`/tasks/update-status/${taskId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `status=${status}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}
</script>
