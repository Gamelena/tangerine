-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Servidor: localhost
-- Tiempo de generación: 02-03-2016 a las 01:10:39
-- Versión del servidor: 5.5.47-0ubuntu0.14.04.1
-- Versión de PHP: 5.5.9-1ubuntu4.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de datos: `fuu`
--

--
-- Truncar tablas antes de insertar `acl_actions`
--

TRUNCATE TABLE `acl_actions`;
--
-- Volcado de datos para la tabla `acl_actions`
--

INSERT INTO `acl_actions` (`id`, `title`) VALUES
('ADD', 'Agregar'),
('DELETE', 'Eliminar'),
('EDIT', 'Editar'),
('LIST', 'Listar');

--
-- Truncar tablas antes de insertar `acl_groups`
--

TRUNCATE TABLE `acl_groups`;
--
-- Truncar tablas antes de insertar `acl_groups_modules_actions`
--

TRUNCATE TABLE `acl_groups_modules_actions`;
--
-- Truncar tablas antes de insertar `acl_modules`
--

TRUNCATE TABLE `acl_modules`;
--
-- Volcado de datos para la tabla `acl_modules`
--

INSERT INTO `acl_modules` (`id`, `parent_id`, `title`, `module`, `tree`, `refresh_on_load`, `type`, `approved`, `order`, `root`, `ownership`, `icons_id`) VALUES
(1, NULL, 'Configuraci&oacute;n', NULL, '1', '0', NULL, '1', 11, '0', '0', 12),
(2, NULL, 'Reportes Generales', NULL, '1', '0', NULL, '1', 6, '0', '0', 1),
(3, NULL, 'Datos Personales', 'personal-info.xml', '0', '0', 'xml', '1', 0, '0', '0', NULL),
(4, 11, 'M&oacute;dulos', 'modules.xml', '1', '0', 'xml', '1', 1, '1', '0', 6),
(5, 10, 'Usuarios', 'users.xml', '1', '0', 'xml', '1', 1, '0', '0', 17),
(7, 11, 'Servidor', 'phpinfo.xml', '1', '0', 'xml', '1', 6, '1', '0', 22),
(8, 10, 'Perfiles', 'roles.xml', '1', '0', 'xml', '1', 2, '1', '0', 7),
(9, 11, 'Configuraci&oacute;n Global', 'settings.xml', '1', '0', 'xml', '1', 5, '1', '0', 20),
(10, 1, 'Perfilamiento', NULL, '1', '0', NULL, '1', 1, '0', '0', 5),
(11, 1, 'Sitio', NULL, '1', '0', NULL, '1', 2, '0', '0', 13),
(12, 11, '&Iacute;conos', 'icons.xml', '1', '0', 'xml', '1', 7, '1', '0', 16),
(13, 9, 'Avanzado', 'settings-advanced.xml', '1', '0', 'xml', '1', 0, '1', '0', 21);

--
-- Truncar tablas antes de insertar `acl_modules_actions`
--

TRUNCATE TABLE `acl_modules_actions`;
--
-- Volcado de datos para la tabla `acl_modules_actions`
--

INSERT INTO `acl_modules_actions` (`id`, `acl_modules_id`, `acl_actions_id`) VALUES
(168, 1, 'LIST'),
(188, 2, 'LIST'),
(62, 3, 'EDIT'),
(63, 3, 'LIST'),
(172, 4, 'ADD'),
(173, 4, 'DELETE'),
(171, 4, 'EDIT'),
(174, 4, 'LIST'),
(163, 5, 'ADD'),
(164, 5, 'DELETE'),
(162, 5, 'EDIT'),
(165, 5, 'LIST'),
(187, 7, 'LIST'),
(260, 8, 'ADD'),
(261, 8, 'DELETE'),
(259, 8, 'EDIT'),
(262, 8, 'LIST'),
(301, 9, 'EDIT'),
(205, 9, 'LIST'),
(267, 10, 'LIST'),
(268, 11, 'LIST'),
(285, 12, 'ADD'),
(286, 12, 'DELETE'),
(287, 12, 'EDIT'),
(288, 12, 'LIST'),
(289, 13, 'ADD'),
(290, 13, 'DELETE'),
(291, 13, 'EDIT'),
(292, 13, 'LIST');

--
-- Truncar tablas antes de insertar `acl_roles`
--

TRUNCATE TABLE `acl_roles`;
--
-- Volcado de datos para la tabla `acl_roles`
--

INSERT INTO `acl_roles` (`id`, `role_name`, `description`, `approved`, `must_refresh`) VALUES
(1, 'Soporte', 'Perfil root.', '1', '0'),
(2, 'Administrador', 'Administrador con acceso a hacer modificaciones administrativas y manejo de usuarios.', '1', '0'),
(3, 'Consultas', 'Acceso a listados y reportes.', '1', '0');

--
-- Truncar tablas antes de insertar `acl_roles_modules_actions`
--

TRUNCATE TABLE `acl_roles_modules_actions`;
--
-- Volcado de datos para la tabla `acl_roles_modules_actions`
--

INSERT INTO `acl_roles_modules_actions` (`id`, `acl_roles_id`, `acl_modules_actions_id`, `permission`) VALUES
(1, 2, 168, 'ALLOW'),
(3, 2, 188, 'ALLOW'),
(5, 2, 62, 'ALLOW'),
(6, 2, 63, 'ALLOW'),
(8, 2, 162, 'ALLOW'),
(9, 2, 163, 'ALLOW'),
(10, 2, 164, 'ALLOW'),
(11, 2, 165, 'ALLOW'),
(15, 1, 168, 'ALLOW'),
(17, 1, 188, 'ALLOW'),
(26, 1, 62, 'ALLOW'),
(27, 1, 63, 'ALLOW'),
(33, 1, 171, 'ALLOW'),
(34, 1, 172, 'ALLOW'),
(35, 1, 173, 'ALLOW'),
(36, 1, 174, 'ALLOW'),
(37, 1, 187, 'ALLOW'),
(38, 1, 204, 'ALLOW'),
(39, 1, 205, 'ALLOW'),
(44, 1, 162, 'ALLOW'),
(45, 1, 163, 'ALLOW'),
(46, 1, 164, 'ALLOW'),
(47, 1, 165, 'ALLOW'),
(62, 3, 188, 'ALLOW'),
(67, 3, 162, 'ALLOW'),
(68, 3, 163, 'ALLOW'),
(69, 3, 164, 'ALLOW'),
(70, 3, 165, 'ALLOW'),
(74, 3, 168, 'ALLOW'),
(105, 3, 62, 'ALLOW'),
(106, 3, 63, 'ALLOW'),
(123, 1, 259, 'ALLOW'),
(124, 1, 260, 'ALLOW'),
(125, 1, 261, 'ALLOW'),
(126, 1, 262, 'ALLOW'),
(131, 1, 267, 'ALLOW'),
(132, 1, 268, 'ALLOW'),
(133, 1, 289, 'ALLOW'),
(134, 1, 290, 'ALLOW'),
(135, 1, 291, 'ALLOW'),
(136, 1, 292, 'ALLOW'),
(145, 1, 285, 'ALLOW'),
(146, 1, 286, 'ALLOW'),
(147, 1, 287, 'ALLOW'),
(148, 1, 288, 'ALLOW');

--
-- Truncar tablas antes de insertar `acl_session`
--

TRUNCATE TABLE `acl_session`;
--
-- Volcado de datos para la tabla `acl_session`
--

INSERT INTO `acl_session` (`id`, `acl_users_id`, `acl_roles_id`, `created`, `modified`, `lifetime`, `data`, `ip`, `user_agent`, `must_refresh`) VALUES
('ad1ho551rprmf8fm6jir8dr733', 1, 1, 1456888702, 1456891833, 864000, 'Zend_Auth|a:3:{s:7:"timeout";i:1456893414;s:10:"requestUri";s:32:"/admin/components?p=settings.xml";s:7:"storage";O:8:"stdClass":11:{s:2:"id";s:1:"1";s:12:"acl_roles_id";s:1:"1";s:9:"user_name";s:8:"gamelena";s:11:"first_names";s:7:"Soporte";s:10:"last_names";s:8:"gamelena";s:5:"email";s:21:"tecnicos@gamelena.com";s:8:"approved";s:1:"1";s:4:"foto";N;s:12:"must_refresh";s:1:"0";s:16:"sessionNamespace";s:3:"fuu";s:6:"groups";a:0:{}}}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.109 Safari/537.36 FirePHP/4Chrome', '0');

--
-- Truncar tablas antes de insertar `acl_users`
--

TRUNCATE TABLE `acl_users`;
--
-- Volcado de datos para la tabla `acl_users`
--

INSERT INTO `acl_users` (`id`, `acl_roles_id`, `user_name`, `password`, `first_names`, `last_names`, `email`, `approved`, `foto`, `must_refresh`) VALUES
(1, 1, 'gamelena', '31cb6a72f8f70612e27af0f59a9322ca', 'Soporte', 'gamelena', 'tecnicos@gamelena.com', '1', NULL, '0'),
(2, 2, 'admin', '21232f297a57a5a743894a0e4a801fc3', 'Administrador', 'Cliente', 'administrador@telefonicamoviles.com.pe', '1', NULL, '0'),
(3, 3, 'consultas', '83da1fbc8f1a993de3f31cec6d7bf5b2', 'Consultas', 'Cliente', '', '1', NULL, '0');

--
-- Truncar tablas antes de insertar `acl_users_groups`
--

TRUNCATE TABLE `acl_users_groups`;
--
-- Truncar tablas antes de insertar `log_book`
--

TRUNCATE TABLE `log_book`;
--
-- Truncar tablas antes de insertar `web_icons`
--

TRUNCATE TABLE `web_icons`;
--
-- Volcado de datos para la tabla `web_icons`
--

INSERT INTO `web_icons` (`id`, `title`, `image`) VALUES
(1, 'Sphere Green', '033d4c25green-sphere.png'),
(2, 'Sphere Blue', '225ca2a1step4c.png'),
(3, 'Sphere Red', '23cf88ccred-sphere-2.png'),
(4, 'Sphere Yellow', 'dedbeb01yellow-sphere.png'),
(5, 'Keys', 'a3093747roles.png'),
(6, 'Module', '03e490e9blockdevice.png'),
(7, 'Roles', '547353feuser-group-icon.png'),
(8, 'Teams', '630e0daesocial-networking-package.jpg'),
(9, 'Online', 'ab789f0auser-online.png'),
(10, 'Chart', '325d3cbfarea-chart-256.png'),
(11, 'Magnifier', '905dbb5bwindows-7-magnifier.png'),
(12, 'Settings', 'e0d79f05setting-icon.png'),
(13, 'Settings 2', '91732629iphone-settings-icon.png'),
(14, 'Audit', '751f9170audit.png'),
(15, 'Setup', '0d7df408setup-l.png'),
(16, 'USSD', 'c62b4507bitdefender-ussd-wipe-stopper.png'),
(17, 'User', 'a4c40f07actions-im-user-icon.png'),
(18, 'Package', 'c244255a50px-crystal-package.png'),
(19, 'Sale', '8a6ca637activshow-icon.png'),
(20, 'Global', '9f8eb80dworld.png'),
(21, 'Setup Warning', '11e455ecsetup.png'),
(22, 'Server', '3ff898e8server-icon.png'),
(23, 'Reporte', '7612a7b7reports.png'),
(24, 'CSV', 'ad4c5a07csv.png');

--
-- Truncar tablas antes de insertar `web_settings`
--

TRUNCATE TABLE `web_settings`;
--
-- Volcado de datos para la tabla `web_settings`
--

INSERT INTO `web_settings` (`id`, `list`, `value`, `type`, `description`, `ord`, `group`, `function`, `approved`, `path`, `url`, `regExp`, `invalidMessage`, `promptMessage`, `formatter`, `xml_children`) VALUES
('credits', '', '&copy; gamelena 2015', 'dijit-form-validation-text-box', '', 2, 'Admin', '', '1', '', NULL, '', '', '', '', ''),
('query_log', '', '1', 'dijit-form-check-box', '', 1, 'Debug', '', '1', '', NULL, '', '', '', '', ''),
('titulo_adm', '', 'Hola Mundo', 'dijit-form-validation-text-box', '', 1, 'Admin', '', '1', '', NULL, '', '', '', '', ''),
('transactions_log', '', '1', 'dijit-form-check-box', '', 1, 'Debug', '', '1', '', NULL, '', '', '', '', ''),
('url_logo_gamelena', '', 'd1e645f8genesis.png', 'dojox-form-uploader', '', 3, 'Admin', '', '1', '{ROOT_DIR}/public/upfiles/', '{BASE_URL}/upfiles/corporative/', '', '', '', 'formatImage', '&lt;thumb height="18" path="{ROOT_DIR}/public/upfiles/corporative/" /&gt;\r\n'),
('url_logo_oper', '', '100a0c75genesis.png', 'dojox-form-uploader', '', 3, 'Admin', '', '1', '{ROOT_DIR}/public/upfiles/', '{BASE_URL}/upfiles/corporative/', '', '', '', 'formatImage', '&lt;thumb height="56" path="{ROOT_DIR}/public/upfiles/corporative/" /&gt;');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
