<?php

/**
 * Modelo de datos personales, requiere sesion ACL iniciada
 *
 * @category Gamelena
 * @package  Models
 * @version  $Id:$
 * @since    0.1
 */

class PersonalInfoModel extends Gamelena_Db_Table
{
    protected $_name = "acl_users";
    protected $_name_roles = "acl_roles";
    protected $_generate_pass = "user_name";

    public function select()
    {
        $oSelect=new Zend_Db_Table_Select($this);
        $oSelect->setIntegrityCheck(false); //de lo contrario no podemos hacer JOIN
        $oSelect->from($this->_name, array('id', 'user_name', 'acl_roles_id', 'first_names', 'last_names', 'email', 'approved'))
            ->joinLeft($this->_name_roles, "$this->_name.acl_roles_id = $this->_name_roles.id", "role_name");
        $oSelect->where($this->getAdapter()->quoteInto('user_name = ?', $this->_user_info->user_name));
        return $oSelect;
    }

    /**
     * En el caso de crearse un usuario nuevo,
     * se genera la password repitiendo el nombre de usuario en md5
     * @return int
     */
    public function insert($data)
    {
        $data["password"] = md5($data[$this->_generate_pass]);
        try {
            $last_insert_id = parent::insert($data);
        } catch(Zend_Db_Exception $e) {
            if ($e->getCode() == '23000') {
                $this->setMessage('Nombre de Usuario en uso.');
                return false;
            } else {
                Gamelena_Utils_Debug::write("error:".$e->getMessage()."code".$e->getCode());
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
                Gamelena_Utils_Debug::write("error:".$e->getMessage()."code".$e->getCode());
            }
        }
        return $update;
    }
}
