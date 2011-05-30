DROP TABLE IF EXISTS `mantenedor_tipos`;
CREATE TABLE `mantenedor_tipos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'Id de tipo',
  `nombre` VARCHAR(30) COMMENT 'Nombre del tipo',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `key_nombre` (`nombre` )
) ENGINE=INNODB DEFAULT CHARSET=latin1 COMMENT='Tabla de permisos';


DROP TABLE IF EXISTS `mantenedor_oper`;
CREATE TABLE `mantenedor_oper` (
  `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'Id de operacion',
  `nombre` VARCHAR(30) COMMENT 'Nombre de la operacion',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `key_nombre` (`nombre` )
) ENGINE=INNODB DEFAULT CHARSET=latin1 COMMENT='Tabla de operaciones';


DROP TABLE IF EXISTS `mantenedor_mantenedores`;
CREATE TABLE `mantenedor_mantenedores` (
  `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'Id de mantenedor',
  `padre` INT(11) NOT NULL COMMENT 'Id de mantenedor padre',
  `nombre` VARCHAR(30) COMMENT 'Nombre del mantenedor',
  `titulo` VARCHAR(30) NOT NULL COMMENT 'Titulo mantenedor',
  `url` VARCHAR(100) NOT NULL COMMENT 'Url a invocar para ejecutar create/set/get/del/load/erase',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `key_titulo` (`titulo`)
) ENGINE=INNODB DEFAULT CHARSET=latin1 COMMENT='Tabla de mantenedores';



DROP TABLE IF EXISTS `mantenedor_campos`;
CREATE TABLE `mantenedor_campos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'Id de campo',
  `id_mant` INT(11) NOT NULL COMMENT 'Id de mantenedor al cual pertenece',
  `id_tipo` INT(11) NOT NULL COMMENT 'Id de tipo',
  `nombre` VARCHAR(30) NOT NULL COMMENT 'Etiqueta del campo',
  `etiqueta` VARCHAR(30) NOT NULL COMMENT 'Etiqueta del campo',
  `hint` VARCHAR(30) NOT NULL COMMENT 'Tips de llenado',
  `id_lista` INT(11) DEFAULT 0 COMMENT 'Identificado seleccion multiple asociado',
  `llave` BOOL DEFAULT FALSe COMMENT 'Es llave o no',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `key_manteti` (`id_mant`, `etiqueta` )
) ENGINE=INNODB DEFAULT CHARSET=latin1 COMMENT='Tabla definición de campos';



DROP TABLE IF EXISTS `mantenedor_listas`;
CREATE TABLE `mantenedor_listas` (
  `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'Id de selección multiple',
  `nombre` VARCHAR(30) NOT NULL COMMENT 'Nombre de la selección',
  `url` VARCHAR(100) NOT NULL COMMENT 'Url a invocar para obtener opciones',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `key_nombre` (`nombre` )
) ENGINE=INNODB DEFAULT CHARSET=latin1 COMMENT='Tabla definición de selecciones multiples';


DROP TABLE IF EXISTS `mantenedor_permoper`;
CREATE TABLE `mantenedor_permoper` (
  `id_mant` INT(11) NOT NULL COMMENT 'Id de mantenedor',
  `id_perfil` INT(11) NOT NULL COMMENT 'Id de perfil de usuario para el cual el mantenedor esta habilitado',
  `id_oper` INT(11) NOT NULL COMMENT 'Id Operación permitida create/set/get/del/load/erase',
  UNIQUE KEY `key_mantperfoper` (`id_mant`, `id_perfil`, `id_oper` )
) ENGINE=INNODB DEFAULT CHARSET=latin1 COMMENT='Tabla permisos operaciones';


DROP TABLE IF EXISTS `mantenedor_permcamp`;
CREATE TABLE `mantenedor_permcamp` (
  `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'Id permiso',
  `id_mant` INT(11) NOT NULL COMMENT 'Id de mantenedor al cual pertenece',
  `id_campo` INT(11) NOT NULL COMMENT 'Id de mantenedor al cual pertenece',
  `id_perfil` INT(11) NOT NULL COMMENT 'Id del perfil permitido',
  `editar` BOOLEAN DEFAULT true COMMENT 'Indica si el campo es editable',
  `ver` BOOLEAN DEFAULT true COMMENT 'Indica si el campo es visible',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `key_perfilcampo` ( `id_campo`, `id_perfil` )
) ENGINE=INNODB DEFAULT CHARSET=latin1 COMMENT='Tabla permisos de campos';



