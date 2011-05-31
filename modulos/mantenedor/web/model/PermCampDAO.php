<?php

require("admportal/base/web/model/BaseDAO.php");


class PermCampDAO extends BaseDAO {

	function __construct($dsn) {

		parent::__construct($dsn);
	}

	public function create( $args, &$resp ){
		$id_campo = $args[ 'id_campo' ];
		$id_perfil = $args[ 'id_perfil' ];
		$modif = $args[ 'modif' ];
		$visible = $args[ 'visible' ];
		
		$db = R::$adapter;
		
		try{
			$rtn = $db->exec( "INSERT  INTO mantenedor_permcamp ( id_campo, id_perfil, modif, visible )
								VALUES ( '$id_campo', '$id_perfil', '$modif', '$visible' );" );

			$rtn = $db->getRow("SELECT id FROM mantenedor_permcamp WHERE id_campo = '$id_campo' AND id_perfil = '$id_perfil';");

			if(!empty ($rtn)){
				$resp[ 'error' ] = "";
				$resp[ 'data' ] = array();
				$resp[ 'data' ][ 'id' ] = $rtn[ 'id' ];
			}
			else {
				$resp[ 'error' ] = "Error al definir los permisos.";
			}
		}catch( Exception $ex) {
			$rtn = $db->getRow("SELECT CAMPO.nombre as campo, MANT.nombre as mant, PERFIL.nombre as perfil
								FROM mantenedor_campos CAMPO, mantenedor_mantenedores MANT, usuarios_perfiles PERFIL
								WHERE CAMPO.id = '$id_campo'
								AND   MANT.id = CAMPO.id_mant
								AND   PERFIL.id = '$id_perfil';");
			$nom_camp = $rtn[ 'campo' ];
			$nom_mant = $rtn[ 'mant' ];
			$nom_perfil = $rtn[ 'perfil' ];
			$resp[ 'error' ] = "Los permisos asociados al perfil $perfil para el campo $nom_camp del mantenedor $nom_mant ya existe.";
			$resp[ 'errmsg' ] = $ex->getMessage();
		}
    }

	public function get( $args, &$resp ){
		$id = $args[ 'id' ];

		$db = R::$adapter;
		
		try{
			if( $id > 0 ){
				$perm = $db->getRow("SELECT id, id_campo, id_perfil, modif, visible FROM mantenedor_permcamp WHERE id = $id;");

				if(!empty ($perm)){
					$resp[ 'error' ] = "";
					$resp[ 'data' ] = array();
					$resp[ 'data' ][ 'id' ] = $perm[ 'id' ];
					$resp[ 'data' ][ 'id_campo' ] = $perm[ 'id_campo' ];
					$resp[ 'data' ][ 'id_perfil' ] = $perm[ 'id_perfil' ];
					$resp[ 'data' ][ 'modif' ] = $perm[ 'modif' ];
					$resp[ 'data' ][ 'visible' ] = $perm[ 'visible' ];
				}
				else {
					$resp[ 'error' ] = "El permiso no existe.";
				}
			} else {
				$permcamps = $db->get("SELECT id, id_campo, id_perfil, modif, visible FROM mantenedor_permcamp;");

				$resp[ 'error' ] = "";
				$resp[ 'count' ] = 0;
				if(!empty ($permcamps)){
					$resp[ 'data' ] = array();
					$x = 0;
					foreach( $permcamps as $perm ){
						$resp[ 'data' ][ $x ] = array();
						$resp[ 'data' ][ $x ][ 'id' ] = $perm[ 'id' ];
						$resp[ 'data' ][ $x ][ 'id_campo' ] = $perm[ 'id_campo' ];
						$resp[ 'data' ][ $x ][ 'id_perfil' ] = $perm[ 'id_perfil' ];
						$resp[ 'data' ][ $x ][ 'modif' ] = $perm[ 'modif' ];
						$resp[ 'data' ][ $x ][ 'visible' ] = $perm[ 'visible' ];
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
		$id_campo = $args[ 'id_campo' ];
		$id_perfil = $args[ 'id_perfil' ];
		$modif = $args[ 'modif' ];
		$visible = $args[ 'visible' ];

		$db = R::$adapter;
		
		try{
			$rtn = $db->exec( "UPDATE mantenedor_permcap SET
								`id_campo` = '$id_campo',
								`id_perfil` = '$id_perfil',
								`modif` = '$modif',
								`visible` = '$visible',
								WHERE `id` = '$id';" );
			$resp[ 'error' ] = "";
		}catch( Exception $ex) {
			$rtn = $db->getRow("SELECT CAMPO.nombre as campo, MANT.nombre as mant, PERFIL.nombre as perfil
								FROM mantenedor_campos CAMPO, mantenedor_mantenedores MANT, usuarios_perfiles PERFIL
								WHERE CAMPO.id = '$id_campo'
								AND   MANT.id = CAMPO.id_mant
								AND   PERFIL.id = '$id_perfil';");
			$nom_camp = $rtn[ 'campo' ];
			$nom_mant = $rtn[ 'mant' ];
			$nom_perfil = $rtn[ 'perfil' ];
			$resp[ 'error' ] = "Los permisos asociados al perfil $perfil para el campo $nom_camp del mantenedor $nom_mant ya existe.";
			$resp[ 'errmsg' ] = $ex->getMessage();
		}
    }
	public function del( $args, &$resp ){
		$id = $args[ 'id' ];

		$db = R::$adapter;
		
		try{
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
