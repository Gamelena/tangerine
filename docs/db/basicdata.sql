-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--
-- Servidor: localhost
-- Tiempo de generación: 05-09-2012 a las 11:26:32
-- Versión del servidor: 5.5.24
-- Versión de PHP: 5.3.10-1ubuntu3.2

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Base de datos: `regla_cobros`
--


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
  PRIMARY KEY (`id`),
  UNIQUE KEY `module` (`module`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;

--
-- Volcado de datos para la tabla `acl_modules`
--

INSERT INTO `acl_modules` (`id`, `parent_id`, `title`, `module`, `tree`, `linkable`, `type`, `approved`, `order`, `root`) VALUES
(9, 1, 'Variables', 'settings.xml', '1', '1', 'xml', '1', 0, '1'),
(8, 1, 'Perfiles', 'roles.xml', '1', '1', 'xml', '1', 0, '1'),
(7, 1, 'Servidor', 'phpinfo.xml', '1', '1', 'xml', '1', 0, '1'),
(6, 1, 'Permisos', 'permissions.xml', '0', '1', 'xml', '0', 0, '1'),
(5, 1, 'Usuarios', 'users.xml', '1', '1', 'xml', '1', 0, '0'),
(4, 1, 'M&oacute;dulos', 'modules.xml', '1', '1', 'xml', '1', 7, '1'),
(3, NULL, 'Datos Personales', 'personal-info.xml', '0', '0', 'xml', '1', 0, '0'),
(2, NULL, 'Reportes Generales', NULL, '1', '0', 'xml', '1', 6, '0'),
(1, NULL, 'Configuraci&oacute;n', NULL, '1', '0', 'xml', '1', 11, '0');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `acl_permissions`
--

DROP TABLE IF EXISTS `acl_permissions`;
CREATE TABLE IF NOT EXISTS `acl_permissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `acl_roles_id` int(1) NOT NULL,
  `acl_modules_id` int(4) NOT NULL,
  `permission` char(64) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `acl_roles_id` (`acl_roles_id`,`acl_modules_id`,`permission`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=162 ;

--
-- Volcado de datos para la tabla `acl_permissions`
--

INSERT INTO `acl_permissions` (`id`, `acl_roles_id`, `acl_modules_id`, `permission`) VALUES
(110, 1, 5, 'LIST'),
(109, 1, 5, 'EDIT'),
(108, 1, 5, 'DELETE'),
(107, 1, 5, 'ADD'),
(106, 1, 9, 'LIST'),
(105, 1, 9, 'EDIT'),
(104, 1, 9, 'DELETE'),
(103, 1, 9, 'ADD'),
(102, 1, 8, 'LIST'),
(101, 1, 8, 'EDIT'),
(100, 1, 8, 'DELETE'),
(99, 1, 8, 'ADD'),
(98, 1, 7, 'LIST'),
(97, 1, 4, 'LIST'),
(96, 1, 4, 'EDIT'),
(95, 1, 4, 'DELETE'),
(94, 1, 4, 'ADD'),
(159, 2, 5, 'DELETE'),
(93, 1, 3, 'LIST'),
(92, 1, 3, 'EDIT'),
(91, 1, 2, 'LIST'),
(90, 1, 1, 'LIST'),
(89, 1, 1, 'EDIT'),
(158, 2, 5, 'ADD'),
(153, 2, 1, 'LIST'),
(154, 2, 2, 'LIST'),
(134, 3, 3, 'LIST'),
(160, 2, 5, 'EDIT'),
(161, 2, 5, 'LIST'),
(157, 2, 8, 'LIST'),
(133, 3, 3, 'EDIT'),
(132, 3, 2, 'LIST'),
(88, 1, 1, 'DELETE'),
(156, 2, 3, 'LIST'),
(155, 2, 3, 'EDIT'),
(87, 1, 1, 'ADD');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `acl_roles`
--

DROP TABLE IF EXISTS `acl_roles`;
CREATE TABLE IF NOT EXISTS `acl_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` char(64) NOT NULL,
  `description` char(100) CHARACTER SET utf8 NOT NULL,
  `approved` enum('0','1') CHARACTER SET utf8 NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_name` (`role_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Volcado de datos para la tabla `acl_roles`
--

INSERT INTO `acl_roles` (`id`, `role_name`, `description`, `approved`) VALUES
(1, 'Soporte', 'Perfil root.', '1'),
(2, 'Administrador', 'Administrador con acceso a hacer modificaciones administrativas y manejo de usuarios.', '1'),
(3, 'Consultas', 'Acceso a listados y reportes.', '1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `acl_users`
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
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_name` (`user_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Volcado de datos para la tabla `acl_users`
--

INSERT INTO `acl_users` (`id`, `acl_roles_id`, `user_name`, `password`, `first_names`, `last_names`, `email`, `approved`) VALUES
(1, 1, 'zweicom', '3a62dc577a0db23fb0b5c1c9e8046c05', 'Soporte', 'Zweicom', 'tecnicos@zweicom.com', '1'),
(2, 2, 'admin', '21232f297a57a5a743894a0e4a801fc3', 'Administrador', 'Cliente', 'administrador@telefonicamoviles.com.pe', '1'),
(3, 3, 'consultas', '83da1fbc8f1a993de3f31cec6d7bf5b2', 'Consultas', 'Cliente', '', '1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `log_book`
--

DROP TABLE IF EXISTS `log_book`;
CREATE TABLE IF NOT EXISTS `log_book` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user` char(40) NOT NULL,
  `table` char(40) NOT NULL,
  `action` char(40) NOT NULL,
  `condition` char(200) NOT NULL,
  `acl_roles_id` int(11) NOT NULL,
  `ip` char(200) NOT NULL,
  `stamp` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `tables_logged`
--
DROP VIEW IF EXISTS `tables_logged`;
CREATE TABLE IF NOT EXISTS `tables_logged` (
`id` char(40)
,`title` char(40)
);
-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `web_permissions`
--

DROP TABLE IF EXISTS `web_permissions`;
CREATE TABLE IF NOT EXISTS `web_permissions` (
  `id` char(20) NOT NULL,
  `title` char(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `web_permissions`
--

INSERT INTO `web_permissions` (`id`, `title`) VALUES
('ADD', 'Agregar'),
('EDIT', 'Editar'),
('DELETE', 'Borrar'),
('LIST', 'Listar');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `web_settings`
--

DROP TABLE IF EXISTS `web_settings`;
CREATE TABLE IF NOT EXISTS `web_settings` (
  `id` char(255) NOT NULL DEFAULT '',
  `enum` char(255) NOT NULL,
  `value` char(255) NOT NULL DEFAULT '0',
  `type` char(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `ord` int(11) NOT NULL DEFAULT '0',
  `group` char(255) NOT NULL,
  `function` char(255) NOT NULL,
  `approved` enum('0','1') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `web_settings`
--

INSERT INTO `web_settings` (`id`, `enum`, `value`, `type`, `description`, `ord`, `group`, `function`, `approved`) VALUES
('query_log', 'SI,NO', 'SI', 'dojo_filtering_select', '', 1, 'Debug', '', '1'),
('transactions_log', 'SI,NO', 'SI', 'dojo_filtering_select', '', 1, 'Debug', '', '1'),
('titulo_adm', '', 'Admin Portal Base', 'dojo_validation_textbox', '', 0, 'Admin', '', '1'),
('url_logo_oper', '', 'images/logo_movistar60x44.png', 'dojo_validation_textbox', '', 0, 'Admin', '', '1'),
('url_logo_zweicom', '', 'images/logo_zweicom26x15.png', 'dojo_validation_textbox', '', 0, 'Admin', '', '1');

-- --------------------------------------------------------


