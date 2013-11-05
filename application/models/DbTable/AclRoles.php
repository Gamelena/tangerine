<?php

class DbTable_AclRoles extends Zwei_Db_Table
{
    protected $_name = 'acl_roles';
    
    protected $_dependentTables = array('DbTable_AclRolesModulesActions', 'DbTable_AclUsers');
}

