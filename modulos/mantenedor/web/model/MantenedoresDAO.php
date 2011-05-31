<?php

require("admportal/base/web/model/BaseDAO.php");

class MantenedoresDAO extends BaseDAO {

	function __construct($dsn) {

		parent::__construct($dsn);
	}

// 	json mantenedor
// 	{
// 		titulo:,
// 		url:,
// 		campos[
// 			{
// 				tipo: ,
//				nombre
// 				etiqueta = $args->etiqueta;
// 				hint = $args->hint;
// 				llave = $args->llave;
// 				permisos[
// 					{ 
//						editar: ;
//						ver: ;
//					}
// 				]
// 				id_lista:;
// 			}
// 		]
// 		permisos{
// 			create[
//				id_perfil;
//			]
// 			get[
//				id_perfil;
//			]
// 			set[
//				id_perfil;
//			]
// 			del[
//				id_perfil;
//			]
// 			load[
//				id_perfil;
//			]
// 			erase[
//				id_perfil;
//			]
// 		}
// 	}

	public function update( $id_mant, $args ){
		$titulo = $args->titulo;
		$url = $args->url;

		$db = R::$adapter;
		
		$permisos = $args->permisos;
		$db->exec( "DELETE FROM mantenedor_permoper WHERE id_mant = '$id_mant'" );
		$db->exec( "DELETE FROM mantenedor_campos WHERE id_mant = '$id_mant'" );
		$db->exec( "DELETE FROM mantenedor_permcamp WHERE id_mant = '$id_mant'" );
		$db->exec( "DELETE FROM mantenedor_permoper WHERE id_mant = '$id_mant'" );

		foreach( $permisos as $oper => $perfiles ){
			$rtn = $db->getRow("SELECT id FROM mantenedor_oper WHERE nombre = '$oper';");
			$id_oper = $rtn[ 'id' ];
			foreach( $perfiles as $id_perfil ){
				$rtn = $db->exec( "INSERT  INTO mantenedor_permoper ( id_mant, id_perfil, id_oper )
								VALUES ( '$id_mant', '$id_perfil', '$id_oper' );" );
			}
		}
		
		$campos = $args->campos;
		foreach( $campos as $campo ){
			$tipo = $campo->tipo;
			$nombre = $campo->nombre;
			$etiqueta = $campo->etiqueta;
			$hint = $campo->hint;
			$lista = $campo->lista;
			$llave = $campo->llave;
			$rtn = $db->exec( "INSERT  INTO mantenedor_campos ( id_mant, id_tipo, nombre, etiqueta, hint, id_lista, llave )
								VALUES ( 
									'$id_mant',
									'$tipo',
									'$nombre',
									'$etiqueta',
									'$hint',
									'$lista',
									'$llave'
								);");

			$rtn = $db->getRow("SELECT id FROM mantenedor_campos WHERE id_mant = '$id_mant' AND etiqueta = '$campo->etiqueta';");

			if(!empty ($rtn)){
				$id_campo = $rtn[ 'id' ];
			}
			else {
				$resp[ 'error' ] = "Error al crear el campos \"$campo->etiqueta\" en mantenedor \"$titulo\".";
				return;
			}

			$rtn = $db->exec( "INSERT  INTO mantenedor_mantenedores ( titulo, url )
								VALUES ( '$titulo', '$url' );" );
			
		}
		$permisos = $args->permisos;
		foreach( $permisos as $permiso ){
			$id_perfil = $permiso->perfil;
			$editar = $permiso->editar;
			$ver = $permiso->ver;
			$rtn = $db->exec( "INSERT  INTO mantenedor_permcamp ( id_mant, id_camp, id_perfil, eidtar, ver )
							VALUES ( '$id_mant', '$id_campo', '$id_perfil', '$editar', '$ver' );" );
		}
    }

	public function create( $args, &$resp ){
		$titulo = $args->titulo;
		$url = $args->url;

		$db = R::$adapter;
		
		try{
			$rtn = $db->exec( "INSERT  INTO mantenedor_mantenedores ( titulo, url )
								VALUES ( '$titulo', '$url' );" );

			$rtn = $db->getRow("SELECT id FROM mantenedor_mantenedores WHERE titulo = '$titulo';");

			if(!empty ($rtn)){
				$id_mant = $rtn[ 'id' ];
				$resp[ 'error' ] = "";
				$resp[ 'data' ] = array();
				$resp[ 'data' ][ 'id' ] = $id_mant;
			}
			else {
				$resp[ 'error' ] = "Error al crear el mantenedor \"$titulo\".";
				return;
			}

			$this->update( $id_mant, $args );
		}catch( Exception $ex) {
			$resp[ 'error' ] = "El mantenedor \"$titulo\" ya existe.";
			$resp[ 'errmsg' ] = $ex->getMessage();
			return;
		}
    }

	private function getmant( $id_mant, &$resp ){
		$db = R::$adapter;

		$rtn = $db->get( "SELECT titulo, url FROM mantenedor_mantenedores WHERE id_mant = '$id_mant';" );
		if( empty ($rtn) ){
			return false;
		}
		$resp[ 'titulo' ] = $rtn[ 'titulo' ];
		$resp[ 'url' ] = $rtn[ 'url' ];

		$permisos = $db->get( "SELECT * FROM mantenedor_permoper WHERE id_mant = '$id_mant';" );
		if(!empty ($permisos)){
			$resp[ 'permisos' ] = array();
			foreach( $permisos as $permiso ){
				$id_oper = $permiso['id_oper'];
				$id_perfil = $permiso['id_perfil'];
				$oper = $db->getRow( "SELECT nombre FROM mantenedor_oper WHERE id = '$id_oper';" );
				if( !isset( $resp[ 'permisos' ][ $oper ] ) ){
					$resp[ 'permisos' ][ $oper ] = array();
				}
				array_push( $resp[ 'permisos' ][ $oper ], $id_perfil );
			}
		}
		$campos = $db->get( "SELECT id, id_tipo as tipo, etiqueta, hint, id_lista as lista, llave FROM mantenedor_campos WHERE id_mant = '$id_mant';" );
		if(!empty ($campos)){
			$resp[ 'campos' ] = array();
			foreach( $campos as $campo ){
				$id_campo = $campo[ 'id' ];
				$permisos = $db->get( "SELECT * FROM mantenedor_permcamp WHERE id_campo = '$id_campo';" );
				$campo[ 'permisos' ] = array();
				foreach( $permisos as $permiso ){
					$id_perfil = $campo[ 'id_perfil' ];
					if( !isset( $campo[ 'permisos' ][ $id_perfil ] ) ){
						$campo[ 'permisos' ][ $id_perfil ] = array();
					}
					$campo[ 'permisos' ][ $id_perfil ][ 'ver' ] = $campo[ 'ver' ];
					$campo[ 'permisos' ][ $id_perfil ][ 'editar' ] = $campo[ 'editar' ];
				}
				array_push( $resp[ 'campos' ], $campo );
			}
		}
	}

	public function get( $args, &$resp ){
		$id = $args->id;

		$db = R::$adapter;

		try{
			if( $id > 0 ){
				$mantenedor = $db->getRow("SELECT id FROM mantenedor_mantenedores WHERE id = '$id';");

				if(!empty ($mantenedor)){
					$resp[ 'error' ] = "";
					$resp[ 'data' ] = array();
					$this->getMant( $id, $resp[ 'data' ] );
				}
				else {
					$resp[ 'error' ] = "El mantenedor no existe.";
				}
			} else {
				$mantenedores = $db->get("SELECT id FROM mantenedor_mantenedores;");

				$resp[ 'error' ] = "";
				$resp[ 'count' ] = 0;
				if(!empty ($mantenedores)){
					$resp[ 'data' ] = array();
					$x = 0;
					foreach( $mantenedores as $mantenedor ){
						$resp[ 'data' ][ $x ] = array();
						$this->getMant( $mantenedor[ 'id' ], $resp[ 'data' ][ $x ] );
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
		$titulo = $args->titulo;
		$url = $args->url;

		$db = R::$adapter;

		try{
			$rtn = $db->exec( "UPDATE mantenedor_mantenedores SET
								`url` = '$url'
								WHERE `id` = '$id';" );
			$resp[ 'error' ] = "";
		}catch( Exception $ex) {
			$resp[ 'error' ] = "El mantenedor \"$titulo\" ya existe.";
			$resp[ 'errmsg' ] = $ex->getMessage();
		}
		$this->update( $id, $args );
    }
	public function del( $args, &$resp ){
		$id = $args->id;

		$db = R::$adapter;

		try{
			$rtn = $db->exec("DELETE FROM mantenedor_mantenedores WHERE id = $id;");
			$rtn = $db->exec("DELETE FROM mantenedor_campos WHERE id_mant = $id;");
			$rtn = $db->exec("DELETE FROM mantenedor_permoper WHERE id_mant = $id;");
			$rtn = $db->exec("DELETE FROM mantenedor_permcamp WHERE id_mant = $id;");
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
