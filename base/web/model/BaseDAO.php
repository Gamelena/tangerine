<?php

require("admportal/base/web/data/global.php");
require("admportal/base/web/model/rb127lg.php");

abstract class baseDAO {

	function __construct($dsn) {
		//Establecer la conexion a la base de datos
		R::setup($dsn['dsn'], $dsn['user'], $dsn['password']);
	}

	function process( $data, &$resp ){

		$oper = $data->oper;
		$args = $data->args;

		$resp = array();
		if( $oper == "create" ){
			$this->create( $args, &$resp );
		}
		else if( $oper == "get" ){
			$this->get( $args, &$resp );
		}
		else if( $oper == "set" ){
			$this->set( $args, $resp );
		}
		else if( $oper == "del" ){
			$this->del( $args, &$resp );
		}
		else if( $oper == "load" ){
			$resp[ 'error' ] = -1;
		}
		else if( $oper == "erase" ){
			$resp[ 'error' ] = -2;
		}
		else
			$resp[ 'error' ] = $oper;
	}
}


?>
