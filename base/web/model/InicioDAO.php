<?php

require("admportal/base/web/model/BaseDAO.php");

class InicioDAO extends BaseDAO {

	function __construct($dsn) {

		parent::__construct($dsn);
	}

	public function needLogin() {

        $database = R::$adapter;

		$modulo = $database->getRow("SELECT activo FROM base_modulos WHERE nombre = 'usuarios' AND activo = true " );

		
		if(!empty ($modulo)) {
			return true;
		}else {
			return false;
		}
    }

}

?>
