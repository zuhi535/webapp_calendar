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
            <span><?= htmlspecialchars($project['name']) ?></span>
        </div>
        <div class="header-actions">
            <button class="btn btn-primary" onclick="openNoteModal()">Új jegyzet</button>
            <button class="btn btn-secondary" onclick="openSubprojectModal()">Új almappa</button>
            <button class="btn btn-outline" onclick="openEditModal()">Szerkesztés</button>
        </div>
    </div>
    
    <div class="project-detail-content">
        <!-- Projekt információ -->
        <div class="project-info" style="border-left-color: <?= $project['color'] ?>;">
            <h1><?= htmlspecialchars($project['name']) ?></h1>
            <?php if (!empty($project['description'])): ?>
                <div class="project-description">
                    <?= nl2br(htmlspecialchars($project['description'])) ?>
                </div>
            <?php endif; ?>
            <div class="project-meta">
                <span>Létrehozva: <?= date('Y.m.d.', strtotime($project['created_at'])) ?></span>
                <?php if ($project['updated_at'] !== $project['created_at']): ?>
                    <span>Utoljára módosítva: <?= date('Y.m.d.', strtotime($project['updated_at'])) ?></span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Almappák -->
        <?php if (!empty($subprojects)): ?>
            <div class="section">
                <h2>Almappák</h2>
                <div class="subproject-grid">
                    <?php foreach ($subprojects as $subproject): ?>
                        <div class="subproject-card" style="border-left-color: <?= $subproject['color'] ?>;"
                             onclick="openProject(<?= $subproject['id'] ?>)">
                            <div class="subproject-title"><?= htmlspecialchars($subproject['name']) ?></div>
                            <?php if (!empty($subproject['description'])): ?>
                                <div class="subproject-description"><?= htmlspecialchars($subproject['description']) ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Jegyzetek -->
        <div class="section">
            <h2>Jegyzetek (<?= count($notes) ?>)</h2>
            <?php if (empty($notes)): ?>
                <div class="empty-state">
                    <p>Még nincsenek jegyzetek ebben a projektben.</p>
                    <button class="btn btn-primary" onclick="openNoteModal()">Első jegyzet létrehozása</button>
                </div>
            <?php else: ?>
                <div class="notes-list">
                    <?php foreach ($notes as $note): ?>
                        <div class="note-card" onclick="openNote(<?= $note['id'] ?>)">
                            <div class="note-header">
                                <h3 class="note-title"><?= htmlspecialchars($note['title']) ?></h3>
                                <?php if ($note['is_pinned']): ?>
                                    <span class="pinned-badge">📌 Rögzítve</span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($note['excerpt'])): ?>
                                <div class="note-excerpt"><?= htmlspecialchars($note['excerpt']) ?></div>
                            <?php endif; ?>
                            <div class="note-meta">
                                <span>Módosítva: <?= date('Y.m.d. H:i', strtotime($note['updated_at'])) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Vissza gomb -->
<div class="card">
    <div class="card-footer">
        <a href="?page=projects" class="btn btn-secondary">← Vissza a projektekhez</a>
    </div>
</div>

<!-- Modal ablakok -->
<!-- Projekt szerkesztése Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Projekt szerkesztése</h2>
            <span class="close" onclick="closeEditModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="editForm" method="POST" action="?page=project_detail">
                <input type="hidden" name="action" value="update_project">
                <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
                
                <div class="form-group">
                    <label for="edit_project_name" class="form-label">Projekt neve *</label>
                    <input type="text" id="edit_project_name" name="project_name" class="form-input" 
                           value="<?= htmlspecialchars($project['name']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_project_description" class="form-label">Leírás</label>
                    <textarea id="edit_project_description" name="project_description" class="form-input" 
                              rows="4" placeholder="Projekt részletes leírása..."><?= htmlspecialchars($project['description'] ?? '') ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="edit_project_color" class="form-label">Szín</label>
                    <select id="edit_project_color" name="project_color" class="form-input">
                        <option value="#3498db" <?= ($project['color'] === '#3498db') ? 'selected' : '' ?>>Kék</option>
                        <option value="#EF4444" <?= ($project['color'] === '#EF4444') ? 'selected' : '' ?>>Piros</option>
                        <option value="#10B981" <?= ($project['color'] === '#10B981') ? 'selected' : '' ?>>Zöld</option>
                        <option value="#F59E0B" <?= ($project['color'] === '#F59E0B') ? 'selected' : '' ?>>Narancs</option>
                        <option value="#8B5CF6" <?= ($project['color'] === '#8B5CF6') ? 'selected' : '' ?>>Lila</option>
                        <option value="#EC4899" <?= ($project['color'] === '#EC4899') ? 'selected' : '' ?>>Rózsaszín</option>
                        <option value="#06B6D4" <?= ($project['color'] === '#06B6D4') ? 'selected' : '' ?>>Türkiz</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Mégse</button>
                    <button type="submit" class="btn btn-primary">Mentés</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Új almappa Modal -->
<div id="subprojectModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Új almappa</h2>
            <span class="close" onclick="closeSubprojectModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="subprojectForm" method="POST" action="?page=project_detail">
                <input type="hidden" name="action" value="add_subproject">
                <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
                
                <div class="form-group">
                    <label for="subproject_name" class="form-label">Almappa neve *</label>
                    <input type="text" id="subproject_name" name="subproject_name" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label for="subproject_description" class="form-label">Leírás</label>
                    <textarea id="subproject_description" name="subproject_description" class="form-input" 
                              rows="3" placeholder="Almappa rövid leírása..."></textarea>
                </div>
                
                <div class="form-group">
                    <label for="subproject_color" class="form-label">Szín</label>
                    <select id="subproject_color" name="subproject_color" class="form-input">
                        <option value="#3498db">Kék</option>
                        <option value="#EF4444">Piros</option>
                        <option value="#10B981">Zöld</option>
                        <option value="#F59E0B">Narancs</option>
                        <option value="#8B5CF6">Lila</option>
                        <option value="#EC4899">Rózsaszín</option>
                        <option value="#06B6D4">Türkiz</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeSubprojectModal()">Mégse</button>
                    <button type="submit" class="btn btn-primary">Létrehozás</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Új jegyzet Modal -->
<div id="noteModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Új jegyzet</h2>
            <span class="close" onclick="closeNoteModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="noteForm" method="POST" action="?page=project_detail">
                <input type="hidden" name="action" value="add_note">
                <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
                
                <div class="form-group">
                    <label for="note_title" class="form-label">Cím *</label>
                    <input type="text" id="note_title" name="note_title" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label for="note_content" class="form-label">Tartalom</label>
                    <div class="formatting-toolbar">
                        <button type="button" onclick="formatText('bold')" title="Félkövér"><strong>B</strong></button>
                        <button type="button" onclick="formatText('italic')" title="Dőlt"><em>I</em></button>
                        <button type="button" onclick="formatText('underline')" title="Aláhúzás"><u>U</u></button>
                        <button type="button" onclick="formatText('link')" title="Hivatkozás">🔗</button>
                    </div>
                    <textarea id="note_content" name="note_content" class="form-input" 
                              rows="8" placeholder="Jegyzet tartalma..."></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeNoteModal()">Mégse</button>
                    <button type="submit" class="btn btn-primary">Létrehozás</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Modal kezelés
function openEditModal() {
    document.getElementById('editModal').style.display = 'block';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

function openSubprojectModal() {
    document.getElementById('subprojectModal').style.display = 'block';
}

function closeSubprojectModal() {
    document.getElementById('subprojectModal').style.display = 'none';
}

function openNoteModal() {
    document.getElementById('noteModal').style.display = 'block';
}

function closeNoteModal() {
    document.getElementById('noteModal').style.display = 'none';
}

// Szövegformázás
function formatText(format) {
    const textarea = document.getElementById('note_content');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selectedText = textarea.value.substring(start, end);
    
    let formattedText = '';
    
    switch (format) {
        case 'bold':
            formattedText = `**${selectedText}**`;
            break;
        case 'italic':
            formattedText = `_${selectedText}_`;
            break;
        case 'underline':
            formattedText = `__${selectedText}__`;
            break;
        case 'link':
            formattedText = `[${selectedText}](https://)`;
            break;
    }
    
    textarea.value = textarea.value.substring(0, start) + formattedText + textarea.value.substring(end);
    textarea.focus();
    textarea.setSelectionRange(start + formattedText.length, start + formattedText.length);
}

// Modal bezárása kattintásra a modal backdrop-ra
window.addEventListener('click', function(event) {
    const modals = ['editModal', 'subprojectModal', 'noteModal'];
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (event.target === modal) {
            eval(`close${modalId.charAt(0).toUpperCase() + modalId.slice(1)}()`);
        }
    });
});

// Projekt megnyitása
function openProject(projectId) {
    window.location.href = `?page=project_detail&id=${projectId}`;
}

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

// Inicializálás
document.addEventListener('DOMContentLoaded', function() {
    initAlerts();
});
</script>

<!-- ... (előző kód változatlan) -->

<!-- Fájlok szekció -->
<div class="section">
    <h2>Fájlok (<?= count($files) ?>)</h2>
    <div class="file-actions">
        <button class="btn btn-primary" onclick="openFileModal()">Fájl feltöltése</button>
    </div>
    
    <?php if (empty($files)): ?>
        <div class="empty-state">
            <p>Még nincsenek feltöltött fájlok.</p>
        </div>
    <?php else: ?>
        <div class="files-grid">
            <?php foreach ($files as $file): ?>
                <div class="file-card">
                    <div class="file-icon">
                        <?php if (strpos($file['mime_type'], 'image/') === 0): ?>
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
                        <a href="?page=project_detail&id=<?= $project['id'] ?>&download_file=<?= $file['id'] ?>" 
                           class="btn btn-sm" title="Letöltés">⬇️</a>
                        
                        <form method="POST" action="?page=project_detail" class="delete-form">
                            <input type="hidden" name="action" value="delete_file">
                            <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
                            <input type="hidden" name="file_id" value="<?= $file['id'] ?>">
                            <input type="hidden" name="file_path" value="<?= $file['file_path'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger" 
                                    onclick="return confirm('Biztosan törölni szeretnéd ezt a fájlt?')" title="Törlés">🗑️</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- ... (egyéb kód változatlan) -->

<!-- Fájl feltöltés Modal -->
<div id="fileModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Fájl feltöltése</h2>
            <span class="close" onclick="closeFileModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="fileForm" method="POST" action="?page=project_detail" enctype="multipart/form-data">
                <input type="hidden" name="action" value="upload_file">
                <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
                
                <div class="form-group">
                    <label for="project_file" class="form-label">Fájl kiválasztása *</label>
                    <input type="file" id="project_file" name="project_file" class="form-input" required 
                           accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif,.txt">
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeFileModal()">Mégse</button>
                    <button type="submit" class="btn btn-primary">Feltöltés</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Fájl előnézet Modal -->
<div id="previewModal" class="modal">
    <div class="modal-content" style="max-width: 90%; max-height: 90%;">
        <div class="modal-header">
            <h2>Fájl előnézet</h2>
            <span class="close" onclick="closePreviewModal()">&times;</span>
        </div>
        <div class="modal-body" id="previewContent">
            <!-- Előnézet tartalma -->
        </div>
    </div>
</div>

<script>
// Fájl modal kezelés
function openFileModal() {
    document.getElementById('fileModal').style.display = 'block';
}

function closeFileModal() {
    document.getElementById('fileModal').style.display = 'none';
}

function previewFile(fileId) {
    const previewContent = document.getElementById('previewContent');
    previewContent.innerHTML = '<div class="loading">Betöltés...</div>';
    
    // Itt lehetne AJAX kérés a fájl adatainak lekérésére
    // Most csak egy egyszerű megjelenítés
    document.getElementById('previewModal').style.display = 'block';
}

function closePreviewModal() {
    document.getElementById('previewModal').style.display = 'none';
}

// Jegyzet törlés megerősítése
function confirmNoteDelete(noteId, noteTitle) {
    if (confirm(`Biztosan törölni szeretnéd a(z) "${noteTitle}" jegyzetet?`)) {
        document.getElementById(`deleteNoteForm${noteId}`).submit();
    }
}

// Modal bezárása kattintásra a modal backdrop-ra
window.addEventListener('click', function(event) {
    const modals = ['editModal', 'subprojectModal', 'noteModal', 'fileModal', 'previewModal'];
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (event.target === modal) {
            eval(`close${modalId.charAt(0).toUpperCase() + modalId.slice(1)}()`);
        }
    });
});
</script>