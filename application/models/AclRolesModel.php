<?php

/**
 * Modelo de datos para roles ACL o perfiles del admin
 *
 *
 * @category Zwei
 * @package Models
 * @version $Id:$
 * @since 0.1
 *
 */

class AclRolesModel extends DbTable_AclRoles
{
    /**
     *
     * @var string
     */
    protected $_nameModules = "acl_modules";
    /**
     *
     * @var string
     */
    protected $_namePermissions = "acl_permissions";
    /**
     *
     * @var array
     */
    protected $_dataRolesModulesActions = array();

    /**
     *
     * @var AclModulesModel
    */
    protected $_aclModulesActions;

    /**
     * (non-PHPdoc)
     * @see Zwei_Db_Table::init()
     */
    public function init() {
        $this->_aclModulesActions = new AclModulesActionsModel();
        parent::init();
    }


    /**
     * (non-PHPdoc)
     * @see Zend_Db_Table_Abstract::select()
     */
    public function select()
    {
        $select = new Zend_Db_Table_Select($this);

        //Si no pertenece al role_id 1, no puede ver a otros usuarios con ese perfil
        if ($this->_user_info->acl_roles_id != ROLES_ROOT_ID) {
            $select->where('id <> ?', '1');
        }

        return $select;
    }

    /**
     * Se agregan permisos asociados a rowset original
     * @param Zend_Db_Table_Rowset
     * @return array
     */

    public function overloadDataForm($data) {
        $data = $data->toArray();

        $select = $this->_aclModulesActions->selectAllActions($data['id']);
        Debug::writeBySettings($select->__toString(), 'query_log');
        $permissions = $this->fetchAll($select);

        if (count($permissions) > 0) {
            foreach ($permissions as $perm) { //  $permissions->id = $permission->permission
                $data["permissions"][] = $perm['id'];
            }
        }
        return $data;
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
        if (!empty($data['permissions'])) {
            $this->_dataRolesModulesActions = $data['permissions'];
            unset($data['permissions']);
        } else {
            $this->_dataRolesModulesActions = array();
        }
        return $data;
    }

    /**
     * (non-PHPdoc)
     * @see Zwei_Db_Table::update()
     */
    public function update($data, $where)
    {
        $data = $this->cleanDataParams($data);

        if (in_array('must_refresh', $this->info('cols'))) {
            $data['must_refresh'] = '1';
        }
        
        try {
            $update = parent::update($data, $where);
        } catch (Zend_Db_Exception $e) {
            $update = false;
            if ($e->getCode() == 23000) {
                $this->setMessage("Nombre de Perfil en uso, por favor escoja otro.");
                return false;
            } else {
                Debug::write($e->getMessage()."-".$e->getCode());
            }
        }


        $arrWhere = $this->whereToArray($where);
        $aclRolesId = $arrWhere['id'];

        $addPermissions = $this->addPermissions($aclRolesId);



        Zwei_Utils_File::clearRecursive(ROOT_DIR ."/cache");
        return $update || $addPermissions;
    }

    /**
     * (non-PHPdoc)
     * @see Zwei_Db_Table::insert()
     */
    public function insert($data)
    {
        $data = $this->cleanDataParams($data);
        try {
            $insert = parent::insert($data);//$insert = {last insert id}
        } catch (Zend_Db_Exception $e) {
            $insert = false;
            if ($e->getCode() == 23000) {
                $this->setMessage("Nombre de Perfil en uso, por favor escoja otro.");
                return false;
            }
        }

        if ($insert) $addPermissions = $this->addPermissions($insert);
        if (!$insert && $addPermissions) $insert = $addPermissions; //Devolver != false frente a cualquier modificacion

        Zwei_Utils_File::clearRecursive(ROOT_DIR ."/cache");
        return $insert;
    }

    /**
     * Agregar los permisos asociados al perfil
     *
     * @param int $aclRolesId
     * @return int || false
     */
    public function addPermissions($aclRolesId)
    {
        $aclRolesModulesAction = new AclRolesModulesActionsModel();
        $ad = $aclRolesModulesAction->getAdapter();

        //(1) Inicialización clausula SQL WHERE para borrar permisos
        $where = array();

        //(2) Se deben borrar todos los permisos asociados a este perfil ...
        $where[] = $ad->quoteInto("acl_roles_id = ?", $aclRolesId);

        $whereOr = array();

        $permissionsRows = array();
        foreach ($this->_dataRolesModulesActions as $aclModulesActionsId) {
            $whereOr[] = $ad->quote($aclModulesActionsId);
        }

        //(3) ... excepto los permisos que se encuentren chequeados en formulario
        if (!empty($this->_dataRolesModulesActions)) {
            $list = implode(",", $whereOr);
            $where[] = "(acl_modules_actions_id) NOT IN ($list)";
        }

        //(4) Clausula WHERE lista, ahora borremos los permisos.
        $delete = $aclRolesModulesAction->delete($where);

        if (!empty($this->_dataRolesModulesActions)) $return = $delete;

        //(5) Agregar los permisos que fueron chequeados.
        $insert =  false;
        foreach ($this->_dataRolesModulesActions as $aclModulesActionsId) {
            $data = array(
                    'acl_roles_id' => $aclRolesId,
                    'acl_modules_actions_id' => $aclModulesActionsId
            );

            try {
                $insert = $aclRolesModulesAction->insert($data);
            } catch (Zend_Db_Exception $e) {
                if ($e->getCode() == 23000) {
                    $printData = print_r($data, 1);
                    Debug::write("Ya existe permiso asociado a $printData");
                }
            }
        }
        return $insert || $delete;
    }

    /**
     * Da permisos para listar en todos los modulos padres para poder acceder a modulo actual.
     *
     * @param int $aclModulesId
     * @param int $aclRolesId
     * @param string $action
     * @return Zend_Db_Table_Row
     */
    public function setParentModulesPermissions($aclModulesId, $aclRolesId, $action = 'LIST', $selfPermission = true)
    {
        $aclModulesModel = new DbTable_AclModules();
        $aclModuleRow = $aclModulesModel->find($aclModulesId)->current();

        $parent = $aclModuleRow->findParentRow('DbTable_AclModules');

        if ($parent && $parentId != $aclModulesId) {
            $actions = $parent->findDependentRowset('DbTable_AclModulesActions');
            $aclModulesActionsId = null;

            foreach ($actions as $a) {
                if ($a->acl_actions_id == $action) {
                    $acl_modules_action_id = $a->id;
                }
            }
            if ($aclModulesActionsId) {
                try {
                    $aclRolesModulesActionsModel = new AclRolesModulesActionsModel();
                    $data = array(
                            'acl_modules_actions_id' => $aclModulesActionsId,
                            'acl_roles_id' => $aclRolesId
                    );
                    $aclRolesModulesActionsModel->insert($data);
                } catch (Zend_Db_Exception $e) {
                    Debug::write($e->getMessage());
                }
            }
            $this->setParentModulesPermissions($aclModulesId, $aclRolesId, 'LIST', false);
        }
        return $aclModuleRow;
    }

    /**
     * (non-PHPdoc)
     * @see Zwei_Db_Table::delete()
     */
    public function delete($where)
    {
        $delete = parent::delete($where);
        if ($delete) { //borrar permisos asociados
            $arrWhere = self::whereToArray($where);
            Debug::write($arrWhere);
            //$aclRolesActions = new DbTable_AclRolesModulesActions();//[FIXME] esto termina la ejecucion del script, no sabemos por qué. Por ello no lo usaremos.

            $where = $this->getAdapter()->quoteInto("acl_roles_id = ? ", $arrWhere['id']);
            Debug::write($where);
            
            $query = "DELETE FROM acl_roles_modules_actions WHERE $where";
            Debug::write($query);
            
            $deleteActions = $this->getAdapter()->query($query); //Ejecucion directa de SQL como workaround
            //$deleteActions = $aclRolesActions->delete($where);//[FIXME]
            Debug::write($deleteActions);
        }
        return $delete;
    }
}
