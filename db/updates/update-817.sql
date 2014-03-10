DROP TABLE IF EXISTS `acl_session`;

CREATE TABLE IF NOT EXISTS `acl_session` (
  `id` char(32) NOT NULL DEFAULT '0',
  `acl_users_id` int(11) NOT NULL,
  `acl_roles_id` int(11) NOT NULL,
  `modified` int(11) DEFAULT NULL,
  `lifetime` int(11) DEFAULT NULL,
  `data` text,
  `ip` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DELETE FROM acl_roles_modules_actions WHERE acl_roles_id NOT IN (SELECT id FROM acl_roles);
DELETE FROM acl_modules_actions WHERE acl_modules_id NOT IN (SELECT id FROM acl_modules);
ALTER TABLE acl_modules ADD `ownership` enum('0','1') NOT NULL DEFAULT '0' COMMENT 'Indica si modulo usa modelo con reglas de owner, funciona con type=xml, ver Zwei_Db_Table::isOwner(item, user)';