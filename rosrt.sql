
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dump della struttura del database anpr
CREATE DATABASE IF NOT EXISTS `anpr` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `anpr`;

-- Dump della struttura di tabella anpr.cam_info
CREATE TABLE IF NOT EXISTS `cam_info` (
  `ID` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT (uuid()),
  `camera_ip` char(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `camera_desc` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `zone_ID` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `support_vehiclebrand` bit(1) NOT NULL DEFAULT b'0',
  `support_vehiclecolor` bit(1) NOT NULL DEFAULT b'0',
  `hour_adjust` int NOT NULL DEFAULT '0',
  `last_timestamp` datetime DEFAULT NULL,
  `last_platenum` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `camera_ip` (`camera_ip`),
  KEY `FK_cam_info_zones` (`zone_ID`) USING BTREE,
  CONSTRAINT `FK_cam_info_zones` FOREIGN KEY (`zone_ID`) REFERENCES `zones` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella anpr.events
CREATE TABLE IF NOT EXISTS `events` (
  `ID` char(36) NOT NULL DEFAULT (uuid()),
  `timestamp` datetime NOT NULL,
  `camera_ip` char(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `plate_num` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `vehicle_brand` varchar(30) DEFAULT NULL,
  `vehicle_color` varchar(30) DEFAULT NULL,
  `filename` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `image_isnull` bit(1) DEFAULT b'0',
  `image` mediumblob,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `filename` (`filename`) USING BTREE,
  KEY `timestamp` (`timestamp`) USING BTREE,
  KEY `timestamp-plate_num-camera_ip` (`timestamp`,`plate_num`,`camera_ip`),
  KEY `plate_num` (`plate_num`),
  KEY `timestamp-camera_ip` (`timestamp`,`camera_ip`),
  KEY `camera_ip` (`camera_ip`) USING BTREE,
  KEY `timestamp-image_isnull` (`timestamp`,`image_isnull`),
  KEY `vehicle_brand` (`vehicle_brand`),
  KEY `vehicle_color` (`vehicle_color`),
  KEY `timestamp-plate_num-camera_ip-vehicle_color-vehicle_brand` (`timestamp`,`camera_ip`,`plate_num`,`vehicle_brand`,`vehicle_color`),
  CONSTRAINT `FK_events_caminfo` FOREIGN KEY (`camera_ip`) REFERENCES `cam_info` (`camera_ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella anpr.logs
CREATE TABLE IF NOT EXISTS `logs` (
  `ID` char(36) NOT NULL DEFAULT (uuid()),
  `user_ID` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `timestamp` datetime NOT NULL DEFAULT (now()),
  `log_text` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `category` varchar(256) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `FK_logs_users` (`user_ID`) USING BTREE,
  KEY `timestamp` (`timestamp`) USING BTREE,
  KEY `timestamp_users` (`user_ID`,`timestamp`) USING BTREE,
  CONSTRAINT `FK_logs_users` FOREIGN KEY (`user_ID`) REFERENCES `users` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella anpr.users
CREATE TABLE IF NOT EXISTS `users` (
  `ID` char(36) NOT NULL DEFAULT (uuid()),
  `username` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `totp` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `name` varchar(50) NOT NULL,
  `surname` varchar(50) NOT NULL,
  `email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `role` int NOT NULL DEFAULT '1',
  `group_ID` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `username` (`username`),
  KEY `FK_users_groups` (`group_ID`),
  CONSTRAINT `FK_users_groups` FOREIGN KEY (`group_ID`) REFERENCES `users_groups` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella anpr.users_groups
CREATE TABLE IF NOT EXISTS `users_groups` (
  `ID` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT (uuid()),
  `name` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `telegram_url` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di vista anpr.vw_logs
-- Creazione di una tabella temporanea per risolvere gli errori di dipendenza della vista
CREATE TABLE `vw_logs` (
	`category` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`ID` CHAR(36) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`user_ID` CHAR(36) NULL COLLATE 'utf8mb4_0900_ai_ci',
	`timestamp` DATETIME NOT NULL,
	`log_text` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`username` VARCHAR(1) NULL COLLATE 'utf8mb4_0900_ai_ci',
	`group_desc` VARCHAR(1) NULL COLLATE 'utf8mb4_0900_ai_ci'
);

-- Dump della struttura di vista anpr.vw_users
-- Creazione di una tabella temporanea per risolvere gli errori di dipendenza della vista
CREATE TABLE `vw_users` (
	`ID` CHAR(36) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`username` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`password` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`totp` VARCHAR(1) NULL COLLATE 'utf8mb4_0900_ai_ci',
	`name` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`surname` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`email` VARCHAR(1) NULL COLLATE 'utf8mb4_0900_ai_ci',
	`role` INT NOT NULL,
	`group_ID` CHAR(36) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`group_desc` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`telegram_url` VARCHAR(1) NULL COLLATE 'utf8mb4_0900_ai_ci'
);

-- Dump della struttura di vista anpr.vw_watchlist
-- Creazione di una tabella temporanea per risolvere gli errori di dipendenza della vista
CREATE TABLE `vw_watchlist` (
	`ID` CHAR(36) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`plate_num` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`comments` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`date_added` DATETIME NOT NULL,
	`added_by` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`status` BIT(1) NOT NULL,
	`username` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`group_desc` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`name` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`surname` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`telegram_url` VARCHAR(1) NULL COLLATE 'utf8mb4_0900_ai_ci',
	`email` VARCHAR(1) NULL COLLATE 'utf8mb4_0900_ai_ci'
);

-- Dump della struttura di tabella anpr.watchlist
CREATE TABLE IF NOT EXISTS `watchlist` (
  `ID` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT (uuid()),
  `plate_num` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `comments` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `date_added` datetime NOT NULL DEFAULT (now()),
  `added_by` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `status` bit(1) NOT NULL DEFAULT b'1',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `plate_num_added_by` (`plate_num`,`added_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella anpr.zones
CREATE TABLE IF NOT EXISTS `zones` (
  `ID` char(36) NOT NULL DEFAULT (uuid()),
  `zone_desc` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `zone_desc` (`zone_desc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di trigger anpr.events_after_insert
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `events_after_insert` AFTER INSERT ON `events` FOR EACH ROW BEGIN
	UPDATE cam_info SET last_timestamp = NEW.timestamp, last_platenum = NEW.plate_num
	WHERE camera_ip = NEW.camera_ip;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Rimozione temporanea di tabella e creazione della struttura finale della vista
DROP TABLE IF EXISTS `vw_logs`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vw_logs` AS select `l`.`category` AS `category`,`l`.`ID` AS `ID`,`l`.`user_ID` AS `user_ID`,`l`.`timestamp` AS `timestamp`,`l`.`log_text` AS `log_text`,`u`.`username` AS `username`,`u`.`group_desc` AS `group_desc` from (`logs` `l` left join `vw_users` `u` on((`u`.`ID` = `l`.`user_ID`))) order by `l`.`timestamp` desc
;

-- Rimozione temporanea di tabella e creazione della struttura finale della vista
DROP TABLE IF EXISTS `vw_users`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vw_users` AS select `u`.`ID` AS `ID`,`u`.`username` AS `username`,`u`.`password` AS `password`,`u`.`totp` AS `totp`,`u`.`name` AS `name`,`u`.`surname` AS `surname`,`u`.`email` AS `email`,`u`.`role` AS `role`,`u`.`group_ID` AS `group_ID`,`g`.`name` AS `group_desc`,`g`.`telegram_url` AS `telegram_url` from (`users` `u` join `users_groups` `g` on((`u`.`group_ID` = `g`.`ID`)))
;

-- Rimozione temporanea di tabella e creazione della struttura finale della vista
DROP TABLE IF EXISTS `vw_watchlist`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vw_watchlist` AS select `w`.`ID` AS `ID`,`w`.`plate_num` AS `plate_num`,`w`.`comments` AS `comments`,`w`.`date_added` AS `date_added`,`w`.`added_by` AS `added_by`,`w`.`status` AS `status`,`u`.`username` AS `username`,`u`.`group_desc` AS `group_desc`,`u`.`name` AS `name`,`u`.`surname` AS `surname`,`u`.`telegram_url` AS `telegram_url`,`u`.`email` AS `email` from (`watchlist` `w` join `vw_users` `u` on((`w`.`added_by` = `u`.`username`)))
;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
