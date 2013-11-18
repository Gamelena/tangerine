<?php

class AclGroupsModulesActionsModel extends DbTable_AclGroupsModulesActions
{
    protected $_dataModulesActionsId;
    protected $_nameModulesActions = 'acl_modules_actions';
    
    public function update($data, $where)
    {
        $data = $this->cleanDataParams($data);
        $delete = $this->deleteUnchecked($data, $where);
        
        $where = self::whereToArray($where);
        $data['acl_groups_id'] = $where['acl_groups_id'];
        $data['acl_modules_item_id'] = $where['acl_modules_item_id'];
        
        $insert = false;
        foreach ($this->_dataModulesActionsId as $aclModulesActionsId) {
            try {
                $data['acl_modules_actions_id'] = $aclModulesActionsId;
                $insert = $this->insert($data);
            } catch (Zend_Db_Exception $e) {
                if ($e->getCode() == '23000') {
                    $printData = print_r($data, 1);
                    Debug::write("Ya existe modulo_accion asociado a $printData");
                }
            }
        }
        return $insert || $delete;
    }
    
    public function cleanDataParams($data)
    {
        foreach ($data as $i => $v) {
            if (preg_match("/^actionsModule/", $i)) {
                foreach ($v as $v2) {
                    $this->_dataModulesActionsId[] = $v2;
                    unset($data[$i]);
                }
            }
        }
        return $data;
    }
    
    public function deleteUnchecked($data, $where)
    {
        $list = !empty($this->_dataModulesActionsId) ?
            implode(",", $this->_dataModulesActionsId) :
            false;
        
        if ($list) $where[] = "acl_modules_actions_id NOT IN ($list)";
        
        $delete = parent::delete($where);
    }
    
    public function findModuleId($id)
    {
        
    }
    
    public function findActionId($id)
    {
        
    }
    
    public function select()
    {
        $select = new Zend_Db_Table_Select($this);
        $select->setIntegrityCheck(false);
        $select->from($this->_name);
        $select->joinLeft($this->_nameModulesActions, 
                "$this->_name.acl_modules_actions_id=$this->_nameModulesActions.id", 
                array('acl_modules_id', 'acl_actions_id')
        );
        return $select;
    }
}

