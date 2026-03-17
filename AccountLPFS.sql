-- Compte d'authentification
CREATE USER 'AuthentificationLPFS2025'@'%' IDENTIFIED BY 'AuthLPFS2025';
GRANT SELECT ON `cliniquelpfs`.`personnel` TO `AuthentificationLPFS2025`@`%`;
GRANT SELECT ON `cliniquelpfs`.`role` TO `AuthentificationLPFS2025`@`%`;
GRANT SELECT ON `cliniquelpfs`.`utilisateurs` TO `AuthentificationLPFS2025`@`%`;

-- Compte ClaireDPT
CREATE USER `ClaireDPT_secretariat`@`%` IDENTIFIED BY 'Cl@ireDPT1993';
GRANT SELECT ON `cliniquelpfs`.`typehospitalisation` TO `ClaireDPT_secretariat`@`%`;
GRANT SELECT ON `cliniquelpfs`.`personnel` TO `ClaireDPT_secretariat`@`%`;
GRANT SELECT, INSERT, UPDATE, DELETE ON `cliniquelpfs`.`couverturesocial` TO `ClaireDPT_secretariat`@`%`;
GRANT SELECT ON `cliniquelpfs`.`civilité` TO `ClaireDPT_secretariat`@`%`;
GRANT SELECT ON `cliniquelpfs`.`chambre` TO `ClaireDPT_secretariat`@`%`;
GRANT SELECT ON `cliniquelpfs`.`typechambre` TO `ClaireDPT_secretariat`@`%`;
GRANT SELECT, INSERT, UPDATE, DELETE ON `cliniquelpfs`.`personne_prevenir` TO `ClaireDPT_secretariat`@`%`;
GRANT SELECT ON `cliniquelpfs`.`role` TO `ClaireDPT_secretariat`@`%`;
GRANT SELECT, INSERT, UPDATE, DELETE ON `cliniquelpfs`.`patient` TO `ClaireDPT_secretariat`@`%`;
GRANT SELECT, INSERT, UPDATE, DELETE ON `cliniquelpfs`.`hospitalisation` TO `ClaireDPT_secretariat`@`%`;
GRANT SELECT, INSERT, UPDATE, DELETE ON `cliniquelpfs`.`preadmission` TO `ClaireDPT_secretariat`@`%`;
GRANT SELECT, INSERT, UPDATE, DELETE ON `cliniquelpfs`.`soustutellede` TO `ClaireDPT_secretariat`@`%`;
GRANT SELECT, INSERT, UPDATE, DELETE ON `cliniquelpfs`.`piecesjoints` TO `ClaireDPT_secretariat`@`%`;
GRANT SELECT, INSERT, UPDATE, DELETE ON `cliniquelpfs`.`responsable` TO `ClaireDPT_secretariat`@`%`;
GRANT SELECT ON `cliniquelpfs`.`service` TO `ClaireDPT_secretariat`@`%`;

-- Compte ADMINLPFS
CREATE USER `ADMINLPFS`@`%` IDENTIFIED BY 'ADMINLPFS2025';
GRANT CREATE USER ON *.* TO `ADMINLPFS`@`%` IDENTIFIED BY PASSWORD 'ADMINLPFS2025';
GRANT ALL PRIVILEGES ON `cliniquelpfs`.* TO `ADMINLPFS`@`%` WITH GRANT OPTION;
