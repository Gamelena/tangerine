<?php

require("admportal/base/web/model/BaseDAO.php");

class ListasDAO extends BaseDAO {

	function __construct($dsn) {

		parent::__construct($dsn);
	}

	public function create( $args, &$resp ){
		$nombre = $args[ 'nombre' ];
		$url = $args[ 'url' ];

		$db = R::$adapter;
		
		try{
			$rtn = $db->exec( "INSERT  INTO mantenedor_listas ( nombre, url )
								VALUES ( '$nombre', '$url' );" );

			$rtn = $db->getRow("SELECT id FROM mantenedor_listas WHERE nombre = '$nombre';");

			if(!empty ($rtn)){
				$resp[ 'error' ] = "";
				$resp[ 'data' ] = array();
				$resp[ 'data' ][ 'id' ] = $rtn[ 'id' ];
			}
			else {
				$resp[ 'error' ] = "Error al crear la lista $nombre.";
			}
		}catch( Exception $ex) {
			$resp[ 'error' ] = "La lista $nombre ya existe.";
			$resp[ 'errmsg' ] = $ex->getMessage();
		}
    }

	public function get( $args, &$resp ){
		$id = $args[ 'id' ];

		$db = R::$adapter;

		try{
			if( $id > 0 ){
				$lista = $db->getRow("SELECT id, nombre, url FROM mantenedor_listas WHERE id = $id AND id_mant = $id_mant ;");

				if(!empty ($lista)){
					$resp[ 'error' ] = "";
					$resp[ 'data' ] = array();
					$resp[ 'data' ][ 'id' ] = $lista[ 'id' ];
					$resp[ 'data' ][ 'nombre' ] = $lista[ 'nombre' ];
					$resp[ 'data' ][ 'url' ] = $lista[ 'url' ];
				}
				else {
					$resp[ 'error' ] = "El mantenedor no existe.";
				}
			} else {
				$listas = $db->get("SELECT id, nombre, url FROM mantenedor_listas;");

				$resp[ 'error' ] = "";
				$resp[ 'count' ] = 0;
				if(!empty ($listas)){
					$resp[ 'data' ] = array();
					$x = 0;
					foreach( $listas as $lista ){
						$resp[ 'data' ][ $x ] = array();
						$resp[ 'data' ][ $x ][ 'id' ] = $lista[ 'id' ];
						$resp[ 'data' ][ $x ][ 'nombre' ] = $lista[ 'nombre' ];
						$resp[ 'data' ][ $x ][ 'url' ] = $lista[ 'url' ];
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
		$id = $args[ 'id' ];
		$nombre = $args[ 'nombre' ];
		$url = $args[ 'url' ];

		$db = R::$adapter;

		try{
			$rtn = $db->exec( "UPDATE mantenedor_listas SET
								`nombre` = '$nombre',
								`url` = '$url',
								WHERE `id` = '$id';" );
			$resp[ 'error' ] = "";
		}catch( Exception $ex) {
			$resp[ 'error' ] = "La lista $nombre ya existe.";
			$resp[ 'errmsg' ] = $ex->getMessage();
		}
    }
	public function del( $args, &$resp ){
		$id = $args[ 'id' ];

		$db = R::$adapter;
		
		try{
			$rtn = $db->exec("DELETE FROM mantenedor_listas WHERE id = $id;");
			$rtn = $db->exec("DELETE FROM mantenedor_campos WHERE id_lista = $id;");
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
