DROP TABLE IF EXISTS `usuarios_usuarios`;
CREATE TABLE `usuarios_usuarios` (
  `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'Id de usuario',
  `nombre_p` VARCHAR(30) NOT NULL COMMENT 'Primer nombre',
  `nombre_s` VARCHAR(30) DEFAULT '' COMMENT 'Segundo nombre',
  `apellido_p` VARCHAR(30) DEFAULT '' COMMENT 'Apellido paterno',
  `apellido_m` VARCHAR(30) DEFAULT '' COMMENT 'Apellido materno',
  `login` VARCHAR(30) NOT NULL COMMENT 'Login',
  `password` VARCHAR(128) NOT NULL COMMENT 'Password',
  `activo` BOOLEAN DEFAULT FALSE COMMENT 'Estado del usuario',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `key_login` (`login`)
) ENGINE=INNODB DEFAULT CHARSET=latin1 COMMENT='Tabla de usuarios';


DROP TABLE IF EXISTS `usuarios_perfiles`;
CREATE TABLE `usuarios_perfiles` (
  `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'Id del perfil',
  `nombre` VARCHAR(30) NOT NULL COMMENT 'Nombre del perfil',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `key_nombre` (`nombre`)
) ENGINE=INNODB DEFAULT CHARSET=latin1 COMMENT='Tabla de Perfiles';


DROP TABLE IF EXISTS `usuarios_roles`;
CREATE TABLE `usuarios_roles` (
  `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'Id de rol',
  `id_usuario` INT(11) NOT NULL COMMENT 'Id de usuario',
  `id_perfil` INT(11) NOT NULL COMMENT 'Id de perfil',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `key_usrperf` (`id_usuario`, `id_perfil` )
) ENGINE=INNODB DEFAULT CHARSET=latin1 COMMENT='Tabla de Roles';

