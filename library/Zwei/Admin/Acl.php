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

	private $_db;
	private $_tb_roles;
	private $_tb_users;
	private $_tb_modules;
	private $_tb_permissions;

	public $_getUserRoleName = null;
	public $_getUserRoleId = null;
	public $_user = null;

	public function __construct($user)
	{
		$this->_user = $user ? $user : 'Guest';

		$this->_tb_roles = 'acl_roles';
		$this->_tb_users = 'acl_users';
		$this->_tb_modules = 'acl_modules';
		$this->_tb_permissions = 'acl_permissions';

		$config = new Zend_Config_Ini(ROOT_DIR.'/application/configs/application.ini', APPLICATION_ENV);
		$this->_db = Zend_Db::factory($config->db);

		self::roleResource();

		$getUserRole = $this->_db->fetchRow(
		$this->_db->select()
		->from($this->_tb_roles)
		->from($this->_tb_users)
		->where($this->_tb_users.'.user_name = "' . $this->_user . '"')
		->where($this->_tb_users.".{$this->_tb_roles}_id = $this->_tb_roles.id"));

		$this->_getUserRoleId = $getUserRole[$this->_tb_roles.'_id'] ? $getUserRole[$this->_tb_roles.'_id'] : 4;
		$this->_getUserRoleName = $getUserRole['role_name'] ? $getUserRole['role_name'] : 'User';

		$this->addRole(new Zend_Acl_Role($this->_user), $this->_getUserRoleName);

	}

	private function initRoles()
	{
		//Zwei_Utils_Debug::write('initRoles');
		$roles = $this->_db->fetchAll(
		$this->_db->select()
		->from($this->_tb_roles)
		->order(array('id DESC')));

		$this->addRole(new Zend_Acl_Role($roles[0]['role_name']));

		for ($i = 1; $i < count($roles); $i++) {
			$this->addRole(new Zend_Acl_Role($roles[$i]['role_name']), $roles[$i-1]['role_name']);
		}
	}

	private function initResources()
	{
		self::initRoles();

		$resources = $this->_db->fetchAll(
		$this->_db->select()
		->from($this->_tb_modules));

		foreach ($resources as $key=>$value){
			if (!$this->has($value['module'])) {
				$this->add(new Zend_Acl_Resource($value['module']));
			}
		}
	}

	private function roleResource()
	{
		self::initResources();

		$acl = $this->_db->fetchAll(
		$this->_db->select()
		->from($this->_tb_roles)
		->from($this->_tb_modules)
		->from($this->_tb_permissions)
		->where($this->_tb_roles.".id = $this->_tb_permissions.{$this->_tb_roles}_id"));

		foreach ($acl as $key=>$value) {
			$this->allow($value['role_name'], $value['module'], $value['permission']);
		}
	}

	public function listRoles()
	{
		return $this->_db->fetchAll(
		$this->_db->select()
		->from($this->_tb_roles));
	}

	public function getRoleId($roleName)
	{
		return $this->_db->fetchRow(
		$this->_db->select()
		->from($this->_tb_roles, 'id')
		->where($this->_tb_roles.'.role_name = "' . $roleName . '"'));
	}

	public function insertAclUser()
	{
		$data = array(
		$this->_tb_roles.'_id' => $this->_getUserRoleId,
            'user_name' => $this->_user);

		return $this->_db->insert($this->_tb_users, $data);
	}

	public function listResources()
	{
		return $this->_db->fetchAll(
		$this->_db->select()
		->from($this->_tb_modules)
		->from($this->_tb_permissions)
		->where("{$this->_tb_modules}_id = $this->_tb_modules.id")
		);
	}

	public function listGrantedResourcesByParentId($parent_id)
	{
		 
		$select=$this->_db->select()
		->from($this->_tb_modules)
		->from($this->_tb_permissions, array())
		->from($this->_tb_roles, array())
		->from($this->_tb_users, array())
		->where($this->_tb_modules."_id = $this->_tb_modules.id")
		->where($this->_tb_permissions.".{$this->_tb_roles}_id = $this->_tb_roles.id")
		->where($this->_tb_users.".acl_roles_id = $this->_tb_permissions.{$this->_tb_roles}_id")
		->where('parent_id ='.(int)$parent_id)
		->where($this->_tb_users.'.user_name = "' . $this->_user . '"')
		->where($this->_tb_modules.'.tree = ?', '1') //[TODO] externalizar la condicion tree segun el caso

		->group($this->_tb_modules.'.id')
		;

		//Zwei_Utils_Debug::write($select->__toString());
		return($this->_db->fetchAll($select));
	}

	public function listResourcesByGroup($group)
	{
		$result = null;
		$group = $this->_db->fetchAll($this->_db->select()
		->from($this->_tb_modules)
		->from($this->_tb_permissions)
		->where($this->_tb_modules.'.module = "' . $group . '"')
		->where($this->_tb_modules.".id = {$this->_tb_modules}_id")
		);

		foreach ($group as $key=>$value) {
			if($this->isAllowed($this->_user, $value['module'], $value['permission'])) {
				$result[] = $value['permission'];
			}
		}

		return $result;
	}

	public function isUserAllowed($resource, $permission=null)
	{
		return ($this->isAllowed($this->_user, $resource, $permission));

	}

	public function isAllowed($user, $resource, $permission){
		//[TODO] este metodo es nativo de Zend_Acl pero debió ser reescrito para que funcionara según lo esperado
		$select=$this->_db->select()
		->from($this->_tb_modules, array('id'))
		->from($this->_tb_permissions, array())
		->from($this->_tb_roles, array())
		->from($this->_tb_users, array())
		->where($this->_tb_modules."_id = $this->_tb_modules.id")
		->where($this->_tb_permissions.".{$this->_tb_roles}_id = $this->_tb_roles.id")
		->where($this->_tb_users.".acl_roles_id = $this->_tb_permissions.{$this->_tb_roles}_id")
		->where($this->_tb_modules.".module ='$resource'");

		if($permission!=null){
			$select->where($this->_tb_permissions.".permission = '$permission'");
		}

		$select->where($this->_tb_users.'.user_name = "' . $user . '"')
		->group($this->_tb_modules.'.id')
		;
		$result=$this->_db->fetchAll($select);

		return (isset($result[0]['id'])) ? true: false;
	}


}
