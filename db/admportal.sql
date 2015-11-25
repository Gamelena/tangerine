CREATE DATABASE IF NOT EXISTS `admportal`;

GRANT ALL ON admportal.* to admportal_user identified by 'admportal_pass';
GRANT ALL ON admportal.* to admportal_user@localhost identified by 'admportal_pass';

USE `admportal`;

-- phpMyAdmin SQL Dump
-- version 4.0.5
-- http://www.phpmyadmin.net
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 16-04-2014 a las 18:14:38
-- Versión del servidor: 5.5.35-0ubuntu0.12.10.2
-- Versión de PHP: 5.4.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Base de datos: `suscripciones`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `acl_actions`
--

DROP TABLE IF EXISTS `acl_actions`;
CREATE TABLE IF NOT EXISTS `acl_actions` (
  `id` varchar(10) NOT NULL,
  `title` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `acl_actions`
--

INSERT INTO `acl_actions` (`id`, `title`) VALUES
('ADD', 'Agregar'),
('DELETE', 'Eliminar'),
('EDIT', 'Editar'),
('LIST', 'Listar');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `acl_groups`
--

DROP TABLE IF EXISTS `acl_groups`;
CREATE TABLE IF NOT EXISTS `acl_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(45) DEFAULT NULL,
  `description` varchar(45) DEFAULT NULL,
  `approved` enum('0','1') DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `acl_groups_modules_actions`
--

DROP TABLE IF EXISTS `acl_groups_modules_actions`;
CREATE TABLE IF NOT EXISTS `acl_groups_modules_actions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `acl_modules_actions_id` int(10) NOT NULL,
  `acl_groups_id` int(11) NOT NULL,
  `acl_modules_item_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `acl_modules_actions_id` (`acl_modules_actions_id`,`acl_groups_id`,`acl_modules_item_id`),
  KEY `fk_acl_groups_modules_actions_acl_modules_actions1` (`acl_modules_actions_id`),
  KEY `fk_acl_groups_modules_actions_acl_groups1` (`acl_groups_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `acl_modules`
--

DROP TABLE IF EXISTS `acl_modules`;
CREATE TABLE IF NOT EXISTS `acl_modules` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) unsigned DEFAULT NULL COMMENT 'Id de Módulo padre',
  `title` char(200) DEFAULT NULL COMMENT 'Nombre',
  `module` char(200) DEFAULT NULL COMMENT 'Url de módulo, puede ser archivo XML, URl de controlador o módulo ZF',
  `tree` enum('0','1') NOT NULL DEFAULT '1',
  `refresh_on_load` enum('0','1') NOT NULL DEFAULT '0' COMMENT 'Define si módulo debe ser refrescado al reseleccionar la pestaña, en caso contrario mantendrá el estatus de como se dejó al abandonarla.',
  `type` enum('xml','zend_module','iframe') DEFAULT NULL,
  `approved` enum('0','1') NOT NULL DEFAULT '1',
  `order` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT 'Orden en que aparece en arbol',
  `root` enum('0','1') NOT NULL DEFAULT '0' COMMENT 'Define si solo puede acceder perfil ROOT (en configuracion por defecto acl_roles_id = 1)',
  `ownership` enum('0','1') NOT NULL DEFAULT '0' COMMENT 'Indica si modulo usa modelo con reglas de owner, funciona con type=xml, ver Zwei_Db_Table::isOwner(item, user)',
  `icons_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `module` (`module`, `type`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=14 ;



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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=326 ;



--
-- Estructura de tabla para la tabla `acl_roles`
--

DROP TABLE IF EXISTS `acl_roles`;
CREATE TABLE IF NOT EXISTS `acl_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` char(64) NOT NULL,
  `description` char(100) CHARACTER SET utf8 NOT NULL,
  `approved` enum('0','1') CHARACTER SET utf8 NOT NULL DEFAULT '1',
  `must_refresh` enum('0','1') CHARACTER SET utf8 NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_name` (`role_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;



--
-- Estructura de tabla para la tabla `acl_roles_modules_actions`
--

DROP TABLE IF EXISTS `acl_roles_modules_actions`;
CREATE TABLE IF NOT EXISTS `acl_roles_modules_actions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `acl_roles_id` int(11) NOT NULL,
  `acl_modules_actions_id` int(11) NOT NULL,
  `permission` enum('ALLOW','DENY') CHARACTER SET utf8 NOT NULL DEFAULT 'ALLOW',
  PRIMARY KEY (`id`),
  UNIQUE KEY `acl_roles_id` (`acl_roles_id`,`acl_modules_actions_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=156 ;


--
-- Estructura de tabla para la tabla `acl_session`
-- 

DROP TABLE IF EXISTS `acl_session`;
CREATE TABLE IF NOT EXISTS `acl_session` (
  `id` char(32) NOT NULL DEFAULT '0',
  `acl_users_id` int(10) DEFAULT NULL,
  `acl_roles_id` int(10) DEFAULT NULL,
  `created` int(10) UNSIGNED DEFAULT NULL,
  `modified` int(10) UNSIGNED DEFAULT NULL,
  `lifetime` int(10)  UNSIGNED DEFAULT NULL,
  `data` text,
  `ip` varchar(20) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `must_refresh` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


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
  `foto` varchar(256) DEFAULT NULL,
  `must_refresh` enum('0','1') CHARACTER SET utf8 NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_name` (`user_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;



--
-- Estructura de tabla para la tabla `acl_users_groups`
--

DROP TABLE IF EXISTS `acl_users_groups`;
CREATE TABLE IF NOT EXISTS `acl_users_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `acl_users_id` int(11) NOT NULL,
  `acl_groups_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `acl_users_id` (`acl_users_id`,`acl_groups_id`),
  KEY `fk_acl_users_groups_acl_users1` (`acl_users_id`),
  KEY `fk_acl_users_groups_acl_groups1` (`acl_groups_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------


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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=18 ;

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
  `url` varchar(256) DEFAULT NULL,
  `regExp` varchar(60) NOT NULL,
  `invalidMessage` varchar(50) NOT NULL,
  `promptMessage` varchar(50) NOT NULL,
  `formatter` varchar(25) NOT NULL,
  `xml_children` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `group` (`group`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



--
-- Estructura para la vista `tables_logged`
--
DROP TABLE IF EXISTS `tables_logged`;
DROP VIEW IF EXISTS `tables_logged`;
CREATE VIEW `tables_logged` AS select distinct `log_book`.`table` AS `id`,`log_book`.`table` AS `title` from `log_book` order by `log_book`.`table`;

