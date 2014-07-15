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
        
        //@TODO "if" Backward Compatibility patch (BCP)
        if (!(Zend_Session::getSaveHandler() instanceof Zend_Session_SaveHandler_DbTable) && in_array('must_refresh', $this->info('cols'))) {
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
        $this->_more = array('AclRolesId' => $aclRolesId);
        
        if ($addPermissions) {
            
            $cache = new Zwei_Controller_Plugin_Cache(Zwei_Controller_Config::getOptions());//@TODO una vez eliminados los BCP limpiar cache en self::notifyUsers()
            $cache->cleanByTags(array("roleid{$aclRolesId}"));
            
            if (Zend_Session::getSaveHandler() instanceof Zend_Session_SaveHandler_DbTable) { //@TODO BCP
                $this->notifyUsers($aclRolesId);
            }
        }
        
        return $update || $addPermissions;
    }
    
    /**
     * 
     * @param string $aclRolesId
     * @return Ambigous <boolean, number>
     */
    public function notifyUsers($aclRolesId)
    {
        $aclUsers = new DbTable_AclSession();
        $notifyUsers = false;
        
        $notifyUsers = $aclUsers->update(
            array('must_refresh' => '1'),
            $aclUsers->getAdapter()->quoteInto('acl_roles_id = ?', $aclRolesId)
        );
        
        return $notifyUsers;
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
        
        $addPermissions = $this->addPermissions($insert);
        
        return $insert || $addPermissions;
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
            //$aclRolesActions = new DbTable_AclRolesModulesActions();//[FIXME] esto termina la ejecucion del script silenciosamente, no sabemos por qué. Por ello no lo usaremos.
            //$deleteActions = $aclRolesActions->delete($where);
            
            $where = $this->getAdapter()->quoteInto("acl_roles_id = ? ", $arrWhere['id']);
            
            $query = "DELETE FROM acl_roles_modules_actions WHERE $where";
            $deleteActions = $this->getAdapter()->query($query); //Ejecucion directa de SQL como workaround
            
            if (Zend_Session::getSaveHandler() instanceof Zend_Session_SaveHandler_DbTable) {
                $query = "DELETE FROM acl_session WHERE $where"; //Terminar sesiones de usuarios con sesion abierta con perfil asociado
                $deleteSessions = $this->getAdapter()->query($query);
            }
        }
        return $delete;
    }
}
