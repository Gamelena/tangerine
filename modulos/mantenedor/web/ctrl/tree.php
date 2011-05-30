<?php
	require_once 'admportal/modulos/mantenedor/web/model/TreeDAO.php';

	$tree = new TreeDAO( $dsn_admportal );

	$id_modulo = $_REQUEST['id_modulo'];
	$id_usuario = $_REQUEST['id_usuario'];
	echo json_encode( $tree->getMantenedores( $id_modulo, $id_usuario ) );
?>
