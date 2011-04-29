<?php

	require_once 'admportal/base/web/model/ModulosDAO.php';

	$modulos = new ModulosDAO( $dsn_admportal );

	$resp = $modulos->cargarModulos();

	echo $resp;
?>
