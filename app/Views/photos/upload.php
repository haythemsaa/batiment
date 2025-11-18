<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="page-header">
    <div>
        <h1>📤 Upload Photos</h1>
        <p>Ajoutez vos photos de chantier avec géolocalisation automatique</p>
    </div>
    <div>
        <a href="/photos" class="btn btn-secondary">← Retour à la galerie</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data" id="uploadForm">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Chantier *</label>
                        <select name="chantier_id" class="form-control" required>
                            <option value="">Sélectionner un chantier</option>
                            <?php foreach ($chantiers as $chantier): ?>
                                <option value="<?= $chantier['id'] ?>">
                                    <?= htmlspecialchars($chantier['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Catégorie *</label>
                        <select name="category[]" class="form-control" required>
                            <option value="avant">🏗️ Avant travaux</option>
                            <option value="pendant" selected>🔨 Pendant travaux</option>
                            <option value="apres">✅ Après travaux</option>
                            <option value="defaut">⚠️ Défaut</option>
                            <option value="autre">📷 Autre</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Photos * (formats acceptés: JPG, PNG, HEIC - max 10MB chacune)</label>
                <input type="file"
                       name="photos[]"
                       class="form-control"
                       accept="image/*"
                       multiple
                       required
                       id="photoInput"
                       capture="environment">
                <small class="text-muted">Maintenez Ctrl/Cmd pour sélectionner plusieurs photos</small>
            </div>

            <div id="photoPreview" class="photo-preview-grid mt-3"></div>

            <div id="photoMetadata"></div>

            <div class="form-group">
                <label>
                    <input type="checkbox" id="useGeolocation" checked>
                    Utiliser la géolocalisation GPS (recommandé)
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="icon">📤</i> Uploader les photos
                </button>
                <a href="/photos" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>

<style>
.photo-preview-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
}

.photo-preview-item {
    position: relative;
    background: #f5f5f5;
    border-radius: 8px;
    overflow: hidden;
}

.photo-preview-item img {
    width: 100%;
    aspect-ratio: 4/3;
    object-fit: cover;
}

.photo-preview-item .metadata {
    padding: 8px;
    background: white;
}

.photo-preview-item input {
    margin-bottom: 4px;
}

.remove-photo {
    position: absolute;
    top: 8px;
    right: 8px;
    background: rgba(220, 38, 38, 0.9);
    color: white;
    border: none;
    border-radius: 50%;
    width: 32px;
    height: 32px;
    cursor: pointer;
    font-size: 18px;
    line-height: 1;
}

.remove-photo:hover {
    background: rgba(220, 38, 38, 1);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const photoInput = document.getElementById('photoInput');
    const previewGrid = document.getElementById('photoPreview');
    const metadataDiv = document.getElementById('photoMetadata');
    const useGeolocation = document.getElementById('useGeolocation');

    let position = null;

    // Récupérer la géolocalisation
    if (navigator.geolocation && useGeolocation.checked) {
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                position = pos;
                console.log('Position GPS récupérée:', pos.coords.latitude, pos.coords.longitude);
            },
            (error) => {
                console.warn('Géolocalisation non disponible:', error);
            }
        );
    }

    photoInput.addEventListener('change', function(e) {
        previewGrid.innerHTML = '';
        metadataDiv.innerHTML = '';

        const files = Array.from(e.target.files);

        files.forEach((file, index) => {
            // Preview
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewItem = document.createElement('div');
                previewItem.className = 'photo-preview-item';
                previewItem.innerHTML = `
                    <img src="${e.target.result}" alt="Preview">
                    <button type="button" class="remove-photo" data-index="${index}">×</button>
                    <div class="metadata">
                        <input type="text"
                               name="title[${index}]"
                               class="form-control form-control-sm mb-1"
                               placeholder="Titre">
                        <input type="text"
                               name="zone[${index}]"
                               class="form-control form-control-sm mb-1"
                               placeholder="Zone (ex: Salon)">
                        <input type="text"
                               name="etage[${index}]"
                               class="form-control form-control-sm"
                               placeholder="Étage">
                    </div>
                `;
                previewGrid.appendChild(previewItem);
            };
            reader.readAsDataURL(file);

            // Ajouter les données GPS si disponibles
            if (position && useGeolocation.checked) {
                metadataDiv.innerHTML += `
                    <input type="hidden" name="latitude[${index}]" value="${position.coords.latitude}">
                    <input type="hidden" name="longitude[${index}]" value="${position.coords.longitude}">
                `;
            }
        });
    });

    // Supprimer une photo
    previewGrid.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-photo')) {
            const index = parseInt(e.target.dataset.index);
            e.target.closest('.photo-preview-item').remove();

            // Note: On ne peut pas retirer un fichier du input file
            // Il faudrait reconstruire la liste avec DataTransfer API
        }
    });

    // Validation avant submit
    document.getElementById('uploadForm').addEventListener('submit', function(e) {
        if (photoInput.files.length === 0) {
            e.preventDefault();
            alert('Veuillez sélectionner au moins une photo');
        }
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
