<?php

class AclRolesModulesActionsModel extends DbTable_AclRolesModulesActions
{
    protected $_nameAclModulesActions = 'acl_modules_actions';
    
    /**
     * 
     * @param string $aclRolesId
     * @param array  $fields
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function findAclRolesId($aclRolesId, $fields = array('*'))
    {
        $select = new Zend_Db_Table_Select($this);
        $select->from($this->_name, $fields)->where($this->getAdapter()->quoteInto('acl_roles_id = ?', $aclRolesId));
        Debug::write($select->__toString());
        return $this->fetchAll($select);
    }
    
    /**
     * 
     * @param string $aclModules
     * @param string $aclRolesId
     * @param array  $fields
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function findAclModulesIdAclRolesId($aclModulesId, $aclRolesId, $aclActionsId = null, $fields = array('*'))
    {
        $select = new Zend_Db_Table_Select($this);
        $select->setIntegrityCheck(false);
        
        $select->from($this->_name, array());
        $select->joinLeft($this->_nameAclModulesActions, "$this->_nameAclModulesActions.id=$this->_name.acl_modules_actions_id"); 
        
        $select->where($this->getAdapter()->quoteInto('acl_roles_id = ?', $aclRolesId));
        $select->where($this->getAdapter()->quoteInto('acl_modules_id = ?', $aclModulesId));
        if ($aclActionsId) {  $select->where($this->getAdapter()->quoteInto('acl_actions_id = ?', $aclActionsId)); 
        }
         
        //Debug::write($select->__toString());
        return $this->fetchAll($select);
    }

}

