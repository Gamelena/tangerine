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
     * Especificar si se deben borrar acciones asociadas.
     *
     * @var boolean
     */
    protected $_deleteUnchecked = true;
    /**
     * Setea flag para borrar acciones asociadas.
     *
     * @param string $value
     * @return void
     */
    public function setDeleteUnchecked($value = true)
    {
        $this->_deleteUnchecked = $value;
    }
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
        $delete = $this->_deleteUnchecked ? $this->deleteUnchecked($data, $where) : false;
        
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
                    Console::warn("Ya existe modulo_accion asociado a $printData");
                }
            }
        }
        if ($insert || $delete) {
            $aclGroups = new AclGroupsModel();
            $aclUsers = new AclUsersModel();
            
            $rowsetUsers = $aclGroups->fetchUsers($data['acl_groups_id']);
            
            if ($rowsetUsers->count()) {
                foreach ($rowsetUsers as $rowUser) {
                    $aclUsers->notify($user->id);
                }
            }
        }
        return $insert || $delete;
    }

    /**
     * @param $data array
     * @return int
     * @see Zwei_Db_Table::insert()
     */
    public function insert($data)
    {
        $data = $this->cleanDataParams($data);
        $insert = parent::insert($data);
        
        if ($insert && isset($data['acl_groups_id'])) {
            $aclGroups = new AclGroupsModel();
            $aclUsers = new AclUsersModel();
            
            $rowsetUsers = $aclGroups->fetchUsers($data['acl_groups_id']);
            
            if ($rowsetUsers->count()) {
                foreach ($rowsetUsers as $rowUser) {
                    $aclUsers->notify($user->id);
                }
            }
            
        }
        
        return $insert;
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

        //Si estan seteados ambos valores de tabla padre 'acl_modules_actions', busca valor de campo 'acl_modules_actions_id' propagado
        if (isset($data['acl_modules_id']) && isset($data['acl_actions_id'])) {
            $modulesActionsModel = new AclModulesActionsModel();

            $gmaRowset = $modulesActionsModel->findByAclModulesIdAclActionsId($data['acl_modules_id'], $data['acl_actions_id']);

            if ($gmaRowset->count()) {
                $data['acl_modules_actions_id'] = $gmaRowset->current()->id;
            }

            unset($data['acl_modules_id']);
            unset($data['acl_actions_id']);
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

