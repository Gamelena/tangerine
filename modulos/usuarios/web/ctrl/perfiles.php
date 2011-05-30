<?php

	require_once 'admportal/modulos/usuarios/web/model/PerfilesDAO.php';

	$dao = new PerfilesDAO( $dsn_admportal );

	$data = json_decode( $HTTP_RAW_POST_DATA );
	$resp = array();
	$dao->process( $data, &$resp );

	echo json_encode($resp)."\n";
?>
