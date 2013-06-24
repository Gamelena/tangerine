-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `acl_modules`
--

DROP TABLE IF EXISTS `acl_modules`;
CREATE TABLE IF NOT EXISTS `acl_modules` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) unsigned DEFAULT NULL,
  `title` char(200) DEFAULT NULL,
  `module` char(200) DEFAULT NULL,
  `tree` enum('0','1') NOT NULL DEFAULT '1',
  `linkable` enum('0','1') NOT NULL,
  `type` enum('xml','xml_mvc','zend_module','legacy','iframe') NOT NULL DEFAULT 'xml',
  `approved` enum('0','1') NOT NULL,
  `order` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `root` enum('0','1') CHARACTER SET utf8 NOT NULL DEFAULT '0',
  `icons_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `module` (`module`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `acl_actions`
--

DROP TABLE IF EXISTS `acl_actions`;
CREATE TABLE IF NOT EXISTS `acl_actions` (
  `id` varchar(10) NOT NULL,
  `title` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `acl_actions`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `acl_modules_actions`
--
DROP TABLE IF EXISTS `acl_modules_actions`;
CREATE TABLE IF NOT EXISTS `acl_modules_actions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `acl_modules_id` int(11) NOT NULL,
  `acl_actions_id` varchar(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `acl_modules_id` (`acl_modules_id`,`acl_actions_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


-- 
-- Structure for table `acl_roles`
-- 
DROP TABLE IF EXISTS `acl_roles`;
CREATE TABLE IF NOT EXISTS `acl_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` char(64) NOT NULL,
  `description` char(100) CHARACTER SET utf8 NOT NULL,
  `approved` enum('0','1') CHARACTER SET utf8 NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_name` (`role_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- 
-- Structure for table `acl_users`
-- 


DROP TABLE IF EXISTS `acl_users`;
CREATE TABLE IF NOT EXISTS `acl_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `acl_roles_id` int(4) NOT NULL,
  `user_name` char(64) NOT NULL,
  `password` char(200) NOT NULL,
  `first_names` char(50) NOT NULL,
  `last_names` char(50) NOT NULL,
  `email` char(50) NOT NULL,
  `approved` enum('0','1') NOT NULL,
  `foto` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_name` (`user_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;



DROP TABLE IF EXISTS `acl_roles_modules_actions`;
CREATE TABLE IF NOT EXISTS `acl_roles_modules_actions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `acl_roles_id` int(11) NOT NULL,
  `acl_modules_actions_id` int(11) NOT NULL,
  `permission` enum('ALLOW','DENY') CHARACTER SET utf8 NOT NULL DEFAULT 'ALLOW',
  PRIMARY KEY (`id`),
  UNIQUE KEY `acl_roles_id` (`acl_roles_id`,`acl_modules_actions_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `web_icons`
--
DROP TABLE IF EXISTS `web_icons`;
CREATE TABLE IF NOT EXISTS `web_icons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL,
  `image` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `web_settings`
--

DROP TABLE IF EXISTS `web_settings`;
CREATE TABLE IF NOT EXISTS `web_settings` (
  `id` char(255) NOT NULL DEFAULT '',
  `list` char(255) NOT NULL,
  `value` char(255) NOT NULL DEFAULT '0',
  `type` char(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `ord` int(11) NOT NULL DEFAULT '0',
  `group` char(255) NOT NULL,
  `function` char(255) NOT NULL,
  `approved` enum('0','1') NOT NULL,
  `path` varchar(50) NOT NULL,
  `regExp` varchar(60) NOT NULL,
  `invalidMessage` varchar(50) NOT NULL,
  `promptMessage` varchar(50) NOT NULL,
  `formatter` varchar(25) NOT NULL,
  `xml_children` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `group` (`group`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Structure for table `log_book`
-- 

DROP TABLE IF EXISTS `log_book`;

CREATE TABLE IF NOT EXISTS `log_book` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user` char(40) NOT NULL,
  `table` char(40) NOT NULL,
  `action` char(40) NOT NULL,
  `condition` char(200) NOT NULL,
  `acl_roles_id` int(11) NOT NULL,
  `ip` char(200) NOT NULL,
  `stamp` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


--
-- Estructura para la vista `tables_logged`
--
DROP VIEW IF EXISTS `tables_logged`;
DROP TABLE IF EXISTS `tables_logged`;

CREATE VIEW `tables_logged` AS select distinct `log_book`.`table` AS `id`,`log_book`.`table` AS `title` from `log_book` order by `log_book`.`table`;
