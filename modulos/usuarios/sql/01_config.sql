INSERT  INTO `base_modulos`(`nombre`, `activo`, `url` ) VALUES ( 'usuarios', true, '/admportal/modulos/usuarios' ) ON DUPLICATE KEY UPDATE activo = true;
INSERT  INTO `usuarios_usuarios`(`nombre_p`, `login`, `password`, `activo`) VALUES ('Administrador','admin', AES_ENCRYPT('zweicom', 'zwpass'), true );
INSERT  INTO `usuarios_perfiles` (`nombre`) VALUES ( 'admin' );

INSERT INTO `mantenedor_mantenedores` ( `nombre`, `titulo`, `url` ) VALUES ( 'usuarios', 'Administrador de usuarios', '/admportal/modulos/usuarios/web/ctrl/usuarios.php' );
INSERT INTO `mantenedor_mantenedores` ( `nombre`, `titulo`, `url` ) VALUES ( 'perfiles', 'Administrador de perfiles', '/admportal/modulos/usuarios/web/ctrl/perfiles.php' );
INSERT INTO `mantenedor_mantenedores` ( `nombre`, `titulo`, `url` ) VALUES ( 'roles', 'Administrador de roles', '/admportal/modulos/usuarios/web/ctrl/roles.php' );

INSERT INTO `mantenedor_listas` ( `nombre`, `url` ) VALUES ( 'perfiles', '/admportal/modulos/usuarios/web/ctrl/perfiles.php' );
INSERT INTO `mantenedor_listas` ( `nombre`, `url` ) VALUES ( 'estado_usr', '/admportal/modulos/usuarios/web/ctrl/estadousr.php' );

-- Campos Mantenedor usuario
INSERT INTO `mantenedor_campos` ( `id_mant`, `id_tipo`, `nombre`, `etiqueta`, `hint`, `id_lista` )
	select mantenedor_mantenedores.id, mantenedor_tipos.id, 'id', 'Id', '', 0
	FROM mantenedor_mantenedores, mantenedor_tipos WHERE 
	mantenedor_mantenedores.nombre = 'usuarios' AND mantenedor_tipos.nombre = 'field';

INSERT INTO `mantenedor_campos` ( `id_mant`, `id_tipo`, `nombre`, `etiqueta`, `hint`, `id_lista` )
	select mantenedor_mantenedores.id, mantenedor_tipos.id, 'nombre_p', 'Primer Nombre', '', 0
	FROM mantenedor_mantenedores, mantenedor_tipos WHERE 
	mantenedor_mantenedores.nombre = 'usuarios' AND mantenedor_tipos.nombre = 'field';

INSERT INTO `mantenedor_campos` ( `id_mant`, `id_tipo`, `nombre`, `etiqueta`, `hint`, `id_lista` )
	select mantenedor_mantenedores.id, mantenedor_tipos.id, 'nombre_s', 'Segundo Nombre', '', 0
	FROM mantenedor_mantenedores, mantenedor_tipos WHERE 
	mantenedor_mantenedores.nombre = 'usuarios' AND mantenedor_tipos.nombre = 'field';

INSERT INTO `mantenedor_campos` ( `id_mant`, `id_tipo`, `nombre`, `etiqueta`, `hint`, `id_lista` )
	select mantenedor_mantenedores.id, mantenedor_tipos.id, 'apellido_p', 'Apellido Paterno', '', 0
	FROM mantenedor_mantenedores, mantenedor_tipos WHERE 
	mantenedor_mantenedores.nombre = 'usuarios' AND mantenedor_tipos.nombre = 'field';

INSERT INTO `mantenedor_campos` ( `id_mant`, `id_tipo`, `nombre`, `etiqueta`, `hint`, `id_lista` )
	select mantenedor_mantenedores.id, mantenedor_tipos.id, 'apellido_m', 'Apellido Materno', '', 0
	FROM mantenedor_mantenedores, mantenedor_tipos WHERE 
	mantenedor_mantenedores.nombre = 'usuarios' AND mantenedor_tipos.nombre = 'field';

INSERT INTO `mantenedor_campos` ( `id_mant`, `id_tipo`, `nombre`, `etiqueta`, `hint`, `id_lista` )
	select mantenedor_mantenedores.id, mantenedor_tipos.id, 'login', 'Nombre de usuario', '', 0
	FROM mantenedor_mantenedores, mantenedor_tipos WHERE 
	mantenedor_mantenedores.nombre = 'usuarios' AND mantenedor_tipos.nombre = 'field';

INSERT INTO `mantenedor_campos` ( `id_mant`, `id_tipo`, `nombre`, `etiqueta`, `hint`, `id_lista` )
	select mantenedor_mantenedores.id, mantenedor_tipos.id, 'pass', 'Clave de ingreso', '', 0
	FROM mantenedor_mantenedores, mantenedor_tipos WHERE 
	mantenedor_mantenedores.nombre = 'usuarios' AND mantenedor_tipos.nombre = 'pass';

INSERT INTO `mantenedor_campos` ( `id_mant`, `id_tipo`, `nombre`, `etiqueta`, `hint`, `id_lista` )
	select mantenedor_mantenedores.id, mantenedor_tipos.id, 'activo', 'Estado del usuario', '', mantenedor_multiple.id
	FROM mantenedor_mantenedores, mantenedor_tipos, mantenedor_multiple WHERE 
	mantenedor_mantenedores.nombre = 'usuarios' AND mantenedor_tipos.nombre = 'radio'
	AND mantenedor_multiple.nombre = 'estado_usr';



-- Campos Mantenedor perfiles
INSERT INTO `mantenedor_campos` ( `id_mant`, `id_tipo`, `nombre`, `etiqueta`, `hint`, `id_lista` )
	select mantenedor_mantenedores.id, mantenedor_tipos.id, 'id', 'Id', '', 0
	FROM mantenedor_mantenedores, mantenedor_tipos WHERE 
	mantenedor_mantenedores.nombre = 'perfiles' AND mantenedor_tipos.nombre = 'field';

INSERT INTO `mantenedor_campos` ( `id_mant`, `id_tipo`, `nombre`, `etiqueta`, `hint`, `id_lista` )
	select mantenedor_mantenedores.id, mantenedor_tipos.id, 'nombre', 'Nombre Perfil', '', 0
	FROM mantenedor_mantenedores, mantenedor_tipos WHERE 
	mantenedor_mantenedores.nombre = 'perfiles' AND mantenedor_tipos.nombre = 'field';




-- Campos Mantenedor roles
INSERT INTO `mantenedor_campos` ( `id_mant`, `id_tipo`, `nombre`, `etiqueta`, `hint`, `id_lista` )
	select mantenedor_mantenedores.id, mantenedor_tipos.id, 'id', 'Id', '', 0
	FROM mantenedor_mantenedores, mantenedor_tipos WHERE 
	mantenedor_mantenedores.nombre = 'roles' AND mantenedor_tipos.nombre = 'field';

INSERT INTO `mantenedor_campos` ( `id_mant`, `id_tipo`, `nombre`, `etiqueta`, `hint`, `id_lista` )
	select mantenedor_mantenedores.id, mantenedor_tipos.id, 'login', 'Login usuario', '', 0
	FROM mantenedor_mantenedores, mantenedor_tipos WHERE 
	mantenedor_mantenedores.nombre = 'roles' AND mantenedor_tipos.nombre = 'field';

INSERT INTO `mantenedor_campos` ( `id_mant`, `id_tipo`, `nombre`, `etiqueta`, `hint`, `id_lista` )
	select mantenedor_mantenedores.id, mantenedor_tipos.id, 'perfiles', 'Perfiles del usuario', '', mantenedor_multiple.id
	FROM mantenedor_mantenedores, mantenedor_tipos, mantenedor_multiple WHERE 
	mantenedor_mantenedores.nombre = 'roles' AND mantenedor_tipos.nombre = 'check'
	AND mantenedor_multiple.nombre = 'perfiles';


-- Permisos Operaciones Mantenedor usuarios
-- INSERT INTO `mantenedor_permoper` ( `id_mant`, `id_tipo`, `nombre`, `etiqueta`, `hint`, `id_lista` )
-- 	select mantenedor_mantenedores.id, mantenedor_tipos.id, 'activo', 'Estado del usuario', '', mantenedor_multiple.id
-- 	FROM mantenedor_mantenedores, mantenedor_tipos, mantenedor_multiple WHERE 
-- 	mantenedor_mantenedores.nombre = 'usuarios' AND mantenedor_tipos.nombre = 'radio'
-- 	AND mantenedor_multiple.nombre = 'estado_usr';

INSERT INTO `mantenedor_permoper` ( `id_mant`, `id_perfil` ) select mantenedor_mantenedores.id, usuarios_perfiles.id from mantenedor_mantenedores, usuarios_perfiles where usuarios_perfiles.nombre = 'admin' and mantenedor_mantenedores.nombre = 'usuarios';
INSERT INTO `mantenedor_permoper` ( `id_mant`, `id_perfil` ) select mantenedor_mantenedores.id, usuarios_perfiles.id from mantenedor_mantenedores, usuarios_perfiles where usuarios_perfiles.nombre = 'admin' and mantenedor_mantenedores.nombre = 'perfiles';
INSERT INTO `mantenedor_permoper` ( `id_mant`, `id_perfil` ) select mantenedor_mantenedores.id, usuarios_perfiles.id from mantenedor_mantenedores, usuarios_perfiles where usuarios_perfiles.nombre = 'admin' and mantenedor_mantenedores.nombre = 'roles';

