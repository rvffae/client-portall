// assets/js/calendar.js

export function initCalendar() {
    const addEventModal = document.getElementById('addEventModal');
    const addEventForm = document.getElementById('addEventForm');
    const allDayCheckbox = document.getElementById('allDayEvent');
    const startTime = document.getElementById('eventStartTime');
    const endTime = document.getElementById('eventEndTime');
    const startDate = document.getElementById('eventStartDate');
    const endDate = document.getElementById('eventEndDate');

    // Gestion des champs de date/heure
    if (allDayCheckbox) {
        allDayCheckbox.addEventListener('change', function() {
            if (this.checked) {
                startTime.disabled = true;
                endTime.disabled = true;
                startTime.value = '';
                endTime.value = '';
            } else {
                startTime.disabled = false;
                endTime.disabled = false;
            }
        });
    }

    // Synchroniser la date de fin avec la date de début
    if (startDate && endDate) {
        startDate.addEventListener('change', function() {
            if (!endDate.value || endDate.value < this.value) {
                endDate.value = this.value;
            }
        });
    }

    // Fermer le modal en cliquant à l'extérieur
    window.addEventListener('click', function(event) {
        if (event.target === addEventModal) {
            closeAddEventModal();
        }
    });

    // Initialiser les dates par défaut
    if (startDate && endDate) {
        const today = new Date().toISOString().split('T')[0];
        startDate.value = today;
        endDate.value = today;
    }
}

export function openAddEventModal() {
    const modal = document.getElementById('addEventModal');
    if (modal) {
        modal.style.display = 'block';
    }
}

export function closeAddEventModal() {
    const modal = document.getElementById('addEventModal');
    const form = document.getElementById('addEventForm');
    
    if (modal) {
        modal.style.display = 'none';
    }
    if (form) {
        form.reset();
    }
}

export async function deleteEvent(eventId) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer cet événement ?')) {
        return;
    }

    try {
        const response = await fetch(`/google-calendar/delete-event/${eventId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const result = await response.json();

        if (result.success) {
            // Recharger la page pour mettre à jour la liste des événements
            window.location.reload();
        } else {
            alert('Erreur lors de la suppression de l\'événement: ' + result.message);
        }
    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors de la suppression de l\'événement');
    }
}

export async function addEvent(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const eventData = Object.fromEntries(formData);

    try {
        const response = await fetch('/google-calendar/add-event', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(eventData)
        });

        const result = await response.json();

        if (result.success) {
            closeAddEventModal();
            // Recharger la page pour mettre à jour la liste des événements
            window.location.reload();
        } else {
            alert('Erreur lors de la création de l\'événement: ' + result.message);
        }
    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors de la création de l\'événement');
    }
}

// Attacher les fonctions au window pour les rendre accessibles globalement
window.openAddEventModal = openAddEventModal;
window.closeAddEventModal = closeAddEventModal;
window.deleteEvent = deleteEvent;
window.addEvent = addEvent;