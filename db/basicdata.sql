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
-- Volcado de datos para la tabla `acl_actions`
--

INSERT INTO `acl_actions` (`id`, `title`) VALUES
('EDIT', 'Editar'),
('ADD', 'Agregar'),
('DELETE', 'Eliminar'),
('LIST', 'Listar');


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
-- Volcado de datos para la tabla `acl_roles`
--

INSERT INTO `acl_roles` (`id`, `role_name`, `description`, `approved`) VALUES
(1, 'Soporte', 'Perfil root.', '1'),
(2, 'Administrador', 'Administrador con acceso a hacer modificaciones administrativas y manejo de usuarios.', '1'),
(3, 'Consultas', 'Acceso a listados y reportes.', '1');

-- --------------------------------------------------------

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
-- Volcado de datos para la tabla `web_settings`
--

INSERT INTO `web_settings` (`id`, `list`, `value`, `type`, `description`, `ord`, `group`, `function`, `approved`, `path`, `regExp`, `invalidMessage`, `promptMessage`, `formatter`, `xml_children`) VALUES
('query_log', '', '1', 'dijit-form-check-box', '', 1, 'Debug', '', '1', '', '', '', '', '', ''),
('transactions_log', '', '1', 'dijit-form-check-box', '', 1, 'Debug', '', '1', '', '', '', '', '', ''),
('titulo_adm', '', 'Gateway USSD', 'dijit-form-validation-text-box', '', 1, 'Admin', '', '1', '', '', '', '', '', ''),
('url_logo_oper', '', '4fcd081blogo_movistar68x50.png', 'dojox-form-uploader', '', 3, 'Admin', '', '1', '{ROOT_DIR}/public/upfiles/', '', '', '', 'formatImage', '&lt;thumb height=&quot;56px&quot; path=&quot;{ROOT_DIR}/upfiles/settings/&quot;/&gt;'),
('url_logo_zweicom', '', '3702066dlogo-zweicom-20x26.png', 'dojox-form-uploader', '', 3, 'Admin', '', '1', '{ROOT_DIR}/public/upfiles/', '', '', '', 'formatImage', '&lt;thumb height=&quot;18px&quot; path=&quot;{ROOT_DIR}/upfiles/settings/&quot;/&gt;'),
('credits', '', '&copy; Zweicom 2012', 'dijit-form-validation-text-box', '', 2, 'Admin', '', '1', '', '', '', '', '', '');

-- --------------------------------------------------------


--
-- Base de datos: `USSD`
--

--
-- Volcado de datos para la tabla `acl_modules_actions`
--

INSERT INTO `acl_modules_actions` (`id`, `acl_modules_id`, `acl_actions_id`) VALUES
(162, 5, 'EDIT'),
(163, 5, 'ADD'),
(164, 5, 'DELETE'),
(165, 5, 'LIST'),
(171, 4, 'EDIT'),
(172, 4, 'ADD'),
(173, 4, 'DELETE'),
(261, 8, 'DELETE'),
(188, 2, 'LIST'),
(204, 9, 'EDIT'),
(205, 9, 'LIST'),
(260, 8, 'ADD'),
(187, 7, 'LIST'),
(62, 3, 'EDIT'),
(63, 3, 'LIST'),
(168, 1, 'LIST'),
(174, 4, 'LIST'),
(259, 8, 'EDIT'),
(262, 8, 'LIST'),
(266, 3, '"LIST"'),
(265, 3, '"EDIT"');

--
-- Volcado de datos para la tabla `acl_roles_modules_actions`
--

INSERT INTO `acl_roles_modules_actions` (`id`, `acl_roles_id`, `acl_modules_actions_id`, `permission`) VALUES
(1, 2, 168, 'ALLOW'),
(13, 2, 197, 'ALLOW'),
(3, 2, 188, 'ALLOW'),
(12, 2, 183, 'ALLOW'),
(5, 2, 62, 'ALLOW'),
(6, 2, 63, 'ALLOW'),
(7, 2, 192, 'ALLOW'),
(8, 2, 162, 'ALLOW'),
(9, 2, 163, 'ALLOW'),
(10, 2, 164, 'ALLOW'),
(11, 2, 165, 'ALLOW'),
(62, 3, 188, 'ALLOW'),
(15, 1, 168, 'ALLOW'),
(16, 1, 183, 'ALLOW'),
(17, 1, 188, 'ALLOW'),
(18, 1, 197, 'ALLOW'),
(19, 1, 198, 'ALLOW'),
(20, 1, 199, 'ALLOW'),
(21, 1, 200, 'ALLOW'),
(102, 1, 235, 'ALLOW'),
(101, 1, 234, 'ALLOW'),
(100, 1, 233, 'ALLOW'),
(99, 1, 232, 'ALLOW'),
(26, 1, 62, 'ALLOW'),
(27, 1, 63, 'ALLOW'),
(28, 1, 72, 'ALLOW'),
(29, 1, 73, 'ALLOW'),
(30, 1, 74, 'ALLOW'),
(31, 1, 75, 'ALLOW'),
(98, 1, 243, 'ALLOW'),
(33, 1, 171, 'ALLOW'),
(34, 1, 172, 'ALLOW'),
(35, 1, 173, 'ALLOW'),
(36, 1, 174, 'ALLOW'),
(37, 1, 187, 'ALLOW'),
(38, 1, 204, 'ALLOW'),
(39, 1, 205, 'ALLOW'),
(126, 1, 262, 'ALLOW'),
(125, 1, 261, 'ALLOW'),
(124, 1, 260, 'ALLOW'),
(123, 1, 259, 'ALLOW'),
(44, 1, 162, 'ALLOW'),
(45, 1, 163, 'ALLOW'),
(46, 1, 164, 'ALLOW'),
(47, 1, 165, 'ALLOW'),
(52, 2, 191, 'ALLOW'),
(61, 3, 183, 'ALLOW'),
(97, 1, 242, 'ALLOW'),
(96, 1, 241, 'ALLOW'),
(95, 1, 240, 'ALLOW'),
(107, 1, 228, 'ALLOW'),
(57, 1, 210, 'ALLOW'),
(58, 1, 211, 'ALLOW'),
(59, 1, 212, 'ALLOW'),
(60, 1, 213, 'ALLOW'),
(89, 3, 227, 'ALLOW'),
(93, 3, 243, 'ALLOW'),
(92, 3, 240, 'ALLOW'),
(91, 3, 242, 'ALLOW'),
(67, 3, 162, 'ALLOW'),
(68, 3, 163, 'ALLOW'),
(69, 3, 164, 'ALLOW'),
(70, 3, 165, 'ALLOW'),
(71, 3, 189, 'ALLOW'),
(72, 3, 190, 'ALLOW'),
(73, 3, 192, 'ALLOW'),
(74, 3, 168, 'ALLOW'),
(75, 3, 218, 'ALLOW'),
(90, 3, 241, 'ALLOW'),
(103, 5, 62, 'ALLOW'),
(104, 5, 63, 'ALLOW'),
(105, 3, 62, 'ALLOW'),
(106, 3, 63, 'ALLOW'),
(108, 1, 229, 'ALLOW'),
(109, 1, 230, 'ALLOW'),
(110, 1, 231, 'ALLOW'),
(111, 1, 218, 'ALLOW'),
(112, 1, 248, 'ALLOW'),
(113, 1, 249, 'ALLOW'),
(114, 1, 250, 'ALLOW'),
(115, 1, 251, 'ALLOW'),
(116, 1, 252, 'ALLOW'),
(117, 1, 253, 'ALLOW'),
(118, 1, 254, 'ALLOW'),
(119, 5, 183, 'ALLOW'),
(120, 5, 72, 'ALLOW'),
(121, 5, 254, 'ALLOW'),
(122, 5, 243, 'ALLOW');


DELETE FROM acl_roles_modules_actions WHERE acl_roles_id NOT IN (SELECT id FROM acl_roles);
DELETE FROM acl_modules_actions WHERE acl_modules_id NOT IN (SELECT id FROM acl_modules);