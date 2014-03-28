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
    public function select()
    {
        $select = parent::select(false);
        $select->setIntegrityCheck(false);
        
        $select->from($this->_name, array('id', 'ip', 'user_agent', 'modified'));
        $select->joinLeft($this->_nameAclUsers, "$this->_name.acl_users_id=$this->_nameAclUsers.id", array('user_name', 'email'));
        $select->joinLeft($this->_nameAclRoles, "$this->_nameAclUsers.acl_roles_id=$this->_nameAclRoles.id", array('role_name'));
        
        if ($this->_user_info->acl_roles_id != ROLES_ROOT_ID) {
            $select->where("acl_roles_id <> '1'");
        }
        
        $select->where("acl_users_id <> '0'");
        $select->order("modified DESC");
        
        return $select;
    }
    
    /**
     * @param Zend_Db_Table_Rowset $data
     * @return array
     * 
     * @see Zwei_Db_Table::overloadDataList()
     */
    public function overloadDataList($data)
    {
        $i=0;
        $data = $data->toArray();
        $config = Zwei_Controller_Config::getOptions();
        
        Debug::write($data);
        foreach ($data as $d) {
            //si la última actividad fue hace más de 10 segundos entonces ya no está logueado, 
            //le damos 2 segundos más como margen de error (12 en total)
            if ((int) $d['modified'] > (time() - 12)) {
                $timeout = $d['modified'] + $config->zwei->session->timeout;
                $data[$i]['expires'] = date('Y-m-d h:i:s', $timeout);
                $data[$i]['modified'] = date('Y-m-d h:i:s', $d['modified']);
            } else {
                unset($data[$i]);
            }
            $i++;
        }
        
        return $data;
    }
    
    public function delete($where)
    {
        $aWhere = self::whereToArray($where);
        if ($aWhere['id'] == Zend_Session::getId()) {
            $this->setMessage("Esta es su propia sesión.");
            return false;
        }
        return parent::delete($where);
    }
}

