INSERT  INTO `base_modulos`(`nombre`, `url`, `activo`) VALUES ( 'mantenedor', '/admportal/modulos/mantenedor/web/ctrl/tree', true ) ON DUPLICATE KEY UPDATE activo = true;
INSERT INTO `mantenedor_tipos` (`nombre`) VALUES ('field'),('pass'),('spin'),('combo'),('list'),('check'),('radio'),('date');


