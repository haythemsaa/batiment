<?php
// Calculer les jours du mois
$firstDayOfMonth = mktime(0, 0, 0, $month, 1, $year);
$daysInMonth = date('t', $firstDayOfMonth);
$dayOfWeek = date('w', $firstDayOfMonth);
$dayOfWeek = ($dayOfWeek == 0) ? 7 : $dayOfWeek; // Lundi = 1

$prevMonth = $month - 1;
$prevYear = $year;
if ($prevMonth < 1) {
    $prevMonth = 12;
    $prevYear--;
}

$nextMonth = $month + 1;
$nextYear = $year;
if ($nextMonth > 12) {
    $nextMonth = 1;
    $nextYear++;
}

$monthName = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
?>

<div class="page-header">
    <h1>
        <i class="fas fa-calendar-alt"></i>
        Calendrier - <?= $monthName[(int)$month] ?> <?= $year ?>
    </h1>
    <div class="header-actions">
        <a href="/planning/gantt" class="btn btn-secondary">
            <i class="fas fa-chart-gantt"></i> Gantt
        </a>
        <a href="/planning/timeline" class="btn btn-secondary">
            <i class="fas fa-stream"></i> Timeline
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="calendar-navigation">
            <a href="/planning/calendar?month=<?= str_pad($prevMonth, 2, '0', STR_PAD_LEFT) ?>&year=<?= $prevYear ?>" class="btn btn-sm btn-secondary">
                <i class="fas fa-chevron-left"></i> Précédent
            </a>
            <h2><?= $monthName[(int)$month] ?> <?= $year ?></h2>
            <a href="/planning/calendar?month=<?= str_pad($nextMonth, 2, '0', STR_PAD_LEFT) ?>&year=<?= $nextYear ?>" class="btn btn-sm btn-secondary">
                Suivant <i class="fas fa-chevron-right"></i>
            </a>
        </div>
        <a href="/planning/calendar?month=<?= date('m') ?>&year=<?= date('Y') ?>" class="btn btn-sm btn-primary">
            <i class="fas fa-calendar-day"></i> Aujourd'hui
        </a>
    </div>

    <div class="card-body p-0">
        <div class="calendar-grid">
            <!-- En-têtes des jours -->
            <div class="calendar-header">
                <div class="calendar-day-name">Lundi</div>
                <div class="calendar-day-name">Mardi</div>
                <div class="calendar-day-name">Mercredi</div>
                <div class="calendar-day-name">Jeudi</div>
                <div class="calendar-day-name">Vendredi</div>
                <div class="calendar-day-name weekend">Samedi</div>
                <div class="calendar-day-name weekend">Dimanche</div>
            </div>

            <!-- Cellules du calendrier -->
            <div class="calendar-body">
                <?php
                // Jours vides au début
                for ($i = 1; $i < $dayOfWeek; $i++) {
                    echo '<div class="calendar-cell empty"></div>';
                }

                // Jours du mois
                for ($day = 1; $day <= $daysInMonth; $day++) {
                    $currentDate = sprintf('%04d-%02d-%02d', $year, $month, $day);
                    $isToday = ($currentDate == date('Y-m-d'));
                    $isWeekend = (date('N', strtotime($currentDate)) >= 6);

                    // Trouver les chantiers de ce jour
                    $dayChantiers = array_filter($chantiers, function($c) use ($currentDate) {
                        return $currentDate >= $c['start_date'] && $currentDate <= $c['end_date'];
                    });

                    $cellClass = 'calendar-cell';
                    if ($isToday) $cellClass .= ' today';
                    if ($isWeekend) $cellClass .= ' weekend';

                    echo "<div class='$cellClass'>";
                    echo "<div class='calendar-day-number'>$day</div>";

                    if (!empty($dayChantiers)) {
                        echo "<div class='calendar-events'>";
                        foreach (array_slice($dayChantiers, 0, 3) as $chantier) {
                            $statusColors = [
                                'planifie' => '#2563eb',
                                'en_cours' => '#10b981',
                                'termine' => '#64748b',
                                'suspendu' => '#f59e0b',
                                'annule' => '#ef4444'
                            ];
                            $color = $statusColors[$chantier['status']] ?? '#64748b';

                            echo "<a href='/chantiers/view/{$chantier['id']}' class='calendar-event' style='border-left-color: $color;' title='" . htmlspecialchars($chantier['name']) . "'>";
                            echo "<span class='event-name'>" . htmlspecialchars(mb_substr($chantier['name'], 0, 20)) . "</span>";
                            echo "</a>";
                        }

                        if (count($dayChantiers) > 3) {
                            echo "<div class='calendar-event-more'>+" . (count($dayChantiers) - 3) . " autres</div>";
                        }
                        echo "</div>";
                    }

                    echo "</div>";
                }

                // Compléter la dernière semaine
                $totalCells = $dayOfWeek - 1 + $daysInMonth;
                $remainingCells = (7 - ($totalCells % 7)) % 7;
                for ($i = 0; $i < $remainingCells; $i++) {
                    echo '<div class="calendar-cell empty"></div>';
                }
                ?>
            </div>
        </div>
    </div>
</div>

<!-- Légende -->
<div class="card mt-3">
    <div class="card-body">
        <div class="calendar-legend">
            <div class="legend-item">
                <span class="legend-dot" style="background: #2563eb;"></span>
                <span>Planifié</span>
            </div>
            <div class="legend-item">
                <span class="legend-dot" style="background: #10b981;"></span>
                <span>En cours</span>
            </div>
            <div class="legend-item">
                <span class="legend-dot" style="background: #64748b;"></span>
                <span>Terminé</span>
            </div>
            <div class="legend-item">
                <span class="legend-dot" style="background: #f59e0b;"></span>
                <span>Suspendu</span>
            </div>
            <div class="legend-item">
                <span class="legend-dot" style="background: #ef4444;"></span>
                <span>Annulé</span>
            </div>
        </div>
    </div>
</div>

<style>
.calendar-navigation {
    display: flex;
    align-items: center;
    gap: 2rem;
}

.calendar-navigation h2 {
    margin: 0;
    font-size: 1.5rem;
    color: #1e293b;
}

.calendar-grid {
    width: 100%;
}

.calendar-header {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    background: #f8fafc;
    border-bottom: 2px solid #e2e8f0;
}

.calendar-day-name {
    padding: 1rem;
    text-align: center;
    font-weight: 600;
    color: #475569;
    text-transform: uppercase;
    font-size: 0.875rem;
}

.calendar-day-name.weekend {
    color: #94a3b8;
}

.calendar-body {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 1px;
    background: #e2e8f0;
}

.calendar-cell {
    background: white;
    min-height: 120px;
    padding: 0.5rem;
    position: relative;
}

.calendar-cell.empty {
    background: #f8fafc;
}

.calendar-cell.today {
    background: #eff6ff;
    border: 2px solid #2563eb;
}

.calendar-cell.weekend {
    background: #fafafa;
}

.calendar-day-number {
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 0.5rem;
}

.calendar-cell.today .calendar-day-number {
    color: #2563eb;
    font-weight: 700;
}

.calendar-events {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.calendar-event {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    background: #f8fafc;
    border-left: 3px solid;
    border-radius: 3px;
    text-decoration: none;
    color: #1e293b;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    transition: all 0.2s;
}

.calendar-event:hover {
    background: #e2e8f0;
    transform: translateX(2px);
}

.calendar-event-more {
    font-size: 0.75rem;
    color: #64748b;
    padding: 0.25rem;
    text-align: center;
}

.calendar-legend {
    display: flex;
    justify-content: center;
    gap: 2rem;
    flex-wrap: wrap;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.legend-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
}

@media (max-width: 768px) {
    .calendar-cell {
        min-height: 80px;
        padding: 0.25rem;
    }

    .calendar-day-name {
        padding: 0.5rem;
        font-size: 0.75rem;
    }

    .calendar-event {
        font-size: 0.65rem;
        padding: 0.125rem 0.25rem;
    }
}
</style>
