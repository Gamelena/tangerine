<?php

require("admportal/base/web/model/BaseDAO.php");

class PermOperDAO extends BaseDAO {

	function __construct($dsn) {

		parent::__construct($dsn);
	}

	public function create( $args, &$resp ){
		$id_mant = $args[ 'id_mant' ];
		$id_perfil = $args[ 'id_perfil' ];
		$operaciones = $args[ 'operaciones' ];

		$db = R::$adapter;
		
		$x = 0;
		$resp[ 'error' ] = "";
		$resp[ 'data' ] = array();
		foreach( $operaciones as $oper ){
			try{
				$rtn = $db->exec( "INSERT  INTO mantenedor_permoper ( id_mant, id_perfil, oper )
									VALUES ( '$id_mant', '$id_perfil', '$oper' );" );

			}catch( Exception $ex) {
			}
		}
		$resp[ 'data' ][ 'id_mant' ] = $id_mant;
		$resp[ 'data' ][ 'id_perfil' ] = $id_perfil;
    }

	public function get( $args, &$resp ){
		$id_mant = $args[ 'id_mant' ];
		$id_perfil = $args[ 'id_perfil' ];

		$db = R::$adapter;
		
		try{
			if( $id_mant > 0 && $id_perfil > 0 ){
				$permisos = $db->get("SELECT id_oper FROM mantenedor_permoper WHERE id_mant = '$id_mant' AND id_perfil = '$id_perfil';");

				if(!empty ($permisos)){
					$resp[ 'error' ] = "";
					$resp[ 'data' ] = array();
					$resp[ 'data' ][ 'id_mant' ] = $id_mant;
					$resp[ 'data' ][ 'id_perfil' ] = $id_perfil;
					$resp[ 'data' ][ 'operaciones' ] = array();
					$x = 0;
					foreach( $permisos as $perm ){
						$resp[ 'data' ][ 'operaciones' ][ $x ] = $perm[ 'id_oper' ];
						$x = $x++;
					}
				}
				else {
					$resp[ 'error' ] = "El permiso no existe.";
				}
			} else {
				$permisos = $db->get("SELECT id, id_mant, id_perfil, id_oper FROM mantenedor_permoper
									   ORDER BY id_mant, id_perfil;");

				$resp[ 'error' ] = "";
				if(!empty ($permisos)){
					$resp[ 'data' ] = array();
					$x = 0;
					$y = -1;
					$mant = $permisos[ 0 ][ 'id_mant' ];
					$perf = $permisos[ 0 ][ 'id_perfil' ];

					$resp[ 'data' ][ $x ][ 'id_mant' ] = $mant;
					$resp[ 'data' ][ $x ][ 'id_perfil' ] = $perf;
					$resp[ 'data' ][ $x ][ 'operaciones' ] = array();
					foreach( $permisos as $perm ){
						if( $mant != $perm[ 'id_mant' ] || $perf != $perm[ 'id_perfil' ] ){
							$mant = $perm[ 'id_mant' ];
							$perf = $perm[ 'id_perf' ];
							$x = $x + 1;
							$y = 0;
							$resp[ 'data' ][ $x ][ 'id_mant' ] = $mant;
							$resp[ 'data' ][ $x ][ 'id_perfil' ] = $perf;
							$resp[ 'data' ][ $x ][ 'operaciones' ] = array();
						}
						else{
							$y = $y + 1;
						}
		
						$resp[ 'data' ][ $x ][ 'operaciones' ][ $y ] = $perm[ 'id_oper' ];
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
		$id_mant = $args[ 'id_mant' ];
		$id_perfil = $args[ 'id_perfil' ];
		$operaciones = $args[ 'operaciones' ];

		$rtn = $db->exec("DELETE FROM mantenedor_permcamp WHERE id_mant = '$id_mant' AND id_perfil = '$id_perfil';");
		$this->create( $args, $resp );
    }
	public function del( $args, &$resp ){
		$id_mant = $args[ 'id_mant' ];
		$id_perfil = $args[ 'id_perfil' ];

		$db = R::$adapter;
		
		try{
			$rtn = $db->exec("DELETE FROM mantenedor_permcamp WHERE id_mant = '$id_mant' AND id_perfil = '$id_perfil';");
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
