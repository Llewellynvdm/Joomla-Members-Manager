CREATE TABLE IF NOT EXISTS `#__membersmanager_member` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`asset_id` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT 'FK to the #__assets table.',
	`account` TINYINT(1) NOT NULL DEFAULT 0,
	`email` VARCHAR(255) NOT NULL DEFAULT '',
	`main_member` INT(11) NOT NULL DEFAULT 0,
	`name` VARCHAR(255) NOT NULL DEFAULT '',
	`password` VARCHAR(255) NOT NULL DEFAULT '',
	`password_check` VARCHAR(255) NOT NULL DEFAULT '',
	`profile_image` TEXT NOT NULL,
	`surname` CHAR(255) NOT NULL DEFAULT '',
	`token` VARCHAR(255) NOT NULL DEFAULT '',
	`type` TEXT NOT NULL,
	`user` INT(11) NOT NULL DEFAULT 0,
	`useremail` VARCHAR(255) NOT NULL DEFAULT '',
	`username` VARCHAR(255) NOT NULL DEFAULT '',
	`params` text NOT NULL,
	`published` TINYINT(3) NOT NULL DEFAULT 1,
	`created_by` INT(10) unsigned NOT NULL DEFAULT 0,
	`modified_by` INT(10) unsigned NOT NULL DEFAULT 0,
	`created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	`modified` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	`checked_out` int(11) unsigned NOT NULL DEFAULT 0,
	`checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	`version` INT(10) unsigned NOT NULL DEFAULT 1,
	`hits` INT(10) unsigned NOT NULL DEFAULT 0,
	`access` INT(10) unsigned NOT NULL DEFAULT 0,
	`ordering` INT(11) NOT NULL DEFAULT 0,
	PRIMARY KEY  (`id`),
	KEY `idx_access` (`access`),
	KEY `idx_checkout` (`checked_out`),
	KEY `idx_createdby` (`created_by`),
	KEY `idx_modifiedby` (`modified_by`),
	KEY `idx_state` (`published`),
	KEY `idx_name` (`name`),
	KEY `idx_account` (`account`),
	KEY `idx_user` (`user`),
	KEY `idx_token` (`token`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__membersmanager_type` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`asset_id` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT 'FK to the #__assets table.',
	`add_relationship` TINYINT(1) NOT NULL DEFAULT 0,
	`alias` CHAR(64) NOT NULL DEFAULT '',
	`communicate` TINYINT(1) NOT NULL DEFAULT 0,
	`description` TEXT NOT NULL,
	`edit_relationship` TEXT NOT NULL,
	`field_type` TINYINT(1) NOT NULL DEFAULT 1,
	`groups_access` TEXT NOT NULL,
	`groups_target` TEXT NOT NULL,
	`name` VARCHAR(255) NOT NULL DEFAULT '',
	`type` TEXT NOT NULL,
	`view_relationship` TEXT NOT NULL,
	`params` text NOT NULL,
	`published` TINYINT(3) NOT NULL DEFAULT 1,
	`created_by` INT(10) unsigned NOT NULL DEFAULT 0,
	`modified_by` INT(10) unsigned NOT NULL DEFAULT 0,
	`created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	`modified` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	`checked_out` int(11) unsigned NOT NULL DEFAULT 0,
	`checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	`version` INT(10) unsigned NOT NULL DEFAULT 1,
	`hits` INT(10) unsigned NOT NULL DEFAULT 0,
	`access` INT(10) unsigned NOT NULL DEFAULT 0,
	`ordering` INT(11) NOT NULL DEFAULT 0,
	PRIMARY KEY  (`id`),
	KEY `idx_access` (`access`),
	KEY `idx_checkout` (`checked_out`),
	KEY `idx_createdby` (`created_by`),
	KEY `idx_modifiedby` (`modified_by`),
	KEY `idx_state` (`published`),
	KEY `idx_name` (`name`),
	KEY `idx_add_relationship` (`add_relationship`),
	KEY `idx_field_type` (`field_type`),
	KEY `idx_communicate` (`communicate`),
	KEY `idx_alias` (`alias`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__membersmanager_type_map` (
	`member` INT(11) NOT NULL DEFAULT 0,
	`type` INT(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__membersmanager_relation_map` (
	`relation` INT(11) NOT NULL DEFAULT 0,
	`member` INT(11) NOT NULL DEFAULT 0,
	`type` INT(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;



--
-- Always insure this column rules is large enough for all the access control values.
--
ALTER TABLE `#__assets` CHANGE `rules` `rules` MEDIUMTEXT NOT NULL COMMENT 'JSON encoded access control.';

--
-- Always insure this column name is large enough for long component and view names.
--
ALTER TABLE `#__assets` CHANGE `name` `name` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'The unique name for the asset.';
