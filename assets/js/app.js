// Általános JavaScript funkciók
console.log('Webapp loaded');

// Aktív navigációs elem beállítása
function setActiveNavItem() {
    const urlParams = new URLSearchParams(window.location.search);
    const currentPage = urlParams.get('page') || 'calendar';
    const navLinks = document.querySelectorAll('.navbar a');
    
    navLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href').includes('page=' + currentPage)) {
            link.classList.add('active');
        }
    });
}

// Naptár interakciók
function initCalendar() {
    const calendarDays = document.querySelectorAll('.calendar-day');
    
    calendarDays.forEach(day => {
        day.addEventListener('click', function() {
            const dayNumber = this.querySelector('.day-number');
            if (dayNumber) {
                console.log('Nap kiválasztva:', dayNumber.textContent);
            }
        });
    });
    
    const events = document.querySelectorAll('.event');
    
    events.forEach(event => {
        event.addEventListener('click', function(e) {
            e.stopPropagation();
            console.log('Esemény kiválasztva:', this.textContent);
        });
    });
}

// Projekt kártya interakciók
function initProjectCards() {
    const projectCards = document.querySelectorAll('.project-card');
    
    projectCards.forEach(card => {
        card.addEventListener('click', function() {
            const projectTitle = this.querySelector('.project-title');
            if (projectTitle) {
                console.log('Projekt kiválasztva:', projectTitle.textContent);
            }
        });
    });
}

// Gombkezelés
function initButtons() {
    // Hónap váltás gombok
    const prevMonthBtn = document.querySelector('.calendar-header .btn:first-child');
    const nextMonthBtn = document.querySelector('.calendar-header .btn:last-child');
    
    if (prevMonthBtn && !prevMonthBtn.hasListener) {
        prevMonthBtn.addEventListener('click', function() {
            console.log('Előző hónap');
        });
        prevMonthBtn.hasListener = true;
    }
    
    if (nextMonthBtn && !nextMonthBtn.hasListener) {
        nextMonthBtn.addEventListener('click', function() {
            console.log('Következő hónap');
        });
        nextMonthBtn.hasListener = true;
    }
    
    // Új esemény gomb
    const newEventBtn = document.querySelector('.card-header .btn-primary');
    if (newEventBtn && newEventBtn.textContent.includes('Új esemény') && !newEventBtn.hasListener) {
        newEventBtn.addEventListener('click', function() {
            console.log('Új esemény létrehozása');
        });
        newEventBtn.hasListener = true;
    }
    
    // Új projekt gomb
    const newProjectBtn = document.querySelector('.card-header .btn-primary');
    if (newProjectBtn && newProjectBtn.textContent.includes('Új projekt') && !newProjectBtn.hasListener) {
        newProjectBtn.addEventListener('click', function() {
            console.log('Új projekt létrehozása');
        });
        newProjectBtn.hasListener = true;
    }
}

// Oldal betöltésekor végrehajtandó műveletek
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded');
    setActiveNavItem();
    initButtons();
    
    // Csak akkor inicializáljuk, ha az adott elemek léteznek az oldalon
    if (document.querySelector('.calendar-day')) {
        initCalendar();
    }
    
    if (document.querySelector('.project-card')) {
        initProjectCards();
    }
});

// Hibakezelés a képbetöltésekhez
window.addEventListener('error', function(e) {
    if (e.target.tagName === 'IMG') {
        console.log('Kép betöltési hiba:', e.target.src);
        e.target.style.display = 'none';
    }
}, true);

// Naptár lapozás kezelése
function initCalendarNavigation() {
    const calendarDays = document.querySelectorAll('.calendar-day');
    
    calendarDays.forEach(day => {
        day.addEventListener('click', function() {
            const dayNumber = this.querySelector('.day-number');
            if (dayNumber && dayNumber.textContent.trim() !== '') {
                console.log('Nap kiválasztva:', dayNumber.textContent);
                // Itt lehet implementálni a nap kiválasztását
            }
        });
    });
    
    // Hónap váltás gombok eseménykezelője
    const prevMonthBtn = document.querySelector('.calendar-header a:first-child');
    const nextMonthBtn = document.querySelector('.calendar-header a:last-child');
    
    if (prevMonthBtn && !prevMonthBtn.hasListener) {
        prevMonthBtn.addEventListener('click', function(e) {
            console.log('Előző hónap');
            // Az anchor elem alapértelmezetten navigál, nincs szükség extra kezelésre
        });
        prevMonthBtn.hasListener = true;
    }
    
    if (nextMonthBtn && !nextMonthBtn.hasListener) {
        nextMonthBtn.addEventListener('click', function(e) {
            console.log('Következő hónap');
            // Az anchor elem alapértelmezetten navigál, nincs szükség extra kezelésre
        });
        nextMonthBtn.hasListener = true;
    }
}

// Frissítsd a DOMContentLoaded eseménykezelőt
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded');
    setActiveNavItem();
    initButtons();
    
    if (document.querySelector('.calendar-day')) {
        initCalendarNavigation();
    }
    
    if (document.querySelector('.project-card')) {
        initProjectCards();
    }
});