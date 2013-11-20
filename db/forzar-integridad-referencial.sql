DELETE from acl_modules_actions WHERE acl_modules_id NOT IN (SELECT id FROM acl_modules);
DELETE from acl_modules_actions WHERE acl_actions_id NOT IN (SELECT id FROM acl_actions);
DELETE from acl_roles_modules_actions WHERE acl_roles_id NOT IN (SELECT id FROM acl_roles);
DELETE from acl_roles_modules_actions WHERE acl_modules_actions_id NOT IN (SELECT id FROM acl_modules_actions);
