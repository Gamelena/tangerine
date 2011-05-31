<?php

	require_once 'admportal/modulos/mantenedor/web/model/PermCampDAO.php';

	$dao = new PermCampDAO( $dsn_admportal );

	$data = json_decode( $HTTP_RAW_POST_DATA );
	$resp = array();
	$dao->process( $data, &$resp );

	echo json_encode($resp)."\n";
?>
