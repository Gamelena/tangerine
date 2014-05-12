DELETE from acl_modules_actions WHERE acl_modules_id NOT IN (SELECT id FROM acl_modules);
DELETE from acl_modules_actions WHERE acl_modules_id NOT IN (NULL, (SELECT m1.parent_id FROM acl_modules m1 LEFT JOIN acl_modules m2 ON m1.parent_id = m2.id WHERE m1.parent_id != NULL ));
DELETE from acl_modules_actions WHERE acl_actions_id NOT IN (SELECT id FROM acl_actions);
DELETE from acl_roles_modules_actions WHERE acl_roles_id NOT IN (SELECT id FROM acl_roles);
DELETE from acl_roles_modules_actions WHERE acl_modules_actions_id NOT IN (SELECT id FROM acl_modules_actions);

DELETE from acl_groups_modules_actions WHERE acl_modules_actions_id NOT IN (SELECT id FROM acl_modules_actions);