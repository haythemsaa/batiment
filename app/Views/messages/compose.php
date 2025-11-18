<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="page-header">
    <div>
        <h1>✉️ Nouveau Message</h1>
        <p>Envoyer un message à un membre de l'équipe</p>
    </div>
    <div>
        <a href="/messages" class="btn btn-secondary">← Retour aux messages</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data" id="messageForm">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Destinataire *</label>
                        <select name="recipient_id" class="form-control" required>
                            <option value="">Sélectionner un destinataire</option>
                            <?php foreach ($users as $user): ?>
                                <?php if ($user['id'] != $_SESSION['user']['id']): ?>
                                    <option value="<?= $user['id'] ?>" <?= $recipientId == $user['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($user['name']) ?> - <?= htmlspecialchars($user['role']) ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label>Chantier (optionnel)</label>
                        <select name="chantier_id" class="form-control">
                            <option value="">Aucun chantier</option>
                            <?php foreach ($chantiers as $chantier): ?>
                                <option value="<?= $chantier['id'] ?>" <?= $chantierId == $chantier['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($chantier['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Sujet</label>
                <input type="text" name="subject" class="form-control" placeholder="Sujet du message">
            </div>

            <div class="form-group">
                <label>Message *</label>
                <textarea name="message" class="form-control" rows="10" required placeholder="Votre message..."></textarea>
            </div>

            <div class="form-group">
                <label>Pièces jointes</label>
                <input type="file" name="attachments[]" class="form-control" multiple>
                <small class="text-muted">Formats acceptés: PDF, images, documents Office (max 10MB chacun)</small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="icon">📤</i> Envoyer le message
                </button>
                <a href="/messages" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>

<style>
textarea.form-control {
    font-family: inherit;
    resize: vertical;
}

.form-actions {
    margin-top: 2rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
