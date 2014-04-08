<?php
/**
 * 
 * Modelo relacional entre grupos, módulos y acciones.
 *
 */
class AclGroupsModulesActionsModel extends DbTable_AclGroupsModulesActions
{
    /**
     * Ids de tabla relacional acciones por módulo.
     * 
     * @var array
     */
    protected $_dataModulesActionsId;
    /**
     * Nombre tabla relacional acciones por módulo.
     * 
     * @var string
     */
    protected $_nameModulesActions;
    
    /**
     * Post Constructor
     * @see Zwei_Db_Table::init()
     * @return void
     */
    public function init(){
        $aclModulesActions = new DbTable_AclModulesActions();
        $this->_nameModulesActions = $aclModulesActions->info(Zend_Db_Table::NAME);
        parent::init();
    }
    
    /**
     * @param array $data
     * @param string $where
     * @see Zwei_Db_Table::update()
     */
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
    
    /**
     * Se separan los datos de tabla principal de tabla acciones por módulo.
     * 
     * @param array $data
     * @return array $data
     * @see Zwei_Db_Table::cleanDataParams()
     */
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
    
    /**
     * Se borra todas las acciones que no existan en $data.
     *  
     * @param array $data
     * @param string $where
     */
    public function deleteUnchecked($data, $where)
    {
        $list = !empty($this->_dataModulesActionsId) ?
            implode(",", $this->_dataModulesActionsId) :
            false;
        
        if ($list) $where[] = "acl_modules_actions_id NOT IN ($list)";
        
        $delete = parent::delete($where);
    }
    
    /**
     * @return Zend_Db_Table_Select
     * @see Zend_Db_Table_Abstract::select()
     */
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

