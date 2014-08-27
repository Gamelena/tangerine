<?php

class AclSessionModel extends DbTable_AclSession
{
    /**
     * 
     * @param int $aclUsersId
     * @param string|array $fields
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function findByAclUsersId($aclUsersId, $fields = array('*')) 
    {
        if (!is_array($fields)) { 
            $fields = array($fields);
        }
        
        $select = parent::select(self::SELECT_WITHOUT_FROM_PART);
        $select->from($this->_name, $fields)->where(
            $this->getAdapter()->quoteInto("acl_users_id = ?", $aclUsersId)
        );
        
        return $this->fetchAll($select);
    }

}

