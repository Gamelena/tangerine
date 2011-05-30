<?php

require_once 'BaseDAO.php';

class PrincipalDAO extends BaseDAO {

    function __construct($dsn) {

        parent::__construct($dsn);
    }

    public function cargarParametros() {

        $resp = array();

        $database = R::$adapter;

        $parametros = $database->get("SELECT nombre, valor FROM base_parametros");

        foreach ($parametros as $parametro) {
            $resp[$parametro['nombre']] = $parametro['valor'];
        }

        return json_encode($resp);
    }

}

?>
