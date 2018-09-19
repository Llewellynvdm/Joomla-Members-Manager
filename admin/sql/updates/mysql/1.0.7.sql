ALTER TABLE `#__membersmanager_type` ADD `groups_access`   TEXT NOT NULL AFTER `description`;

ALTER TABLE `#__membersmanager_type` ADD `groups_target` TEXT NOT NULL AFTER `groups_access`;
