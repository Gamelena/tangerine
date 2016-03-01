<?php
class Helpers_AclGroupableCrud
{
    /**
     * 
     * @var Zend_Auth_Storage_Interface
     */
    protected $_userInfo;
    
    /**
     * 
     * @var string
     */
    protected $_name;
    
    /**
     * 
     * @var string
     */
    protected $_nameGroups;
    
    /**
     * 
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_adapter;
    
    /**
     * 
     * @var string
     */
    protected $_component;
    
    /**
     * 
     * @var string
     */
    protected $_ownerColumn = 'acl_users_id';
    
    /**
     * 
     * @param Zend_Db_Table $dbTable
     * @param Zend_Db_Table $dbTableGroups
     * @return void
     */
    public function __construct($dbTable, $dbTableGroups, $component)
    {
        $this->_userInfo = Zend_Auth::getInstance()->getStorage()->read();
        $this->_name = $dbTable->info(Zend_Db_Table::NAME);
        $this->_nameGroups = $dbTableGroups->info(Zend_Db_Table::NAME);
        $this->_adapter = $dbTable->getAdapter();
        $this->_component = $component;
    }
    
    /**
     * 
     * @param string $columnName
     * @return void
     */
    public function setOwnerColumn($columnName)
    {
        $this->_ownerColumn = $columnName;
    }
    
    /**
     * 
     * @return Zend_Db_Adapter_Abstract
     */
    public function getAdapter()
    {
        return $this->_adapter;
    }
    
    /**
     * Se sobrecarga método para perfilamiento avanzado.
     * @param Zend_Db_Table_Select $select
     * @param string               $action
     * @return Zend_Db_Table_Select
     */
    public function select($select, $action = 'LIST')
    {
        $this->_acl = new Zwei_Admin_Acl();
        
        $select->setIntegrityCheck(false);
        $select->distinct();
        $select->joinLeft($this->_nameGroups, "$this->_name.id=$this->_nameGroups.{$this->_name}_id", array());
        
        if (!$this->_acl->userHasRoleAllowed('questions.xml', $action)) {
            $groups = implode(",", $this->_userInfo->groups);
            if (empty($groups)) {
                $groups = "'-1'";
            }
            
            if ($this->_ownerColumn) {
                $select->where($this->getAdapter()->quoteInto("$this->_ownerColumn = ?", $this->_userInfo->id));
            }
            $select->orWhere("acl_groups_id IN ($groups)");
        }
    
        return $select;
    }
    
    /**
     * @param Zend_Db_Table_Rowset|array $data
     * @return array
     */
    public function overloadDataList($data)
    {
        if ($data instanceof Zend_Db_Table_Rowset) { $data = $data->toArray(); 
        }
        $modulesModel = new AclModulesModel();
        $moduleId     = $modulesModel->getModuleId($this->_component);
        $groups       = !empty($this->_userInfo->groups) ? implode(',', $this->_userInfo->groups) : false;
        $actions      = $modulesModel->getActions($moduleId);
    
        if (empty($this->_acl)) {
            $this->_acl = new Zwei_Admin_Acl();
        }
    
        $i = 0;
        foreach ($data as $d) {
            foreach ($actions as $action) {
                $data[$i]['admPortalIsAllowed' . $action->acl_actions_id] =
                $this->_acl->isUserAllowed($this->_component, $action->acl_actions_id, $d['id'])
                ? '1' : '0';
            }
            $i++;
        }
        return $data;
    }
}