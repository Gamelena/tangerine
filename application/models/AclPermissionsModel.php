<?php

/**
 * Modelo de datos para permisos ACL del admin
 *
 *
 * @category Zwei
 * @package Models
 * @version $Id:$
 * @since 0.1
 *
 */

class AclPermissionsModel extends Zwei_Db_Table
{
    protected $_name = "acl_permissions";
    protected $_name_roles = "acl_roles";
    protected $_name_modules = "acl_modules";
    protected $_name_permissions = "web_permissions";
        protected $_primary = 'id';

    public function select()
    {
        $select=new Zend_Db_Table_Select($this);
        $select->setIntegrityCheck(false); //de lo contrario no podemos hacer JOIN
        $select->from($this->_name)
        ->joinLeft($this->_name_permissions, "$this->_name.permission=$this->_name_permissions.id", array("permission_title"=>"title"))
        ->joinLeft($this->_name_roles, "$this->_name.{$this->_name_roles}_id = $this->_name_roles.id", "role_name")
        ->joinLeft($this->_name_modules, "$this->_name.{$this->_name_modules}_id = $this->_name_modules.id", array("module","module_title"=>"title"));
        
        //Si no es root no puede ver a usuarios root
        if ($this->_user_info->acl_roles_id != '1') {
            $select->where("$this->_name_roles.id != ?", "1");
        }
        //Zwei_Utils_Debug::write($select->__toString());
        return $select;
    }
    
    public function update($data, $where)
    {
       Zwei_Utils_File::clearRecursive(ROOT_DIR ."/cache");    
       return parent::update($data, $where)    ;
    }
    
    public function insert($data)
    {
       Zwei_Utils_File::clearRecursive(ROOT_DIR ."/cache"); 
       return parent::insert($data);
    }
    
    public function delete($where)
    {
       Zwei_Utils_File::clearRecursive(ROOT_DIR ."/cache");         
       return parent::delete($where);        
    }
}

