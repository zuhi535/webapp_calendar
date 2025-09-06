<?php
// Üzenetek megjelenítése
session_start();
$successMessage = $_SESSION['success_message'] ?? '';
$errorMessage = $_SESSION['error_message'] ?? '';
// Üzenetek törlése a sessionből
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);
?>

<?php if ($successMessage): ?>
<div class="alert alert-success" id="successAlert">
    <span class="alert-message"><?= htmlspecialchars($successMessage) ?></span>
    <span class="alert-close" onclick="closeAlert('successAlert')">&times;</span>
</div>
<?php endif; ?>

<?php if ($errorMessage): ?>
<div class="alert alert-error" id="errorAlert">
    <span class="alert-message"><?= htmlspecialchars($errorMessage) ?></span>
    <span class="alert-close" onclick="closeAlert('errorAlert')">&times;</span>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <div class="breadcrumb">
            <a href="?page=projects">Projektek</a> &raquo;
            <a href="?page=project_detail&id=<?= $project['id'] ?>" style="color: <?= $project['color'] ?>;">
                <?= htmlspecialchars($project['name']) ?>
            </a> &raquo;
            <span><?= htmlspecialchars($note['title']) ?></span>
        </div>
        <div class="header-actions">
            <button class="btn btn-secondary" onclick="history.back()">Vissza</button>
            <button class="btn btn-primary" onclick="editNote()">Szerkesztés</button>
        </div>
    </div>
    
    <div class="note-detail-content">
        <!-- Jegyzet információ -->
        <div class="note-info">
            <h1><?= htmlspecialchars($note['title']) ?></h1>
            
            <?php if (!empty($note['excerpt'])): ?>
                <div class="note-excerpt">
                    <?= htmlspecialchars($note['excerpt']) ?>
                </div>
            <?php endif; ?>
            
            <div class="note-meta">
                <span>Projekt: <?= htmlspecialchars($note['collection_name']) ?></span>
                <span>Létrehozva: <?= date('Y.m.d.', strtotime($note['created_at'])) ?></span>
                <?php if ($note['updated_at'] !== $note['created_at']): ?>
                    <span>Utoljára módosítva: <?= date('Y.m.d. H:i', strtotime($note['updated_at'])) ?></span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Jegyzet tartalma -->
        <div class="note-content">
            <?php if (!empty($note['content'])): ?>
                <div class="content-box">
                    <?= nl2br(htmlspecialchars($note['content'])) ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <p>Ennek a jegyzetnek még nincs tartalma.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Csatolt fájlok -->
        <?php if (!empty($files)): ?>
            <div class="section">
                <h2>Csatolt fájlok (<?= count($files) ?>)</h2>
                <div class="files-grid">
                    <?php foreach ($files as $file): ?>
                        <div class="file-card">
                            <div class="file-icon">
                                <?php if ($file['is_image']): ?>
                                    <img src="<?= $file['file_path'] ?>" alt="<?= htmlspecialchars($file['original_filename']) ?>" 
                                         onclick="previewFile(<?= $file['id'] ?>)">
                                <?php else: ?>
                                    <div class="file-type-icon">📄</div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="file-info">
                                <div class="file-name" title="<?= htmlspecialchars($file['original_filename']) ?>">
                                    <?= htmlspecialchars($file['original_filename']) ?>
                                </div>
                                
                                <div class="file-meta">
                                    <span><?= round($file['file_size'] / 1024, 1) ?> KB</span>
                                    <span><?= date('Y.m.d.', strtotime($file['created_at'])) ?></span>
                                </div>
                            </div>
                            
                            <div class="file-actions">
                                <a href="?page=note_detail&id=<?= $note['id'] ?>&download_file=<?= $file['id'] ?>" 
                                   class="btn btn-sm" title="Letöltés">⬇️</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Vissza gomb -->
<div class="card">
    <div class="card-footer">
        <a href="?page=project_detail&id=<?= $project['id'] ?>" class="btn btn-secondary">← Vissza a projekthez</a>
    </div>
</div>

<script>
// Alert kezelés
function initAlerts() {
    const successAlert = document.getElementById('successAlert');
    const errorAlert = document.getElementById('errorAlert');
    
    if (successAlert) {
        setTimeout(() => closeAlert('successAlert'), 5000);
    }
    
    if (errorAlert) {
        setTimeout(() => closeAlert('errorAlert'), 5000);
    }
}

function closeAlert(alertId) {
    const alert = document.getElementById(alertId);
    if (alert) {
        alert.classList.add('fade-out');
        setTimeout(() => {
            if (alert.parentNode) {
                alert.parentNode.removeChild(alert);
            }
        }, 300);
    }
}

// Jegyzet szerkesztése
function editNote() {
    // Átirányítás a szerkesztő oldalra (jelenleg még nincs implementálva)
    alert('A jegyzet szerkesztése hamarosan elérhető lesz.');
    // window.location.href = `?page=note_edit&id=<?= $note['id'] ?>`;
}

// Fájl előnézet
function previewFile(fileId) {
    alert('Fájl előnézet hamarosan elérhető lesz.');
    // Itt jönne a fájl előnézet modal megnyitása
}

// Inicializálás
document.addEventListener('DOMContentLoaded', function() {
    initAlerts();
});
</script>