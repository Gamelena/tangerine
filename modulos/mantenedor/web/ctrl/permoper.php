<?php

	require_once 'admportal/modulos/mantenedor/web/model/PermOperDAO.php';

	$dao = new PermOperDAO( $dsn_admportal );

	$data = json_decode( $HTTP_RAW_POST_DATA );
	$resp = array();
	$dao->process( $data, &$resp );

	echo json_encode($resp)."\n";
?>
