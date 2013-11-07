<?php

class AclUsersGroupsModel extends DbTable_AclUsersGroups
{
    public function findByUserId($aclUsersId) 
    {
        $select = new Zend_Db_Table_Select($this);
        $select->from($this->_name, array('id'));
        $select->where($this->getAdapter()->quoteInto('acl_users_id = ?', $aclUsersId));
        Debug::writeBySettings($select->__toString(), 'query_log');
        return $this->getAdapter()->fetchAll($select);
    }

}

