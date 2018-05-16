<?php

class DbTable_LogBook extends Gamelena_Db_Table
{

    protected $_name = 'log_book';

    protected $_validateXmlAcl = array(
            'EDIT' => true,
            'ADD' => true,
            'DELETE' => true,
            'LIST' => false
    );
}

