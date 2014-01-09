<?php

/**
 * Modelo de datos para usuarios ACL del admin
 *
 * @category Zwei
 * @package Models
 * @version $Id:$
 * @since 0.1
 *
 */

class AclUsersModel extends DbTable_AclUsers
{
    protected $_name_roles = "acl_roles";
    protected $_generate_pass = "user_name";

    public function select()
    {
        $select = new Zend_Db_Table_Select($this);
        $select->setIntegrityCheck(false); //de lo contrario no podemos hacer JOIN
        $select->from($this->_name, array('id', 'user_name', 'acl_roles_id', 'first_names', 'last_names', 'email', 'approved'));
        $select->joinLeft($this->_name_roles, "$this->_name.acl_roles_id = $this->_name_roles.id", "role_name");
        //[TODO] esto está en duro, debiera ser dinámico via campo root en acl_roles
        if ($this->_user_info->acl_roles_id != ROLES_ROOT_ID) {
            $select->where('acl_roles_id <> ?', '1');
        }
        $select->order('user_name');

        return $select;
    }

    /**
     * Se agregan grupos asociados a rowset original
     * @param Zend_Db_Table_Rowset
     * @return array
     */
    public function overloadDataForm($data) {
        $data = $data->toArray();
    
        $model = new DbTable_AclUsersGroups();
        $select = $model->select()->where("acl_users_id = ?", $data['id']);
        $usuarios = $model->fetchAll($select);
    
        foreach ($usuarios as $usuario) { //  $permissions->id = $permission->permission
            $data["grupos"][] = $usuario['acl_groups_id'];
        }
        return $data;
    }
    
    /**
     * Un usuario no podría borrarse a si mismo, si es que no existen otros usuarios que puedan cumplir su misión.
     * (si no existen otros usuarios con su mismo perfil)
     * @see Zwei_Db_Table::delete()
     */
    public function delete($where)
    {
        $aWhere = $this->whereToArray($where);
        $id = $aWhere['id'];
        
        $users = new DbTable_AclUsers();
        $row = $users->find($id)->current();
        
        if ($id == $this->_user_info->id) {
            //El usuario se está tratando de "suicidar" del sistema.

            
            if ($row->acl_roles_id == $this->_user_info->acl_roles_id) {
                //Nadie más puede cumplir su misión encomendada, no lo permitiremos.
                $this->setMessage("No puede darse de baja usted mismo,\nno hay más usuarios con perfil $row->role_name.\n\nSi detectó un problema de seguridad se le sugiere cambiar su contraseña en Configuración.");
                return false;
            } 
        }
        
        $delete = parent::delete($where);
        
        if ($delete) {
            $aclRoles = new DbTable_AclRoles();
            if (in_array('must_refresh', $aclRoles->info('cols'))) {
                $currentRole = $aclRoles->find($row->acl_roles_id)->current();
            
                $currentRole->must_refresh = '1';
                $currentRole->save();
            }
        }
        
        return $delete;
    }
    
    
    /**
     * En el caso de crearse un usuario nuevo y tener seteado cambio de password,
     * se genera la password repitiendo el nombre de usuario en md5
     * @return int
     */
    public function insert($data)
    {
        if (!isset($data["password"])) {
            $data["password"] = md5($data[$this->_generate_pass]);
        } else {
            $data["password"] = md5($data["password"]);
        }
        
        try {
            $last_insert_id = parent::insert($data);
        } catch(Zend_Db_Exception $e) {
            if ($e->getCode() == '23000') {
                $this->setMessage('Nombre de Usuario en uso.');
                return false;
            } else {
                Zwei_Utils_Debug::write("error:".$e->getMessage()."code".$e->getCode());
            }
        }
        return $last_insert_id;
    }

    /**
     * Captura de excepciones posibles como nombre de usuario en uso
     */

    public function update($data, $where)
    {
        try {
            $update = parent::update($data, $where);
        } catch(Zend_Db_Exception $e) {
            if ($e->getCode()=='23000') {
                $this->setMessage('Nombre de Usuario en uso.');
                return false;
            } else {
                Zwei_Utils_Debug::write("error:".$e->getMessage()."code".$e->getCode());
            }
        }
        
        if ($update && $data['approved'] != '1') {
            $aclRoles = new DbTable_AclRoles();
            if (in_array('must_refresh', $aclRoles->info('cols'))) {
                $currentRole = $aclRoles->find($data['acl_roles_id'])->current();
                $currentRole->must_refresh = '1';
                $currentRole->save();
            }
        }
        
        return $update;

    }
}
