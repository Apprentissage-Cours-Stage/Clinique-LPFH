// Sélection des champs
const dateInput = document.getElementById('date');
const timeInput = document.getElementById('heure');
const timeError = document.getElementById('time-error');
const form = document.querySelector('.form-container form'); // ton formulaire

// Mettre à jour la date minimale
function setMinDate() {
    const today = new Date();
    const yyyy = today.getFullYear();
    let mm = today.getMonth() + 1;
    let dd = today.getDate();

    if (mm < 10) mm = '0' + mm;
    if (dd < 10) dd = '0' + dd;

    dateInput.setAttribute('min', `${yyyy}-${mm}-${dd}`);
}

// Mettre à jour l'heure minimale si la date choisie est aujourd'hui
function updateMinTime() {
    const now = new Date();
    const selectedDate = new Date(dateInput.value);

    if (dateInput.value === "" || selectedDate.toDateString() !== now.toDateString()) {
        // Pas de limite si ce n'est pas aujourd'hui
        timeInput.removeAttribute('min');
    } else {
        // Limite à l'heure et minute actuelles
        let hh = now.getHours();
        let min = now.getMinutes();
        if (hh < 10) hh = '0' + hh;
        if (min < 10) min = '0' + min;
        timeInput.setAttribute('min', `${hh}:${min}`);
    }
}

function checkTimeValidity() {
    const now = new Date();
    const selectedDate = new Date(dateInput.value);
    const timeValue = timeInput.value;

    // Si aucun temps ou date sélectionné, cacher le message
    if (!timeValue || !dateInput.value) {
        timeError.style.display = 'none';
        return;
    }

    // Vérifier si la date est aujourd'hui
    if (selectedDate.toDateString() === now.toDateString()) {
        const [hh, mm] = timeValue.split(':').map(Number);
        if (hh < now.getHours() || (hh === now.getHours() && mm < now.getMinutes())) {
            // Heure invalide
            timeError.style.display = 'block';
            return false;
        }
    }

    // Heure valide
    timeError.style.display = 'none';
    return true;
}
// Initialisation
setMinDate();
updateMinTime();

// Événements
dateInput.addEventListener('change', () => {
    updateMinTime();
    checkTimeValidity();
});

timeInput.addEventListener('input', checkTimeValidity);

// Vérifier à la soumission du formulaire
form.addEventListener('submit', function(e) {
    if (!checkTimeValidity()) {
        e.preventDefault();
        timeInput.focus();
    }
});

// Mise à jour toutes les minutes si l'utilisateur reste sur la page
setInterval(() => {
    if (dateInput.value === new Date().toISOString().split('T')[0]) {
        updateMinTime();
    }
}, 60000);