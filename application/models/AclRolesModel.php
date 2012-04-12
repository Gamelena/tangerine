<?php

/**
 * Modelo de datos para roles ACL o perfiles del admin
 *
 *
 * @category Zwei
 * @package Models
 * @version $Id:$
 * @since 0.1
 *
 */

class AclRolesModel extends Zwei_Db_Table
{
	protected $_name = "acl_roles";

	public function select(){
		$select=new Zend_Db_Table_Select($this);

		//Si no pertenece al role_id 1, no puede ver a otros usuarios con ese perfil
		if($this->_user_info->acl_roles_id != '1'){
			$select->where('id <> ?', '1');
		}

		return $select;
	}
}
