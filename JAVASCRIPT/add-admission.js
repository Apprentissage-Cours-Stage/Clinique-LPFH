document.addEventListener('DOMContentLoaded', () => {
    const steps = document.querySelectorAll('.progress-step');
    const progressLine = document.querySelector('.progress-line');

    let currentStep = 0; // index du step courant
    const formSteps = document.querySelectorAll('.form-step');

    // Step 1
    const typeHosp = document.getElementById('type_hosp');
    const dateInput = document.getElementById('hospidate');
    const timeInput = document.getElementById('heure');
    const medecin = document.getElementById('medecin');
    const timeError = document.getElementById('time-error');

    // Step 2
    const nomOrgaSocial = document.getElementById('nom_orga_social');
    const numSecuSocial = document.getElementById('num_secusocial');
    const isAssure = document.getElementById('assure');
    const isADL = document.getElementById('adl');
    const nomMutuelle = document.getElementById('nom_mutuelle');
    const numAdherent = document.getElementById('num_adhérent');
    const chambre = document.getElementById('chambre');

    //Step 3
    const civilité = document.getElementById('civilité')
    const nomNaissance = document.getElementById('nomNaissance');
    const prenom = document.getElementById('prenom');
    const datenaissance = document.getElementById('datenaissance');
    const addresse = document.getElementById('addresse');
    const cp = document.getElementById('cp');
    const ville = document.getElementById('ville');
    const telephone = document.getElementById('telephone');
    const mail = document.getElementById('mail');

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
            nomOrgaSocial: nomOrgaSocial.value,
            numSecuSocial: numSecuSocial.value,
            isAssure: isAssure.value,
            isADL: isADL.value,
            nomMutuelle: nomMutuelle.value,
            numAdherent: numAdherent.value,
            chambre: chambre.value
        };
        sessionStorage.setItem('step2Data', JSON.stringify(data));
    }

    function loadStep2Data() {
        const saved = sessionStorage.getItem('step2Data');
        if (!saved) return;
        const data = JSON.parse(saved);
        nomOrgaSocial.value = data.nomOrgaSocial || '';
        numSecuSocial.value = data.numSecuSocial || '';
        isAssure.value = data.isAssure || '';
        isADL.value = data.isADL || '';
        nomMutuelle.value = data.nomMutuelle || '';
        numAdherent.value = data.numAdherent || '';
        chambre.value = data.chambre || '';
    }

    function loadStep3Data() {
        const saved = sessionStorage.getItem('step3Data');
        if (!saved) return;
        const data = JSON.parse(saved);
        civilité.value = data.civilité || '';
        nomNaissance.value = data.nomNaissance || '';
        nomEpouse.value = data.nomEpouse || '';
        datenaissance.value = data.datenaissance || '';
        addresse.value = data.addresse || '';
        cp.value = data.cp || '';
        ville.value = data.ville || '';
        telephone.value = data.telephone || '';
        mail.value = data.mail || '';
        if (isMarried.checked) {
            nomEpouse.value = data.nomEpouse || '';
        } else {
            nomEpouse.value = '';
        }
    }

    function saveStep3Data() {
        const data = {
            civilité: civilité.value,
            nomNaissance: nomNaissance.value,
            nomEpouse: isMarried.checked ? nomEpouse.value : '',
            prenom: prenom.value,
            datenaissance: datenaissance.value,
            addresse: addresse.value,
            cp: cp.value,
            ville: ville.value,
            telephone: telephone.value,
            mail: mail.value
        };
        sessionStorage.setItem('step3Data', JSON.stringify(data));
    }

    // ------------------ Navigation entre steps ------------------
    const nextStep1Btn = document.getElementById('nextStep1');
    const prevStep2Btn = document.getElementById('prevStep2');
    const nextStep2Btn = document.getElementById('nextStep2');
    const prevStep3Btn = document.getElementById('prevStep3');
    const nextStep3Btn = document.getElementById('nextStep3');

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

    nextStep2Btn.addEventListener('click', () => {
        if (!nomOrgaSocial.value || !numSecuSocial.value || !isAssure.value || !isADL.value || !nomMutuelle.value || !numAdherent.value || !chambre.value) {
            alert('Veuillez remplir correctement tous les champs avant de continuer.');
            return;
        }
        saveStep2Data();
        formSteps[currentStep].style.display = 'none';
        currentStep++;
        formSteps[currentStep].style.display = 'block';
        loadStep3Data();
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

    prevStep3Btn.addEventListener('click', () => {
        saveStep3Data();
        formSteps[currentStep].style.display = 'none';
        currentStep--;
        formSteps[currentStep].style.display = 'block';
        loadStep2Data();
        updateProgress();
    });

    nextStep3Btn.addEventListener('click', () => {
        if (!nomNaissance.value || !datenaissance.value || !addresse.value || !cp.value || !ville.value || !telephone.value || !mail.value) {
            alert('Veuillez remplir correctement tous les champs avant de continuer.');
            return;
        }
        saveStep3Data();
        formSteps[currentStep].style.display = 'none';
        currentStep++;
        formSteps[currentStep].style.display = 'block';
        loadStep4Data();
        updateProgress();
    })

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
        const step3Fields = [civilité, nomNaissance, datenaissance, addresse, cp, ville, telephone, mail];
        if (isMarried.checked) step3Fields.push(nomEpouse);

        const stepFields = [
            [typeHosp, dateInput, timeInput, medecin], // Step 1 
            [nomOrgaSocial, numSecuSocial, isAssure, isADL, nomMutuelle, numAdherent, chambre], // Step 2
            step3Fields,// Step 3 
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

    const isMarried = document.getElementById('isMarried');
    const nomEpouse = document.getElementById('nomEpouse');

    isMarried.addEventListener('change', () => {
        if (isMarried.checked) {
            nomEpouse.disabled = false;
            nomEpouse.required = true;
        } else {
            nomEpouse.disabled = true;
            nomEpouse.required = false;
            nomEpouse.value = ''; // vider si décoché
        }
    });
});