DROP TABLE IF EXISTS `base_parametros`;
CREATE TABLE `base_parametros` (
  `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'Id de parametro',
  `nombre` VARCHAR(30) NOT NULL COMMENT 'Nombre de parametro',
  `valor` VARCHAR(50) NOT NULL COMMENT 'Valor del parametro',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `key_nombre` (`nombre`)
) ENGINE=INNODB DEFAULT CHARSET=latin1 COMMENT='Tabla de parametros del administrador';


DROP TABLE IF EXISTS `base_modulos`;
CREATE TABLE `base_modulos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'Id del módulo',
  `nombre` VARCHAR(30) NOT NULL COMMENT 'Nombre del módulo',
  `url` VARCHAR(100) NOT NULL COMMENT 'Url a invocar para obtener el arbol de opciones.',
  `activo` BOOLEAN DEFAULT FALSE COMMENT 'Estado del módulo',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `key_nombre` (`nombre`)
) ENGINE=INNODB DEFAULT CHARSET=latin1 COMMENT='Tabla de módulos';

