DROP TABLE IF EXISTS `mantenedor_mantenedores`;
CREATE TABLE `mantenedor_mantenedores` (
  `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'Id de mantenedor',
  `nombre` VARCHAR(30) COMMENT 'Nombre mantendor',
  `titulo` VARCHAR(30) NOT NULL COMMENT 'Titulo mantenedor',
  `url` VARCHAR(100) NOT NULL COMMENT 'Url a invocar para ejecutar list/get/verif/set/load',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `key_nombre` (`nombre`)
) ENGINE=INNODB DEFAULT CHARSET=latin1 COMMENT='Tabla de mantenedores';


DROP TABLE IF EXISTS `mantenedor_permisos`;
CREATE TABLE `mantenedor_permisos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'Id de permiso',
  `id_mantenedor` INT(11) NOT NULL COMMENT 'Id de mantenedor',
  `id_perfil` INT(11) NOT NULL COMMENT 'Id de perfil de usuario para el cual el mantenedor esta habilitado',
  `oper` VARCHAR(10) NOT NULL COMMENT 'Operación permitida list/get/verif/set/load',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `key_mantperf` (`id_mantenedor`, `id_perfil` )
) ENGINE=INNODB DEFAULT CHARSET=latin1 COMMENT='Tabla de permisos';


DROP TABLE IF EXISTS `mantenedor_tipos`;
CREATE TABLE `mantenedor_tipos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'Id de tipo',
  `nombre` VARCHAR(30) COMMENT 'Nombre del tipo',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `key_nombre` (`nombre` )
) ENGINE=INNODB DEFAULT CHARSET=latin1 COMMENT='Tabla de permisos';


DROP TABLE IF EXISTS `mantenedor_campos`;
CREATE TABLE `mantenedor_campos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'Id de campo',
  `id_mant` INT(11) NOT NULL COMMENT 'Id de mantenedor al cual pertenece',
  `id_tipo` INT(11) NOT NULL COMMENT 'Id de tipo',
  `nombre` VARCHAR(30) NOT NULL COMMENT 'Nombre del campo',
  `etiqueta` VARCHAR(30) NOT NULL COMMENT 'Etiqueta del campo',
  `hint` VARCHAR(30) NOT NULL COMMENT 'Tips de llenado',
  `id_multiple` INT(11) DEFAULT 0 COMMENT 'Identificado seleccion multiple asociado',
  `fijo` BOOLEAN DEFAULT false COMMENT 'Indica si el campo es editable',
  PRIMARY KEY  (`id`)
) ENGINE=INNODB DEFAULT CHARSET=latin1 COMMENT='Tabla definición de campos';


DROP TABLE IF EXISTS `mantenedor_keys`;
CREATE TABLE `mantenedor_keys` (
  `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'Id de campo',
  `id_mant` INT(11) NOT NULL COMMENT 'Id de mantenedor al cual pertenece',
  `id_campo` INT(11) NOT NULL COMMENT 'Id del campo que es una key',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `key_key` (`id_mant`, `id_campo` )
) ENGINE=INNODB DEFAULT CHARSET=latin1 COMMENT='Tabla definición de campos';


DROP TABLE IF EXISTS `mantenedor_multiple`;
CREATE TABLE `mantenedor_multiple` (
  `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'Id de selección multiple',
  `nombre` VARCHAR(30) NOT NULL COMMENT 'Nombre de la selección',
  `url` VARCHAR(100) NOT NULL COMMENT 'Url a invocar para obtener opciones',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `key_nombre` (`nombre` )
) ENGINE=INNODB DEFAULT CHARSET=latin1 COMMENT='Tabla definición de selecciones multiples';


DROP TABLE IF EXISTS `mantenedor_permisos`;
CREATE TABLE `mantenedor_permisos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'Id de permiso',
  `id_mant` INT(11) NOT NULL COMMENT 'Id de mantenedor al cual pertenece',
  `id_perfil` INT(11) NOT NULL COMMENT 'Id del perfil permitido',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `key_perfil` ( `id_perfil` )
) ENGINE=INNODB DEFAULT CHARSET=latin1 COMMENT='Tabla definición de campos';


