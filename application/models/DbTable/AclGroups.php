<?php

class DbTable_AclGroups extends Gamelena_Db_TableLoggeable
{

    protected $_name = 'acl_groups';
    
    protected $_dependentTables = array('DbTable_AclUsersGroups', 'DbTable_AclGroupsModulesActions');
    
    protected $_validateXmlAcl = array('EDIT' => true, 'ADD' => true, 'DELETE' => true, 'LIST' => false);
}

