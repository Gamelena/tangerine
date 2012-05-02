-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `acl_modules`
--

DROP TABLE IF EXISTS `acl_modules`;
CREATE TABLE IF NOT EXISTS `acl_modules` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) unsigned DEFAULT NULL,
  `title` varchar(200) DEFAULT NULL,
  `module` varchar(200) DEFAULT NULL,
  `tree` enum('0','1') NOT NULL DEFAULT '1',
  `linkable` enum('0','1') NOT NULL,
  `type` enum('xml','zend_module','legacy') NOT NULL DEFAULT 'xml',
  `approved` enum('0','1') NOT NULL,
  `order` tinyint(4) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `module` (`module`)
) ENGINE=MyISAM AUTO_INCREMENT=32 DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `acl_modules`
--

INSERT INTO `acl_modules` (`id`, `parent_id`, `title`, `module`, `tree`, `linkable`, `xml`, `approved`) VALUES
(1, 0, 'Configuraci&oacute;n', '', '1', '0', '1', '1'),
(2, 0, 'Reportes', 'reports', '1', '0', '1', '1'),
(3, 1, 'Datos Personales', 'personal-info', '0', '0', '1', '1'),
(4, 1, 'M&oacute;dulos', 'modules', '1', '1', '1', '1'),
(5, 1, 'Usuarios', 'users', '1', '1', '1', '1'),
(6, 1, 'Permisos', 'permissions', '1', '1', '1', '1'),
(7, 1, 'Servidor', 'phpinfo', '1', '1', '1', '1'),
(8, 2, 'Ejemplo', 'ejemplo', '1', '1', '1', '1'),
(9, 1, 'Variables', 'settings', '1', '1', '1', '1'),
(30, 2, 'Clientes', 'clientes', '1', '1', '0', '1'),
(31, NULL, 'Ninguno', NULL, '0', '0', '0', '0');



-- 
-- Structure for table `acl_permissions`
-- 

DROP TABLE IF EXISTS `acl_permissions`;
CREATE TABLE IF NOT EXISTS `acl_permissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `acl_roles_id` int(1) NOT NULL,
  `acl_modules_id` int(4) NOT NULL,
  `permission` varchar(64) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `acl_roles_id` (`acl_roles_id`,`acl_modules_id`,`permission`)
) ENGINE=MyISAM AUTO_INCREMENT=94 DEFAULT CHARSET=latin1;

-- 
-- Data for table `acl_permissions`
-- 

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

-- 
-- Structure for table `acl_roles`
-- 

DROP TABLE IF EXISTS `acl_roles`;
CREATE TABLE IF NOT EXISTS `acl_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(64) NOT NULL,
  `root` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_name` (`role_name`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

-- 
-- Data for table `acl_roles`
-- 

INSERT INTO `acl_roles` (`id`, `role_name`, `root`) VALUES
  ('1', 'Desarrollador', '1'),
  ('2', 'Administrador', '0'),
  ('3', 'Consultas', '0');

-- 
-- Structure for table `acl_users`
-- 

DROP TABLE IF EXISTS `acl_users`;
CREATE TABLE IF NOT EXISTS `acl_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `acl_roles_id` int(4) NOT NULL,
  `user_name` varchar(64) NOT NULL,
  `password` varchar(200) NOT NULL,
  `first_names` varchar(50) NOT NULL,
  `last_names` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `approved` enum('0','1') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_name` (`user_name`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

-- 
-- Data for table `acl_users`
-- 

INSERT INTO `acl_users` (`id`, `acl_roles_id`, `user_name`, `password`, `first_names`, `last_names`, `email`, `approved`) VALUES
  ('1', '1', 'zweicom', 'bc5ac95d42e44c9ba777516afe83fc3f', 'Soporte', 'Zweicom', 'tecnicos@zweicom.com', '1'),
  ('2', '2', 'admin', '21232f297a57a5a743894a0e4a801fc3', 'Administrador', 'Cliente', 'administrador@telefonicamoviles.com.pe', '1'),
  ('3', '3', 'consultas', '83da1fbc8f1a993de3f31cec6d7bf5b2', 'Consultas', 'Cliente', '', '1');

-- 
-- Structure for table `web_permissions`
-- 

DROP TABLE IF EXISTS `web_permissions`;
CREATE TABLE IF NOT EXISTS `web_permissions` (
  `id` varchar(20) NOT NULL,
  `title` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Data for table `web_permissions`
-- 

INSERT INTO `web_permissions` (`id`, `title`) VALUES
  ('ADD', 'Agregar'),
  ('EDIT', 'Editar'),
  ('DELETE', 'Borrar'),
  ('LIST', 'Listar');

-- 
-- Structure for table `web_settings`
-- 

DROP TABLE IF EXISTS `web_settings`;
CREATE TABLE IF NOT EXISTS `web_settings` (
  `id` varchar(255) NOT NULL DEFAULT '',
  `enum` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL DEFAULT '0',
  `type` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `ord` int(11) NOT NULL DEFAULT '0',
  `group` varchar(255) NOT NULL,
  `function` varchar(255) NOT NULL,
  `approved` enum('0','1') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- 
-- Data for table `web_settings`
-- 

INSERT INTO `web_settings` (`id`, `enum`, `value`, `type`, `description`, `ord`, `group`, `function`, `approved`) VALUES
  ('query_log', 'SI,NO', 'NO', 'dojo_filtering_select', '', '1', 'Debug', '', '1'),
  ('titulo_adm', '', 'Admin Portal Base', 'dojo_validation_textbox', '', '0', 'Admin', '', '1'),
  ('url_logo_oper', '', 'images/logo_movistar60x44.png', 'dojo_validation_textbox', '', '0', 'Admin', '', '1'),
  ('url_logo_zweicom', '', 'images/logo_zweicom26x15.png', 'dojo_validation_textbox', '', '0', 'Admin', '', '1');

