# Système de Pré-admission Clinique

> **Plateforme web de gestion et de suivi des pré-admissions hospitalières.**
> Ce projet permet de digitaliser le parcours d'admission des patients, de l'inscription administrative jusqu'au suivi par l'équipe médicale et les chefs de service.

---

## Sommaire
1. [Fonctionnalités réalisées](#fonctionnalités-réalisées)
2. [Architecture du projet](#architecture-du-projet)
3. [Charte Graphique & Design](#charte-graphique--design)
4. [Base de données (Schéma Relationnel)](#base-de-données-schéma-relationnel)
5. [Cycle de vie d'une pré-admission (Workflow)](#cycle-de-vie-dune-pré-admission-workflow)
6. [Installation & Déploiement](#installation--déploiement)
7. [Sécurité appliquée](#sécurité-appliquée)

---

## Fonctionnalités réalisées

### Espace Patient & Saisie (Multi-étapes)
- **Formulaire dynamique en 5 étapes** avec barre de progression :
  1. Informations personnelles du patient (Civilité, Coordonnées).
  2. Couverture sociale (Sécurité sociale, Mutuelle, ALD).
  3. Planification d'hospitalisation (Date, Heure, Type, Médecin, Chambre).
  4. Personnes à prévenir / de confiance / tuteur légal.
  5. Téléversement des documents obligatoires (CNI, Carte Vitale, Mutuelle, etc.).
- **Prévisualisation en direct** des documents importés (images, PDF).
- **Vérification automatique** de la concordance des champs (bloque les dates/heures passées, etc.).

### Espace Praticien (Médecin)
- Visualisation de la liste des patients rattachés **uniquement à son service**.
- Séparation automatique entre :
  - Les pré-admissions **à venir**.
  - Les pré-admissions **passées/terminées**.

### Espace Chef de Service
- **Filtrage par mois** d'admission dynamique.
- Visualisation globale du personnel médical de son service.
- **Export Excel automatisé** :
  - Génération de plannings via une classe Orientée Objet (`AdmissionExcelExporter` avec PhpSpreadsheet).
  - Fusion intelligente des cellules par médecin traitant (Row-spanning).

### Espace Administrateur
- Visualisation de la liste globale des employés.

---

## Architecture du projet

Le projet est basé sur une architecture modulaire **PHP (MySQLi) / JS / CSS standard**, avec séparation des vues et des utilitaires de traitement.

```text
CLINIQUE_LPFS/
├──  CSS/                         # Fichiers de styles globaux et spécifiques
│   ├── index.css                   # Page de connexion
│   ├── dashboard.css               # Acceuil des dashboard
│   ├── add-admission.css           # Formulaire de pré-admission (stepper)
│   ├── list-admission.css          # Listes des admissions (cartes et filtres)
│   ├── add-service.css             # Formulaire d'ajout de service
│   ├── edit-service.css            # Formulaire de modification de service
│   ├── list-service.css            # Liste des service
│   ├── add-user.css                # Formulaire d'ajout d'employées et d'accès
│   ├── edit-user.css               # Formulaire de modification du personnel
│   └── list-user.css               # Cartes du personnel
│
├──  INCLUDES/                   # Fichiers partagés et configuration
│   ├── db.php                      # Connexion sécurisée à la Base de données
│   ├── header.php                  # Barre de navigation globale
│   ├── submitBDD.php               # Envoie des données de la pré-admissions
│   ├── ficheP_template.php         # Fichier template des fiche d'
│   ├── exportExcel.php             # Fichier d'initilisation de la classe Excel et de récupération de données
│   ├── generatePDF.php             # Fichier d'initilisation de la classe PDF et de récupération de données
│   ├── CSS/                        # Styles du header
│   ├── ICONS/                      # Icônes de l'interface (pdf, delete, edit)
│   ├── IMAGES/                     # Images de l'interface (logo, ...)
│   ├── SERVICES/                   # Classes POO (Excel/PDF)
│   │   ├── PDFService.php              # Classe de création de PDF
│   │   └── ExcelExporterService.php    # Classe de création de fichier Excel
│   └── LIBRAIRIES/vendor/          # Dépendances Composer (PhpSpreadsheet, etc.)
│
├──  JAVASCRIPT/                  # Logique Front-end
│   └── add-admission.js            # Gestion du stepper et preview fichiers
│
├──  HEAD/                       # Dossier des comptes Chef de service
│   ├── dashboard.php               # Acceuil des dashboard
│   ├── list-serviceadmission.php   # Listes des admissions du services (cartes et filtres)
│   └── list-serviceuser.php        # Cartes du personnel du service
│
├──  SECRETARY/                  # Dossier des comptes Secretaires
│   ├── dashboard.php               # Acceuil des dashboard
│   ├── add-admission.php           # Page du formulaire d'admission
│   └── list-admission.php          # Vue médecin et chef de service
│
├──  DOCTOR/                     # Dossier des comptes Médecins
│   ├── dashboard.php               # Acceuil des dashboard
│   └── list-ownadmission.php       # Listes des admissions du medecin (cartes et filtres)
│
├──  ADMIN/                      # Dossier des comptes Administrateur
│   ├── dashboard.php               # Acceuil des dashboard
│   ├── add-admission.php           # Formulaire de pré-admission (stepper)
│   ├── list-admission.php          # Listes des admissions
│   ├── add-service.php             # Formulaire d'ajout de service
│   ├── edit-service.php            # Formulaire de modification de service
│   ├── delete-service.php          # Formulaire de suppresion de service
│   ├── list-service.php            # Liste des service
│   ├── add-user.php                # Formulaire d'ajout d'employées et d'accès
│   ├── edit-user.php               # Formulaire de modification du personnel
│   ├── delete-user.php             # Formulaire de suppresion du personnel
│   └── list-user.php               # Cartes du personnel
│
├──  .gitignore                   # Fichiers ignorés par Git (Uploads, vendor, etc.)
├──  composer.json                # Fichier de dépendance PHP
├──  index.php                    # Page de connexion au portail
├──  logout.php                   # Fichier de deconnexion
└──  README.md                    # Documentation du projet (ce fichier)
```

## 🎨 Charte Graphique & Design

L'identité visuelle est épurée, moderne et orientée vers le secteur médical :
- **Couleurs principales :** Bleu primaire (`#007bff`) et Bleu profond (`#005f99`).
- **Couleurs de fond :** Dégradés très clairs pour la lisibilité (`#e9f4ff` vers `#ffffff`).
- **Composants :** Cartes blanches (`.card`) avec ombres portées douces (`box-shadow`), boutons à bords arrondis (`6px`).
- **Responsive :** Grilles adaptables pour tablettes et mobiles (Media Queries flexbox).

---

## 📊 Base de données (Schéma Relationnel)

La base de données MySQL est structurée pour assurer l'intégrité des données médicales et l'étanchéité des services. Elle s'articule autour de 5 axes majeurs :

- **`patient`** : Stocke l'identité unique via le NIR (Numéro de Sécurité Sociale).
- **`personnel` & `utilisateurs`** : Gère les employés de la clinique (Praticiens, Secrétaires, Chefs, Admins) et leurs habilitations de connexion sécurisées.
- **`service` & `chambre`** : Lie le personnel et l'occupation des lits aux départements médicaux réels.
- **`hospitalisation`** : Enregistre les rendez-vous planifiés (Date, Heure, Médecin en charge).
- **`preadmission`** : Table pivot liant le patient, son hospitalisation et l'historique administratif.

---

## 🔄 Cycle de vie d'une pré-admission (Workflow)

Voici le cheminement fonctionnel d'un dossier dans l'application :

1. **Création (Secrétaire / Admin) :** Remplissage du formulaire dynamique (Stepper JS en 5 étapes) et téléversement des pièces justificatives. Le dossier est marqué "À venir".
2. **Consultation et Tri (Médecins / Chefs de service) :** Les praticiens accèdent à leur tableau de bord étanche. Les vues dynamiques séparent les dossiers du jour/futurs et archives.
3. **Pilotage (Chef de Service) :** Analyse de la charge du service via le tri mensuel et génération de rapports d'activité via la passerelle d'extraction Excel Orientée Objet.
4. **Archivage automatique :** Dès que la date d'hospitalisation est dépassée, le système bascule dynamiquement le dossier dans la catégorie "Terminée".

---

## 🔒 Sécurité appliquée

- **🔒 Requêtes préparées :** Utilisation systématique de `mysqli_prepare` et `mysqli_bind_param` sur l'ensemble des requêtes pour éliminer nativement tout risque d'injection SQL.
- **🛡️ Protection XSS :** Filtrage rigoureux des sorties HTML à l'aide de la fonction native `htmlspecialchars()`.
- **🔑 Contrôle d'accès & cloisonnement (RBAC) :** Les variables de session (`$_SESSION['user_id']`) et les rôles (`$context`) sont vérifiés au chargement de chaque page. Un utilisateur ne peut pas accéder aux dossiers (`/ADMIN`, `/HEAD`, etc.) s'il n'a pas les autorisations requises.
- **👁️ Variables masquées (RGPD) :** Les dossiers de téléversements réels et les librairies lourdes sont indexés dans le `.gitignore` pour éviter toute fuite accidentelle sur les dépôts de code centralisés (GitHub/GitLab).
- **👤 Authentification Nominative & Traçabilité :** Chaque employé possède ses propres identifiants d'accès. Le système bannit les comptes génériques partagés. Cela garantit l'imputabilité des actions (Secret Médical) et facilite la révocation des accès en cas de départ d'un agent.