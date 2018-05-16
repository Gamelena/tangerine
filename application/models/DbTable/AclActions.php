<?php

class DbTable_AclActions extends Gamelena_Db_Table
{

    protected $_name = 'acl_actions';
    
    protected $_dependentTables = 'DbTable_AclModulesActions';
    
    protected $_validateXmlAcl = array('EDIT' => true, 'ADD' => true, 'DELETE' => true, 'LIST' => false);
}

