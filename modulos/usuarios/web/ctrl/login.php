<?php
	require_once 'admportal/modulos/usuarios/web/model/LoginDAO.php';

	$login = new LoginDAO( $dsn_admportal );

	$data = json_decode( $HTTP_RAW_POST_DATA );
	echo $login->validaLogin( $data->usr, $data->pwd )
	
?>
