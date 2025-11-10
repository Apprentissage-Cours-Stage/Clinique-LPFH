document.addEventListener('DOMContentLoaded', () => {
    const steps = document.querySelectorAll('.progress-step');
    const progressLine = document.querySelector('.progress-line');

    let currentStep = 0; // index du step courant
    const formSteps = document.querySelectorAll('.form-step');

    // Step 1
    const typeHosp = document.getElementById('type_hosp');
    const dateInput = document.getElementById('date');
    const timeInput = document.getElementById('heure');
    const medecin = document.getElementById('medecin');
    const timeError = document.getElementById('time-error');

    // Step 2 (exemple)
    const patientNom = document.getElementById('patient_nom');
    const patientPrenom = document.getElementById('patient_prenom');

    // ------------------ Date & Heure ------------------
    function setMinDate() {
        const today = new Date();
        dateInput.setAttribute('min', today.toISOString().split('T')[0]);
    }

    function updateMinTime() {
        if (!dateInput.value) {
            timeInput.removeAttribute('min');
            return;
        }
        const today = new Date();
        const selectedDate = new Date(dateInput.value);
        if (selectedDate.toDateString() === today.toDateString()) {
            let hh = today.getHours().toString().padStart(2, '0');
            let mm = today.getMinutes().toString().padStart(2, '0');
            timeInput.setAttribute('min', `${hh}:${mm}`);
        } else {
            timeInput.removeAttribute('min');
        }
    }

    function checkTimeValidity() {
        if (!dateInput.value || !timeInput.value) {
            timeError.style.display = 'none';
            return false;
        }
        const now = new Date();
        const selectedDate = new Date(dateInput.value);
        const [hh, mm] = timeInput.value.split(':').map(Number);

        if (selectedDate.toDateString() === now.toDateString()) {
            if (hh < now.getHours() || (hh === now.getHours() && mm < now.getMinutes())) {
                timeError.style.display = 'block';
                return false;
            }
        }
        timeError.style.display = 'none';
        return true;
    }

    // ------------------ Sauvegarde / Chargement ------------------
    function saveStep1Data() {
        const data = {
            typeHosp: typeHosp.value,
            date: dateInput.value,
            heure: timeInput.value,
            medecin: medecin.value
        };
        sessionStorage.setItem('step1Data', JSON.stringify(data));
    }

    function loadStep1Data() {
        const saved = sessionStorage.getItem('step1Data');
        if (!saved) return;
        const data = JSON.parse(saved);
        typeHosp.value = data.typeHosp || '';
        dateInput.value = data.date || '';
        timeInput.value = data.heure || '';
        medecin.value = data.medecin || '';
        updateMinTime();
        checkTimeValidity();
    }

    function saveStep2Data() {
        const data = {
            patientNom: patientNom.value,
            patientPrenom: patientPrenom.value
        };
        sessionStorage.setItem('step2Data', JSON.stringify(data));
    }

    function loadStep2Data() {
        const saved = sessionStorage.getItem('step2Data');
        if (!saved) return;
        const data = JSON.parse(saved);
        patientNom.value = data.patientNom || '';
        patientPrenom.value = data.patientPrenom || '';
    }

    // ------------------ Navigation entre steps ------------------
    const nextStep1Btn = document.getElementById('nextStep1');
    const prevStep2Btn = document.getElementById('prevStep2');
    const formStep2 = document.getElementById('formStep2');

    nextStep1Btn.addEventListener('click', () => {
        if (!typeHosp.value || !dateInput.value || !timeInput.value || !medecin.value || !checkTimeValidity()) {
            alert('Veuillez remplir correctement tous les champs avant de continuer.');
            return;
        }
        saveStep1Data();
        formSteps[currentStep].style.display = 'none';
        currentStep++;
        formSteps[currentStep].style.display = 'block';
        loadStep2Data();
        updateProgress();
    });

    prevStep2Btn.addEventListener('click', () => {
        saveStep2Data();
        formSteps[currentStep].style.display = 'none';
        currentStep--;
        formSteps[currentStep].style.display = 'block';
        loadStep1Data();
        updateProgress();
    });

    // ------------------ Soumission finale Step 2 ------------------
    formStep2.addEventListener('submit', e => {
        e.preventDefault();
        if (!patientNom.value || !patientPrenom.value) {
            alert('Veuillez remplir tous les champs du patient.');
            return;
        }
        saveStep2Data();
        // Ici vous pouvez envoyer les données via fetch ou submit classique
        alert('Formulaire complet et sauvegardé !');
        // formStep2.submit(); // si vous voulez soumettre à PHP
    });

    // ------------------ Initialisation ------------------
    setMinDate();
    loadStep1Data();
    updateMinTime();
    formSteps.forEach((step, index) => step.style.display = index === 0 ? 'block' : 'none');

    setInterval(() => {
        const todayStr = new Date().toISOString().split('T')[0];
        if (dateInput.value === todayStr) updateMinTime();
    }, 60000);

    // ------------------ Barre de progression ------------------
    function updateProgress() {
        const stepFields = [
            [typeHosp, dateInput, timeInput, medecin], // Step 1 
            [/*Patient*/], // Step 2 
            [/*Personne à prevenir*/], // Step 3 
            [/*Documents*/] // Step 4 
        ];
        let completedSteps = 0;
        let percent = 0;
        stepFields.forEach((fields, stepIndex) => {
            if (fields.length === 0) return;
            let validFields = fields.filter(f => f.value && f.value.trim() !== ""); // Vérifier spécifiquement la validité de timeInput 
            if (fields.includes(timeInput) && !checkTimeValidity()) {
                validFields = validFields.filter(f => f !== timeInput);
            } // Progression proportionnelle à ce step 
            const stepProgress = validFields.length / fields.length; // Additionner la progression pour tous les steps précédents 
            percent += stepProgress / stepFields.length * 100; // Activer le step suivant seulement si tous les champs du step sont valides 
            if (stepProgress === 1) completedSteps = stepIndex + 1;
        }); // Mise à jour de la barre 
        progressLine.style.width = percent + '%'; // Mise à jour des classes active 
        steps.forEach((step, index) => {
            if (index <= completedSteps) step.classList.add('active');
            else step.classList.remove('active');
        });
    }
});