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
        <h1>Projektek</h1>
        <button class="btn btn-primary" onclick="openProjectModal()">Új projekt</button>
    </div>
    
    <div class="project-grid">
        <!-- Projekt kártyák -->
        <?php if (empty($projects)): ?>
            <div class="empty-state">
                <p>Még nincsenek projektek.</p>
                <button class="btn btn-primary" onclick="openProjectModal()">Első projekt létrehozása</button>
            </div>
        <?php else: ?>
            <?php foreach ($projects as $project): ?>
                <div class="project-card" style="border-left-color: <?= $project['color'] ?>;" 
                     onclick="openProject(<?= $project['id'] ?>)">
                    <div class="project-title"><?= htmlspecialchars($project['name']) ?></div>
                    <div class="project-description"><?= htmlspecialchars($project['description'] ?? '') ?></div>
                    <div class="project-meta">
                        <?php if ($project['subproject_count'] > 0): ?>
                            <span class="subproject-count">📁 <?= $project['subproject_count'] ?> alkönyvtár</span>
                        <?php endif; ?>
                        <?php if ($project['note_count'] > 0): ?>
                            <span class="note-count">📝 <?= $project['note_count'] ?> jegyzet</span>
                        <?php endif; ?>
                        <span><?= date('Y.m.d.', strtotime($project['created_at'])) ?></span>
                    </div>
                    <div class="project-actions">
                        <button class="btn btn-sm btn-danger" onclick="deleteProject(<?= $project['id'] ?>, event)">
                            Törlés
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal ablak új projekt hozzáadásához -->
<div id="projectModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Új projekt létrehozása</h2>
            <span class="close" onclick="closeProjectModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="projectForm" method="POST" action="?page=projects">
                <input type="hidden" name="action" value="add_project">
                <input type="hidden" name="project_type" value="project">
                
                <div class="form-group">
                    <label for="project_name" class="form-label">Projekt neve *</label>
                    <input type="text" id="project_name" name="project_name" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label for="project_description" class="form-label">Leírás</label>
                    <textarea id="project_description" name="project_description" class="form-input" rows="3" placeholder="Projekt rövid leírása..."></textarea>
                </div>
                
                <div class="form-group">
                    <label for="project_color" class="form-label">Szín</label>
                    <select id="project_color" name="project_color" class="form-input">
                        <option value="#3498db">Kék (alapértelmezett)</option>
                        <option value="#EF4444">Piros</option>
                        <option value="#10B981">Zöld</option>
                        <option value="#F59E0B">Narancs</option>
                        <option value="#8B5CF6">Lila</option>
                        <option value="#EC4899">Rózsaszín</option>
                        <option value="#06B6D4">Türkiz</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeProjectModal()">Mégse</button>
                    <button type="submit" class="btn btn-primary">Projekt létrehozása</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Törlés megerősítő modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Projekt törlése</h2>
            <span class="close" onclick="closeDeleteModal()">&times;</span>
        </div>
        <div class="modal-body">
            <p>Biztosan törölni szeretnéd ezt a projektet?</p>
            <p class="text-sm">A projekt összes almappája és jegyzete is törlődni fog!</p>
            <form id="deleteForm" method="POST" action="?page=projects">
                <input type="hidden" name="action" value="delete_project">
                <input type="hidden" name="project_id" id="delete_project_id">
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Mégse</button>
            <button type="button" class="btn btn-danger" onclick="confirmDelete()">Törlés</button>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>Legutóbb módosított jegyzetek</h2>
    </div>
    
    <ul class="note-list">
        <?php if (empty($recentNotes)): ?>
            <li class="note-item">
                <div class="note-excerpt">Még nincsenek jegyzetek.</div>
            </li>
        <?php else: ?>
            <?php foreach ($recentNotes as $note): ?>
                <li class="note-item" onclick="openNote(<?= $note['id'] ?>)">
                    <div class="note-title"><?= htmlspecialchars($note['title']) ?></div>
                    <div class="note-excerpt"><?= htmlspecialchars($note['excerpt'] ?? mb_substr(strip_tags($note['content']), 0, 100)) ?>...</div>
                    <div class="project-meta">
                        <span><?= htmlspecialchars($note['collection_name']) ?></span>
                        <span><?= date('Y.m.d.', strtotime($note['updated_at'])) ?></span>
                    </div>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
</div>

<script>
// Projekt modal kezelése
function openProjectModal() {
    document.getElementById('projectModal').style.display = 'block';
}

function closeProjectModal() {
    document.getElementById('projectModal').style.display = 'none';
}

// Törlés modal kezelése
function deleteProject(projectId, e) {
    if (e) e.stopPropagation(); // Megakadályozza, hogy a projekt kártyára kattintás is triggerelődjön
    document.getElementById('delete_project_id').value = projectId;
    document.getElementById('deleteModal').style.display = 'block';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

function confirmDelete() {
    document.getElementById('deleteForm').submit();
}

// Projekt megnyitása
function openProject(projectId) {
    window.location.href = `?page=project_detail&id=${projectId}`;
}

// Jegyzet megnyitása
function openNote(noteId) {
    window.location.href = `?page=note_detail&id=${noteId}`;
}

// Modal bezárása kattintásra a modal backdrop-ra
window.addEventListener('click', function(event) {
    const projectModal = document.getElementById('projectModal');
    const deleteModal = document.getElementById('deleteModal');
    
    if (event.target === projectModal) {
        closeProjectModal();
    }
    if (event.target === deleteModal) {
        closeDeleteModal();
    }
});

// Alert kezelés (ugyanaz, mint a naptárnál)
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
    
    // Aktív navigációs elem beállítása
    const urlParams = new URLSearchParams(window.location.search);
    const currentPage = urlParams.get('page') || 'calendar';
    const navLinks = document.querySelectorAll('.navbar a');
    
    navLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href').includes('page=' + currentPage)) {
            link.classList.add('active');
        }
    });
});
</script>