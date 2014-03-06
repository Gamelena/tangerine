ALTER TABLE `acl_roles` ADD `must_refresh` enum('0', '1') NOT NULL DEFAULT '0';
ALTER TABLE `acl_users` ADD `must_refresh` ENUM('0', '1') NOT NULL DEFAULT '0';