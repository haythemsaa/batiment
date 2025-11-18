<div class="page-header">
    <h1>
        <i class="fas fa-chart-gantt"></i>
        Planning Gantt
    </h1>
    <div class="header-actions">
        <a href="/planning/calendar" class="btn btn-secondary">
            <i class="fas fa-calendar-alt"></i> Calendrier
        </a>
        <a href="/planning/timeline" class="btn btn-secondary">
            <i class="fas fa-stream"></i> Timeline
        </a>
        <a href="/planning/resources" class="btn btn-secondary">
            <i class="fas fa-users"></i> Ressources
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($chantiers)): ?>
        <div class="empty-state">
            <i class="fas fa-chart-gantt fa-3x"></i>
            <h3>Aucun chantier à planifier</h3>
            <p>Ajoutez des chantiers pour visualiser le planning</p>
            <a href="/chantiers/create" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Créer un chantier
            </a>
        </div>
        <?php else: ?>
        <div id="gantt-chart"></div>
        <?php endif; ?>
    </div>
</div>

<style>
.gantt-wrapper {
    width: 100%;
}

.gantt-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding: 1rem;
    background: #f8fafc;
    border-radius: 8px;
}

.gantt-view-modes {
    display: flex;
    gap: 0.5rem;
}

.btn-view {
    padding: 0.5rem 1rem;
    border: 1px solid #e2e8f0;
    background: white;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.875rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s;
}

.btn-view:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
}

.btn-view.active {
    background: #2563eb;
    color: white;
    border-color: #2563eb;
}

.gantt-actions {
    display: flex;
    gap: 0.5rem;
}

.gantt-container {
    overflow-x: auto;
    overflow-y: auto;
    max-height: 70vh;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: white;
}

#gantt-svg {
    display: block;
}

.gantt-legend {
    display: flex;
    justify-content: center;
    gap: 2rem;
    margin-top: 1rem;
    padding: 1rem;
    background: #f8fafc;
    border-radius: 8px;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
}

.legend-color {
    width: 20px;
    height: 12px;
    border-radius: 2px;
}

/* Tooltip pour les barres */
.gantt-tooltip {
    position: absolute;
    background: #1e293b;
    color: white;
    padding: 0.75rem;
    border-radius: 6px;
    font-size: 0.875rem;
    pointer-events: none;
    z-index: 1000;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

/* Impression */
@media print {
    .gantt-controls,
    .gantt-legend,
    .page-header {
        display: none;
    }

    .gantt-container {
        max-height: none;
        overflow: visible;
        border: none;
    }
}
</style>

<script src="/js/gantt.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if (!empty($chantiers)): ?>
    // Préparer les données pour le Gantt
    const tasks = <?= json_encode(array_map(function($c) {
        return [
            'id' => $c['id'],
            'name' => $c['name'],
            'start_date' => $c['start_date'],
            'end_date' => $c['end_date'],
            'progress' => $c['progress'],
            'status' => $c['status'],
            'client_name' => $c['client_name'] ?? '',
            'estimated_budget' => $c['estimated_budget'],
            'dependencies' => []
        ];
    }, $chantiers)) ?>;

    // Initialiser le Gantt
    const gantt = new GanttPlanner('gantt-chart', {
        viewMode: 'Month',
        onTaskClick: function(taskId) {
            window.location.href = '/chantiers/view/' + taskId;
        }
    });

    gantt.loadTasks(tasks);

    // Actualiser au redimensionnement de la fenêtre
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            gantt.render();
        }, 250);
    });
    <?php endif; ?>
});
</script>
