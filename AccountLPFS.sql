-- Compte d'authentification
CREATE USER 'AuthentificationLPFS2025'@'%' IDENTIFIED BY 'AuthLPFS2025';
GRANT SELECT ON `CliniqueLPFS`.`Personnel` TO `AuthentificationLPFS2025`@`%`;
GRANT SELECT ON `CliniqueLPFS`.`Role` TO `AuthentificationLPFS2025`@`%`;
GRANT SELECT ON `CliniqueLPFS`.`Utilisateurs` TO `AuthentificationLPFS2025`@`%`;

-- Compte ClaireDPT
CREATE USER `ClaireDPT_secretariat`@`%` IDENTIFIED BY 'Cl@ireDPT1993';
GRANT SELECT ON `CliniqueLPFS`.`TypeHospitalisation` TO `ClaireDPT_secretariat`@`%`;
GRANT SELECT, INSERT ON `CliniqueLPFS`.`Patient` TO `ClaireDPT_secretariat`@`%`;
GRANT SELECT ON `CliniqueLPFS`.`Personnel` TO `ClaireDPT_secretariat`@`%`;
GRANT SELECT, INSERT ON `CliniqueLPFS`.`CouvertureSocial` TO `ClaireDPT_secretariat`@`%`;
GRANT SELECT ON `CliniqueLPFS`.`Civilité` TO `ClaireDPT_secretariat`@`%`;
GRANT SELECT ON `CliniqueLPFS`.`TypeChambre` TO `ClaireDPT_secretariat`@`%`;
GRANT SELECT, INSERT ON `CliniqueLPFS`.`PreAdmission` TO `ClaireDPT_secretariat`@`%`;
GRANT SELECT ON `CliniqueLPFS`.`Role` TO `ClaireDPT_secretariat`@`%`;
GRANT SELECT ON `CliniqueLPFS`.`Chambre` TO `ClaireDPT_secretariat`@`%`;
GRANT SELECT, INSERT ON `CliniqueLPFS`.`Hospitalisation` TO `ClaireDPT_secretariat`@`%`;
GRANT SELECT, INSERT ON `CliniqueLPFS`.`Personne_prevenir` TO `ClaireDPT_secretariat`@`%`;
GRANT SELECT, INSERT ON `CliniqueLPFS`.`SousTutellede` TO `ClaireDPT_secretariat`@`%`;
GRANT SELECT, INSERT ON `CliniqueLPFS`.`PiecesJoints` TO `ClaireDPT_secretariat`@`%`;
GRANT SELECT ON `CliniqueLPFS`.`Service` TO `ClaireDPT_secretariat`@`%`;
GRANT SELECT, INSERT ON `CliniqueLPFS`.`Responsable` TO `ClaireDPT_secretariat`@`%`;

-- Compte ADMINLPFS
CREATE USER `ADMINLPFS`@`%` IDENTIFIED BY `ADMINLPFS2025`;
GRANT ALL PRIVILEGES ON `CliniqueLPFS`.* TO `ADMINLPFS`@`%`;
FLUSH PRIVILEGES;