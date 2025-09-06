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
<div class="alert alert-success">
    <?= htmlspecialchars($successMessage) ?>
</div>
<?php endif; ?>

<?php if ($errorMessage): ?>
<div class="alert alert-error">
    <?= htmlspecialchars($errorMessage) ?>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h1>Naptár - <?= $calendar['monthName'] ?> <?= $calendar['year'] ?></h1>
        <button class="btn btn-primary" onclick="openEventModal()">Új esemény</button>
    </div>
    
    <div class="calendar-container">
        <div class="calendar-header">
            <a href="?page=calendar&year=<?= $calendar['prev']['year'] ?>&month=<?= $calendar['prev']['month'] ?>" class="btn">&lt; Előző hónap</a>
            <h2><?= $calendar['monthName'] ?> <?= $calendar['year'] ?></h2>
            <a href="?page=calendar&year=<?= $calendar['next']['year'] ?>&month=<?= $calendar['next']['month'] ?>" class="btn">Következő hónap &gt;</a>
        </div>
        
        <div class="calendar-grid">
            <div class="weekday">Hétfő</div>
            <div class="weekday">Kedd</div>
            <div class="weekday">Szerda</div>
            <div class="weekday">Csütörtök</div>
            <div class="weekday">Péntek</div>
            <div class="weekday">Szombat</div>
            <div class="weekday">Vasárnap</div>
            
            <!-- Naptár napjai -->
            <?php foreach ($calendar['weeks'] as $week): ?>
                <?php foreach ($week as $day): ?>
                    <div class="calendar-day<?= $day === null ? ' empty' : '' ?>" 
                         <?php if ($day !== null): ?>onclick="openEventModalForDay(<?= $day ?>)"<?php endif; ?>>
                        <?php if ($day !== null): ?>
                            <div class="day-number"><?= $day ?></div>
                            <?php 
                            // Események megjelenítése ehhez a naphoz
                            $currentDate = sprintf('%04d-%02d-%02d', $calendar['year'], $calendar['month'], $day);
                            $dayEvents = [];
                            
                            foreach ($events as $event): 
                                $eventDate = date('Y-m-d', strtotime($event['start_datetime']));
                                if ($eventDate === $currentDate): 
                                    $dayEvents[] = $event;
                                endif;
                            endforeach;
                            
                            // Maximum 2 eseményt jelenítünk meg, a többit tooltip-ben
                            $displayEvents = array_slice($dayEvents, 0, 2);
                            $hiddenEventsCount = count($dayEvents) - 2;
                            ?>
                            
                            <?php foreach ($displayEvents as $event): ?>
                                <div class="event" style="background-color: <?= $event['color'] ?? '#3498db' ?>;" 
                                     title="<?= htmlspecialchars($event['title']) ?> (<?= date('H:i', strtotime($event['start_datetime'])) ?>)">
                                    <?= htmlspecialchars(mb_substr($event['title'], 0, 12)) ?><?= mb_strlen($event['title']) > 12 ? '...' : '' ?> 
                                    <?= date('H:i', strtotime($event['start_datetime'])) ?>
                                    
                                    <!-- Törlés gomb -->
                                    <span class="event-delete" onclick="deleteEvent(<?= $event['id'] ?>, event)">
                                        &times;
                                    </span>
                                </div>
                            <?php endforeach; ?>
                            
                            <?php if ($hiddenEventsCount > 0): ?>
                                <div class="event-more">+<?= $hiddenEventsCount ?> több</div>
                            <?php endif; ?>
                            
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Modal ablak új esemény hozzáadásához -->
<div id="eventModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Új esemény hozzáadása</h2>
            <span class="close" onclick="closeEventModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="eventForm" method="POST" action="?page=calendar">
                <input type="hidden" name="action" value="add_event">
                <input type="hidden" name="event_year" id="event_year" value="<?= $calendar['year'] ?>">
                <input type="hidden" name="event_month" id="event_month" value="<?= $calendar['month'] ?>">
                
                <div class="form-group">
                    <label for="event_title" class="form-label">Esemény neve *</label>
                    <input type="text" id="event_title" name="event_title" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label for="event_date" class="form-label">Dátum *</label>
                    <input type="date" id="event_date" name="event_date" class="form-input" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="event_start_time" class="form-label">Kezdési idő</label>
                        <input type="time" id="event_start_time" name="event_start_time" class="form-input" value="09:00">
                    </div>
                    
                    <div class="form-group">
                        <label for="event_end_time" class="form-label">Befejezési idő</label>
                        <input type="time" id="event_end_time" name="event_end_time" class="form-input" value="10:00">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="event_all_day" class="form-checkbox">
                        <input type="checkbox" id="event_all_day" name="event_all_day" value="1">
                        <span>Egész napos esemény</span>
                    </label>
                </div>
                
                <div class="form-group">
                    <label for="event_color" class="form-label">Szín</label>
                    <select id="event_color" name="event_color" class="form-input">
                        <option value="#3498db">Kék (alapértelmezett)</option>
                        <option value="#EF4444">Piros</option>
                        <option value="#10B981">Zöld</option>
                        <option value="#F59E0B">Narancs</option>
                        <option value="#8B5CF6">Lila</option>
                        <option value="#EC4899">Rózsaszín</option>
                        <option value="#06B6D4">Türkiz</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="event_description" class="form-label">Leírás</label>
                    <textarea id="event_description" name="event_description" class="form-input" rows="4" placeholder="Esemény részletei..."></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeEventModal()">Mégse</button>
                    <button type="submit" class="btn btn-primary">Esemény mentése</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Törlés megerősítő modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Esemény törlése</h2>
            <span class="close" onclick="closeDeleteModal()">&times;</span>
        </div>
        <div class="modal-body">
            <p>Biztosan törölni szeretnéd ezt az eseményt?</p>
            <form id="deleteForm" method="POST" action="?page=calendar">
                <input type="hidden" name="action" value="delete_event">
                <input type="hidden" name="event_id" id="delete_event_id">
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
        <h2>Közelgő események - <?= $calendar['monthName'] ?> <?= $calendar['year'] ?></h2>
    </div>
    
    <ul class="note-list">
        <?php if (empty($events)): ?>
            <li class="note-item">
                <div class="note-excerpt">Nincsenek események ebben a hónapban.</div>
            </li>
        <?php else: ?>
            <?php foreach ($events as $event): ?>
                <li class="note-item">
                    <div class="note-title"><?= htmlspecialchars($event['title']) ?></div>
                    <div class="note-excerpt">
                        <?= date('Y. F j., H:i', strtotime($event['start_datetime'])) ?> 
                        <?= $event['end_datetime'] ? ' - ' . date('H:i', strtotime($event['end_datetime'])) : '' ?>
                        <?= $event['description'] ? ' · ' . htmlspecialchars($event['description']) : '' ?>
                    </div>
                    <div class="note-actions">
                        <form method="POST" action="?page=calendar" style="display: inline;">
                            <input type="hidden" name="action" value="delete_event">
                            <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Biztosan törölni szeretnéd ezt az eseményt?')">Törlés</button>
                        </form>
                    </div>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
</div>

<script>
// Modal ablak kezelése
function openEventModal() {
    document.getElementById('eventModal').style.display = 'block';
    // Alapértelmezett dátum beállítása
    const today = new Date();
    document.getElementById('event_date').value = today.toISOString().split('T')[0];
}

function openEventModalForDay(day) {
    document.getElementById('eventModal').style.display = 'block';
    // Kiválasztott nap dátumának beállítása
    const year = <?= $calendar['year'] ?>;
    const month = <?= $calendar['month'] ?>;
    const formattedDate = `${year}-${month.toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
    document.getElementById('event_date').value = formattedDate;
}

function closeEventModal() {
    document.getElementById('eventModal').style.display = 'none';
}

// Törlés modal kezelése
function deleteEvent(eventId, e) {
    if (e) e.stopPropagation(); // Megakadályozza, hogy a napra kattintás is triggerelődjön
    document.getElementById('delete_event_id').value = eventId;
    document.getElementById('deleteModal').style.display = 'block';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

function confirmDelete() {
    document.getElementById('deleteForm').submit();
}

// Modal bezárása kattintásra a modal backdrop-ra
window.addEventListener('click', function(event) {
    const modal = document.getElementById('eventModal');
    const deleteModal = document.getElementById('deleteModal');
    
    if (event.target === modal) {
        closeEventModal();
    }
    if (event.target === deleteModal) {
        closeDeleteModal();
    }
});

// Egész napos esemény kezelése
document.getElementById('event_all_day').addEventListener('change', function() {
    const startTime = document.getElementById('event_start_time');
    const endTime = document.getElementById('event_end_time');
    
    if (this.checked) {
        startTime.disabled = true;
        endTime.disabled = true;
    } else {
        startTime.disabled = false;
        endTime.disabled = false;
    }
});
</script>