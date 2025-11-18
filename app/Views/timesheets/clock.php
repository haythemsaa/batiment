<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="page-header">
    <div>
        <h1>⏰ Pointage</h1>
        <p>Pointez votre arrivée et départ avec géolocalisation</p>
    </div>
    <div>
        <a href="/timesheets" class="btn btn-secondary">← Retour aux feuilles de temps</a>
    </div>
</div>

<div class="clock-container">
    <div class="current-time">
        <div id="currentTime" class="time-display"></div>
        <div id="currentDate" class="date-display"></div>
    </div>

    <?php if ($activeTimesheet): ?>
        <!-- Pointage actif - Afficher clock out -->
        <div class="card clock-card active-clock">
            <div class="card-body text-center">
                <div class="clock-status">
                    <div class="status-badge status-active">
                        <span class="pulse"></span>
                        EN SERVICE
                    </div>
                </div>

                <div class="clock-info">
                    <h3>Vous êtes pointé depuis</h3>
                    <div class="clock-duration" id="duration">
                        <?php
                            $start = strtotime($activeTimesheet['date'] . ' ' . $activeTimesheet['clock_in']);
                            $elapsed = time() - $start;
                            $hours = floor($elapsed / 3600);
                            $minutes = floor(($elapsed % 3600) / 60);
                            echo sprintf('%02d:%02d', $hours, $minutes);
                        ?>
                    </div>

                    <div class="clock-details">
                        <p>
                            <strong>Arrivée:</strong> <?= date('H:i', strtotime($activeTimesheet['clock_in'])) ?>
                        </p>
                        <?php if ($activeTimesheet['chantier_id']): ?>
                            <p>
                                <strong>Chantier:</strong>
                                <?= htmlspecialchars($activeTimesheet['chantier_name'] ?? 'Chantier #' . $activeTimesheet['chantier_id']) ?>
                            </p>
                        <?php endif; ?>
                        <?php if ($activeTimesheet['latitude_in']): ?>
                            <p>
                                <strong>GPS arrivée:</strong> 📍 Enregistré
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <form method="POST" id="clockOutForm">
                    <input type="hidden" name="action" value="clock_out">
                    <input type="hidden" name="timesheet_id" value="<?= $activeTimesheet['id'] ?>">
                    <input type="hidden" name="latitude" id="latitudeOut">
                    <input type="hidden" name="longitude" id="longitudeOut">

                    <div class="form-group">
                        <label>Temps de pause (minutes)</label>
                        <input type="number" name="break_minutes" class="form-control form-control-lg text-center" value="0" min="0" max="480">
                    </div>

                    <div class="form-group">
                        <label>Notes de fin de journée</label>
                        <textarea name="notes_out" class="form-control" rows="3" placeholder="Travaux réalisés aujourd'hui..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-danger btn-lg btn-block">
                        ⏱️ POINTER LA SORTIE
                    </button>
                </form>
            </div>
        </div>
    <?php else: ?>
        <!-- Pas de pointage actif - Afficher clock in -->
        <div class="card clock-card">
            <div class="card-body text-center">
                <div class="clock-status">
                    <div class="status-badge status-inactive">
                        PAS EN SERVICE
                    </div>
                </div>

                <div class="clock-info">
                    <h3>Prêt à pointer votre arrivée?</h3>
                    <p class="text-muted">Votre position GPS sera enregistrée automatiquement</p>
                </div>

                <form method="POST" id="clockInForm">
                    <input type="hidden" name="action" value="clock_in">
                    <input type="hidden" name="latitude" id="latitudeIn">
                    <input type="hidden" name="longitude" id="longitudeIn">

                    <div class="form-group">
                        <label>Chantier (optionnel)</label>
                        <select name="chantier_id" class="form-control form-control-lg">
                            <option value="">Aucun chantier spécifique</option>
                            <?php foreach ($chantiers as $chantier): ?>
                                <option value="<?= $chantier['id'] ?>">
                                    <?= htmlspecialchars($chantier['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Notes (optionnel)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Tâches prévues aujourd'hui..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-success btn-lg btn-block">
                        ⏰ POINTER L'ARRIVÉE
                    </button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <div class="gps-status" id="gpsStatus">
        <span class="spinner"></span> Récupération de votre position GPS...
    </div>
</div>

<style>
.clock-container {
    max-width: 600px;
    margin: 0 auto;
}

.current-time {
    text-align: center;
    margin-bottom: 2rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
}

.time-display {
    font-size: 4rem;
    font-weight: 700;
    font-family: 'Courier New', monospace;
    letter-spacing: 2px;
}

.date-display {
    font-size: 1.25rem;
    margin-top: 0.5rem;
    opacity: 0.9;
}

.clock-card {
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.clock-card.active-clock {
    border: 3px solid #10b981;
}

.status-badge {
    display: inline-block;
    padding: 12px 24px;
    border-radius: 24px;
    font-weight: 700;
    font-size: 1.125rem;
    margin-bottom: 2rem;
    position: relative;
}

.status-active {
    background: #10b981;
    color: white;
}

.status-inactive {
    background: #6b7280;
    color: white;
}

.pulse {
    position: absolute;
    width: 12px;
    height: 12px;
    background: white;
    border-radius: 50%;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; transform: translateY(-50%) scale(1); }
    50% { opacity: 0.5; transform: translateY(-50%) scale(1.2); }
}

.clock-duration {
    font-size: 3.5rem;
    font-weight: 700;
    font-family: 'Courier New', monospace;
    color: #10b981;
    margin: 1rem 0;
}

.clock-details {
    background: #f3f4f6;
    padding: 1rem;
    border-radius: 8px;
    margin: 1.5rem 0;
}

.clock-details p {
    margin: 0.5rem 0;
}

.gps-status {
    text-align: center;
    margin-top: 1rem;
    padding: 0.75rem;
    background: #fef3c7;
    border-radius: 8px;
    font-size: 0.875rem;
    color: #92400e;
}

.gps-status.success {
    background: #d1fae5;
    color: #065f46;
}

.gps-status.error {
    background: #fee2e2;
    color: #991b1b;
}

.spinner {
    display: inline-block;
    width: 12px;
    height: 12px;
    border: 2px solid #f59e0b;
    border-top-color: transparent;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-right: 8px;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>

<script>
// Affichage de l'heure en temps réel
function updateTime() {
    const now = new Date();
    const timeStr = now.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    const dateStr = now.toLocaleDateString('fr-FR', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });

    document.getElementById('currentTime').textContent = timeStr;
    document.getElementById('currentDate').textContent = dateStr;
}

updateTime();
setInterval(updateTime, 1000);

// Mise à jour du compteur de durée
<?php if ($activeTimesheet): ?>
    function updateDuration() {
        const start = new Date('<?= $activeTimesheet['date'] ?>T<?= $activeTimesheet['clock_in'] ?>');
        const now = new Date();
        const elapsed = Math.floor((now - start) / 1000);

        const hours = Math.floor(elapsed / 3600);
        const minutes = Math.floor((elapsed % 3600) / 60);

        document.getElementById('duration').textContent =
            String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0');
    }

    updateDuration();
    setInterval(updateDuration, 60000); // Mise à jour chaque minute
<?php endif; ?>

// Géolocalisation
const gpsStatus = document.getElementById('gpsStatus');

if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
        (position) => {
            document.getElementById('latitudeIn')?.setAttribute('value', position.coords.latitude);
            document.getElementById('longitudeIn')?.setAttribute('value', position.coords.longitude);
            document.getElementById('latitudeOut')?.setAttribute('value', position.coords.latitude);
            document.getElementById('longitudeOut')?.setAttribute('value', position.coords.longitude);

            gpsStatus.className = 'gps-status success';
            gpsStatus.innerHTML = '✓ Position GPS enregistrée';
        },
        (error) => {
            gpsStatus.className = 'gps-status error';
            gpsStatus.innerHTML = '⚠️ GPS non disponible - Le pointage sera enregistré sans position';
            console.error('Erreur GPS:', error);
        }
    );
} else {
    gpsStatus.className = 'gps-status error';
    gpsStatus.innerHTML = '⚠️ Géolocalisation non supportée par ce navigateur';
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
