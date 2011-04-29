<?php
	require_once 'admportal/base/web/model/InicioDAO.php';

	$inicio = new InicioDAO( $dsn_admportal );

	if( $inicio->needLogin() )
		echo 1;
	else
		echo 0;
	
?>
