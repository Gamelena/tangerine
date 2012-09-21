<?php

/**
 * Modelo de datos para usuarios ACL del admin
 *
 * @category Zwei
 * @package Models
 * @version $Id:$
 * @since 0.1
 *
 */

class AclUsersModel extends Zwei_Db_Table
{
	protected $_name = "acl_users";
	protected $_name_roles = "acl_roles";
	protected $_generate_pass = "user_name";

	public function select()
	{
		$select = new Zend_Db_Table_Select($this);
		$select->setIntegrityCheck(false); //de lo contrario no podemos hacer JOIN
		$select->from($this->_name)
		->joinLeft($this->_name_roles, "$this->_name.acl_roles_id = $this->_name_roles.id", "role_name")
		;

		//[TODO] esto está en duro, debiera ser dinámico via campo root en acl_roles
		if ($this->_user_info->acl_roles_id != '1') {
			$select->where('acl_roles_id <> ?', '1');
		}

		return $select;
	}

	/**
	 * En el caso de crearse un usuario nuevo,
	 * se genera la password repitiendo el nombre de usuario en md5
	 * @return int
	 */
	public function insert($data)
	{
		$data["password"] = md5($data[$this->_generate_pass]);
		try {
			$last_insert_id = parent::insert($data);
		} catch(Zend_Db_Exception $e) {
			if ($e->getCode() == '23000') {
				$this->setMessage('Nombre de Usuario en uso.');
				return false;
			} else {
				Zwei_Utils_Debug::write("error:".$e->getMessage()."code".$e->getCode());
			}
		}
		return $last_insert_id;
	}

	/**
	 * Captura de excepciones posibles como nombre de usuario en uso
	 */

	public function update($data, $where)
	{
		try {
			$update = parent::update($data, $where);
		} catch(Zend_Db_Exception $e) {
			if ($e->getCode()=='23000') {
				$this->setMessage('Nombre de Usuario en uso.');
				return false;
			} else {
				Zwei_Utils_Debug::write("error:".$e->getMessage()."code".$e->getCode());
			}
		}
		return $update;

	}
}
