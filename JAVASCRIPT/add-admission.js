document.addEventListener('DOMContentLoaded', () => {
    const steps = document.querySelectorAll('.progress-step');
    const progressLine = document.querySelector('.progress-line');

    let currentStep = 0; // index du step courant
    const formSteps = document.querySelectorAll('.form-step');

    // Helper : Séparation Numéro et Rue (Remonté ici pour être accessible partout)
    function splitNumAndRue(fullAddress) {
        if (!fullAddress) return { num: '', rue: '' };
        fullAddress = fullAddress.trim();
        const match = fullAddress.match(/^(\d+)\s+(.*)/);
        if (match) {
            return { num: match[1], rue: match[2] };
        } else {
            return { num: '', rue: fullAddress };
        }
    }

    // --- Step 1 ---
    const civilité = document.getElementById('civilité');
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

    // --- Step 2 (Sécurité Sociale / Mutuelle) ---
    const nomOrgaSocial = document.getElementById('nom_orga_social');
    const numSecuSocial = document.getElementById('num_secusocial');
    const isAssure = document.getElementById('assure');
    const isADL = document.getElementById('adl');
    const nomMutuelle = document.getElementById('nom_mutuelle');
    const numAdherent = document.getElementById('num_adhérent');

    // --- Step 3 (Hospitalisation) ---
    const typeHosp = document.getElementById('type_hosp');
    const dateInput = document.getElementById('hospidate');
    const timeInput = document.getElementById('heure');
    const medecin = document.getElementById('medecin');
    const chambre = document.getElementById('chambre'); // 🎯 Récupéré ici pour l'Étape 3
    const timeError = document.getElementById('time-error');

    // --- Step 4 (Tiers / Accompagnants) ---
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

    // --- Step 5 (Fichiers joints) ---
    const CI = document.getElementById('CarteID');
    const CV = document.getElementById('CarteVitale');
    const CM = document.getElementById('CarteMutuelle');
    const LF = document.getElementById('LivretFamille');
    const AS = document.getElementById('AutoSoin');
    const DJ = document.getElementById('DecisionJuge');

    const isMultiPersonne = document.getElementById('isMultiPersonne');
    const personnesWrapper = document.getElementById('personnesWrapper');
    const formResponsable = document.getElementById('formResponsable');
    const formMineur = document.getElementById('formMineur');
    const title = document.getElementById('titlePPPC');
    const formPC = document.getElementById('formPC');


    // ------------------ Date & Heure ------------------
    function setMinDate() {
        const today = new Date();
        if (dateInput) dateInput.setAttribute('min', today.toISOString().split('T')[0]);
    }

    function updateMinTime() {
        if (!dateInput || !timeInput || !dateInput.value) {
            if (timeInput) timeInput.removeAttribute('min');
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
            if (timeError) timeError.style.display = 'none';
            return false;
        }
        const now = new Date();
        const selectedDate = new Date(dateInput.value);
        const [hh, mm] = timeInput.value.split(':').map(Number);

        if (selectedDate.toDateString() === now.toDateString()) {
            if (hh < now.getHours() || (hh === now.getHours() && mm < now.getMinutes())) {
                if (timeError) timeError.style.display = 'block';
                return false;
            }
        }
        if (timeError) timeError.style.display = 'none';
        return true;
    }

    function checkAgeForHospitalisation(dateValue) {
        if (!dateValue) return { valid: false, message: "Date de naissance manquante" };

        const birthDate = new Date(dateValue);
        const today = new Date();

        if (birthDate > today) {
            return { valid: false, message: "La date de naissance ne peut pas être future." };
        }

        let age = today.getFullYear() - birthDate.getFullYear();
        const m = today.getMonth() - birthDate.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }

        const AGE_MIN = 1;
        const AGE_MAX = 115;

        if (age < AGE_MIN) {
            return { valid: false, message: "Âge incompatible avec une hospitalisation." };
        }
        if (age > AGE_MAX) {
            return { valid: false, message: "Âge biologiquement incohérent." };
        }
        return { valid: true, age: age };
    }

    function isValidNIR(nir) {
        nir = nir.replace(/\s+/g, '');
        if (nir.length !== 15 || !/^[12][0-9]{14}$/.test(nir)) {
            return false;
        }
        const nirBase = nir.substring(0, 13);
        const cleSaisie = nir.substring(13, 15);

        const nirNumber = BigInt(nirBase);
        const cleCalc = 97n - (nirNumber % 97n);
        const cleCalcStr = cleCalc.toString().padStart(2, '0');

        return cleSaisie === cleCalcStr;
    }

    function isNIRConsistent(nir, datenaissance, civilité) {
        if (!isValidNIR(nir)) return false;

        const sexeNIR = nir.charAt(0);
        const anneeNIR = nir.substring(1, 3);
        const moisNIR = nir.substring(3, 5);

        const birthDate = new Date(datenaissance);
        if (isNaN(birthDate)) return false;

        const anneeNaissance = birthDate.getFullYear().toString().slice(-2);
        const moisNaissance = (birthDate.getMonth() + 1).toString().padStart(2, '0');

        if ((civilité === '1' && sexeNIR !== '1') || (civilité === '2' && sexeNIR !== '2')) {
            return false;
        }
        if (anneeNIR !== anneeNaissance || moisNIR !== moisNaissance) {
            return false;
        }

        return true;
    }


    // ------------------ Formatage Input ------------------
    if (cp) {
        cp.addEventListener('input', function () {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 5);
            updateProgress();
        });
    }
    if (telephone) {
        telephone.addEventListener('input', function () {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
            updateProgress();
        });
    }
    if (numSecuSocial) {
        numSecuSocial.addEventListener('input', function () {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 15);
            updateProgress();
        });
        numSecuSocial.addEventListener('blur', function () {
            if (this.value && !isValidNIR(this.value)) {
                alert("Numéro de sécurité sociale invalide.");
                this.value = "";
                this.focus();
            }
            updateProgress();
        });
    }


    // ------------------ Fonctions d'Affichage Conditionnel ------------------
    function toggleForms() {
        if (!isMultiPersonne || !personnesWrapper || !formPC || !title) return;

        const allGroups = personnesWrapper.querySelectorAll('.form-group');

        if (isMultiPersonne.checked) {
            formPC.style.display = 'none';
            title.textContent = 'Personne à prévenir et de confiance';
            allGroups.forEach(g => g.classList.remove('double'));
        } else {
            formPC.style.display = 'block';
            title.textContent = 'Personne à prévenir';
            allGroups.forEach(g => g.classList.add('double'));
        }
        updateProgress();
    }

    function toggleResponsableByAge() {
        if (!datenaissance || !formResponsable) return;
        const result = checkAgeForHospitalisation(datenaissance.value);
        if (result.valid && result.age < 18) {
            formResponsable.style.display = 'block';
        } else {
            formResponsable.style.display = 'none';
        }
        updateProgress();
    }

    function toggleDocumentByAge() {
        if (!datenaissance || !formMineur) return;
        const result = checkAgeForHospitalisation(datenaissance.value);
        if (result.valid && result.age < 18) {
            formMineur.style.display = 'block';
        } else {
            formMineur.style.display = 'none';
        }
        updateProgress();
    }


    if (datenaissance) {
        datenaissance.addEventListener('change', function () {
            const result = checkAgeForHospitalisation(this.value);

            if (!result.valid) {
                alert(result.message);
                this.value = "";
                if (formMineur) formMineur.style.display = 'none';
                if (formResponsable) formResponsable.style.display = 'none';
                updateProgress();
                return;
            }

            toggleResponsableByAge();
            toggleDocumentByAge();
            updateProgress();
        });
    }


    // ------------------ Upload Aperçu ------------------
    function setupDocumentPreview(inputId, previewId) {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        if (!input || !preview) return;

        input.addEventListener('change', () => {
            preview.innerHTML = '';

            Array.from(input.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    let element;
                    if (file.type.startsWith('image/')) {
                        element = document.createElement('img');
                        element.src = e.target.result;
                        element.style.maxWidth = "200px";
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
            updateProgress();
        });
    }


    if (isMultiPersonne) isMultiPersonne.addEventListener('change', toggleForms);


    // ------------------ Navigation entre steps (Actions) ------------------
    const nextStep1Btn = document.getElementById('nextStep1');
    const nextStep2Btn = document.getElementById('nextStep2');
    const nextStep3Btn = document.getElementById('nextStep3');
    const nextStep4Btn = document.getElementById('nextStep4');
    const nextStep5Btn = document.getElementById('nextStep5');

    if (nextStep1Btn) {
        nextStep1Btn.addEventListener('click', () => {
            const ageCheck = checkAgeForHospitalisation(datenaissance.value);
            if (!ageCheck.valid) {
                alert(ageCheck.message);
                return;
            }

            const isNomEpouseValid = !isMarried.checked || (isMarried.checked && nomEpouse.value.trim() !== "");

            if (!civilité.value || !nomNaissance.value || !datenaissance.value || !prenom.value || !addresse.value || !cp.value || !ville.value || !telephone.value || !mail.value || !isNomEpouseValid) {
                alert('Veuillez remplir correctement tous les champs obligatoires avant de continuer.');
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
    }

    if (nextStep2Btn) {
        nextStep2Btn.addEventListener('click', async () => {
            if (!isNIRConsistent(numSecuSocial.value, datenaissance.value, civilité.value)) {
                alert("Le numéro de sécurité sociale ne correspond pas aux informations du patient.");
                return;
            }
            if (!nomOrgaSocial.value || !numSecuSocial.value || !isAssure.value || !isADL.value || !nomMutuelle.value || !numAdherent.value) {
                alert('Veuillez remplir correctement tous les champs avant de continuer.');
                return;
            }

            const StepPatient = JSON.parse(sessionStorage.getItem('StepPatient') || '{}');
            const stepCouverture = {
                nomOrgaSocial: nomOrgaSocial.value,
                numSecuSocial: numSecuSocial.value,
                isAssure: isAssure.value,
                isADL: isADL.value,
                nomMutuelle: nomMutuelle.value,
                numAdherent: numAdherent.value,
            };
            const CouverturePatientData = { ...StepPatient, ...stepCouverture };

            try {
                const res = await fetch('../INCLUDES/submitBDD.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ step: 2, data: CouverturePatientData })
                });

                const resClone = res.clone();
                let result;

                try {
                    result = await res.json();
                    if (result.success) {
                        formSteps[currentStep].style.display = 'none';
                        currentStep++;
                        formSteps[currentStep].style.display = 'block';
                        updateProgress();
                    } else {
                        alert('Erreur enregistrement (Étape 2) : ' + (result.message || 'Erreur inconnue.'));
                    }
                } catch (e) {
                    const text = await resClone.text();
                    console.error("Format JSON invalide :", text);
                    alert('Erreur serveur : format JSON invalide.');
                }
            } catch (err) {
                console.error(err);
                alert('Erreur réseau ou serveur');
            }
        });
    }

    if (nextStep3Btn) {
        nextStep3Btn.addEventListener('click', async () => {
            if (!typeHosp.value || !dateInput.value || !timeInput.value || !medecin.value || !chambre.value || !checkTimeValidity()) {
                alert('Veuillez remplir correctement tous les champs avant de continuer.');
                return;
            }

            const stepHospitalisation = {
                TypeHospi: typeHosp.value,
                DateHospi: dateInput.value,
                HeureHospi: timeInput.value,
                Medecin: medecin.value,
                Chambre: chambre.value // 🎯 Lu ici à l'Étape 3 et pas depuis le sessionStorage
            };

            try {
                const res = await fetch('../INCLUDES/submitBDD.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ step: 3, data: stepHospitalisation })
                });

                const resClone = res.clone();
                let result;

                try {
                    result = await res.json();
                    if (result.success) {
                        formSteps[currentStep].style.display = 'none';
                        currentStep++;
                        formSteps[currentStep].style.display = 'block';
                        updateProgress();
                    } else {
                        alert('Erreur enregistrement (Étape 3) : ' + (result.message || 'Erreur inconnue.'));
                    }
                } catch (e) {
                    const text = await resClone.text();
                    console.error("Format JSON invalide :", text);
                    alert('Erreur serveur : format JSON invalide.');
                }
            } catch (err) {
                console.error(err);
                alert('Erreur réseau ou serveur');
            }
        });
    }

    if (nextStep4Btn) {
        nextStep4Btn.addEventListener('click', async () => {
            if (!NomPP.value || !PrenomPP.value || !TelPP.value || !RuePP.value || !VillePP.value || !CPPP.value) {
                alert('Veuillez remplir les informations de la personne à prévenir.');
                return;
            }

            if (!isMultiPersonne.checked) {
                if (!NomPC.value || !PrenomPC.value || !TelPC.value || !RuePC.value || !VillePC.value || !CPPC.value) {
                    alert('Veuillez remplir les informations de la personne de confiance.');
                    return;
                }
            }

            if (formResponsable.style.display === 'block') {
                if (!NomResp.value || !PrenomResp.value || !TelResp.value || !MailResp.value || !RueResp.value || !VilleResp.value || !CPResp.value) {
                    alert('Veuillez remplir les informations du représentant légal.');
                    return;
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
            };

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
                    if (result.success) {
                        formSteps[currentStep].style.display = 'none';
                        currentStep++;
                        formSteps[currentStep].style.display = 'block';
                        updateProgress();
                    } else {
                        alert('Erreur enregistrement (Étape 4) : ' + (result.message || 'Erreur inconnue.'));
                    }
                } catch (e) {
                    const text = await resClone.text();
                    console.error("Format JSON invalide :", text);
                    alert('Erreur serveur : format JSON invalide.');
                }
            } catch (err) {
                console.error(err);
                alert('Erreur réseau ou serveur');
            }
        });
    }

    if (nextStep5Btn) {
        nextStep5Btn.addEventListener('click', async (e) => {
            e.preventDefault();
            const requiredFiles = [CI, CV, CM];

            if (formMineur && formMineur.style.display === 'block') {
                requiredFiles.push(LF, AS, DJ);
            }

            if (requiredFiles.some(input => !input.files || input.files.length === 0)) {
                alert('Veuillez téléverser tous les fichiers requis avant de continuer.');
                return;
            }

            const formData = new FormData();
            formData.append('step', 5);

            requiredFiles.forEach(input => {
                if (input.files.length > 0) {
                    formData.append(input.name || input.id, input.files[0]);
                }
            });

            try {
                const res = await fetch('../INCLUDES/submitBDD.php', {
                    method: 'POST',
                    body: formData
                });

                const resClone = res.clone();
                let result;

                try {
                    result = await res.json();
                    if (result.success) {
                        alert('Documents enregistrés avec succès ! Fin du formulaire.');
                    } else {
                        alert('Erreur lors de l\'enregistrement des documents (Étape 5) : ' + (result.message || 'Erreur inconnue.'));
                    }
                } catch (e) {
                    const text = await resClone.text();
                    console.error("Format JSON invalide :", text);
                    alert('Erreur serveur : format JSON invalide.');
                }
            } catch (err) {
                console.error(err);
                alert('Erreur réseau ou serveur lors de l\'envoi des fichiers.');
            }
        });
    }


    // ------------------ Barre de Progression Dynamique ------------------
    function updateProgress() {
        if (!progressLine) return;

        const step1Fields = [civilité, nomNaissance, datenaissance, addresse, cp, ville, telephone, mail];
        if (isMarried.checked) step1Fields.push(nomEpouse);

        const step4Fields = [NomPP, PrenomPP, TelPP, RuePP, CPPP, VillePP];
        if (!isMultiPersonne.checked) step4Fields.push(NomPC, PrenomPC, TelPC, RuePC, CPPC, VillePC);
        if (formResponsable.style.display === 'block') {
            step4Fields.push(NomResp, PrenomResp, TelResp, MailResp, RueResp, VilleResp, CPResp);
        }

        const step5Fields = [CI, CV, CM];
        if (formMineur.style.display === 'block') {
            step5Fields.push(LF, AS, DJ);
        }

        const stepFields = [
            step1Fields,
            [nomOrgaSocial, numSecuSocial, isAssure, isADL, nomMutuelle, numAdherent],
            [typeHosp, dateInput, timeInput, medecin, chambre],
            step4Fields,
            step5Fields
        ];

        let completedSteps = 0;
        let percent = 0;

        stepFields.forEach((fields, stepIndex) => {
            if (fields.length === 0) return;
            let validFields = fields.filter(f => f && f.value && f.value.trim() !== "");

            if (fields.includes(timeInput) && !checkTimeValidity()) {
                validFields = validFields.filter(f => f !== timeInput);
            }

            const stepProgress = validFields.length / fields.length;
            percent += (stepProgress / stepFields.length) * 100;

            if (stepProgress === 1) completedSteps = stepIndex + 1;
        });

        progressLine.style.width = percent + '%';

        if (steps) {
            steps.forEach((step, index) => {
                if (index <= completedSteps) step.classList.add('active');
                else step.classList.remove('active');
            });
        }
    }


    if (isMarried) {
        isMarried.addEventListener('change', () => {
            if (isMarried.checked) {
                nomEpouse.disabled = false;
                nomEpouse.required = true;
            } else {
                nomEpouse.disabled = true;
                nomEpouse.required = false;
                nomEpouse.value = '';
            }
            updateProgress();
        });
    }

    const allInputs = document.querySelectorAll('input, select, textarea');
    allInputs.forEach(input => {
        input.addEventListener('input', updateProgress);
        input.addEventListener('change', updateProgress);
    });

    // ------------------ Initialisation finale ------------------
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

    updateProgress(); 
});