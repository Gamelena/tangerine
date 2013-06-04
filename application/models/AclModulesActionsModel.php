<?php

class AclModulesActionsModel extends DbTable_AclModulesActions
{
    protected $_nameModules = 'acl_modules';
    protected $_nameActions = 'acl_actions';
    protected $_namePermissions = 'acl_permissions';
    
    public function select(){
        $select = new Zend_Db_Table_Select($this);
        $select->setIntegrityCheck(false);
        
        $select->from($this->_name, array('id', 'acl_modules_id', 'acl_actions_id'));
        $select->joinLeft($this->_nameModules, "$this->_name.acl_modules_id=$this->_nameModules.id", array('modules_title'=>'title'));
        $select->joinLeft($this->_nameActions, "$this->_name.acl_actions_id=$this->_nameActions.id", array('modules_action'=>'title'));
        
        return $select;
    }
    
    public function selectAllActions($aclRolesId = null){
        $select = new Zend_Db_Table_Select($this);
        $select->setIntegrityCheck(false);
    
        $select->from($this->_name, array('id', 'acl_modules_id', 'acl_actions_id'));
        $select->joinLeft($this->_nameModules, "$this->_name.acl_modules_id=$this->_nameModules.id");
        $select->joinLeft(array('parent' => $this->_nameModules), "$this->_nameModules.parent_id = parent.id", array('parent_title' => 'title'));
        $select->joinLeft($this->_nameActions, 
            "$this->_name.acl_actions_id=$this->_nameActions.id", 
            array('title' => 
                new Zend_Db_Expr(
                    "IF($this->_nameModules.parent_id > 0, 
                     CONCAT(parent.title, '->', $this->_nameModules.title, ' <b>(', $this->_nameActions.title, ')</b>'), 
                     CONCAT($this->_nameModules.title, ' <b>(', $this->_nameActions.title, ')</b>'))"
                )
            )
        );
        
        if ($aclRolesId) {
            $select->joinLeft(
                    $this->_namePermissions, 
                    "$this->_nameModules.id = $this->_namePermissions.acl_modules_id AND $this->_nameActions.id = $this->_namePermissions.permission ", 
                    array());
            $select->where("$this->_namePermissions.acl_roles_id = ?", $aclRolesId);
        }
        
        if ($this->_user_info->acl_roles_id != '1') {
            $selectTmp->where("$this->_name_modules.root != ?", "1");
        } else {
            $select->order("parent_title");
            $select->order("module");
            $select->order("$this->_name.id");
        }
        return $select;
    }
}

