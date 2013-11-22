<?php
/**
 * Operaciones y validaciones, arquitectura de Lista de Control de Acceso (ACL)
 *
 *
 * @category Zwei
 * @package Zwei_Admin
 * @version $Id:$
 * @since 0.1
 *
 *
 */
 
class Zwei_Admin_Acl extends Zend_Acl
{
    /**
     * Permisos asociados a sesión de usuario.
     * @var Zwei_Admin_Acl
     */
    private $_acl = null;
    /**
     * Base de datos a conectar.
     * @var Zend_Db_Adapter
     */
    private $_db = null;
    /**
     * Tabla de perfiles.
     * @var string
     */
    private $_tb_roles = 'acl_roles';
    /**
     * Tabla de usuarios.
     * @var string
     */
    private $_tb_users = 'acl_users';
    /**
     * Tabla de módulos.
     * @var string
     */
    private $_tb_modules = 'acl_modules';
    /**
     * Tabla de acciones por módulo.
     * @var string
     */
    private $_tb_modules_actions = 'acl_modules_actions';
    /**
     * Tabla de permisos.
     * @var string
     */
    private $_tb_roles_modules_actions = 'acl_roles_modules_actions';
    /**
     * Tabla de permisos.
     * @var string
     */
    private $_tb_groups_modules_actions = 'acl_groups_modules_actions';
    /**
     * Tabla de action.
     * @var string
     */
    private $_tb_actions = 'acl_actions';
    /**
     * Campo nombre de perfil de sesión activa.
     * @var string
     */
    private $_getUserRoleName = 'role_name';
    /**
     * Campo id de perfil de sesión activa.
     * @var string
     */
    private $_getUserRoleId = null;
    /**
     * Campo nombre de usuario en sesión.
     * @var string
     */
    private $_user = null;
    /**
     * Datos de usuario en sesión.
     * @var Zend_Auth_Storage
     */
    private $_userInfo = null;
    /**
     * Campo de user login en tabla $this->_tb_users.
     * @var string
     */
    private $_user_login = 'user_name';
    /**
     * Campo id de perfil en tabla $this->_tb_users.
     * @var string
     */
    private $_user_role_id = 'acl_roles_id';

    /**
     * 
     * @param Zend_Auth $user
     */
    public function __construct() {
        $this->_userInfo = Zend_Auth::getInstance()->getStorage()->read();
        
        if (Zwei_Controller_Config::getResourceMultiDb()) {
            $resource = Zwei_Controller_Config::getResourceMultiDb();
            $this->_db = $resource->getDb("auth");
        } else {
            $this->_db = Zwei_Controller_Config::getResourceDb();
        }
        self::roleResource();
    }
    
    /**
     * Inicializa los perfiles
     * @return void
     */
    private function initRoles()
    {
        $select = $this->_db->select()
            ->from($this->_tb_roles)
            ->order(array('id DESC'));
        
        $roles = $this->_db->fetchAll($select);
        
        $this->addRole(new Zend_Acl_Role($roles[0]['id']));
        
        for ($i = 1; $i < count($roles); $i++) { 
            $this->addRole(new Zend_Acl_Role($roles[$i]['id']), $roles[$i-1]['id']);
        }
    }
    
    /**
     * Inicializa los recursos (módulos).
     * @return void
     */
    private function initResources()
    {
        self::initRoles();
        
        $select = $this->_db->select()
            ->from($this->_tb_modules);
        
        $resources = $this->_db->fetchAll( $select );
        
        foreach ($resources as $key=>$value) {
            if (!$this->has($value['id'])) {
                $this->add(new Zend_Acl_Resource($value['id']));
            }
        }
    }

    /**
     * 
     * 
     * Añade las reglas "allow" al usuario en sesión.
     * 
     * @return void
     */
    private function roleResource()
    {
        self::initResources();
        
        $select = $this->_db->select();
        $select->from($this->_tb_roles_modules_actions, array('acl_roles_id'));
        $select->joinLeft($this->_tb_modules_actions, 
            "$this->_tb_modules_actions.id = $this->_tb_roles_modules_actions.acl_modules_actions_id", array()
        );
        $select->joinLeft($this->_tb_modules, 
            "$this->_tb_modules.id = $this->_tb_modules_actions.acl_modules_id", array('acl_modules_id' => 'id')
        );
        $select->joinLeft($this->_tb_actions,
            "$this->_tb_actions.id = $this->_tb_modules_actions.acl_actions_id", array('acl_actions_id' => 'id')
        );
        $select->where("$this->_tb_modules.id IS NOT NULL");
        
        $acl = $this->_db->fetchAll($select);
        
        foreach ($acl as $row) {
            $this->allow($row['acl_roles_id'], $row['acl_modules_id'], $row['acl_actions_id']);
        }
    }

    /**
     * Lista todos los perfiles.
     * 
     * @return Zend_Db_Rowset
     */
    public function listRoles()
    {
        $select = $this->_db->select()->from($this->_tb_roles);
        return $this->_db->fetchAll( $select );
    }

    /**
     * Obtiene id de perfil a través del nombre.
     * 
     * @param string $roleName
     * @return Zend_Db_Table_Row
     */
    public function getRoleId($roleName)
    {
        $select = $this->_db->select()
            ->from($this->_tb_roles, "id")
            ->where($this->_tb_roles.".role_name = '".$roleName."'");
        
        //Zwei_Utils_Debug::write($select->__toString());
        return $this->_db->fetchRow( $select );
    }

    /**
     * Inserta usuario.
     * 
     * @return int - last insert id
     */
    public function insertAclUser()
    {
        $data = array(
        $this->_tb_roles.'_id' => $this->_getUserRoleId,
            'user_name' => $this->_user);

        return $this->_db->insert($this->_tb_users, $data);
    }

    /**
     * Listado de los módulos.
     * 
     * @return Zend_Db_Table_Rowset
     */
    public function listResources()
    {
        $select = $this->_db->select()
            ->from($this->_tb_modules)
            ->from($this->_tb_roles_modules_actions)
            ->where($this->_tb_modules."_id = ".$this->_tb_modules.".id");
        return $this->_db->fetchAll( $select );
    }

    /**
     * Devuelve una lista con los recursos que a que tiene acceso el usuario en sesión.
     * 
     * @param $parent_id int
     * @return Zend_Db_Rowset
     */
    public function listGrantedResourcesByParentId($parentId)
    {
        $selectRoles = $selectGroups = $this->_db->select()
            ->from($this->_tb_modules, array('id', 'title', 'module', 'type', 'tree', 'refresh_on_load', 'ownership'));
        
        $groups = $this->_userInfo->groups;
        if (!empty($groups)) {
            $groups = implode(",", $groups);
        } else {
            $groups = false;
        }
        
        if ($this->_userInfo->{$this->_user_role_id} != ROLES_ROOT_ID) {
            $selectRoles->joinLeft($this->_tb_modules_actions, $this->_tb_modules_actions.".acl_modules_id=".$this->_tb_modules.".id", array());
            $selectRoles->joinLeft($this->_tb_roles_modules_actions, $this->_tb_roles_modules_actions.".acl_modules_actions_id=".$this->_tb_modules_actions.".id", array());
            if ($groups) {
                $selectRoles->joinLeft($this->_tb_groups_modules_actions, $this->_tb_groups_modules_actions.".acl_modules_actions_id=".$this->_tb_modules_actions.".id", array());
            }
            
            if ($groups) {
                $selectRoles->where("$this->_tb_roles_modules_actions.acl_roles_id={$this->_userInfo->acl_roles_id} OR $this->_tb_groups_modules_actions.acl_groups_id IN ($groups) OR ownership='1'");
            } else {
                $selectRoles->where("$this->_tb_roles_modules_actions.acl_roles_id={$this->_userInfo->acl_roles_id} OR ownership='1'");
            }
        }
        
        if (is_null($parentId)) {
            $selectRoles->where('parent_id IS NULL');
        } else {
            $selectRoles->where($this->_db->quoteInto('parent_id = ?', $parentId));
        }
        
        $selectRoles->joinLeft('web_icons', "web_icons.id=".$this->_tb_modules.'.icons_id', array('image'));
        $selectRoles->order("order");
        
        //Debug::writeBySettings($selectRoles->__toString(), 'query_log');
        
        return($this->_db->fetchAll($selectRoles));
    }

    /**
     * Lista permisos de un módulo.
     * 
     * @param $group
     * @return Zend_Db_Table_Rowset
     */
    public function listResourcesByGroup($group)
    {
        $result = null;
        
        $select = $this->_db->select()
            ->from($this->_tb_modules)
            ->from($this->_tb_roles_modules_actions)
            ->where($this->_tb_modules.'.module = "' . $group . '"')
            ->where($this->_tb_modules.".id = ".$this->_tb_modules."_id");
        
        //Zwei_Utils_Debug::write($select->__toString());
        
        $group = $this->_db->fetchAll( $select );

        foreach ($group as $key=>$value) {
            if ($this->isUserAllowed($value['module'], $value['permission'])) {
                $result[] = $value['permission'];
            }
        }

        return $result;
    }

    /**
     * Verifica si usuario en sesión tiene tal permiso en tal módulo. 
     * 
     * @param $resource string
     * @param $permission string
     * @return boolean
     */
    public function isUserAllowed($module, $permission = null, $itemId = null)
    {
        $allowed = $this->userHasRoleAllowed($module, $permission);
        
        $this->isOwner($module, $itemId);
        
        if (!$allowed) {
            $allowed = $this->userHasGroupsAllowed($module, $permission, $itemId);
        }
        
        return $allowed;
    }
    
    /**
     * Acceso de perfiles a modulos
     * 
     * @param string $module
     * @param string $permission
     * @return boolean
     */
    public function userHasRoleAllowed($module, $permission = null)
    {
        $aclModulesModel = new AclModulesModel();
        $resource = $aclModulesModel->getModuleId($module);
        $allowed =  $this->isAllowed($this->_userInfo->acl_roles_id, $resource, $permission);
        return $allowed;
    }
    
    /**
     * Acceso de grupos a contextos
     * [TODO] buscar una forma de resolver esto por reglas de acceso para subcontextos en lugar de usar las sig queries.
     *
     * @param string $module
     * @param string $permission
     * @param string $itemId
     * @param Zwei_Db_Table $model
     * @return boolean
     */
    public function userHasGroupsAllowed($module, $permission, $itemId = null)
    {
        $aclModulesModel = new AclModulesModel();
        $moduleRow = $aclModulesModel->findModule($module);
        $resource = $moduleRow->id;
        $moduleType = $moduleRow->type;
        
        if ($moduleType == 'xml') {
            $file = Zwei_Admin_Xml::getFullPath($module);
            $xml = new Zwei_Admin_Xml($file, null, true);
            if ($xml->getAttribute('target')) {
                $modelName = $xml->getAttribute('target');
                $model = new $modelName();
                
                if (is_a($model, 'Zwei_Db_Table')) {
                    if ($model->isOwner($itemId)) return true;
                }
            }
        }
        
        $groups = $this->_userInfo->groups;
        if (!empty($groups)) {
            $groups = implode(",", $groups);
            $aclModulesActionsModel = new DbTable_AclModulesActions();
            
            $permission = $aclModulesActionsModel->getAdapter()->quote($permission);
            $resource = $aclModulesActionsModel->getAdapter()->quote($resource);
            
            $select = $aclModulesActionsModel->select()->where("acl_modules_id=$resource AND acl_actions_id=$permission");
            
            Debug::writeBySettings($select->__toString(), 'query_log');
            
            //Obtenemos acl_modules_actions.id para usar acl_groups_modules_action.acl_modules_actions_id
            $aclModulesActions = $aclModulesActionsModel->fetchAll($select);
            
            foreach ($aclModulesActions as $rowAclModulesActions) {
                //Si $itemId es nulo, sólo se verifica que el grupo tenga la acción cualquiera sobre el módulo en acl_groups_modules_actions . 
                $aclGMAModel = new AclGroupsModulesActionsModel();
                $select = $aclGMAModel->select();
                $where = array();
                $select->where("acl_groups_id IN($groups)");
                $select->where($aclGMAModel->getAdapter()->quoteInto('acl_modules_actions_id = ?', $rowAclModulesActions->id));

                if ($itemId) {
                    $select->where($aclGMAModel->getAdapter()->quoteInto('acl_modules_item_id = ?', $itemId));
                }
                Debug::writeBySettings($select->__toString(), 'query_log');
                return $aclGMAModel->fetchRow($select) ? true : false;
            }
        } else {
            return false;
        }
    }
    /**
     * 
     * @param string $module
     * @param string $permission
     * @param string $itemId
     */
    public function isOwner($module, $itemId = null)
    {
        $file = Zwei_Admin_Xml::getFullPath($module);
        $xml = new Zwei_Admin_Xml($file, null, true);
        Debug::write($xml);
    }
    
    /**
     * Verifica si usuario en sesión tiene tal permiso en tal módulo 
     * [FIXME] está repetida debería deprecarse y borrarse.
     * 
     * @param $permission string
     * @param $resource string
     * @return boolean
     */
    public function isActionResourceAllowed($permission, $resource)
    {
        return ($this->isAllowed($this->_userInfo->acl_roles_id, $resource, $permission));
    }

    /**
     * Verifica si sesión de usuario tiene acceso a módulo $_REQUEST['p']
     * 
     * @param $permission string - 'LIST'|'EDIT'|'ADD'|'DELETE' 
     * @return boolean 
     */
    public function isActionAllowed($permission)
    {
        return ($this->_acl->isAllowed($this->_user, $_REQUEST['p'], $permission));
    
    }
}
