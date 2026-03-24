document.addEventListener('DOMContentLoaded', () => {

    const permissionsMap = {
        "1": "ADMIN : Accès total sur toutes les tables.",
        "2": "SECRETAIRE : Lecture/Écriture sur les dossiers patients.",
        "3": "CHEF : Consultation complète (Lecture seule).",
        "4": "PRATICIEN : Consultation complète (Lecture seule)."
    };

    const nomInput = document.getElementById('nom');
    const prenomInput = document.getElementById('prenom');
    const roleSelect = document.getElementById('roleSelect');
    const toggleSqlCheckbox = document.getElementById('toggleSql');

    function updateLogins() {
        if (!nomInput || !prenomInput || !roleSelect) return;

        const nom = nomInput.value.trim().toLowerCase().replace(/[^a-z0-9]/g, '');
        const prenom = prenomInput.value.trim().toLowerCase().replace(/[^a-z0-9]/g, '');
        
        // On force la conversion en String pour être sûr de matcher le switch case
        const roleId = String(roleSelect.value); 

        let prefix = "";
        switch (roleId) {
            case "1": prefix = "adm_"; break;
            case "2": prefix = "sec_"; break;
            case "3": prefix = "chs_"; break;
            case "4": prefix = "med_"; break;
            default: prefix = ""; // Aucun préfixe si inconnu
        }

        if (nom && prenom) {
            const usernameField = document.getElementById('db_username');
            if (usernameField) {
                usernameField.value = prefix + prenom.charAt(0) + nom;
            }

            const emailField = document.getElementById('email_portail');
            if (emailField && (!emailField.value || emailField.value.includes('@LPFclinique8.com'))) {
                emailField.value = prenom + "." + nom + "@LPFclinique8.com";
            }
        }

        const rightsDesc = document.getElementById('rightsDesc');
        if (rightsDesc) {
            rightsDesc.innerText = permissionsMap[roleId] || "Veuillez sélectionner un rôle.";
        }
    }

    function toggleAccessSection() {
        const section = document.getElementById('accessSection');
        if (!toggleSqlCheckbox || !section) return;

        const isChecked = toggleSqlCheckbox.checked;
        section.style.opacity = isChecked ? "1" : "0.2";
        section.style.pointerEvents = isChecked ? "auto" : "none";

        section.querySelectorAll('input').forEach(input => {
            if (!input.classList.contains('readonly-input')) {
                input.required = isChecked;
            }
        });
    }

    // Déclencheurs automatiques dès que l'admin tape ou sélectionne quelque chose
    if (nomInput) nomInput.addEventListener('input', updateLogins);
    if (prenomInput) prenomInput.addEventListener('input', updateLogins);
    if (roleSelect) roleSelect.addEventListener('change', updateLogins);
    if (toggleSqlCheckbox) toggleSqlCheckbox.addEventListener('change', toggleAccessSection);

    // Initialisation au chargement de la page
    updateLogins();
    toggleAccessSection();
});