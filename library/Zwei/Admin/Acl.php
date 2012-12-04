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
    private static $_acl = null;
    /**
     * Flag que indica si ya existe una instancia estática de Zwei_Admin_Acl.
     * @var boolean
     */
    private static $_ready = false;
    /**
     * Tabla de permisos.
     * @var string
     */
    private static $_permisos = null;
    /**
     * Base de datos a conectar.
     * @var Zend_Db_Adapter
     */
    private static $_db = null;
    /**
     * Tabla de perfiles.
     * @var string
     */
    private static $_tb_roles = null;
    /**
     * Tabla de usuarios.
     * @var string
     */
    private static $_tb_users = null;
    /**
     * Tabla de módulos.
     * @var string
     */
    private static $_tb_modules = null;
    /**
     * Tabla de permisos.
     * @var string
     */
    private static $_tb_permissions = null;
    /**
     * Campo nombre de perfil de sesión activa.
     * @var string
     */
    private static $_getUserRoleName = null;
    /**
     * Campo id de perfil de sesión activa.
     * @var string
     */
    private static $_getUserRoleId = null;
    /**
     * Campo nombre de usuario en sesión.
     * @var string
     */
    private static $_user = null;
    /**
     * Datos de usuario en sesión.
     * @var Zend_Auth_Storage
     */
    private static $_userInfo = null;
    /**
     * Campo de user login en tabla self::$_tb_users.
     * @var string
     */
    private static $_user_login = null;
    /**
     * Campo id de perfil en tabla self::$_tb_users.
     * @var string
     */
    private static $_user_role_id = null;

    /**
     * 
     * @return void
     */
    public static function _init()
    {
        //throw new Exception("Error");
        
        if (!Zend_Auth::getInstance()->hasIdentity()) {
            Zend_Controller_Action_HelperBroker::getStaticHelper('redirector')->gotoAndExit('login');
        } else {
            self::$_userInfo = Zend_Auth::getInstance()->getStorage()->read();
            self::$_user = self::$_userInfo->user_name;
        }

        self::$_acl = new Zwei_Admin_Acl();

        if (Zend_Controller_Front::getInstance()->getParam('bootstrap')) {
            $configOptions = Zend_Controller_Front::getInstance()->getParam("bootstrap")->getApplication()->getOptions();
            $config = new Zend_Config($configOptions);
        } else {
            //[TODO] This is backward compatibility
            $config = new Zend_Config_Ini(ROOT_DIR.'/application/configs/application.ini', APPLICATION_ENV);   
        }  
        

        /**
         * [TODO] codigo backward compatibility, debiera ser reducido en la version 2 de AdmPortal
         */
        if (isset($config->resources->multidb->auth)) {
            if (isset($config->resources->multidb->auth->params)) {
                //Este bloque debe deprecarse
                $db_params = $config->resources->multidb->auth;
                self::$_db = Zend_Db::factory($db_params);
            } else {
                $resource = Zend_Controller_Front::getInstance()->getParam("bootstrap")->getResource("multidb");
                self::$_db = $resource->getDb("auth");
            }
         } else {
            $db_params = isset($config->resources->db) ? $config->resources->db : $config->db; //[TODO] backward compatibility, $config->db debe deprecarse.
            self::$_db = Zend_Db::factory($db_params);
        }

        
        
        self::$_tb_roles = 'acl_roles';
        self::$_tb_users = 'acl_users';
        self::$_tb_modules = 'acl_modules';
        self::$_tb_permissions = 'acl_permissions';
        self::$_user_login = 'user_name';
        self::$_user_role_id = 'acl_roles_id';

        self::roleResource();

        $select = self::$_db->select()
                 ->from(self::$_tb_roles)
                ->from(self::$_tb_users)
                ->where(self::$_tb_users.".".self::$_user_login." = '".self::$_user."'")
                ->where(self::$_tb_users.".".self::$_user_role_id." = ".self::$_tb_roles.".id");
        
        //Zwei_Utils_Debug::write($select->__toString());

        $getUserRole = self::$_db->fetchRow($select);
        
        self::$_getUserRoleId = $getUserRole[self::$_user_role_id] ? $getUserRole[self::$_user_role_id] : 4;
        self::$_getUserRoleName = $getUserRole["role_name"] ? $getUserRole["role_name"] : 'User';

        self::$_acl->addRole(new Zend_Acl_Role(self::$_user), self::$_getUserRoleName);
        self::$_ready = true;
    }

    /**
     *
     * Implementación Singleton.
     *
     * @return Zend_Admin_Acl
     */
    public static function getInstance()
    {
        if (null === self::$_acl) {
            self::$_acl = new self();
        }
        return self::$_acl;
    }
    
    
    /**
     * Inicializa los perfiles
     * @return void
     */
    private static function initRoles()
    {
        $select = self::$_db->select()
                ->from(self::$_tb_roles)
                ->order(array('id DESC'));

        //Zwei_Utils_Debug::write($select->__toString());
        
        $roles = self::$_db->fetchAll($select);

        self::$_acl->addRole(new Zend_Acl_Role($roles[0]['role_name']));

        for ($i = 1; $i < count($roles); $i++) {
            self::$_acl->addRole(new Zend_Acl_Role($roles[$i]['role_name']), $roles[$i-1]['role_name']);
        }
    }

    /**
     * Inicializa los recursos (módulos).
     * @return void
     */
    private static function initResources()
    {
        self::initRoles();

        $select = self::$_db->select()
        ->from(self::$_tb_modules);
        
        //Zwei_Utils_Debug::write($select->__toString());

        $resources = self::$_db->fetchAll( $select );

        foreach ($resources as $key=>$value){
            if (!self::$_acl->has($value['module'])) {
                self::$_acl->add(new Zend_Acl_Resource($value['module']));
            }
        }
    }

    /**
     * Añade las reglas "allow" al usuario en sesión.
     * 
     * @return void
     */
    private static function roleResource()
    {
        self::initResources();

        $select = self::$_db->select()
        ->from(self::$_tb_roles)
        ->from(self::$_tb_modules)
        ->from(self::$_tb_permissions)
        ->where(self::$_tb_roles.".id = ".self::$_tb_permissions.".".self::$_tb_roles."_id");
        
        //Zwei_Utils_Debug::write($select->__toString());

        $acl = self::$_db->fetchAll( $select );

        foreach ($acl as $key=>$value) {
            self::$_acl->allow($value['role_name'], $value['module'], $value['permission']);
        }
    }

    /**
     * Lista todos los perfiles.
     * 
     * @return Zend_Db_Rowset
     */
    public static function listRoles()
    {
        if( !self::$_ready ){
            Zwei_Utils_Debug::write("Inicializando modulo permisos");
            self::_init();
        }


        $select = self::$_db->select()
        ->from(self::$_tb_roles);
        
        //Zwei_Utils_Debug::write($select->__toString());
        
        return self::$_db->fetchAll( $select );
    }

    /**
     * Obtiene id de perfil a través del nombre.
     * 
     * @param string $roleName
     * @return Zend_Db_Table_Row
     */
    public static function getRoleId($roleName)
    {
        if( !self::$_ready ){
            Zwei_Utils_Debug::write("Inicializando modulo permisos");
            self::_init();
        }


        $select = self::$_db->select()
        ->from(self::$_tb_roles, "id")
        ->where(self::$_tb_roles.".role_name = '".$roleName."'");
        
        //Zwei_Utils_Debug::write($select->__toString());
        return self::$_db->fetchRow( $select );
    }

    /**
     * Inserta usuario.
     * [TODO] se usará esto a futuro? tiene sentido si se piensa en un log de sesiones abiertas. 
     * 
     * @return int - last insert id
     */
    public static function insertAclUser()
    {
        if( !self::$_ready ){
            Zwei_Utils_Debug::write("Inicializando modulo permisos");
            self::_init();
        }

        $data = array(
        self::$_tb_roles.'_id' => self::$_getUserRoleId,
            'user_name' => self::$_user);

        return self::$_db->insert(self::$_tb_users, $data);
    }

    /**
     * Listado de los módulos.
     * 
     * @return Zend_Db_Table_Rowset
     */
    public static function listResources()
    {
        if( !self::$_ready ){
            Zwei_Utils_Debug::write("Inicializando modulo permisos");
            self::_init();
        }

        $select = self::$_db->select()
        ->from(self::$_tb_modules)
        ->from(self::$_tb_permissions)
        ->where(self::$_tb_modules."_id = ".self::$_tb_modules.".id");
        
        //Zwei_Utils_Debug::write($select->__toString());
        
        return self::$_db->fetchAll( $select );
    }

    /**
     * Devuelve una lista con los recursos que a que tiene acceso el usuario en sesión.
     * 
     * @param $parent_id int
     * @return Zend_Db_Rowset
     */
    public static function listGrantedResourcesByParentId($parent_id)
    {
        if( !self::$_ready ){
            Zwei_Utils_Debug::write("Inicializando modulo permisos");
            self::_init();
        }

        $select=self::$_db->select()
        ->from(self::$_tb_modules);
        
        if (self::$_userInfo->{self::$_user_role_id} != '1') {
            $select
                ->distinct()  
                ->from(self::$_tb_permissions, array())
                   ->from(self::$_tb_roles, array())
                ->from(self::$_tb_users, array())
                ->where(self::$_tb_modules."_id = ".self::$_tb_modules.".id")
                ->where(self::$_tb_permissions.".".self::$_tb_roles."_id = ".self::$_tb_roles.".id")
                ->where(self::$_tb_users.".".self::$_user_role_id." = ".self::$_tb_permissions.".".self::$_tb_roles."_id")
                ->where(self::$_tb_users.".".self::$_user_login." = '".self::$_user."'");
        } 
        if (is_null($parent_id)) {
            $select->where('parent_id IS NULL');
        } else {
            $select->where('parent_id = ?', (int) $parent_id);
        }    
        
        $select->where(self::$_tb_modules.'.tree = ?', '1'); //[TODO] externalizar la condicion tree segun el caso
        $select->order("order");

        Debug::write($select->__toString());
        
        return(self::$_db->fetchAll($select));
    }

    /**
     * Lista permisos de un módulo.
     * [FIXME] se usa esto en algún lado?
     * 
     * @param $group
     * @return Zend_Db_Table_Rowset
     */
    public static function listResourcesByGroup($group)
    {
        if( !self::$_ready ){
            self::_init();
        }

        $result = null;
        
        $select = self::$_db->select()
        ->from(self::$_tb_modules)
        ->from(self::$_tb_permissions)
        ->where(self::$_tb_modules.'.module = "' . $group . '"')
        ->where(self::$_tb_modules.".id = ".self::$_tb_modules."_id");
        
        //Zwei_Utils_Debug::write($select->__toString());
        
        $group = self::$_db->fetchAll( $select );

        foreach ($group as $key=>$value) {
            if(self::$_acl->isAllowed(self::$_user, $value['module'], $value['permission'])) {
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
    public static function isUserAllowed($resource, $permission=null)
    {
        if (!self::$_ready) {
            self::_init();
        }

        return (self::$_acl->isAllowed(self::$_user, $resource, $permission));

    }

    /**
     * Verifica si usuario en sesión tiene tal permiso en tal módulo 
     * [FIXME] está repetida debería deprecarse y borrarse.
     * 
     * @param $permission string
     * @param $resource string
     * @return boolean
     */
    public static function isActionResourceAllowed($permission, $resource)
    {
        if (!self::$_ready) {
            self::_init();
        }

        return (self::$_acl->isAllowed(self::$_user, $resource, $permission));

    }

    /**
     * Verifica si sesión de usuario tiene acceso a módulo $_REQUEST['p']
     * 
     * @param $permission string - 'LIST'|'EDIT'|'ADD'|'DELETE' 
     * @return boolean 
     */
    public static function isActionAllowed( $permission )
    {
        if( !self::$_ready ){
            self::_init();
        }

        return (self::$_acl->isAllowed(self::$_user, $_REQUEST['p'], $permission));

    }

    /**
     * Verifica si tal usuario tiene tal permiso en tal módulo 
     * @param $user string - tal usuario.
     * @param $resource string - tal módulo.
     * @param $permission string - tal permiso 'LIST'|'EDIT'|'ADD'|'DELETE' 
     * @return boolean 
     */
    public function isAllowed($user, $resource, $permission = null)
    {

        if( isset( self::$_permisos[ $user ][ md5( $resource ) ][ $permission ] ) ){
            return self::$_permisos[ $user ][ md5( $resource ) ][ $permission ];
        }

        $select = self::$_db->select()
        ->from(self::$_tb_modules, array('id'))
        ->from(self::$_tb_permissions, array())
        ->from(self::$_tb_roles, array())
        ->from(self::$_tb_users, array())
        ->where(self::$_tb_modules."_id = ".self::$_tb_modules.".id" )
        ->where(self::$_tb_permissions.".".self::$_tb_roles."_id = ".self::$_tb_roles.".id")
        ->where(self::$_tb_users.".".self::$_user_role_id." = ".self::$_tb_permissions.".".self::$_tb_roles."_id")
        ->where(self::$_tb_modules.".module ='$resource'");


        if ($permission != null) {
            $select->where(self::$_tb_permissions.".permission = '$permission'");
        }

        $select->where( self::$_tb_users.".".self::$_user_login." = '".$user."'")
            ->group( self::$_tb_modules.".id" );
            
        Debug::writeBySettings($select->__toString(), 'query_log');
            
        $result = self::$_db->fetchAll($select);//Todo acá podía usarse $_db->fetchRow($select).
        
        $rtn = (isset($result[0]['id'])) ? true: false;
        self::$_permisos[$user][md5($resource)][$permission] = $rtn;
        
        return $rtn;
    }
}
