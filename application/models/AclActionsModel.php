<?php

class AclActionsModel extends DbTable_AclActions
{
    public function delete($where)
    {
        $table = new DbTable_AclActions();
        return $table->delete($where);
    }

}

