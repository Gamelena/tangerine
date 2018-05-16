<?php

class DbTable_AclSession extends Gamelena_Db_Table
{

    protected $_name = 'acl_session';
    
    protected $_validateXmlAcl = array('EDIT' => true, 'ADD' => true, 'DELETE' => true, 'LIST' => true);


}

