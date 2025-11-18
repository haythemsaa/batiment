<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="page-header">
    <div>
        <h1>📄 Documents</h1>
        <p>Gestion documentaire avec versioning</p>
    </div>
    <div>
        <a href="/documents/upload" class="btn btn-primary">
            <i class="icon">📤</i> Upload Document
        </a>
    </div>
</div>

<!-- Filtres -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="filters-form">
            <div class="row">
                <div class="col-md-4">
                    <label>Chantier</label>
                    <select name="chantier_id" class="form-control" onchange="this.form.submit()">
                        <option value="">Tous les chantiers</option>
                        <?php foreach ($chantiers as $chantier): ?>
                            <option value="<?= $chantier['id'] ?>" <?= $currentChantierId == $chantier['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($chantier['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Catégorie</label>
                    <select name="category" class="form-control" onchange="this.form.submit()">
                        <option value="">Toutes</option>
                        <option value="plan" <?= $currentCategory === 'plan' ? 'selected' : '' ?>>Plans</option>
                        <option value="permis" <?= $currentCategory === 'permis' ? 'selected' : '' ?>>Permis</option>
                        <option value="facture" <?= $currentCategory === 'facture' ? 'selected' : '' ?>>Factures</option>
                        <option value="contrat" <?= $currentCategory === 'contrat' ? 'selected' : '' ?>>Contrats</option>
                        <option value="rapport" <?= $currentCategory === 'rapport' ? 'selected' : '' ?>>Rapports</option>
                        <option value="photo" <?= $currentCategory === 'photo' ? 'selected' : '' ?>>Photos</option>
                        <option value="autre" <?= $currentCategory === 'autre' ? 'selected' : '' ?>>Autre</option>
                    </select>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Liste des documents -->
<div class="card">
    <div class="card-body">
        <?php if (empty($documents)): ?>
            <div class="empty-state">
                <p><strong>Aucun document</strong></p>
                <p>Commencez par uploader vos premiers documents.</p>
                <a href="/documents/upload" class="btn btn-primary mt-2">Upload Document</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Document</th>
                            <th>Catégorie</th>
                            <th>Chantier</th>
                            <th>Version</th>
                            <th>Taille</th>
                            <th>Uploadé par</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documents as $doc): ?>
                            <tr>
                                <td>
                                    <div class="document-name">
                                        <?php
                                            $ext = pathinfo($doc['file_path'], PATHINFO_EXTENSION);
                                            $icons = [
                                                'pdf' => '📕',
                                                'doc' => '📘',
                                                'docx' => '📘',
                                                'xls' => '📗',
                                                'xlsx' => '📗',
                                                'jpg' => '🖼️',
                                                'jpeg' => '🖼️',
                                                'png' => '🖼️',
                                                'zip' => '📦'
                                            ];
                                            echo $icons[strtolower($ext)] ?? '📄';
                                        ?>
                                        <strong><?= htmlspecialchars($doc['name']) ?></strong>
                                        <?php if ($doc['description']): ?>
                                            <br>
                                            <small class="text-muted">
                                                <?= htmlspecialchars(substr($doc['description'], 0, 50)) ?>
                                                <?= strlen($doc['description']) > 50 ? '...' : '' ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                        $categories = [
                                            'plan' => '📐 Plans',
                                            'permis' => '📋 Permis',
                                            'facture' => '💰 Factures',
                                            'contrat' => '📜 Contrats',
                                            'rapport' => '📊 Rapports',
                                            'photo' => '📸 Photos',
                                            'autre' => '📄 Autre'
                                        ];
                                        echo $categories[$doc['category']] ?? $doc['category'];
                                    ?>
                                </td>
                                <td>
                                    <?php if ($doc['chantier_id']): ?>
                                        <a href="/chantiers/view/<?= $doc['chantier_id'] ?>">
                                            <?= htmlspecialchars($doc['chantier_name'] ?? 'Chantier #' . $doc['chantier_id']) ?>
                                        </a>
                                    <?php else: ?>
                                        <em class="text-muted">-</em>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="version-badge">v<?= $doc['version'] ?></span>
                                    <?php if ($doc['is_latest']): ?>
                                        <span class="badge-latest">Dernière</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= formatFileSize($doc['file_size']) ?></td>
                                <td><?= htmlspecialchars($doc['uploaded_by_name'] ?? '-') ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($doc['created_at'])) ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="/documents/view/<?= $doc['id'] ?>"
                                           class="btn btn-sm btn-secondary"
                                           title="Voir">
                                            👁️
                                        </a>
                                        <a href="/documents/download/<?= $doc['id'] ?>"
                                           class="btn btn-sm btn-primary"
                                           title="Télécharger">
                                            ⬇️
                                        </a>
                                        <?php if ($doc['is_latest']): ?>
                                            <a href="/documents/new-version/<?= $doc['id'] ?>"
                                               class="btn btn-sm btn-info"
                                               title="Nouvelle version">
                                                🔄
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.document-name {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.version-badge {
    background: #e0e7ff;
    color: #3730a3;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge-latest {
    background: #10b981;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    margin-left: 0.5rem;
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
