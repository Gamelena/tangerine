<?php

class AclModulesActionsModel extends DbTable_AclModulesActions
{
    protected $_nameModules = 'acl_modules';
    protected $_nameActions = 'acl_actions';
    protected $_nameModulesAction = 'acl_roles_modules_actions';
    
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
        $select->joinLeft($this->_nameModules, "$this->_name.acl_modules_id=$this->_nameModules.id", array('module'));
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
                $this->_nameModulesAction, 
                "$this->_name.id = $this->_nameModulesAction.acl_modules_actions_id  ", 
                array());
            $select->where("$this->_nameModulesAction.acl_roles_id = ?", $aclRolesId);
        }
        
        if ($this->_user_info->acl_roles_id != '1') {
            $selectTmp->where("$this->_nameModules.root != ?", "1");
        } else {
            $select->order("parent_title");
            $select->order("module");
            $select->order("$this->_name.id");
        }
        return $select;
    }
}

