<?php

/**
 * Modelo de datos para usuarios.
 *
 * @category Gamelena
 * @package  Models
 * @version  $Id:$
 * @since    0.1
 */

class AclUsersModel extends DbTable_AclUsers
{
    /**
     * tabla auxiliar
     * @var string
     */
    protected $_name_roles = "acl_roles";
    /**
     * campo con el cual generar la password por default
     * @var string
     */
    protected $_generate_pass = "user_name";

    /**
     * @return Zend_Db_Table_Select
     * @see Zend_Db_Table_Abstract::select()
     */
    public function select($withFromPart = self::SELECT_WITHOUT_FROM_PART)
    {
        $select = new Zend_Db_Table_Select($this);
        $select->setIntegrityCheck(false); //de lo contrario no podemos hacer JOIN
        $select->from($this->_name, array('id', 'user_name', 'acl_roles_id', 'first_names', 'last_names', 'email', 'approved'));
        $select->joinLeft($this->_name_roles, "$this->_name.acl_roles_id = $this->_name_roles.id", "role_name");

        if ($this->_user_info->acl_roles_id != ROLES_ROOT_ID) {
            $select->where('acl_roles_id <> ?', '1');
        }
        $select->order("$this->_name.user_name");

        return $select;
    }

    /**
     * Agrega grupos asociados a rowset original
     * @param Zend_Db_Table_Rowset
     * @return array
     */
    public function overloadDataForm($data)
    {
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
     * @param string $where
     * @return boolean
     * 
     * @see Gamelena_Db_Table::delete()
     */
    public function delete($where)
    {
        $aWhere = $this->whereToArray($where);
        $id = $aWhere['id'];
        $table = new DbTable_AclUsers();

        if ($id == $this->_user_info->id) {
            $this->setMessage("No puede darse de baja usted mismo.");
            return false;
        }

        $delete = $table->delete($where);

        if ($delete) {
            $aWhere = self::whereToArray($where);
            $this->notify($aWhere['id']);
        }

        return $delete;
    }


    /**
     * En el caso de crearse un usuario nuevo y tener seteado cambio de password,
     * se genera la password repitiendo el nombre de usuario en md5
     * @return int
     */
    public function insert(array $data)
    {
        if (!isset($data["password"])) {
            $data["password"] = md5($data[$this->_generate_pass]);
        } else {
            $data["password"] = md5($data["password"]);
        }

        $last_insert_id = false;
        try {
            $last_insert_id = parent::insert($data);
        } catch (Zend_Db_Exception $e) {
            if ($e->getCode() == '23000') {
                $this->setMessage('Nombre de Usuario en uso.');
                return false;
            } else {
                Console::error("error:" . $e->getMessage() . "code");
            }
        }
        return $last_insert_id;
    }
    /**
     * 
     * @param string $userName
     * @param array  $columns
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function findUserName($userName, $columns = array('*'))
    {
        $select = parent::select(false)->setIntegrityCheck(false);
        $select
            ->from($this->info(Zend_Db_Table::NAME), $columns)
            ->where($this->getAdapter()->quoteInto('user_name = ?', $userName));
        return $this->fetchAll($select);
    }

    /**
     * Captura de excepciones posibles como nombre de usuario en uso
     *
     * @param  array  $data
     * @param  string $where
     * @return boolean
     * 
     * @see Gamelena_Db_TableLoggeable::update()
     */
    public function update(array $data, $where)
    {
        try {
            $update = parent::update($data, $where);
        } catch (Zend_Db_Exception $e) {
            if ($e->getCode() == '23000') {
                $this->setMessage('Nombre de Usuario en uso.');
                return false;
            } else {
                Debug::write(array($e->getMessage(), $e->getCode()));
            }
        }

        if ($update) {
            $aWhere = self::whereToArray($where);
            $this->notify($aWhere['id']);
        }

        return $update;

    }
    /*
     * @param string|array $aclusersId
     * @return boolean
     */
    public function notify($aclUsersId)
    {
        $tagsSufix = (array) $aclUsersId;

        //Limpiar cache
        foreach ($tagsSufix as $sufix) {
            $cache = new Gamelena_Controller_Plugin_Cache(Gamelena_Controller_Config::getOptions());
            $cleaned = $cache->cleanByTags(array("userid{$sufix}"));
        }

        if (!(Zend_Session::getSaveHandler() instanceof Zend_Session_SaveHandler_DbTable)) { //@TODO BACKWARD compatibility
            Debug::write('!Zend_Session_SaveHandler_DbTable');
            $aclRoles = new DbTable_AclRoles();
            if (in_array('must_refresh', $aclRoles->info('cols'))) {
                $users = new DbTable_AclUsers();
                $row = $users->find($aclUsersId)->current();

                $currentRole = $aclRoles->find($row->acl_roles_id)->current();

                $currentRole->must_refresh = '1';
                $currentRole->save();
            }

        } else { //END BACKWARD compatibility
            Debug::write('Zend_Session_SaveHandler_DbTable');
            $aclSession = new DbTable_AclSession();

            $data = array('must_refresh' => '1');
            $where = is_array($aclUsersId) ?
                "acl_users_id IN(" . implode(",", $aclUsersId) . ")" : $aclSession->getAdapter()->quoteInto('acl_users_id = ?', $aclUsersId);

            return $aclSession->update($data, $where);
        }
    }
}
