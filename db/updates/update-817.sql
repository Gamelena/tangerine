DELETE FROM acl_roles_modules_actions WHERE acl_roles_id NOT IN (SELECT id FROM acl_roles);
DELETE FROM acl_modules_actions WHERE acl_modules_id NOT IN (SELECT id FROM acl_modules);
ALTER TABLE acl_modules ADD `ownership` enum('0','1') NOT NULL DEFAULT '0' COMMENT 'Indica si modulo usa modelo con reglas de owner, funciona con type=xml, ver Zwei_Db_Table::isOwner(item, user)';