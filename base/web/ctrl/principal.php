<?php

	require_once 'admportal/base/web/model/PrincipalDAO.php';

	$principal = new PrincipalDAO( $dsn_admportal );

	$metodo = $_REQUEST['fc'];

	$cadResp = "";


	if($metodo == "cargarDatosTituloAdm")
		$cadResp = $principal->cargarDatosTituloAdm();
	else
		$cadResp = "";

	echo $cadResp;
?>
