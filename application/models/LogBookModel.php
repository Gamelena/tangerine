<?php
class LogBookModel extends DbTable_LogBook
{
    public function select()
    {
        $select = new Zend_Db_Table_Select($this);
        if ($this->_user_info->acl_roles_id != ROLES_ROOT_ID) {
            $select->where('acl_roles_id <> ?', '1');
        }
        return $select;
    }
}
