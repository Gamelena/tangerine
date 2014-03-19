<?php

class AclSessionOnlineModel extends DbTable_AclSession
{
    public function select()
    {
        $select = parent::select();
        $select->where("acl_users_id <> '0'");
        return $select;
    }
}

