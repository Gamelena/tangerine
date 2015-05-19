<?php
/**
 * Modelo de grupos (o equipos) de usuario
 *
 */
class AclGroupsModel extends DbTable_AclGroups
{
    /**
     * Usuarios a ingresar en DbTable_AclUsersGroups.
     * 
     * @var array
     */
    protected $_dataUsuarios = array();
    
    /**
     * Se agregan usuarios asociados a rowset original.
     * 
     * @param Zend_Db_Table_Rowset
     * @return array
    */
    public function overloadDataForm($data) {
        if (method_exists($data, 'toArray')) {
            $data = $data->toArray();
        
            $model = new DbTable_AclUsersGroups();
            $select = $model->select()->where("acl_groups_id = ?", $data['id']);
            $usuarios = $model->fetchAll($select);
        
            foreach ($usuarios as $usuario) { //  $permissions->id = $permission->permission
                $data["usuarios"][] = $usuario['acl_users_id'];
            }
            return $data;
        }
    }
    
    /**
     * Separa del array $data, que usa como parametro al insertar o actualizar,
     * los datos de la tabla self::$_name de los datos de la tabla self::$_permissions
     *
     * @param array $data
     * @return array
     */
    public function cleanDataParams($data)
    {
        if (!empty($data['usuarios'])) {
            $this->_dataUsuarios = $data['usuarios'];
            unset($data['usuarios']);
        } else {
            $this->_dataUsuarios = array();
        }
        return $data;
    }
    /**
     * (non-PHPdoc)
     * @see Zwei_Db_Table::insert()
     */
    public function insert(array $data)
    {
        $data = $this->cleanDataParams($data);
        $lastInsertedId = parent::insert($data);
        $addUsers = $this->addUsers($lastInsertedId);
        return $lastInsertedId;
    }
    /**
     * (non-PHPdoc)
     * @see Zwei_Db_Table::update()
     */
    public function update(array $data, $where)
    {
        $data = $this->cleanDataParams($data);
        $update = parent::update($data, $where);
        
        $arrWhere = $this->whereToArray($where);
        $aclGroupsId = $arrWhere['id'];
        $addUsers = $this->addUsers($aclGroupsId);
        
        return $update || $addUsers;
    }
    
    /**
     * Mantener a los usuarios asociados al grupo
     *
     * @param int $aclGroupsId
     * @return int || false
     */
    public function addUsers($aclGroupsId)
    {
        $model = new DbTable_AclUsersGroups();
        $usersModel = new AclUsersModel();
        
        $ad = $model->getAdapter();
        
        //(1) Inicialización clausula SQL WHERE para borrar usuarios
        $where = array();
        
        //(2) Se deben borrar todos los usuarios que pertenecen a este grupo ...
        $where[] = $ad->quoteInto("acl_groups_id = ?", $aclGroupsId);
    
        $whereOr = array();
    
        $permissionsRows = array();
        foreach ($this->_dataUsuarios as $aclUsersId) {
            $whereOr[] = $ad->quote($aclUsersId);
        }
        
        //(3) ... excepto los usuarios que se encuentren chequeados en formulario
        if (!empty($this->_dataUsuarios)) {
            $list = implode(",", $whereOr);
            $where[] = "(acl_users_id) NOT IN ($list)";
        }
    
        //(4) Clausula WHERE lista, borremos!!
        $delete = $model->delete($where);
    
        if (!empty($this->_dataUsuarios)) $return = $delete;
        
        //Agregar a los usuarios que fueron chequeados.
        $insert =  false;
        foreach ($this->_dataUsuarios as $aclUsersId) {
            $data = array(
                'acl_groups_id' => $aclGroupsId,
                'acl_users_id' => $aclUsersId
            );
    
            try {
                $insert = $model->insert($data);
                if ($insert) {
                    $usersModel->notify($aclUsersId);
                }
            } catch (Zend_Db_Exception $e) {
                if ($e->getCode() == 23000) {
                    $printData = print_r($data, true);
                    Debug::write("Ya existe usuario asociado a $printData");
                }
            }
        }
        return $insert || $delete;
    }
    
    /**
     * Obtener los usuarios asociados al grupo.
     * 
     * @param int $aclGroupsId
     * @return Zend_Db_Table_Row
     */
    public function fetchUsers($aclGroupsId)
    {
        return $this->fetchRow($aclGroupsId)->findDependentRowset("DbTable_AclUsers");
    }
}

