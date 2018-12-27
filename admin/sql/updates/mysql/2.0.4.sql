ALTER TABLE `#__membersmanager_type` ADD `communicate` TINYINT(1) NOT NULL DEFAULT 0 AFTER `alias`;

ALTER TABLE `#__membersmanager_type` ADD `field_type` TINYINT(1) NOT NULL DEFAULT 1 AFTER `description`;

CREATE TABLE IF NOT EXISTS `#__membersmanager_type_map` (
	`member` INT(11) NOT NULL DEFAULT 0,
	`type` INT(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__membersmanager_relation_map` (
	`relation` INT(11) NOT NULL DEFAULT 0,
	`member` INT(11) NOT NULL DEFAULT 0,
	`type` INT(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
