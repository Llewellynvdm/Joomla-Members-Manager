ALTER TABLE `#__membersmanager_member` ADD `password` VARCHAR(255) NOT NULL DEFAULT '' AFTER `name`;

ALTER TABLE `#__membersmanager_member` ADD `password_check` VARCHAR(255) NOT NULL DEFAULT '' AFTER `password`;

ALTER TABLE `#__membersmanager_member` ADD `surname` CHAR(255) NOT NULL DEFAULT '' AFTER `profile_image`;
