<?php

class DbTable_AclRoles extends Zwei_Db_TableLoggeable
{
    protected $_name = 'acl_roles';
    
    //protected $_dependentTables = array('DbTable_AclRolesModulesActions', 'DbTable_AclUsers');
    protected $_validateXmlAcl = array('EDIT' => true, 'ADD' => true, 'DELETE' => true, 'LIST' => false);
}

