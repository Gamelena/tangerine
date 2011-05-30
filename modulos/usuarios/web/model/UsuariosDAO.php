<?php

require("admportal/base/web/model/BaseDAO.php");

class UsuariosDAO extends BaseDAO {

	function __construct($dsn) {

		parent::__construct($dsn);
	}

	private function defGrupo( $id_usuario, $perfiles ){
		$db = R::$adapter;
		$rtn = $db->exec( "DELETE FROM usuarios_grupos WHERE id_usuario = '$id_usuario';");
		foreach( $perfiles as $id_perfil ){
			$rtn = $db->exec( "INSERT INTO usuarios_grupos ( id_perfil, id_usuario ) VALUES ( '$id_perfil', '$id_usuario' );");
		}
	}

	public function create( $args, $resp ){
		$nombre_p = $args->nombre_p;
		$nombre_s = $args->nombre_s;
		$apellido_p = $args->apellido_p;
		$apellido_m = $args->apellido_m;
		$login = $args->login;
		$password = $args->password;
		$activo = $args->activo;
		$perfiles = $args->perfiles;

		$db = R::$adapter;
		
		if( strlen( $nombre_p ) == 0 ||
			strlen( $login ) == 0 ||
			strlen( $password ) == 0 ){
			$resp[ 'error' ] = "Informacion insuficiente para crear usuario.";
			return;
		}
		if( strlen( $activo ) == 0 ){
			$activo = 1;
		}

		try{
			$rtn = $db->exec( "INSERT  INTO usuarios_usuarios ( nombre_p, nombre_s, apellido_p, apellido_m, login, password, activo )
								VALUES ( '$nombre_p', '$nombre_s', '$apellido_p', '$apellido_m', '$login', AES_ENCRYPT('$password', 'zwpass'), '$activo');" );

			$rtn = $db->getRow("SELECT id FROM usuarios_usuarios WHERE login = '$login';");

			if(!empty ($rtn)){
				$id_usuario = $rtn[ 'id' ];
				$this->defGrupo( $id_usuario, $perfiles );
				$resp[ 'error' ] = "";
				$resp[ 'data' ] = array();
				$resp[ 'data' ][ 'id' ] = $rtn[ 'id' ];
			}
			else {
				$resp[ 'error' ] = "Error al crear el usuario $login.";
			}
		}catch( Exception $ex) {
			$resp[ 'error' ] = "El usuario $login ya existe.";
			$resp[ 'errmsg' ] = $ex->getMessage();
		}
    }

	public function get( $args, &$resp ){
		$id = $args->id;

		$db = R::$adapter;

		try{
			if( $id > 0 )
			{
				$usuario = $db->getRow("SELECT id, nombre_p, nombre_s, apellido_p, apellido_m, login, AES_DECRYPT( `password`, 'zwpass') as password, activo FROM usuarios_usuarios WHERE id = $id;");

				if(!empty ($usuario)){
					$resp[ 'error' ] = "";
					$resp[ 'data' ] = array();
					$resp[ 'data' ][ 'id' ] = $usuario[ 'id' ];
					$resp[ 'data' ][ 'nombre_p' ] = $usuario[ 'nombre_p' ];
					$resp[ 'data' ][ 'nombre_s' ] = $usuario[ 'nombre_s' ];
					$resp[ 'data' ][ 'apellido_p' ] = $usuario[ 'apellido_p' ];
					$resp[ 'data' ][ 'apellido_m' ] = $usuario[ 'apellido_m' ];
					$resp[ 'data' ][ 'login' ] = $usuario[ 'login' ];
					$resp[ 'data' ][ 'password' ] = $usuario[ 'password' ];
					$resp[ 'data' ][ 'activo' ] = $usuario[ 'activo' ];
					$resp[ 'data' ][ 'perfiles' ] = array();

					$perfiles = $db->get("SELECT id_perfil FROM usuarios_grupos WHERE id_usuario = '$id';");
					if(!empty ($perfiles)){
						$resp[ 'data' ][ 'perfiles' ] = array();
						$i = 0;
						foreach( $perfiles as $perfil ) {
							$resp[ 'data' ][ 'perfiles' ][ $i ] = $perfil[ 'id_perfil' ];
							$i = $i + 1;
						}
					}
					$resp[ 'error' ] = "";
				}
				else {
					$resp[ 'error' ] = "El usuario no existe.";
				}
			} else{
				$usuarios = $db->get("SELECT id, nombre_p, nombre_s, apellido_p, apellido_m, login, AES_DECRYPT( `password`, 'zwpass') as password, activo FROM usuarios_usuarios;");

				$resp[ 'error' ] = "";
				$resp[ 'count' ] = 0;
				if(!empty ($usuarios)){
					$resp[ 'data' ] = array();
					$x = 0;
					foreach( $usuarios as $usuario ){
						$resp[ 'data' ][ $x ] = array();
						$resp[ 'data' ][ $x ][ 'id' ] = $usuario[ 'id' ];
						$resp[ 'data' ][ $x ][ 'nombre_p' ] = $usuario[ 'nombre_p' ];
						$resp[ 'data' ][ $x ][ 'nombre_s' ] = $usuario[ 'nombre_s' ];
						$resp[ 'data' ][ $x ][ 'apellido_p' ] = $usuario[ 'apellido_p' ];
						$resp[ 'data' ][ $x ][ 'apellido_m' ] = $usuario[ 'apellido_m' ];
						$resp[ 'data' ][ $x ][ 'login' ] = $usuario[ 'login' ];
						$resp[ 'data' ][ $x ][ 'password' ] = $usuario[ 'password' ];
						$resp[ 'data' ][ $x ][ 'activo' ] = $usuario[ 'activo' ];
						$resp[ 'data' ][ $x ][ 'perfiles' ] = array();
						
						$id = $usuario[ 'id' ];
						$perfiles = $db->get("SELECT id_perfil FROM usuarios_grupos WHERE id_usuario = '$id';");
						if(!empty ($perfiles)){
							$resp[ 'data' ][ $x ][ 'perfiles' ] = array();
							$i = 0;
							foreach( $perfiles as $perfil ) {
								$resp[ 'data' ][ $i ][ 'perfiles' ][ $i ] = $perfil[ 'id_perfil' ];
								$i = $i + 1;
							}
						}
						$x = $x + 1;
					}
					$resp[ 'count' ] = $x;
				}
			}
		}catch( Exception $ex) {
			$resp[ 'error' ] = "Comunicación base de datos.";
			$resp[ 'errmsg' ] = $ex->getMessage();
		}
	}

	public function set( $args, $resp ){
		$id = $args->id;
		$nombre_p = $args->nombre_p;
		$nombre_s = $args->nombre_s;
		$apellido_p = $args->apellido_p;
		$apellido_m = $args->apellido_m;
		$login = $args->login;
		$password = $args->password;
		$activo = $args->activo;
		$perfiles = $args->perfiles;

		$db = R::$adapter;
		
		if( strlen( $nombre_p ) == 0 ||
			strlen( $login ) == 0 ||
			strlen( $password ) == 0 ){
			$resp[ 'error' ] = "Informacion insuficiente para actualizar usuario.";
			return;
		}
		
		try{
			$rtn = $db->exec( "UPDATE usuarios_usuarios SET
								`nombre_p` = '$nombre_p', `nombre_s` = '$nombre_s',
								`apellido_p` = '$apellido_p', `apellido_m` = '$apellido_m',
								`login` = '$login', `password` = AES_ENCRYPT('$password', 'zwpass'),
								`activo` = '$activo' 
								WHERE `id` = '$id';" );
			$this->defGrupo( $id, $perfiles );

			$resp[ 'error' ] = "";
		}catch( Exception $ex) {
			$resp[ 'error' ] = "El usuario $login ya existe.";
			$resp[ 'errmsg' ] = $ex->getMessage();
		}
    }
	public function del( $args, &$resp ){
		$id = $args->id;

		$db = R::$adapter;
		
		try{
			$rtn = $db->exec("DELETE FROM usuarios_usuarios WHERE id = $id;");
			$rtn = $db->exec("DELETE FROM usuarios_grupos WHERE id_usuario = $id;");
			$resp[ 'error' ] = "";
		}catch( Exception $ex) {
			$resp[ 'error' ] = "Comunicación base de datos.";
			$resp[ 'errmsg' ] = $ex->getMessage();
		}
	}
	public function load(  &$resp ){
		$resp[ 'error' ] = "";
    }
	public function erase(  &$resp ){
		$resp[ 'error' ] = "";
    }
}

?>
