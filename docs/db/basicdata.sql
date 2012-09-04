INSERT INTO `acl_users` (`id`, `acl_roles_id`, `user_name`, `password`, `first_names`, `last_names`, `email`, `approved`) VALUES
  ('1', '1', 'zweicom', 'bc5ac95d42e44c9ba777516afe83fc3f', 'Soporte', 'Zweicom', 'tecnicos@zweicom.com', '1'),
  ('2', '2', 'admin', '21232f297a57a5a743894a0e4a801fc3', 'Administrador', 'Cliente', 'administrador@telefonicamoviles.com.pe', '1'),
  ('3', '3', 'consultas', '83da1fbc8f1a993de3f31cec6d7bf5b2', 'Consultas', 'Cliente', '', '1');
  
INSERT INTO `acl_roles` (`id`, `role_name`, `description`) VALUES
  ('1', 'Soporte', 'Usuario Root'),
  ('2', 'Administrador', 'Usuario Administrador'),
  ('3', 'Consultas', 'Usuario de Consultas');
  
  
INSERT INTO `web_permissions` (`id`, `title`) VALUES
  ('ADD', 'Agregar'),
  ('EDIT', 'Editar'),
  ('DELETE', 'Borrar'),
  ('LIST', 'Listar');
  
INSERT INTO `acl_permissions` (`id`, `acl_roles_id`, `acl_modules_id`, `permission`) VALUES
  ('1', '1', '1', 'LIST'),
  ('2', '1', '2', 'LIST'),
  ('3', '1', '3', 'LIST'),
  ('4', '1', '3', 'EDIT'),
  ('5', '1', '3', 'ADD'),
  ('6', '1', '3', 'DELETE'),
  ('7', '1', '4', 'LIST'),
  ('8', '1', '4', 'EDIT'),
  ('9', '1', '4', 'ADD'),
  ('10', '1', '4', 'DELETE'),
  ('11', '1', '5', 'LIST'),
  ('12', '1', '5', 'EDIT'),
  ('13', '1', '5', 'ADD'),
  ('57', '2', '5', 'DELETE'),
  ('15', '1', '6', 'LIST'),
  ('16', '1', '6', 'EDIT'),
  ('17', '1', '6', 'ADD'),
  ('18', '1', '6', 'DELETE'),
  ('19', '1', '7', 'LIST'),
  ('20', '2', '1', 'LIST'),
  ('21', '2', '2', 'LIST'),
  ('40', '1', '9', 'LIST'),
  ('42', '1', '8', 'LIST'),
  ('43', '1', '8', 'EDIT'),
  ('46', '1', '9', 'ADD'),
  ('47', '1', '9', 'EDIT'),
  ('48', '2', '9', 'LIST'),
  ('49', '2', '9', 'ADD'),
  ('50', '2', '9', 'EDIT'),
  ('52', '3', '8', 'LIST'),
  ('53', '2', '8', 'LIST'),
  ('54', '2', '5', 'LIST'),
  ('55', '2', '5', 'ADD'),
  ('56', '3', '2', 'LIST'),
  ('60', '3', '3', 'LIST'),
  ('61', '3', '3', 'EDIT'),
  ('62', '1', '5', 'DELETE'),
  ('63', '2', '3', 'LIST'),
  ('64', '2', '3', 'EDIT'),
  ('65', '1', '9', 'DELETE');
  
  
  
INSERT INTO `web_settings` (`id`, `enum`, `value`, `type`, `description`, `ord`, `group`, `function`, `approved`) VALUES
  ('query_log', 'SI,NO', 'NO', 'dojo_filtering_select', '', '1', 'Debug', '', '1'),
  ('titulo_adm', '', 'Admin Portal Base', 'dojo_validation_textbox', '', '0', 'Admin', '', '1'),
  ('url_logo_oper', '', 'images/logo_movistar60x44.png', 'dojo_validation_textbox', '', '0', 'Admin', '', '1'),
  ('url_logo_zweicom', '', 'images/logo_zweicom26x15.png', 'dojo_validation_textbox', '', '0', 'Admin', '', '1');
  
INSERT INTO `acl_modules` (`id`, `parent_id`, `title`, `module`, `tree`, `linkable`, `xml`, `approved`) VALUES
(1, 0, 'Configuraci&oacute;n', '', '1', '0', '1', '1'),
(2, 0, 'Reportes', 'reports', '1', '0', '1', '1'),
(3, 1, 'Datos Personales', 'personal-info', '0', '0', '1', '1'),
(4, 1, 'M&oacute;dulos', 'modules', '1', '1', '1', '1'),
(5, 1, 'Usuarios', 'users', '1', '1', '1', '1'),
(6, 1, 'Permisos', 'permissions', '1', '1', '1', '1'),
(7, 1, 'Servidor', 'phpinfo', '1', '1', '1', '1'),
(8, 2, 'Ejemplo', 'ejemplo', '1', '1', '1', '1'),
(9, 1, 'Variables', 'settings', '1', '1', '1', '1');


INSERT INTO `acl_modules` (`id`, `parent_id`, `title`, `module`, `tree`, `linkable`, `type`, `approved`, `order`, `root`) VALUES
(9, 1, 'Variables', 'settings.xml', '1', '1', 'xml', '1', 0, '1'),
(8, 2, 'Compras', 'rep-general-compra.xml', '1', '1', 'xml', '1', 0, '0'),
(7, 1, 'Servidor', 'phpinfo.xml', '1', '1', 'xml', '1', 0, '1'),
(6, 1, 'Permisos', 'permissions.xml', '0', '1', 'xml', '0', 0, '1'),
(5, 1, 'Usuarios', 'users.xml', '1', '1', 'xml', '1', 0, '0'),
(4, 1, 'M&oacute;dulos', 'modules.xml', '1', '1', 'xml', '1', 7, '1'),
(3, 0, 'Datos Personales', 'personal-info.xml', '0', '0', 'xml', '1', 0, '0'),
(2, NULL, 'Reportes Generales', NULL, '1', '0', 'xml', '1', 6, '0'),
(1, NULL, 'Configuraci&oacute;n', NULL, '1', '0', 'xml', '1', 11, '0');