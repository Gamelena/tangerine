<?php

require("admportal/base/web/data/global.php");
require("admportal/base/web/model/rb127lg.php");

abstract class baseDAO {

    function __construct($dsn) {

        //Establecer la conexion a la base de datos
        R::setup($dsn['dsn'], $dsn['user'], $dsn['password']);
    }
}

?>
