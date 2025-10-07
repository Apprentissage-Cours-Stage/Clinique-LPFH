-- phpMyAdmin SQL Dump
-- version 5.2.1deb1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le : mar. 07 oct. 2025 à 06:38
-- Version du serveur : 10.11.4-MariaDB-1~deb12u1
-- Version de PHP : 8.2.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `CliniqueLPFS`
--

-- --------------------------------------------------------

--
-- Structure de la table `Chambre`
--

CREATE TABLE `Chambre` (
  `NumeroChambre` int(11) NOT NULL,
  `ID_Etage` int(11) NOT NULL,
  `ID_TypeChambre` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `Civilité`
--

CREATE TABLE `Civilité` (
  `ID_Civilité` int(11) NOT NULL,
  `Libellé_Civilité` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `Civilité`
--

INSERT INTO `Civilité` (`ID_Civilité`, `Libellé_Civilité`) VALUES
(1, 'Monsieur'),
(2, 'Madame');

-- --------------------------------------------------------

--
-- Structure de la table `CouvertureSocial`
--

CREATE TABLE `CouvertureSocial` (
  `Numero_Sec_Social` varchar(20) NOT NULL,
  `Nom_OrganismeSecuSocial` varchar(150) NOT NULL,
  `Patient_Assuré` tinyint(1) NOT NULL,
  `Patient_ADL` tinyint(1) NOT NULL,
  `Nom_Mutuelle` varchar(150) NOT NULL,
  `Numéro_Adhérent` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `Hospitalisation`
--

CREATE TABLE `Hospitalisation` (
  `ID_Hospitalisation` int(11) NOT NULL,
  `Date_Hospitalisation` date NOT NULL,
  `Heure_Hospitalisation` time NOT NULL,
  `TypeHospitalisation` int(11) NOT NULL,
  `ChambreOccupé` int(11) NOT NULL,
  `Medecin_En_Charge` int(11) NOT NULL,
  `ID_Patient` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `Patient`
--

CREATE TABLE `Patient` (
  `Num_SecuSocial_Patient` varchar(20) NOT NULL,
  `Civilité_Patient` int(11) NOT NULL,
  `Nom_Naissance` varchar(150) NOT NULL,
  `Nom_Epouse` varchar(150) DEFAULT NULL,
  `Prénom_Patient` varchar(150) NOT NULL,
  `Date_Naissance` date NOT NULL,
  `Num_Adresse` int(11) NOT NULL,
  `Rue_Adresse` varchar(150) NOT NULL,
  `Code_Postal` int(11) NOT NULL,
  `Ville_Adresse` varchar(100) NOT NULL,
  `Adresse_Mail` varchar(150) NOT NULL,
  `SousTutelle` tinyint(1) NOT NULL,
  `Telephone_Patient` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `Personnel`
--

CREATE TABLE `Personnel` (
  `ID_Personnel` int(11) NOT NULL,
  `Nom_Personnel` varchar(150) NOT NULL,
  `Prénom_Personnel` varchar(150) NOT NULL,
  `Role_Personnel` int(11) NOT NULL,
  `ID_Service` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `Personnel`
--

INSERT INTO `Personnel` (`ID_Personnel`, `Nom_Personnel`, `Prénom_Personnel`, `Role_Personnel`, `ID_Service`) VALUES
(1, 'ADMIN', 'ADMIN', 1, 1),
(2, 'Dupont', 'Claire', 2, 1);

-- --------------------------------------------------------

--
-- Structure de la table `Personne_prevenir`
--

CREATE TABLE `Personne_prevenir` (
  `ID_Personne` int(11) NOT NULL,
  `Nom_Pers` varchar(150) NOT NULL,
  `Prénom_Pers` varchar(150) NOT NULL,
  `Telephone_Pers` varchar(10) NOT NULL,
  `Num_Adresse` int(11) NOT NULL,
  `Rue_Adresse` varchar(150) NOT NULL,
  `Ville_Adresse` varchar(100) NOT NULL,
  `Code_Postal_Pers` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `PiecesJoints`
--

CREATE TABLE `PiecesJoints` (
  `Numéro_SecSocial_Document` varchar(20) NOT NULL,
  `Carte_Identité` longblob NOT NULL,
  `Carte_Vitale` longblob NOT NULL,
  `Carte_mutuelle` longblob NOT NULL,
  `Livret_Famille` longblob DEFAULT NULL,
  `Autorisation_soin` longblob DEFAULT NULL,
  `Decision_juge` longblob DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `PreAdmission`
--

CREATE TABLE `PreAdmission` (
  `Id_PreAdmin` int(11) NOT NULL,
  `Patient_PreAdmi` varchar(20) NOT NULL,
  `Hospitalisation_PreAdmi` int(11) NOT NULL,
  `Personne_aprev` int(11) NOT NULL,
  `Personne_deconf` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `Responsable`
--

CREATE TABLE `Responsable` (
  `Id_Responsable` int(11) NOT NULL,
  `Nom_Responsable` varchar(150) NOT NULL,
  `Prenom_Responsable` varchar(150) NOT NULL,
  `Telephone_Responsable` varchar(10) NOT NULL,
  `AdresseMail_Responsable` varchar(150) NOT NULL,
  `Num_Adresse_Responsable` int(11) NOT NULL,
  `Rue_Responsable` varchar(150) NOT NULL,
  `Ville_Responsable` varchar(100) NOT NULL,
  `Code_Postal_Responsable` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `Role`
--

CREATE TABLE `Role` (
  `ID_Role` int(11) NOT NULL,
  `Libellé_Role` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `Role`
--

INSERT INTO `Role` (`ID_Role`, `Libellé_Role`) VALUES
(1, 'Administrateur'),
(2, 'Secrétaire');

-- --------------------------------------------------------

--
-- Structure de la table `Service`
--

CREATE TABLE `Service` (
  `ID_Service` int(11) NOT NULL,
  `Libellé_Service` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `Service`
--

INSERT INTO `Service` (`ID_Service`, `Libellé_Service`) VALUES
(1, 'Administratif');

-- --------------------------------------------------------

--
-- Structure de la table `SousTutellede`
--

CREATE TABLE `SousTutellede` (
  `ID_Responsable` int(11) NOT NULL,
  `ID_Patient` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `TypeChambre`
--

CREATE TABLE `TypeChambre` (
  `ID_TypeChambre` int(11) NOT NULL,
  `Type_Chambre` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `TypeHospitalisation`
--

CREATE TABLE `TypeHospitalisation` (
  `ID_TypeHopsitalisation` int(11) NOT NULL,
  `Libellé_TypeHospitalisation` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `TypeHospitalisation`
--

INSERT INTO `TypeHospitalisation` (`ID_TypeHopsitalisation`, `Libellé_TypeHospitalisation`) VALUES
(1, 'Ambulatoire Chirurgie'),
(2, 'Hospitalisation (Au moins une nuit)');

-- --------------------------------------------------------

--
-- Structure de la table `Utilisateurs`
--

CREATE TABLE `Utilisateurs` (
  `ID_Employé` int(11) NOT NULL,
  `Identifiant_User` text NOT NULL,
  `CompteSQL` varchar(50) DEFAULT NULL,
  `MDP` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `Utilisateurs`
--

INSERT INTO `Utilisateurs` (`ID_Employé`, `Identifiant_User`, `CompteSQL`, `MDP`) VALUES
(1, 'Admin.admin@clinique-lpfs.com', NULL, 'ADMINLPFS2025'),
(2, 'claire.dupont@clinique-lpfs.com', NULL, 'Cl@ireDPT1993');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `Chambre`
--
ALTER TABLE `Chambre`
  ADD PRIMARY KEY (`NumeroChambre`),
  ADD KEY `ID_TypeChambre` (`ID_TypeChambre`);

--
-- Index pour la table `Civilité`
--
ALTER TABLE `Civilité`
  ADD PRIMARY KEY (`ID_Civilité`);

--
-- Index pour la table `CouvertureSocial`
--
ALTER TABLE `CouvertureSocial`
  ADD PRIMARY KEY (`Numero_Sec_Social`),
  ADD KEY `Numero_Sec_Social` (`Numero_Sec_Social`);

--
-- Index pour la table `Hospitalisation`
--
ALTER TABLE `Hospitalisation`
  ADD PRIMARY KEY (`ID_Hospitalisation`),
  ADD KEY `ForeignsKeys` (`TypeHospitalisation`,`Medecin_En_Charge`,`ID_Patient`) USING BTREE,
  ADD KEY `Medecin_En_Charge` (`Medecin_En_Charge`),
  ADD KEY `ChambreOccupé` (`ChambreOccupé`),
  ADD KEY `ID_Patient` (`ID_Patient`);

--
-- Index pour la table `Patient`
--
ALTER TABLE `Patient`
  ADD PRIMARY KEY (`Num_SecuSocial_Patient`),
  ADD KEY `Civilité_Patient` (`Civilité_Patient`);

--
-- Index pour la table `Personnel`
--
ALTER TABLE `Personnel`
  ADD PRIMARY KEY (`ID_Personnel`),
  ADD KEY `ForeignsKeys` (`Role_Personnel`,`ID_Service`) USING BTREE,
  ADD KEY `ID_Service` (`ID_Service`);

--
-- Index pour la table `Personne_prevenir`
--
ALTER TABLE `Personne_prevenir`
  ADD PRIMARY KEY (`ID_Personne`);

--
-- Index pour la table `PiecesJoints`
--
ALTER TABLE `PiecesJoints`
  ADD PRIMARY KEY (`Numéro_SecSocial_Document`),
  ADD KEY `Numéro_SecSocial_Document` (`Numéro_SecSocial_Document`);

--
-- Index pour la table `PreAdmission`
--
ALTER TABLE `PreAdmission`
  ADD PRIMARY KEY (`Id_PreAdmin`),
  ADD KEY `Patient_PreAdmi` (`Patient_PreAdmi`,`Hospitalisation_PreAdmi`,`Personne_aprev`,`Personne_deconf`),
  ADD KEY `Hospitalisation_PreAdmi` (`Hospitalisation_PreAdmi`),
  ADD KEY `Personne_aprev` (`Personne_aprev`),
  ADD KEY `Personne_deconf` (`Personne_deconf`);

--
-- Index pour la table `Responsable`
--
ALTER TABLE `Responsable`
  ADD PRIMARY KEY (`Id_Responsable`);

--
-- Index pour la table `Role`
--
ALTER TABLE `Role`
  ADD PRIMARY KEY (`ID_Role`);

--
-- Index pour la table `Service`
--
ALTER TABLE `Service`
  ADD PRIMARY KEY (`ID_Service`);

--
-- Index pour la table `SousTutellede`
--
ALTER TABLE `SousTutellede`
  ADD PRIMARY KEY (`ID_Responsable`,`ID_Patient`),
  ADD KEY `ID_Responsable` (`ID_Responsable`,`ID_Patient`),
  ADD KEY `ID_Patient` (`ID_Patient`);

--
-- Index pour la table `TypeChambre`
--
ALTER TABLE `TypeChambre`
  ADD PRIMARY KEY (`ID_TypeChambre`);

--
-- Index pour la table `TypeHospitalisation`
--
ALTER TABLE `TypeHospitalisation`
  ADD PRIMARY KEY (`ID_TypeHopsitalisation`);

--
-- Index pour la table `Utilisateurs`
--
ALTER TABLE `Utilisateurs`
  ADD PRIMARY KEY (`ID_Employé`),
  ADD KEY `ID_Employé` (`ID_Employé`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `Civilité`
--
ALTER TABLE `Civilité`
  MODIFY `ID_Civilité` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `Hospitalisation`
--
ALTER TABLE `Hospitalisation`
  MODIFY `ID_Hospitalisation` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `Personnel`
--
ALTER TABLE `Personnel`
  MODIFY `ID_Personnel` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `Personne_prevenir`
--
ALTER TABLE `Personne_prevenir`
  MODIFY `ID_Personne` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `PreAdmission`
--
ALTER TABLE `PreAdmission`
  MODIFY `Id_PreAdmin` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `Responsable`
--
ALTER TABLE `Responsable`
  MODIFY `Id_Responsable` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `Role`
--
ALTER TABLE `Role`
  MODIFY `ID_Role` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `Service`
--
ALTER TABLE `Service`
  MODIFY `ID_Service` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `TypeHospitalisation`
--
ALTER TABLE `TypeHospitalisation`
  MODIFY `ID_TypeHopsitalisation` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `Chambre`
--
ALTER TABLE `Chambre`
  ADD CONSTRAINT `Chambre_ibfk_1` FOREIGN KEY (`ID_TypeChambre`) REFERENCES `TypeChambre` (`ID_TypeChambre`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `CouvertureSocial`
--
ALTER TABLE `CouvertureSocial`
  ADD CONSTRAINT `CouvertureSocial_ibfk_1` FOREIGN KEY (`Numero_Sec_Social`) REFERENCES `Patient` (`Num_SecuSocial_Patient`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `Hospitalisation`
--
ALTER TABLE `Hospitalisation`
  ADD CONSTRAINT `Hospitalisation_ibfk_1` FOREIGN KEY (`TypeHospitalisation`) REFERENCES `TypeHospitalisation` (`ID_TypeHopsitalisation`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `Hospitalisation_ibfk_2` FOREIGN KEY (`Medecin_En_Charge`) REFERENCES `Personnel` (`ID_Personnel`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `Hospitalisation_ibfk_3` FOREIGN KEY (`ID_Patient`) REFERENCES `Patient` (`Num_SecuSocial_Patient`) ON UPDATE CASCADE,
  ADD CONSTRAINT `Hospitalisation_ibfk_6` FOREIGN KEY (`ChambreOccupé`) REFERENCES `Chambre` (`NumeroChambre`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `Patient`
--
ALTER TABLE `Patient`
  ADD CONSTRAINT `Civilité_ibfk1` FOREIGN KEY (`Civilité_Patient`) REFERENCES `Civilité` (`ID_Civilité`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `Personnel`
--
ALTER TABLE `Personnel`
  ADD CONSTRAINT `Personnel_ibfk_1` FOREIGN KEY (`Role_Personnel`) REFERENCES `Role` (`ID_Role`) ON UPDATE CASCADE,
  ADD CONSTRAINT `Personnel_ibfk_2` FOREIGN KEY (`ID_Service`) REFERENCES `Service` (`ID_Service`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `PiecesJoints`
--
ALTER TABLE `PiecesJoints`
  ADD CONSTRAINT `PiecesJoints_ibfk_1` FOREIGN KEY (`Numéro_SecSocial_Document`) REFERENCES `Patient` (`Num_SecuSocial_Patient`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `PreAdmission`
--
ALTER TABLE `PreAdmission`
  ADD CONSTRAINT `PreAdmission_ibfk_1` FOREIGN KEY (`Patient_PreAdmi`) REFERENCES `Patient` (`Num_SecuSocial_Patient`) ON UPDATE CASCADE,
  ADD CONSTRAINT `PreAdmission_ibfk_2` FOREIGN KEY (`Hospitalisation_PreAdmi`) REFERENCES `Hospitalisation` (`ID_Hospitalisation`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `PreAdmission_ibfk_3` FOREIGN KEY (`Personne_aprev`) REFERENCES `Personne_prevenir` (`ID_Personne`) ON UPDATE CASCADE,
  ADD CONSTRAINT `PreAdmission_ibfk_4` FOREIGN KEY (`Personne_deconf`) REFERENCES `Personne_prevenir` (`ID_Personne`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `SousTutellede`
--
ALTER TABLE `SousTutellede`
  ADD CONSTRAINT `SousTutelleDe_ibfk_1` FOREIGN KEY (`ID_Responsable`) REFERENCES `Responsable` (`Id_Responsable`) ON UPDATE CASCADE,
  ADD CONSTRAINT `SousTutelleDe_ibfk_2` FOREIGN KEY (`ID_Patient`) REFERENCES `Patient` (`Num_SecuSocial_Patient`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `Utilisateurs`
--
ALTER TABLE `Utilisateurs`
  ADD CONSTRAINT `Utilisateurs_ibfk_1` FOREIGN KEY (`ID_Employé`) REFERENCES `Personnel` (`ID_Personnel`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
