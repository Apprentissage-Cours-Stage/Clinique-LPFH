<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Fiche de Pré-Admission - Clinique LPFS</title>
    <style>
        @page { margin: 40px; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #2c3e50;
            font-size: 11px;
            line-height: 1.4;
        }
        /* --- En-tête --- */
        .header {
            text-align: center;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 22px;
            margin: 0;
            color: #2c3e50;
            text-transform: uppercase;
        }
        .header p {
            margin: 5px 0 0;
            color: #7f8c8d;
            font-size: 10px;
        }

        /* --- Blocs Sections --- */
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            background-color: #34495e;
            color: white;
            padding: 6px 10px;
            font-size: 12px;
            font-weight: bold;
            border-radius: 3px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        /* --- Tableaux de données --- */
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th, table td {
            padding: 8px 10px;
            border: 1px solid #bdc3c7;
            text-align: left;
            vertical-align: top;
        }
        table th {
            background-color: #f9f9f9;
            color: #2c3e50;
            width: 30%;
            font-weight: bold;
            font-size: 11px;
        }

        /* --- Pied de page --- */
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 9px;
            color: #7f8c8d;
            border-top: 1px solid #bdc3c7;
            padding-top: 10px;
        }
        
        .badge {
            display: inline-block;
            padding: 3px 6px;
            background: #e67e22;
            color: white;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Fiche de Pré-Admission Hospitalière</h1>
        <p>Généré informatiquement le <?= date('d/m/Y à H:i') ?> | Clinique LPFS</p>
    </div>

    <div class="section">
        <div class="section-title">I. Identité du Patient</div>
        <table>
            <tr>
                <th>Numéro de Sécurité Sociale</th>
                <td><strong><?= htmlspecialchars($data['Num_SecuSocial_Patient'] ?? 'N/A') ?></strong></td>
            </tr>
            <tr>
                <th>Nom et Prénom</th>
                <td>
                    <?= htmlspecialchars($data['Libellé_Civilité'] ?? '') ?> 
                    <?= htmlspecialchars(!empty($data['Nom_Epouse']) ? $data['Nom_Epouse'] : ($data['Nom_Naissance'] ?? '')) ?> 
                    <?= htmlspecialchars($data['Prénom_Patient'] ?? '') ?>
                </td>
            </tr>
            <tr>
                <th>Date de Naissance</th>
                <td><?= !empty($data['Date_Naissance']) ? date('d/m/Y', strtotime($data['Date_Naissance'])) : 'N/A' ?></td>
            </tr>
            <tr>
                <th>Coordonnées de Contact</th>
                <td>
                    📞 <?= htmlspecialchars($data['Telephone_Patient'] ?? 'Non renseigné') ?><br>
                    ✉️ <?= htmlspecialchars($data['Adresse_Mail'] ?? 'Non renseigné') ?>
                </td>
            </tr>
            <tr>
                <th>Adresse Domiciliaire</th>
                <td>
                    <?= htmlspecialchars($data['Num_Adresse'] ?? '') ?> <?= htmlspecialchars($data['Rue_Adresse'] ?? '') ?><br>
                    <?= htmlspecialchars($data['Code_Postal'] ?? '') ?> <?= htmlspecialchars($data['Ville_Adresse'] ?? '') ?>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">II. Couverture Sociale et Mutuelle</div>
        <table>
            <tr>
                <th>Organisme Sécurité Sociale</th>
                <td><?= htmlspecialchars($data['Nom_OrganismeSecuSocial'] ?? 'Non renseigné') ?></td>
            </tr>
            <tr>
                <th>Prise en Charge Spécifique</th>
                <td>
                    L'assuré est le patient : <?= ($data['Patient_Assuré'] == 1) ? 'Oui' : 'Non' ?><br>
                    Prise en charge ALD (100%) : <?= ($data['Patient_ADL'] == 1) ? 'Oui' : 'Non' ?>
                </td>
            </tr>
            <tr>
                <th>Complémentaire (Mutuelle)</th>
                <td>
                    Nom : <?= htmlspecialchars($data['Nom_Mutuelle'] ?? 'Aucune') ?><br>
                    Numéro Adhérent : <?= htmlspecialchars($data['Numéro_Adhérent'] ?? 'N/A') ?>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">III. Séjour Hospitalier Prévu</div>
        <table>
            <tr>
                <th>Date et Heure d'Admission</th>
                <td>
                    <strong>
                        <?= !empty($data['Date_Hospitalisation']) ? date('d/m/Y', strtotime($data['Date_Hospitalisation'])) : 'N/A' ?> 
                        à <?= htmlspecialchars($data['Heure_Hospitalisation'] ?? 'N/A') ?>
                    </strong>
                </td>
            </tr>
            <tr>
                <th>Mode d'Hospitalisation</th>
                <td><?= htmlspecialchars($data['Libellé_TypeHospitalisation'] ?? 'Non renseigné') ?></td>
            </tr>
            <tr>
                <th>Médecin Praticien Référent</th>
                <td>Dr. <?= htmlspecialchars(($data['Nom_Personnel'] ?? '') . ' ' . ($data['Prenom_Personnel'] ?? '')) ?></td>
            </tr>
            <tr>
                <th>Hébergement attribué</th>
                <td>
                    <?= !empty($data['ChambreOccupé']) ? 'Chambre N° ' . htmlspecialchars($data['ChambreOccupé']) : '<em>Attribution en cours (Accueil)</em>' ?>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">IV. Personnes de l'Entourage et Responsabilité</div>
        <table>
            <tr>
                <th>Personne à Prévenir</th>
                <td>
                    <strong><?= htmlspecialchars(($data['Nom_Pers'] ?? '') . ' ' . ($data['Prénom_Pers'] ?? '')) ?></strong><br>
                    📞 Tel : <?= htmlspecialchars($data['Telephone_Pers'] ?? 'N/A') ?><br>
                    📍 Adresse : <?= htmlspecialchars(($data['Num_Add_PP'] ?? '') . ' ' . ($data['Rue_Add_PP'] ?? '') . ' ' . ($data['Code_Postal_Pers'] ?? '') . ' ' . ($data['Ville_Add_PP'] ?? '')) ?>
                </td>
            </tr>
            <?php if (!empty($data['Nom_Responsable'])): ?>
            <tr>
                <th>Responsable Légal (Sous Tutelle)</th>
                <td>
                    <strong><?= htmlspecialchars($data['Nom_Responsable'] . ' ' . $data['Prenom_Responsable']) ?></strong><br>
                    📞 Tel : <?= htmlspecialchars($data['Telephone_Responsable']) ?><br>
                    ✉️ Mail : <?= htmlspecialchars($data['AdresseMail_Responsable']) ?>
                </td>
            </tr>
            <?php endif; ?>
        </table>
    </div>

    <div class="footer">
        Établissement de Santé Clinique LPFS - Document strictement confidentiel soumis au secret médical partagé.
    </div>

</body>
</html>