<?php
	require_once 'admportal/modulos/usuarios/web/model/LoginDAO.php';

	$inicio = new LoginDAO( $dsn_admportal );

	echo $inicio->validaLogin( $_REQUEST['usr'], $_REQUEST['pwd'] )
	
?>
