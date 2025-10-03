-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : jeu. 02 oct. 2025 à 14:48
-- Version du serveur : 9.1.0
-- Version de PHP : 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `cliniquelpfs`
--

-- --------------------------------------------------------

--
-- Structure de la table `chambre`
--

DROP TABLE IF EXISTS `chambre`;
CREATE TABLE IF NOT EXISTS `chambre` (
  `NumeroChambre` int NOT NULL,
  `ID_Etage` int NOT NULL,
  `ID_TypeChambre` int NOT NULL,
  PRIMARY KEY (`NumeroChambre`),
  KEY `ID_TypeChambre` (`ID_TypeChambre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `civilité`
--

DROP TABLE IF EXISTS `civilité`;
CREATE TABLE IF NOT EXISTS `civilité` (
  `ID_Civilité` int NOT NULL AUTO_INCREMENT,
  `Libellé_Civilité` text COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`ID_Civilité`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `civilité`
--

INSERT INTO `civilité` (`ID_Civilité`, `Libellé_Civilité`) VALUES
(1, 'Monsieur'),
(2, 'Madame');

-- --------------------------------------------------------

--
-- Structure de la table `couverturesocial`
--

DROP TABLE IF EXISTS `couverturesocial`;
CREATE TABLE IF NOT EXISTS `couverturesocial` (
  `Numero_Sec_Social` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `Nom_OrganismeSecuSocial` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `Patient_Assuré` tinyint(1) NOT NULL,
  `Patient_ADL` tinyint(1) NOT NULL,
  `Nom_Mutuelle` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `Numéro_Adhérent` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`Numero_Sec_Social`),
  KEY `Numero_Sec_Social` (`Numero_Sec_Social`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `hospitalisation`
--

DROP TABLE IF EXISTS `hospitalisation`;
CREATE TABLE IF NOT EXISTS `hospitalisation` (
  `ID_Hospitalisation` int NOT NULL AUTO_INCREMENT,
  `Date_Hospitalisation` date NOT NULL,
  `Heure_Hospitalisation` time NOT NULL,
  `TypeHospitalisation` int NOT NULL,
  `ChambreOccupé` int NOT NULL,
  `Medecin_En_Charge` int NOT NULL,
  `ID_Patient` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`ID_Hospitalisation`),
  KEY `ForeignsKeys` (`TypeHospitalisation`,`Medecin_En_Charge`,`ID_Patient`) USING BTREE,
  KEY `Medecin_En_Charge` (`Medecin_En_Charge`),
  KEY `ChambreOccupé` (`ChambreOccupé`),
  KEY `ID_Patient` (`ID_Patient`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `patient`
--

DROP TABLE IF EXISTS `patient`;
CREATE TABLE IF NOT EXISTS `patient` (
  `Num_SecuSocial_Patient` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `Civilité_Patient` int NOT NULL,
  `Nom_Naissance` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `Nom_Epouse` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Prénom_Patient` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `Date_Naissance` date NOT NULL,
  `Num_Adresse` int NOT NULL,
  `Rue_Adresse` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `Code_Postal` int NOT NULL,
  `Ville_Adresse` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `Adresse_Mail` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `SousTutelle` tinyint(1) NOT NULL,
  `Telephone_Patient` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`Num_SecuSocial_Patient`),
  KEY `Civilité_Patient` (`Civilité_Patient`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `personnel`
--

DROP TABLE IF EXISTS `personnel`;
CREATE TABLE IF NOT EXISTS `personnel` (
  `ID_Personnel` int NOT NULL AUTO_INCREMENT,
  `Nom_Personnel` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `Prénom_Personnel` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `Role_Personnel` int NOT NULL,
  `ID_Service` int NOT NULL,
  PRIMARY KEY (`ID_Personnel`),
  KEY `ForeignsKeys` (`Role_Personnel`,`ID_Service`) USING BTREE,
  KEY `ID_Service` (`ID_Service`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `personnel`
--

INSERT INTO `personnel` (`ID_Personnel`, `Nom_Personnel`, `Prénom_Personnel`, `Role_Personnel`, `ID_Service`) VALUES
(1, 'ADMIN', 'ADMIN', 1, 1),
(2, 'Dupont', 'Claire', 2, 1);

-- --------------------------------------------------------

--
-- Structure de la table `personne_prevenir`
--

DROP TABLE IF EXISTS `personne_prevenir`;
CREATE TABLE IF NOT EXISTS `personne_prevenir` (
  `ID_Personne` int NOT NULL AUTO_INCREMENT,
  `Nom_Pers` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `Prénom_Pers` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `Telephone_Pers` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `Num_Adresse` int NOT NULL,
  `Rue_Adresse` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `Ville_Adresse` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `Code_Postal_Pers` varchar(5) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`ID_Personne`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `piecesjoints`
--

DROP TABLE IF EXISTS `piecesjoints`;
CREATE TABLE IF NOT EXISTS `piecesjoints` (
  `Numéro_SecSocial_Document` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `Carte_Identité` longblob NOT NULL,
  `Carte_Vitale` longblob NOT NULL,
  `Carte_mutuelle` longblob NOT NULL,
  `Livret_Famille` longblob,
  `Autorisation_soin` longblob,
  `Decision_juge` longblob,
  PRIMARY KEY (`Numéro_SecSocial_Document`),
  KEY `Numéro_SecSocial_Document` (`Numéro_SecSocial_Document`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `preadmission`
--

DROP TABLE IF EXISTS `preadmission`;
CREATE TABLE IF NOT EXISTS `preadmission` (
  `Id_PreAdmin` int NOT NULL AUTO_INCREMENT,
  `Patient_PreAdmi` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `Hospitalisation_PreAdmi` int NOT NULL,
  `Personne_aprev` int NOT NULL,
  `Personne_deconf` int NOT NULL,
  PRIMARY KEY (`Id_PreAdmin`),
  KEY `Patient_PreAdmi` (`Patient_PreAdmi`,`Hospitalisation_PreAdmi`,`Personne_aprev`,`Personne_deconf`),
  KEY `Hospitalisation_PreAdmi` (`Hospitalisation_PreAdmi`),
  KEY `Personne_aprev` (`Personne_aprev`),
  KEY `Personne_deconf` (`Personne_deconf`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `responsable`
--

DROP TABLE IF EXISTS `responsable`;
CREATE TABLE IF NOT EXISTS `responsable` (
  `Id_Responsable` int NOT NULL AUTO_INCREMENT,
  `Nom_Responsable` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `Prenom_Responsable` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `Telephone_Responsable` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `AdresseMail_Responsable` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `Num_Adresse_Responsable` int NOT NULL,
  `Rue_Responsable` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `Ville_Responsable` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `Code_Postal_Responsable` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`Id_Responsable`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `role`
--

DROP TABLE IF EXISTS `role`;
CREATE TABLE IF NOT EXISTS `role` (
  `ID_Role` int NOT NULL AUTO_INCREMENT,
  `Libellé_Role` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`ID_Role`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `role`
--

INSERT INTO `role` (`ID_Role`, `Libellé_Role`) VALUES
(1, 'Administrateur'),
(2, 'Secrétaire');

-- --------------------------------------------------------

--
-- Structure de la table `service`
--

DROP TABLE IF EXISTS `service`;
CREATE TABLE IF NOT EXISTS `service` (
  `ID_Service` int NOT NULL AUTO_INCREMENT,
  `Libellé_Service` text COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`ID_Service`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `service`
--

INSERT INTO `service` (`ID_Service`, `Libellé_Service`) VALUES
(1, 'Administratif');

-- --------------------------------------------------------

--
-- Structure de la table `soustutellede`
--

DROP TABLE IF EXISTS `soustutellede`;
CREATE TABLE IF NOT EXISTS `soustutellede` (
  `ID_Responsable` int NOT NULL,
  `ID_Patient` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`ID_Responsable`,`ID_Patient`),
  KEY `ID_Responsable` (`ID_Responsable`,`ID_Patient`),
  KEY `ID_Patient` (`ID_Patient`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `typechambre`
--

DROP TABLE IF EXISTS `typechambre`;
CREATE TABLE IF NOT EXISTS `typechambre` (
  `ID_TypeChambre` int NOT NULL,
  `Type_Chambre` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`ID_TypeChambre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `typehospitalisation`
--

DROP TABLE IF EXISTS `typehospitalisation`;
CREATE TABLE IF NOT EXISTS `typehospitalisation` (
  `ID_TypeHopsitalisation` int NOT NULL AUTO_INCREMENT,
  `Libellé_TypeHospitalisation` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`ID_TypeHopsitalisation`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `typehospitalisation`
--

INSERT INTO `typehospitalisation` (`ID_TypeHopsitalisation`, `Libellé_TypeHospitalisation`) VALUES
(1, 'Ambulatoire Chirurgie'),
(2, 'Hospitalisation (Au moins une nuit)');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

DROP TABLE IF EXISTS `utilisateurs`;
CREATE TABLE IF NOT EXISTS `utilisateurs` (
  `ID_Employé` int NOT NULL,
  `Identifiant_User` text COLLATE utf8mb4_general_ci NOT NULL,
  `MDP` text COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`ID_Employé`),
  KEY `ID_Employé` (`ID_Employé`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`ID_Employé`, `Identifiant_User`, `MDP`) VALUES
(1, 'Admin.admin@clinique-lpfs.com', 'ADMINLPFS2025'),
(2, 'claire.dupont@clinique-lpfs.com', 'Cl@ireDPT1993');

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `chambre`
--
ALTER TABLE `chambre`
  ADD CONSTRAINT `Chambre_ibfk_1` FOREIGN KEY (`ID_TypeChambre`) REFERENCES `typechambre` (`ID_TypeChambre`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `couverturesocial`
--
ALTER TABLE `couverturesocial`
  ADD CONSTRAINT `couverturesocial_ibfk_1` FOREIGN KEY (`Numero_Sec_Social`) REFERENCES `patient` (`Num_SecuSocial_Patient`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Contraintes pour la table `hospitalisation`
--
ALTER TABLE `hospitalisation`
  ADD CONSTRAINT `Hospitalisation_ibfk_1` FOREIGN KEY (`TypeHospitalisation`) REFERENCES `typehospitalisation` (`ID_TypeHopsitalisation`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `Hospitalisation_ibfk_2` FOREIGN KEY (`Medecin_En_Charge`) REFERENCES `personnel` (`ID_Personnel`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `Hospitalisation_ibfk_3` FOREIGN KEY (`ID_Patient`) REFERENCES `patient` (`Num_SecuSocial_Patient`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `Hospitalisation_ibfk_6` FOREIGN KEY (`ChambreOccupé`) REFERENCES `chambre` (`NumeroChambre`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Contraintes pour la table `patient`
--
ALTER TABLE `patient`
  ADD CONSTRAINT `Civilité_ibfk1` FOREIGN KEY (`Civilité_Patient`) REFERENCES `civilité` (`ID_Civilité`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Contraintes pour la table `personnel`
--
ALTER TABLE `personnel`
  ADD CONSTRAINT `Personnel_ibfk_1` FOREIGN KEY (`Role_Personnel`) REFERENCES `role` (`ID_Role`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `Personnel_ibfk_2` FOREIGN KEY (`ID_Service`) REFERENCES `service` (`ID_Service`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Contraintes pour la table `piecesjoints`
--
ALTER TABLE `piecesjoints`
  ADD CONSTRAINT `piecesjoints_ibfk_1` FOREIGN KEY (`Numéro_SecSocial_Document`) REFERENCES `patient` (`Num_SecuSocial_Patient`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Contraintes pour la table `preadmission`
--
ALTER TABLE `preadmission`
  ADD CONSTRAINT `preadmission_ibfk_1` FOREIGN KEY (`Patient_PreAdmi`) REFERENCES `patient` (`Num_SecuSocial_Patient`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `PreAdmission_ibfk_2` FOREIGN KEY (`Hospitalisation_PreAdmi`) REFERENCES `hospitalisation` (`ID_Hospitalisation`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `PreAdmission_ibfk_3` FOREIGN KEY (`Personne_aprev`) REFERENCES `personne_prevenir` (`ID_Personne`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `PreAdmission_ibfk_4` FOREIGN KEY (`Personne_deconf`) REFERENCES `personne_prevenir` (`ID_Personne`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Contraintes pour la table `soustutellede`
--
ALTER TABLE `soustutellede`
  ADD CONSTRAINT `SousTutelleDe_ibfk_1` FOREIGN KEY (`ID_Responsable`) REFERENCES `responsable` (`Id_Responsable`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `SousTutelleDe_ibfk_2` FOREIGN KEY (`ID_Patient`) REFERENCES `patient` (`Num_SecuSocial_Patient`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Contraintes pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD CONSTRAINT `Utilisateurs_ibfk_1` FOREIGN KEY (`ID_Employé`) REFERENCES `personnel` (`ID_Personnel`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
