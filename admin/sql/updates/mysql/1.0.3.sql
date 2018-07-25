ALTER TABLE `#__membersmanager_member` ADD `email` VARCHAR(255) NOT NULL DEFAULT '' AFTER `country`;

ALTER TABLE `#__membersmanager_member` ADD `main_member` INT(11) NOT NULL DEFAULT 0 AFTER `email`;

ALTER TABLE `#__membersmanager_member` ADD `name` VARCHAR(255) NOT NULL DEFAULT '' AFTER `mobile_phone`;
