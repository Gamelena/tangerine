<?php

class AclSessionOnlineModel extends DbTable_AclSession
{
    /**
     * Tabla de usuarios
     * @var string
     */
    private $_nameAclUsers = 'acl_users';
    /**
     * Tabla de perfiles
     * @var string
     */
    private $_nameAclRoles = 'acl_roles';
    
    /**
     * @return Zend_Db_Table_Select
     * @see Zend_Db_Table_Abstract::select()
     */
    public function select($withFromPart = self::SELECT_WITHOUT_FROM_PART)
    {
        $select = parent::select($withFromPart);
        $select->setIntegrityCheck(false);
        
        $select->from($this->_name, array('id', 'ip', 'user_agent', 'modified' => new Zend_Db_Expr("FROM_UNIXTIME(modified)")));
        $select->joinLeft($this->_nameAclUsers, "$this->_name.acl_users_id=$this->_nameAclUsers.id", array('user_name', 'email'));
        $select->joinLeft($this->_nameAclRoles, "$this->_nameAclUsers.acl_roles_id=$this->_nameAclRoles.id", array('role_name'));
        
        if ($this->_user_info->acl_roles_id != ROLES_ROOT_ID) {
            $select->where("acl_roles_id <> '1'");
        }
        
        $select->where("acl_users_id <> '0'");
        $select->where("modified > " . (time() - 12));
        $select->order("modified DESC");
        
        return $select;
    }
    
    public function delete($where)
    {
        $aWhere = self::whereToArray($where);
        if ($aWhere['id'] == Zend_Session::getId()) {
            $this->setMessage("No puede borrar su propia sesión. <br/> Para cerrar sesión use 'Salir'.");
            return false;
        }
        return parent::delete($where);
    }
}

