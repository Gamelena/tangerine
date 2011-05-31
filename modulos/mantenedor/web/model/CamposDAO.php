<?php

require("admportal/base/web/model/BaseDAO.php");

class CamposDAO extends BaseDAO {

	function __construct($dsn) {

		parent::__construct($dsn);
	}

	public function create( $args, &$resp ){
		$id_mant = $args->id_mant;
		$id_tipo = $args->id_tipo;
		$etiqueta = $args->etiqueta;
		$hint = $args->hint;
		$id_lista = $args->id_lista;
		$llave = $args->llave;
		
		$db = R::$adapter;
		
		try{
			$rtn = $db->exec( "INSERT  INTO mantenedor_campos ( id_mant, id_tipo, etiqueta, hint, id_lista, llave )
								VALUES ( '$id_mant', '$id_tipo', '$etiqueta', '$hint', '$id_lista', '$llave' );" );

			$rtn = $db->getRow("SELECT id FROM mantenedor_campos WHERE etiqueta = '$etiqueta';");

			if(!empty ($rtn)){
				$resp[ 'error' ] = "";
				$resp[ 'data' ] = array();
				$resp[ 'data' ][ 'id' ] = $rtn[ 'id' ];
			}
			else {
				$resp[ 'error' ] = "Error al crear el campo \"$etiqueta\".";
			}
		}catch( Exception $ex) {
			$rtn = $db->getRow("SELECT nombre FROM mantenedor_mantenedores WHERE id = '$id_mant';");
			$nom_mant = $rtn[ 'nombre' ];
			$resp[ 'error' ] = "El campo $nombre ya existe en el manenedor $nom_mant.";
			$resp[ 'errmsg' ] = $ex->getMessage();
		}
    }

	public function get( $args, &$resp ){
		$id = $args->id;

		$db = R::$adapter;

		try{
			if( $id > 0 ){
				$campo = $db->getRow("SELECT id, nombre, id_mant, id_tipo, etiqueta, hint, id_lista FROM mantenedor_campos WHERE id = $id;");

				if(!empty ($rtn)){
					$resp[ 'error' ] = "";
					$resp[ 'data' ] = array();
					$resp[ 'data' ][ 'id' ] = $campo[ 'id' ];
					$resp[ 'data' ][ 'nombre' ] = $campo[ 'nombre' ];
					$resp[ 'data' ][ 'id_mant' ] = $campo[ 'id_mant' ];
					$resp[ 'data' ][ 'id_tipo' ] = $campo[ 'id_tipo' ];
					$resp[ 'data' ][ 'etiqueta' ] = $campo[ 'etiqueta' ];
					$resp[ 'data' ][ 'hint' ] = $campo[ 'hint' ];
					$resp[ 'data' ][ 'id_lista' ] = $campo[ 'id_lista' ];
				}
				else {
					$resp[ 'error' ] = "El campo no existe.";
				}
			} else {
				$campos = $db->get("SELECT id, nombre, id_mant, id_tipo, etiqueta, hint, id_lista FROM mantenedor_campos;");

				$resp[ 'error' ] = "";
				$resp[ 'count' ] = 0;
				if(!empty ($campos)){
					$resp[ 'data' ] = array();
					$x = 0;
					foreach( $campos as $campo ){
						$resp[ 'data' ][ $x ] = array();
						$resp[ 'data' ][ $x ][ 'id' ] = $campo[ 'id' ];
						$resp[ 'data' ][ $x ][ 'nombre' ] = $campo[ 'nombre' ];
						$resp[ 'data' ][ $x ][ 'id_mant' ] = $campo[ 'id_mant' ];
						$resp[ 'data' ][ $x ][ 'id_tipo' ] = $campo[ 'id_tipo' ];
						$resp[ 'data' ][ $x ][ 'etiqueta' ] = $campo[ 'etiqueta' ];
						$resp[ 'data' ][ $x ][ 'hint' ] = $campo[ 'hint' ];
						$resp[ 'data' ][ $x ][ 'id_lista' ] = $campo[ 'id_lista' ];
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
		$id_mant = $args->id_mant;
		$id_tipo = $args->id_tipo;
		$etiqueta = $args->etiqueta;
		$hint = $args->hint;
		$id_lista = $args->id_lista;

		$db = R::$adapter;

		try{
			$rtn = $db->exec( "UPDATE mantenedor_campos SET
								`nombre` = '$nombre',
								`id_mant` = '$id_mant',
								`id_tipo` = '$id_tipo',
								`etiqueta` = '$etiqueta',
								`hint` = '$hint',
								`id_lista` = '$id_lista',
								WHERE `id` = '$id';" );
			$resp[ 'error' ] = "";
		}catch( Exception $ex) {
			$rtn = $db->getRow("SELECT nombre FROM mantenedor_mantenedores WHERE id = '$id_mant';");
			$nom_mant = $rtn[ 'nombre' ];
			$resp[ 'error' ] = "El campo $nombre ya existe en el manenedor $nom_mant.";
			$resp[ 'errmsg' ] = $ex->getMessage();
		}
    }
	public function del( $key, &$resp ){
		$id = $args->id;

		$db = R::$adapter;

		try{
			$rtn = $db->exec("DELETE FROM mantenedor_campos WHERE id = $id;");
			$rtn = $db->exec("DELETE FROM mantenedor_permcamp WHERE id_campo = $id;");
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
