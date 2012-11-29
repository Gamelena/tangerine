<?php

/**
 * Modelo de datos para permisos ACL del admin
 *
 *
 * @category Zwei
 * @package Models
 * @version $Id:$
 * @since 0.1
 *
 */

class AclPermissionsComboModel extends Zwei_Db_Table
{
    protected $_name = "acl_permissions";
    public $_name_roles = "acl_roles";
    public $_name_modules = "acl_modules";
    public $_name_permissions = "web_permissions";


    public function getPrimary()
    {
        return $this->_name_roles.'.id';
    }
    
    public function select()
    {
        $select=new Zend_Db_Table_Select($this);
        $select->setIntegrityCheck(false); //de lo contrario no podemos hacer JOIN
        $select->from($this->_name, array('id'))
        ->joinLeft($this->_name_permissions, "$this->_name.permission=$this->_name_permissions.id", array())
        ->joinLeft($this->_name_roles, "$this->_name.{$this->_name_roles}_id = $this->_name_roles.id", "role_name", array())
        ->joinLeft($this->_name_modules, "$this->_name.{$this->_name_modules}_id = $this->_name_modules.id", array());
        
        //Si no es root no puede ver a usuarios root
        if ($this->_user_info->acl_roles_id != '1') {
            $select->where("$this->_name_roles.id != ?", "1");
        }
        //Zwei_Utils_Debug::write($select->__toString());
        return $select;
    }
}

