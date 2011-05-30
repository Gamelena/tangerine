<?php

	require_once 'admportal/base/web/model/PrincipalDAO.php';

	$principal = new PrincipalDAO( $dsn_admportal );

	$cadResp = $principal->cargarParametros();

	echo $cadResp;
?>
