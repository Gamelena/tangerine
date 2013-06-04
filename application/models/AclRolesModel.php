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

class AclRolesModel extends Zwei_Db_Table
{
    /**
     * 
     * @var string
     */
    protected $_name = "acl_roles";
    /**
     * 
     * @var string
     */
    protected $_name_modules = "acl_modules";
    /**
     * 
     * @var string
     */
    protected $_name_permissions = "acl_permissions";
    /**
     * 
     * @var array
     */
    protected $_data_permissions = array();
    
    /**
     * 
     * @var AclModulesModel
     */
    protected $_aclModulesActions;

    public function init() {
        $this->_aclModulesActions = new AclModulesActionsModel();
        parent::init();
    }
    
    public function select()
    {
        $select = new Zend_Db_Table_Select($this);

        //Si no pertenece al role_id 1, no puede ver a otros usuarios con ese perfil
        if ($this->_user_info->acl_roles_id != '1') {
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

        //$select = $this->selectPermissions($data['id']);
        $select = $this->_aclModulesActions->selectAllActions($data['id']);
        Debug::writeBySettings($select->__toString(), 'query_log');
        $permissions = $this->fetchAll($select);
        
        if (count($permissions) > 0) {
           foreach ($permissions as $perm) { //  $permissions->id = $permission->permission
               $data["permissions"][] = $perm['id'];
           }     
        }

        Debug::write($data);
        return $data;
    }

    
    
    /**
     * Retorna: permisos asociados a un usuario o todos los permisos posibles si no especifica usuario.
     * @param int|false
     * @return Zend_Db_Table_Select
     */
    public function selectPermissions($acl_roles_id = false) 
    {
        $webPermissions = new PermissionsModel();
        $select = $webPermissions->select();
        $select->order("title");
        
        $actions = $this->fetchAll($select);
        
        $aSelect = array();
        foreach ($actions as $v) {
            $selectTmp = new Zend_Db_Table_Select($this);
            $selectTmp->setIntegrityCheck(false);
            
            $selectTmp->distinct();
            $selectTmp->from($this->_name_modules, 
                array(
                    'id' => new Zend_Db_Expr("CONCAT($this->_name_modules.id, ';$v->id')"),  
                    'title', 
                    'module',
                    'permission' => new Zend_Db_Expr("'$v->id'")
                )
            );
            
            $selectTmp->joinLeft(array('parent'=>$this->_name_modules), "$this->_name_modules.parent_id = parent.id",
                array(
                    "title" => new Zend_Db_Expr(
                                "IF($this->_name_modules.parent_id > 0, 
                                    CONCAT(parent.title, '->', $this->_name_modules.title, ' <b>($v->title)</b>'), 
                                    CONCAT($this->_name_modules.title, ' <b>($v->title)</b>'))"
                    ),
                    "parent_title" => "title"
                )
            );
            
            if ($acl_roles_id) {
                $selectTmp->joinLeft($this->_name_permissions, "$this->_name_modules.id = $this->_name_permissions.{$this->_name_modules}_id", array('permission_id'=>'id'));
                $selectTmp->where("$this->_name_permissions.acl_roles_id = ?", $acl_roles_id);
                $selectTmp->where("$this->_name_permissions.permission = ?", $v->id);
            }
            
            $selectTmp->where("$this->_name_modules.approved = ?", "1");
            
            //Si no pertenece al role_id 1, no puede ver módulos root
            if ($this->_user_info->acl_roles_id != '1') $selectTmp->where("$this->_name_modules.root != ?", "1");
            
            $aSelect[] = $selectTmp;
        }
        
        $select = new Zend_Db_Table_Select($this);
        $select->union($aSelect);
        $select->order("parent_title");
        $select->order("module");
        $select->order("id");
        
        return $select;
    }
    
    
   /**
     * Retorna: permisos asociados a un usuario o todos los permisos posibles si no especifica usuario.
     * @param int|false
     * @return Zend_Db_Select
     */
    public function selectIdPermissions($acl_roles_id = false) 
    {
        $select = new Zend_Db_Select($this->getAdapter());
        
        $select->from($this->_name_permissions, array('id'));
       
        if ($acl_roles_id) {
            $select->where("$this->_name_permissions.acl_roles_id = ?", $acl_roles_id);
        }
        return $select;
    }

    public function cleanDataParams($data)
    {
        if (!empty($data['permissions'])) {
            $this->_data_permissions = $data['permissions'];
            unset($data['permissions']);
        } else {
            $this->_data_permissions = array();
        }
        return $data;
    }
    
    public function update($data, $where)
    {
        $data = $this->cleanDataParams($data);
        
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
        $acl_roles_id = $arrWhere[1];
        
        $addPermissions = $this->addPermissions($acl_roles_id);
        
        if (!$update && $addPermissions) $update = $addPermissions; //Devolver <> false frente a cualquier modificacion
        
        Zwei_Utils_File::clearRecursive(ROOT_DIR ."/cache"); 
        return $update;
    }
    
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
        if (!$insert && $addPermissions) $insert = $addPermissions; //Devolver <> false frente a cualquier modificacion
        
        Zwei_Utils_File::clearRecursive(ROOT_DIR ."/cache"); 
        return $insert;    
    }
    
    
    public function addPermissions($acl_roles_id)
    {
        $aclPermissions = new AclPermissionsModel();
        $where = array();
        $where[] = $aclPermissions->getAdapter()->quoteInto("acl_roles_id = ?", $acl_roles_id);
        
        $return = false;
        
        $whereOr = array();
        foreach ($this->_data_permissions as $i => $v) {
            $mp = explode(';', $v);
            $acl_modules_id = $mp[0];
            $permission = $mp[1];
            $whereOr[] = 
                    "(".$aclPermissions->getAdapter()->quote($acl_modules_id) . ", " .
                    $aclPermissions->getAdapter()->quote($permission).")";
        }
        
        if (!empty($this->_data_permissions)) {
            $list = implode(",", $whereOr);
            $where[] = "(acl_modules_id, permission) NOT IN ($list)";
        }
        
        
        $delete = $aclPermissions->delete($where);
        
        if (empty($this->_data_permissions)) $return = $delete;

        $insert =  false;
        foreach ($this->_data_permissions as $v) {
            $mp = explode(';', $v);
            $acl_modules_id = $mp[0];
            $permission = $mp[1];
            
            $data = array(
               'acl_roles_id' => $acl_roles_id, 
               'acl_modules_id' => $acl_modules_id, 
               'permission' => $permission
            );
            
            try {
                $insert = $aclPermissions->insert($data);
            } catch (Zend_Db_Exception $e) {
                if ($e->getCode() == 23000) {
                    $printData = print_r($data, 1);
                    Debug::write("Ya existe permiso asociado a $printData");
                }
            }
            
            if ($insert) $return = true;
        }
        return $return;
    }
    
    
    public function delete($where)
    {
        $delete = parent::delete($where);
        if ($delete) { //borrar permisos asociados
            $where = str_replace('id', 'acl_roles_id', $where);
            $aclPermissions = new AclPermissionsModel();
            $aclPermissions->delete($where);
        }
        return $delete;
    }
}
