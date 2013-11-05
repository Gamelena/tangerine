<?php

class DbTable_AclGroups extends Zwei_Db_Table
{

    protected $_name = 'acl_groups';
    
    protected $_dependentTables = array('DbTable_AclUsersGroups', 'DbTable_AclGroupsModulesActions');
}

