<?php

class DbTable_WebSettings extends Gamelena_Db_Table
{

    protected $_name = 'web_settings';

    protected $_validateXmlAcl = array('EDIT' => true, 'ADD' => true, 'DELETE' => true, 'LIST' => false);
}

