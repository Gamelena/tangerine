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
	private static $_acl;
	private static $_ready = false;
	private static $_permisos = null;
	private static $_db = null;
	private static $_tb_roles = null;
	private static $_tb_users = null;
	private static $_tb_modules = null;
	private static $_tb_permissions = null;

	private static $_getUserRoleName = null;
	private static $_getUserRoleId = null;
	private static $_user = null;
	private static $_userInfo = null;
	private static $_user_login = null;
	private static $_user_role_id = null;

	public static function _init()
	{
		#throw new Exception("Error");
		self::$_acl = new Zwei_Admin_Acl();
		
		if (!Zend_Auth::getInstance()->hasIdentity()){
			self::$_user = $user ? $user : 'Guest';
		} else {
			self::$_userInfo = Zend_Auth::getInstance()->getStorage()->read();
			self::$_user = self::$_userInfo->user_name;
		}

		$config = new Zend_Config_Ini(ROOT_DIR.'/application/configs/application.ini', APPLICATION_ENV);

		$db_params = $config->resources->multidb->auth ? $config->resources->multidb->auth : $config->db;
		
		self::$_db = Zend_Db::factory($db_params);

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
                ->where( self::$_tb_users.".".self::$_user_login." = '".self::$_user."'")
                ->where( self::$_tb_users.".".self::$_user_role_id." = ".self::$_tb_roles.".id");
		
		#Zwei_Utils_Debug::write($select->__toString());

		$getUserRole = self::$_db->fetchRow($select);
		
		self::$_getUserRoleId = $getUserRole[self::$_user_role_id] ? $getUserRole[self::$_user_role_id] : 4;
		self::$_getUserRoleName = $getUserRole["role_name"] ? $getUserRole["role_name"] : 'User';

		self::$_acl->addRole(new Zend_Acl_Role(self::$_user), self::$_getUserRoleName);
		self::$_ready = true;
	}

	private static function initRoles()
	{
		$select = self::$_db->select()
		        ->from(self::$_tb_roles)
		        ->order(array('id DESC'));

		#Zwei_Utils_Debug::write($select->__toString());
        
		$roles = self::$_db->fetchAll($select);

		self::$_acl->addRole(new Zend_Acl_Role($roles[0]['role_name']));

		for ($i = 1; $i < count($roles); $i++) {
			self::$_acl->addRole(new Zend_Acl_Role($roles[$i]['role_name']), $roles[$i-1]['role_name']);
		}
	}

	private static function initResources()
	{
		self::initRoles();

		$select = self::$_db->select()
		->from(self::$_tb_modules);
		
		#Zwei_Utils_Debug::write($select->__toString());

		$resources = self::$_db->fetchAll( $select );

		foreach ($resources as $key=>$value){
			if (!self::$_acl->has($value['module'])) {
				self::$_acl->add(new Zend_Acl_Resource($value['module']));
			}
		}
	}

	private static function roleResource()
	{
		self::initResources();

		$select = self::$_db->select()
		->from(self::$_tb_roles)
		->from(self::$_tb_modules)
		->from(self::$_tb_permissions)
		->where(self::$_tb_roles.".id = ".self::$_tb_permissions.".".self::$_tb_roles."_id");
		
		#Zwei_Utils_Debug::write($select->__toString());

		$acl = self::$_db->fetchAll( $select );

		foreach ($acl as $key=>$value) {
			self::$_acl->allow($value['role_name'], $value['module'], $value['permission']);
		}
	}

	public static function listRoles()
	{
		if( !self::$_ready ){
			Zwei_Utils_Debug::write("Inicializando modulo permisos");
			self::_init();
		}


		$select = self::$_db->select()
		->from(self::$_tb_roles);
		
		#Zwei_Utils_Debug::write($select->__toString());
		
		return self::$_db->fetchAll( $select );
	}

	public static function getRoleId($roleName)
	{
		if( !self::$_ready ){
			Zwei_Utils_Debug::write("Inicializando modulo permisos");
			self::_init();
		}


		$select = self::$_db->select()
		->from(self::$_tb_roles, "id")
		->where(self::$_tb_roles.".role_name = '".$roleName."'");
		
		#Zwei_Utils_Debug::write($select->__toString());

		return self::$_db->fetchRow( $select );
	}

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
		
		#Zwei_Utils_Debug::write($select->__toString());
		
		return self::$_db->fetchAll( $select );
	}

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
                ->from(self::$_tb_permissions, array())
	   	        ->from(self::$_tb_roles, array())
		        ->from(self::$_tb_users, array())
		        ->where(self::$_tb_modules."_id = ".self::$_tb_modules.id)
		        ->where(self::$_tb_permissions.".".self::$_tb_roles."_id = ".self::$_tb_roles.id)
		        ->where(self::$_tb_users.".".self::$_user_role_id." = ".self::$_tb_permissions.self::$_tb_roles.id)
		        ->where(self::$_tb_users.self::$_user_login." = '".self::$_user."'");
		} 
		$select->where('parent_id ='.(int)$parent_id);
		
		$select->where(self::$_tb_modules.'.tree = ?', '1') //[TODO] externalizar la condicion tree segun el caso
		;

		#Zwei_Utils_Debug::write($select->__toString());
		
		return(self::$_db->fetchAll($select));
	}

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
		
		#Zwei_Utils_Debug::write($select->__toString());
		
		$group = self::$_db->fetchAll( $select );

		foreach ($group as $key=>$value) {
			if(self::$_acl->isAllowed(self::$_user, $value['module'], $value['permission'])) {
				$result[] = $value['permission'];
			}
		}

		return $result;
	}

	public static function isUserAllowed($resource, $permission=null)
	{
		if( !self::$_ready ){
			self::_init();
		}

		return (self::$_acl->isAllowed(self::$_user, $resource, $permission));

	}

	public static function isActionResourceAllowed( $permission, $resource )
	{
		if( !self::$_ready ){
			self::_init();
		}

		return (self::$_acl->isAllowed(self::$_user, $resource, $permission));

	}

	public static function isActionAllowed( $permission )
	{
		if( !self::$_ready ){
			self::_init();
		}

		return (self::$_acl->isAllowed(self::$_user, $_REQUEST['p'], $permission));

	}

	public function isAllowed($user, $resource, $permission){

		if( isset( self::$_permisos[ $user ][ md5( $resource ) ][ $permission ] ) ){
			return self::$_permisos[ $user ][ md5( $resource ) ][ $permission ];
		}

		$select=self::$_db->select()
		->from(self::$_tb_modules, array('id'))
		->from(self::$_tb_permissions, array())
		->from(self::$_tb_roles, array())
		->from(self::$_tb_users, array())
		->where(self::$_tb_modules."_id = ".self::$_tb_modules.".id" )
		->where(self::$_tb_permissions.".".self::$_tb_roles."_id = ".self::$_tb_roles.".id")
		->where(self::$_tb_users.".".self::$_user_role_id." = ".self::$_tb_permissions.".".self::$_tb_roles."_id")
		->where(self::$_tb_modules.".module ='$resource'");


		if($permission!=null){
			$select->where(self::$_tb_permissions.".permission = '$permission'");
		}

		$select->where( self::$_tb_users.".".self::$_user_login." = '".$user."'")
			->group( self::$_tb_modules.".id" );
			
		#Zwei_Utils_Debug::write($select->__toString());
			
		$result=self::$_db->fetchAll($select);
		
		$rtn = (isset($result[0]['id'])) ? true: false;
		self::$_permisos[ $user ][ md5( $resource ) ][ $permission ] = $rtn;
		
		return $rtn;
	}


}
