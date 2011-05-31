<?php

	require_once 'admportal/modulos/mantenedor/web/model/MantenedoresDAO.php';

	$dao = new MantenedoresDAO( $dsn_admportal );

	$data = json_decode( $HTTP_RAW_POST_DATA );
	$resp = array();
	$dao->process( $data, &$resp );

	echo json_encode($resp)."\n";
?>
