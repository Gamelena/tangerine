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
        
        $select->where("acl_users_id <> '0'");
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
        
        
        foreach ($data as $d) {
            $timeout = $d['modified'] + $config->zwei->session->timeout;
            $data[$i]['expires'] = date('Y-m-d h:i:s', $timeout);
            $data[$i]['modified'] = date('Y-m-d h:i:s', $d['modified']);
            $i++;
        }
        
        return $data;
    }
}

