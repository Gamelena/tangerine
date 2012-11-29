<?php

/**
 * Modelo de permisos
 *
 * @category Zwei
 * @package Models
 * @version $Id:$
 * @since 0.1
 *
 */

class PermissionsModel extends Zwei_Db_Table
{
    protected $_name = "web_permissions";

    public function getPermissions()
    {
        $select=new Zend_Db_Table_Select($this);
        $select->from($this->_name, array('id','permission_title'=>'title'));
        return $select;
    }
}

