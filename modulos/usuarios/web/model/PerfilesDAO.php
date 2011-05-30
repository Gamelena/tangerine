<?php

require("admportal/base/web/model/BaseDAO.php");

class PerfilesDAO extends BaseDAO {

	function __construct($dsn) {

		parent::__construct($dsn);
	}

	public function create( $args, &$resp ){
		$nombre = $args->nombre;
		$descrip = $args->descrip;

		$db = R::$adapter;
		
		try{
			$rtn = $db->exec( "INSERT  INTO usuarios_perfiles ( nombre, descrip )
								VALUES ( '$nombre', '$descrip');" );

			$rtn = $db->getRow("SELECT id FROM usuarios_perfiles WHERE nombre = '$nombre';");

			if(!empty ($rtn)){
				$resp[ 'error' ] = "";
				$resp[ 'data' ] = array();
				$resp[ 'data' ][ 'id' ] = $rtn[ 'id' ];
			}
			else {
				$resp[ 'error' ] = "Error al crear el perfil $nombre.";
			}
		}catch( Exception $ex) {
			$resp[ 'error' ] = "El perfil $nombre ya existe.";
			$resp[ 'errmsg' ] = $ex->getMessage();
		}
    }

	public function get( $args, &$resp ){
		$id = $args->id;

		$db = R::$adapter;

		try{
			if( $id > 0 ){
				$perfil = $db->getRow("SELECT id, nombre, descrip FROM usuarios_perfiles WHERE id = $id;");

				if(!empty ($perfil)){
					$resp[ 'error' ] = "";
					$resp[ 'data' ] = array();
					$resp[ 'data' ][ 'id' ] = $perfil[ 'id' ];
					$resp[ 'data' ][ 'nombre' ] = $perfil[ 'nombre' ];
					$resp[ 'data' ][ 'descrip' ] = $perfil[ 'descrip' ];
				}
				else {
					$resp[ 'error' ] = "El perfil no existe.";
				}
			} else {
				$perfiles = $db->get("SELECT id, nombre, descrip FROM usuarios_perfiles;");

				$resp[ 'error' ] = "";
				$resp[ 'count' ] = 0;
				if(!empty ($perfiles)){
					$resp[ 'data' ] = array();
					$x = 0;
					foreach( $perfiles as $perfil ){
						$resp[ 'data' ][ $x ] = array();
						$resp[ 'data' ][ $x ][ 'id' ] = $perfil[ 'id' ];
						$resp[ 'data' ][ $x ][ 'nombre' ] = $perfil[ 'nombre' ];
						$resp[ 'data' ][ $x ][ 'descrip' ] = $perfil[ 'descrip' ];
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

	public function set( $args, &$resp ){
		$id = $args->id;
		$nombre = $args->nombre;
		$descrip = $args->descrip;

		$db = R::$adapter;

		try{
			$rtn = $db->exec( "UPDATE usuarios_perfiles SET
								`nombre` = '$nombre', `descrip` = '$descrip'
								WHERE `id` = '$id';" );
			$resp[ 'error' ] = "";
		}catch( Exception $ex) {
			$resp[ 'error' ] = "El perfil $nombre ya existe.";
			$resp[ 'errmsg' ] = $ex->getMessage();
		}
    }
	public function del( $args, &$resp ){
		$id = $args->id;

		$db = R::$adapter;

		try{
			$rtn = $db->exec("DELETE FROM usuarios_perfiles WHERE id = '$id';");
			$rtn = $db->exec("DELETE FROM usuarios_grupos WHERE id_perfil = '$id';");
			try{
				$rtn = $db->exec("DELETE FROM mantenedor_permcamp WHERE id_perfil = '$id';");
				$rtn = $db->exec("DELETE FROM mantenedor_permoper WHERE id_perfil = '$id';");
			}catch( Exception $ex) {
			}

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
