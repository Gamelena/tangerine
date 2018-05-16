<?php
class DbTable_WebIcons extends Gamelena_Db_Table
{
    protected $_name = 'web_icons';

    protected $_validateXmlAcl = array(
            'EDIT' => true,
            'ADD' => true,
            'DELETE' => true,
            'LIST' => false
    );
}

