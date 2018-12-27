ALTER TABLE `#__membersmanager_type` ADD `add_relationship` TINYINT(1) NOT NULL DEFAULT 0 AFTER `asset_id`;

ALTER TABLE `#__membersmanager_type` ADD `type` TEXT NOT NULL AFTER `name`;
