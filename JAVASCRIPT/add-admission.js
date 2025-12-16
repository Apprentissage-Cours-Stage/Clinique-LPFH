document.addEventListener('DOMContentLoaded', () => {
    const steps = document.querySelectorAll('.progress-step');
    const progressLine = document.querySelector('.progress-line');

    let currentStep = 0; // index du step courant
    const formSteps = document.querySelectorAll('.form-step');

    //Step 1
    const civilité = document.getElementById('civilité')
    const nomNaissance = document.getElementById('nomNaissance');
    const isMarried = document.getElementById('isMarried');
    const nomEpouse = document.getElementById('nomEpouse');
    const prenom = document.getElementById('prenom');
    const datenaissance = document.getElementById('datenaissance');
    const addresse = document.getElementById('addresse');
    const cp = document.getElementById('cp');
    const ville = document.getElementById('ville');
    const telephone = document.getElementById('telephone');
    const mail = document.getElementById('mail');

    // Step 2
    const typeHosp = document.getElementById('type_hosp');
    const dateInput = document.getElementById('hospidate');
    const timeInput = document.getElementById('heure');
    const medecin = document.getElementById('medecin');
    const timeError = document.getElementById('time-error');

    // Step 3
    const nomOrgaSocial = document.getElementById('nom_orga_social');
    const numSecuSocial = document.getElementById('num_secusocial');
    const isAssure = document.getElementById('assure');
    const isADL = document.getElementById('adl');
    const nomMutuelle = document.getElementById('nom_mutuelle');
    const numAdherent = document.getElementById('num_adhérent');
    const chambre = document.getElementById('chambre');

    //Step 4
    const NomPP = document.getElementById('NomPP');
    const PrenomPP = document.getElementById('PrenomPP');
    const TelPP = document.getElementById('TelPP');
    const RuePP = document.getElementById('RuePP');
    const CPPP = document.getElementById('CPPP');
    const VillePP = document.getElementById('VillePP');

    const NomPC = document.getElementById('NomPC');
    const PrenomPC = document.getElementById('PrenomPC');
    const TelPC = document.getElementById('TelPC');
    const RuePC = document.getElementById('RuePC');
    const CPPC = document.getElementById('CPPC');
    const VillePC = document.getElementById('VillePC');

    const NomResp = document.getElementById('NomResp');
    const PrenomResp = document.getElementById('PrenomResp');
    const TelResp = document.getElementById('TelResp');
    const RueResp = document.getElementById('RueResp');
    const CPResp = document.getElementById('CPResp');
    const VilleResp = document.getElementById('VilleResp');
    const MailResp = document.getElementById('MailResp');

    //Step5
    const CI = document.getElementById('CarteID');
    const CV = document.getElementById('CarteVitale');
    const CM = document.getElementById('CarteMutuelle');
    const LF = document.getElementById('LivretFamille');
    const AS = document.getElementById('AutoSoin');
    const DJ = document.getElementById('DecisionJuge');

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

    // ------------------ Gestion dynamique des form ------------------
    const isMultiPersonne = document.getElementById('isMultiPersonne');
    const personnesWrapper = document.getElementById('personnesWrapper');
    const formResponsable = document.getElementById('formResponsable');
    const formMineur = document.getElementById('formMineur');
    const title = document.getElementById('titlePPPC');
    const formPC = document.getElementById('formPC');

    function toggleForms() {
        const allGroups = personnesWrapper.querySelectorAll('.form-group');

        if (isMultiPersonne.checked) {
            // Une seule personne : une colonne
            formPC.style.display = 'none';
            title.textContent = 'Personne à prevenir et de confiance'

            // Retirer la mise en page double
            allGroups.forEach(g => g.classList.remove('double'));
        } else {
            // Deux personnes : côte à côte
            formPC.style.display = 'block';
            title.textContent = 'Personne à prevenir'

            // Ajouter la classe double sur tous les groupes
            allGroups.forEach(g => g.classList.add('double'));
        }
    }

    function toggleDocumentByAge() {
        const dateValue = datenaissance.value;
        if (!dateValue) {
            formMineur.style.display = 'none';
            return;
        }
        const birthDate = new Date(dateValue);
        const today = new Date();
        // Calcul de l'âge
        let age = today.getFullYear() - birthDate.getFullYear();
        const m = today.getMonth() - birthDate.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        // Afficher Responsable si moins de 18 ans
        if (age < 18) {
            formMineur.style.display = 'block';
        } else {
            formMineur.style.display = 'none';
        }
    }

    function toggleResponsableByAge() {
        const dateValue = datenaissance.value;
        if (!dateValue) {
            formResponsable.style.display = 'none';
            return;
        }
        const birthDate = new Date(dateValue);
        const today = new Date();
        // Calcul de l'âge
        let age = today.getFullYear() - birthDate.getFullYear();
        const m = today.getMonth() - birthDate.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        // Afficher Responsable si moins de 18 ans
        if (age < 18) {
            formResponsable.style.display = 'block';
        } else {
            formResponsable.style.display = 'none';
        }
    }

    function setupDocumentPreview(inputId, previewId) {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);

        input.addEventListener('change', () => {
            preview.innerHTML = ''; // vider ancien aperçu

            Array.from(input.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    let element;
                    if (file.type.startsWith('image/')) {
                        element = document.createElement('img');
                        element.src = e.target.result;
                    } else if (file.type === 'application/pdf') {
                        element = document.createElement('embed');
                        element.src = e.target.result;
                        element.type = 'application/pdf';
                        element.width = 200;
                        element.height = 150;
                    } else {
                        element = document.createElement('span');
                        element.textContent = file.name;
                    }
                    preview.appendChild(element);
                };
                reader.readAsDataURL(file);
            });
        });
    }

    isMultiPersonne.addEventListener('change', toggleForms);
    datenaissance.addEventListener('change', toggleResponsableByAge);

    // ------------------ Navigation entre steps ------------------
    const nextStep1Btn = document.getElementById('nextStep1');
    const nextStep2Btn = document.getElementById('nextStep2');
    const nextStep3Btn = document.getElementById('nextStep3');
    const nextStep4Btn = document.getElementById('nextStep4');
    const nextStep5Btn = document.getElementById('nextStep5');

    nextStep1Btn.addEventListener('click', () => {
        if (!nomNaissance.value || !datenaissance.value || !prenom.value || !addresse.value || !cp.value || !ville.value || !telephone.value || !mail.value || (isMarried.checked && !nomEpouse.value)) {
            alert('Veuillez remplir correctement tous les champs avant de continuer.');
            return;
        }
        const { num: numAdresse, rue: rueAdresse } = splitNumAndRue(addresse.value);
        const PatientData = {
            civilité: civilité.value,
            nomNaissance: nomNaissance.value,
            nomEpouse: isMarried.checked ? nomEpouse.value : '',
            prenom: prenom.value,
            datenaissance: datenaissance.value,
            numAddresse: numAdresse,
            rueAddresse: rueAdresse,
            cp: cp.value,
            ville: ville.value,
            telephone: telephone.value,
            mail: mail.value
        };
        sessionStorage.setItem('StepPatient', JSON.stringify(PatientData));
        formSteps[currentStep].style.display = 'none';
        currentStep++;
        formSteps[currentStep].style.display = 'block';
        updateProgress();
    });

    nextStep2Btn.addEventListener('click', async () => {
        const StepPatient = JSON.parse(sessionStorage.getItem('StepPatient') || '{}');
        if (!nomOrgaSocial.value || !numSecuSocial.value || !isAssure.value || !isADL.value || !nomMutuelle.value || !numAdherent.value || !chambre.value) {
            alert('Veuillez remplir correctement tous les champs avant de continuer.');
            return;
        }
        const stepCouverture = {
            nomOrgaSocial: nomOrgaSocial.value,
            numSecuSocial: numSecuSocial.value,
            isAssure: isAssure.value,
            isADL: isADL.value,
            nomMutuelle: nomMutuelle.value,
            numAdherent: numAdherent.value,
        }
        const CouverturePatientData = { ...StepPatient, ...stepCouverture };
        try {
            const res = await fetch('../INCLUDES/submitBDD.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ step: 2, data: CouverturePatientData })
            })
            const resClone = res.clone();
            let result;

            try {
                result = await res.json();

                // --- VÉRIFICATION DU SUCCÈS DU SERVEUR ---
                if (result.success) {
                    sessionStorage.setItem('Chambre', chambre.value);
                    formSteps[currentStep].style.display = 'none';
                    currentStep++;
                    formSteps[currentStep].style.display = 'block';
                    updateProgress();
                } else {
                    alert('Erreur enregistrement (Étape 2) : ' + (result.message || 'Erreur inconnue.'));
                }
                // ------------------------------------------

            } catch (e) {
                const text = await resClone.text();
                console.error("Échec de la lecture JSON. Réponse du serveur brute:", text);
                alert('Erreur serveur: La réponse n\'est pas au format JSON. Voir console.');
                return;
            }
        } catch (err) {
            console.log(err);
            alert('Erreur réseau ou serveur');
        }
    });

    nextStep3Btn.addEventListener('click', async () => {
        if (!typeHosp.value || !dateInput.value || !timeInput.value || !medecin.value || !checkTimeValidity()) {
            alert('Veuillez remplir correctement tous les champs avant de continuer.');
            return;
        }
        const chambreUtilisé = sessionStorage.getItem('Chambre');
        const stepHospitalisation = {
            TypeHospi: typeHosp.value,
            DateHospi: dateInput.value,
            HeureHospi: timeInput.value,
            Medecin: medecin.value,
            Chambre: chambreUtilisé
        }
        try {
            const res = await fetch('../INCLUDES/submitBDD.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ step: 3, data: stepHospitalisation })
            })
            const resClone = res.clone();
            let result;

            try {
                result = await res.json();

                // --- VÉRIFICATION DU SUCCÈS DU SERVEUR ---
                if (result.success) {
                    formSteps[currentStep].style.display = 'none';
                    currentStep++;
                    formSteps[currentStep].style.display = 'block';
                    updateProgress();
                    toggleResponsableByAge();
                } else {
                    alert('Erreur enregistrement (Étape 3) : ' + (result.message || 'Erreur inconnue.'));
                }
                // ------------------------------------------

            } catch (e) {
                const text = await resClone.text();
                console.error("Échec de la lecture JSON. Réponse du serveur brute:", text);
                alert('Erreur serveur: La réponse n\'est pas au format JSON. Voir console.');
                return;
            }
        } catch (err) {
            console.log(err);
            alert('Erreur réseau ou serveur');
        }
    });

    nextStep4Btn.addEventListener('click', async () => {
        if (!NomPP.value || !PrenomPP.value || !TelPP.value || !RuePP.value || !VillePP.value || !CPPP.value) {
            if (!isMultiPersonne.checked && (!NomPC.value || !PrenomPC.value || !TelPC.value || !RuePC.value || !VillePC.value || !CPPC.value)) {
                if (formResponsable.style.display == 'block' && (!NomResp.value || !PrenomResp.value || !TelResp.value || !MailResp.value || !RueResp.value || !VilleResp.value || !CPResp.value)) {
                    alert('Veuillez remplir correctement tous les champs avant de continuer.');
                    return;
                }
            }
        }
        const { num: numAdressePP, rue: rueAdressePP } = splitNumAndRue(RuePP.value);
        const PersonnePrev = {
            Nom: NomPP.value,
            Prenom: PrenomPP.value,
            Telephone: TelPP.value,
            NumAdresse: numAdressePP,
            RueAdresse: rueAdressePP,
            CP: CPPP.value,
            Ville: VillePP.value,
        };

        let PersonneConf = null;
        if (!isMultiPersonne.checked) {
            const { num: numAdressePC, rue: rueAdressePC } = splitNumAndRue(RuePC.value);
            PersonneConf = {
                Nom: NomPC.value,
                Prenom: PrenomPC.value,
                Telephone: TelPC.value,
                NumAdresse: numAdressePC,
                RueAdresse: rueAdressePC,
                CP: CPPC.value,
                Ville: VillePC.value
            };
        }

        let ResponsableLegal = null;
        if (formResponsable.style.display === 'block') {
            const { num: numAdresseResp, rue: rueAdresseResp } = splitNumAndRue(RueResp.value);
            ResponsableLegal = {
                Nom: NomResp.value,
                Prenom: PrenomResp.value,
                Telephone: TelResp.value,
                Mail: MailResp.value,
                NumAdresse: numAdresseResp,
                RueAdresse: rueAdresseResp,
                CP: CPResp.value,
                Ville: VilleResp.value
            };
        }
        const stepPersonnesData = {
            PersonnePrev: PersonnePrev,
            PersonneConf: PersonneConf,
            ResponsableLegal: ResponsableLegal
        }
        try {
            const res = await fetch('../INCLUDES/submitBDD.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ step: 4, data: stepPersonnesData })
            });

            const resClone = res.clone();
            let result;

            try {
                result = await res.json();
            } catch (e) {
                const text = await resClone.text();
                console.error("Échec de la lecture JSON. Réponse du serveur brute:", text);
                alert('Erreur serveur: La réponse n\'est pas au format JSON. Voir console.');
                return;
            }

            // --- 4. GESTION DU SUCCÈS ---
            if (result.success) {
                // Avancer seulement si la BDD a réussi l'enregistrement
                formSteps[currentStep].style.display = 'none';
                currentStep++;
                formSteps[currentStep].style.display = 'block';
                toggleDocumentByAge(); // Maintenir l'appel pour les étapes suivantes
                updateProgress();
            } else {
                alert('Erreur enregistrement (Étape 4) : ' + (result.message || 'Erreur inconnue.'));
            }

        } catch (err) {
            console.log(err);
            alert('Erreur réseau ou serveur');
        }
    });

    nextStep5Btn.addEventListener('click', async (e) => {
        e.preventDefault();
        const requiredFiles = [
            CI,
            CV,
            CM,
        ]
        if (formMineur && formMineur.style.display !== 'none') {
            requiredFiles.push(LF, AS, DJ)
        }

        if (requiredFiles.some(input => !input.files || input.files.length === 0)) {
            alert('Veuillez remplir correctement tous les champs avant de continuer.');
            return;
        }
        const formData = new FormData();
        formData.append('step', 5);

        requiredFiles.forEach(input => {
            if (input.files.length > 0) {
                formData.append(input.name, input.files[0]);
            }
        });
        try {
            // Note: Lors de l'envoi de FormData, vous ne devez PAS définir le 'Content-Type'
            const res = await fetch('../INCLUDES/submitBDD.php', {
                method: 'POST',
                body: formData
            });

            const resClone = res.clone();
            let result;

            try {
                // Le serveur PHP doit répondre en JSON, même s'il traite des fichiers
                result = await res.json();
            } catch (e) {
                const text = await resClone.text();
                console.error("Échec de la lecture JSON. Réponse du serveur brute:", text);
                alert('Erreur serveur: La réponse n\'est pas au format JSON. Voir console.');
                return;
            }

            // --- 4. GESTION DU SUCCÈS ---
            if (result.success) {
                alert('Documents enregistrés avec succès ! Fin du formulaire.');
            } else {
                alert('Erreur lors de l\'enregistrement des documents (Étape 5) : ' + (result.message || 'Erreur inconnue.'));
            }

        } catch (err) {
            console.log(err);
            alert('Erreur réseau ou serveur lors de l\'envoi des fichiers.');
        }
    });

    // ------------------ Initialisation ------------------
    toggleForms();
    setupDocumentPreview('CarteID', 'previewCI');
    setupDocumentPreview('CarteVitale', 'previewCV');
    setupDocumentPreview('CarteMutuelle', 'previewCM');
    setupDocumentPreview('LivretFamille', 'previewLF');
    setupDocumentPreview('AutoSoin', 'previewAS');
    setupDocumentPreview('DecisionJuge', 'previewDJ');
    toggleResponsableByAge();
    toggleDocumentByAge();
    setMinDate();
    updateMinTime();
    formSteps.forEach((step, index) => step.style.display = index === 0 ? 'block' : 'none');

    setInterval(() => {
        const todayStr = new Date().toISOString().split('T')[0];
        if (dateInput.value === todayStr) updateMinTime();
    }, 60000);

    // ------------------ Barre de progression ------------------
    function updateProgress() {
        const step1Fields = [civilité, nomNaissance, datenaissance, addresse, cp, ville, telephone, mail];
        if (isMarried.checked) step1Fields.push(nomEpouse);

        const step4Fields = [NomPP, PrenomPP, TelPP, RuePP, CPPP, VillePP];
        if (!isMultiPersonne.checked) step4Fields.push(NomPC, PrenomPC, TelPC, RuePC, CPPC, VillePC);
        if (formResponsable.style.display == 'block') {
            step4Fields.push(NomResp, PrenomResp, TelResp, MailResp, RueResp, VilleResp, CPResp);
        }

        const step5Fields = [CI, CV, CM]
        if (formMineur.style.display == 'block') {
            step5Fields.push(LF, AS, DJ);
        }

        const stepFields = [
            step1Fields,// Step 1
            [nomOrgaSocial, numSecuSocial, isAssure, isADL, nomMutuelle, numAdherent, chambre], // Step 2 
            [typeHosp, dateInput, timeInput, medecin], // Step 3 
            step4Fields, // Step 3 
            step5Fields // Step 4 
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

    function splitNumAndRue(fullAddress) {
        fullAddress = fullAddress.trim();
        const match = fullAddress.match(/^(\d+)\s+(.*)/);
        if (match) {
            return { num: match[1], rue: match[2] };
        } else {
            return { num: '', rue: fullAddress };
        }
    }
});
