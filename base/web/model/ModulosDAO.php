<?php

require_once 'BaseDAO.php';

class ModulosDAO extends BaseDAO {

    function __construct($dsn) {

        parent::__construct($dsn);
    }

    public function cargarModulos() {

        $resp = array();

        $database = R::$adapter;

        $modulos = $database->get("SELECT nombre, url FROM base_modulos WHERE activo = true");

		$i = 0;
        foreach ($modulos as $modulo) {
            $resp[ $i ] = array();
			$resp[ $i ][ 'nombre' ] = $modulo[ 'nombre' ];
			$resp[ $i ][ 'url' ] = $modulo[ 'url' ];
			$i = $i + 1;
        }

        return json_encode($resp);
    }

}

?>
