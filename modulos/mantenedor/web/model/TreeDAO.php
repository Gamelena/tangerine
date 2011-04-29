<?php

require("admportal/base/web/model/BaseDAO.php");

class TreeDAO extends BaseDAO {

	function __construct($dsn) {

		parent::__construct($dsn);
	}

	public function getMantenedores( $id_modulo, $id_usuario ) {

		$database = R::$adapter;

		if( $id_usuario > 0 ){
			$mantenedores = $database->get("SELECT * from mantenedor_mantenedores A, usuarios_roles B, usuarios_usuarios C, mantenedor_permisos D WHERE C.id = ".$id_usuario." AND B.id_usuario = C.id AND A.id = D.id_mant AND B.id_perfil = D.id_perfil;");
		}
		else{
			$mantenedores = $database->get("SELECT id, titulo, url FROM mantenedor_mantenedores" );
		}
		

		$tree = array();
		
		$tree[ 'label' ] = '<b>Mantenedores</b>';
		$tree[ 'id' ] = $id_modulo;
		$children = array();

		$i = 0;
        foreach ($mantenedores as $mantenedor ) {
            $children[ $i ] = array();
			$children[ $i ][ 'label' ] = $mantenedor[ 'titulo' ];
			$children[ $i ][ 'url' ] = $mantenedor[ 'url' ];
			$children[ $i ][ 'id' ] = $id_modulo.".".$mantenedor[ 'id' ];
			$i = $i + 1;
        }
		$tree[ 'children' ] = $children;
        return json_encode($tree);
    }

}

?>
