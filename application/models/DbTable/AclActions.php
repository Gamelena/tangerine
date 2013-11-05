<?php

class DbTable_AclActions extends Zwei_Db_Table
{

    protected $_name = 'acl_actions';
    
    protected $_dependentTables = 'DbTable_AclModulesActions';
}

