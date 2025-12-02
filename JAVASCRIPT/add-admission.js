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
        const saved = sessionStorage.getItem('step3Data');
        if (!saved) return;
        const data = JSON.parse(saved);
        const dateValue = data.datenaissance;
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
        const saved = sessionStorage.getItem('step3Data');
        if (!saved) return;
        const data = JSON.parse(saved);
        const dateValue = data.datenaissance;
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
        prenom.value = data.prenom || '';
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

    function loadStep4Data() {
        const saved = sessionStorage.getItem('step4Data');
        if (!saved) return;
        const data = JSON.parse(saved);
        // Personne à prévenir
        NomPP.value = data.NomPP || '';
        PrenomPP.value = data.PrenomPP || '';
        TelPP.value = data.TelPP || '';
        RuePP.value = data.RuePP || '';
        CPPP.value = data.CPPP || '';
        VillePP.value = data.VillePP || '';
        // Personne de confiance
        if (!isMultiPersonne.checked) {
            NomPC.value = data.NomPC || '';
            PrenomPC.value = data.PrenomPC || '';
            TelPC.value = data.TelPC || '';
            RuePC.value = data.RuePC || '';
            CPPC.value = data.CPPC || '';
            VillePC.value = data.VillePC || '';
        } else {
            NomPC.value = '';
            PrenomPC.value = '';
            TelPC.value = '';
            RuePC.value = '';
            CPPC.value = '';
            VillePC.value = '';
        }
        // Responsable légal
        NomResp.value = data.NomResp || '';
        PrenomResp.value = data.PrenomResp || '';
        TelResp.value = data.TelResp || '';
        RueResp.value = data.RueResp || '';
        CPResp.value = data.CPResp || '';
        VilleResp.value = data.VilleResp || '';
        MailResp.value = data.MailResp || '';
    }

    function saveStep4Data() {
        const data = {
            NomPP: NomPP.value,
            PrenomPP: PrenomPP.value,
            TelPP: TelPP.value,
            RuePP: RuePP.value,
            CPPP: CPPP.value,
            VillePP: VillePP.value,

            NomPC: !isMultiPersonne.checked ? NomPC.value : '',
            PrenomPC: !isMultiPersonne.checked ? PrenomPC.value : '',
            TelPC: !isMultiPersonne.checked ? TelPC.value : '',
            RuePC: !isMultiPersonne.checked ? RuePC.value : '',
            CPPC: !isMultiPersonne.checked ? CPPC.value : '',
            VillePC: !isMultiPersonne.checked ? VillePC.value : '',

            NomResp: NomResp.value,
            PrenomResp: PrenomResp.value,
            TelResp: TelResp.value,
            RueResp: RueResp.value,
            CPResp: CPResp.value,
            VilleResp: VilleResp.value,
            MailResp: MailResp.value
        }
        sessionStorage.setItem('step4Data', JSON.stringify(data));
    }

    function saveStep5Data() {
        const data = {};

        const inputs = {
            CI,
            CV,
            CM,
            LF,
            AS,
            DJ
        };

        const promises = [];

        for (const key in inputs) {
            const file = inputs[key].files[0];
            if (file) {
                const reader = new FileReader();
                const p = new Promise(resolve => {
                    reader.onload = () => {
                        data[key] = reader.result; // Base64
                        resolve();
                    };
                });
                reader.readAsDataURL(file);
                promises.push(p);
            } else {
                data[key] = null;
            }
        }

        Promise.all(promises).then(() => {
            sessionStorage.setItem('step5Data', JSON.stringify(data));
        });
    }

    // ------------------ Navigation entre steps ------------------
    const nextStep1Btn = document.getElementById('nextStep1');
    const prevStep2Btn = document.getElementById('prevStep2');
    const nextStep2Btn = document.getElementById('nextStep2');
    const prevStep3Btn = document.getElementById('prevStep3');
    const nextStep3Btn = document.getElementById('nextStep3');
    const prevStep4Btn = document.getElementById('prevStep4');
    const nextStep4Btn = document.getElementById('nextStep4');
    const prevStep5Btn = document.getElementById('prevStep5');
    const nextStep5Btn = document.getElementById('nextStep5');

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
            if (isMarried.checked && !nomEpouse.value) {
                alert('Veuillez remplir correctement tous les champs avant de continuer.');
                return;
            }
        }
        saveStep3Data();
        formSteps[currentStep].style.display = 'none';
        currentStep++;
        formSteps[currentStep].style.display = 'block';
        loadStep4Data();
        toggleResponsableByAge();
        toggleDocumentByAge();
        updateProgress();
    });

    prevStep4Btn.addEventListener('click', () => {
        saveStep4Data();
        formSteps[currentStep].style.display = 'none';
        currentStep--;
        formSteps[currentStep].style.display = 'block';
        loadStep3Data();
        updateProgress();
    });

    nextStep4Btn.addEventListener('click', () => {
        if (!NomPP.value || !PrenomPP.value || !TelPP.value || !RuePP.value || !VillePP.value || !CPPP.value) {
            if (!isMultiPersonne.checked && (!NomPC.value || !PrenomPC.value || !TelPC.value || !RuePC.value || !VillePC.value || !CPPC.value)) {
                if (formResponsable.style.display == 'block' && (!NomResp.value || !PrenomResp.value || !TelResp.value || !MailResp.value || !RueResp.value || !VilleResp.value || !CPResp.value)) {
                    alert('Veuillez remplir correctement tous les champs avant de continuer.');
                    return;
                }
            }
        }
        saveStep4Data();
        formSteps[currentStep].style.display = 'none';
        currentStep++;
        formSteps[currentStep].style.display = 'block';
        toggleResponsableByAge();
        toggleDocumentByAge();
        updateProgress();
    });

    nextStep5Btn.addEventListener('click', () => {
        if (!CI.value || !CV.value || !CM.value || !LF.value || !AS.value || !DJ.value) {
            alert('Veuillez remplir correctement tous les champs avant de continuer.');
            return;
        }
        saveStep5Data();
        formSteps[currentStep].style.display = 'none';
        currentStep++;
        formSteps[currentStep].style.display = 'block';
        updateProgress();
        fillReviewCards();
    });

    prevStep5Btn.addEventListener('click', () => {
        saveStep5Data();
        formSteps[currentStep].style.display = 'none';
        currentStep--;
        formSteps[currentStep].style.display = 'block';
        loadStep4Data();
        updateProgress();
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
            [typeHosp, dateInput, timeInput, medecin], // Step 1 
            [nomOrgaSocial, numSecuSocial, isAssure, isADL, nomMutuelle, numAdherent, chambre], // Step 2
            step3Fields,// Step 3 
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

    function fillReviewCards() {
        let saved = sessionStorage.getItem('step3Data');
        if (!saved) return;
        const data3 = JSON.parse(saved);
        // Information du patient
        document.getElementById('cardNom').textContent = data3.nomNaissance;
        document.getElementById('cardPrenom').textContent = data3.prenom;
        document.getElementById('cardDOB').textContent = data3.datenaissance;
        document.getElementById('cardEpouse').textContent = data3.nomEpouse || 'Pas Marié';
        document.getElementById('cardAdresse').textContent = data3.addresse;
        document.getElementById('cardCP').textContent = data3.cp;
        document.getElementById('cardVille').textContent = data3.ville;
        document.getElementById('cardTel').textContent = data3.telephone;
        document.getElementById('cardMail').textContent = data3.mail;

        saved = sessionStorage.getItem('step2Data');
        if (!saved) return;
        const data2 = JSON.parse(saved);
        // Couverture Social du Patient
        document.getElementById('cardNomOrga').textContent = data2.nomOrgaSocial;
        document.getElementById('cardNumSecu').textContent = data2.numSecuSocial;
        document.getElementById('cardAssure').textContent = data2.isAssure == 1 ? "Oui" : "Non";
        document.getElementById('cardADL').textContent = data2.isADL == 1 ? "Oui" : "Non";
        document.getElementById('cardNomMut').textContent = data2.nomMutuelle;
        document.getElementById('cardNumMut').textContent = data2.numAdherent;
        document.getElementById('cardChambre').textContent = data2.chambre;

        saved = sessionStorage.getItem('step5Data');
        if (!saved) return;
        const data5 = JSON.parse(saved);
        // Documents
        document.getElementById('cardCI').textContent = data5.CI ? "Présent" : "";
        document.getElementById('cardCV').textContent = data5.CV ? "Présent" : "";
        document.getElementById('cardCM').textContent = data5.CM ? "Présent" : "";
        document.getElementById('cardLF').textContent = data5.LF ? "Présent" : "Pas Besoin";
        document.getElementById('cardAS').textContent = data5.AS ? "Présent" : "Pas Besoin";
        document.getElementById('cardDJ').textContent = data5.DJ ? "Présent" : "Pas Besoin";

        saved = sessionStorage.getItem('step4Data');
        if (!saved) return;
        const data4 = JSON.parse(saved);
        // Personne à Prev/Confiance/Responsable
        document.getElementById('cardNomPrev').textContent = data4.NomPP;
        document.getElementById('cardPrenomPrev').textContent = data4.PrenomPP;
        document.getElementById('cardTelPrev').textContent = data4.TelPP;
        document.getElementById('cardRuePrev').textContent = data4.RuePP;
        document.getElementById('cardCPPrev').textContent = data4.CPPP;
        document.getElementById('cardVillePrev').textContent = data4.VillePP;
        if(data4.NomPC) {
            document.getElementById('cardNomConf').textContent = data4.NomPC;
            document.getElementById('cardPrenomConf').textContent = data4.PrenomPC;
            document.getElementById('cardTelConf').textContent = data4.TelPC;
            document.getElementById('cardRueConf').textContent = data4.RuePC;
            document.getElementById('cardCPConf').textContent = data4.CPPC;
            document.getElementById('cardVilleConf').textContent = data4.VillePC;
        } else {
            document.getElementById('cardConf').style.display = 'none';
        }
        if (data4.NomResp) {
            document.getElementById('cardNomResp').textContent = data4.NomResp;
            document.getElementById('cardPrenomResp').textContent = data4.PrenomResp;
            document.getElementById('cardTelResp').textContent = data4.TelResp;
            document.getElementById('cardRueResp').textContent = data4.RueResp;
            document.getElementById('cardCPResp').textContent = data4.CPResp;
            document.getElementById('cardVilleResp').textContent = data4.VilleResp;
        } else {
            document.getElementById('cardResp').style.display = 'none';
        }

        saved = sessionStorage.getItem('step1Data');
        if (!saved) return;
        const data1 = JSON.parse(saved);
        //Hospitalisation
        document.getElementById('cardTypeHospi').textContent = data1.typeHosp;
        document.getElementById('cardDateHospi').textContent = data1.date;
        document.getElementById('cardHeureHospi').textContent = data1.heure;
    }

    function editSection(step) {
        console.log("editSection called with:", step);
        const n = parseInt(step.replace('step',''), 10);
        if (isNaN(n)) return;
        const targetId = 'step' + n; // doit correspondre à l'id réel
        const targetEl = document.getElementById(targetId);
        if (!targetEl) return;
        formSteps.forEach(s => s.style.display = 'none');
        targetEl.style.display = 'block';
        currentStep = Array.from(formSteps).indexOf(targetEl);
        updateProgress();
    }

    window.editSection = editSection;
});
