<?php

require("admportal/base/web/model/BaseDAO.php");

class LoginDAO extends BaseDAO {

	function __construct($dsn) {

		parent::__construct($dsn);
	}

	public function validaLogin( $usr, $pass ) {

		$database = R::$adapter;

		$usuario = $database->getRow("SELECT id, nombre_p, apellido_p
										FROM usuarios_usuarios 
										WHERE login = '$usr' AND password=AES_ENCRYPT('$pass', 'zwpass') 
										AND activo = true ");

		if(!empty ($usuario)) {
			$usuario['exito'] = true;
		}else {
			$usuario['exito'] = false;
		}
		echo json_encode($usuario);
    }
}

?>
