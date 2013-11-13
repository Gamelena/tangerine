<?php

class DbTable_AclModules extends Zwei_Db_Table
{

    protected $_name = 'acl_modules';

    /**
     * Tablas débiles asociadas,esto emula DRI (declarative referential integrity) en base de datos y permite usar métodos Zend_Db que dependen de esto.
     *
     * NOTA: Si se usa DRI real, por ejemplo si se declaran foreign keys en SQL, esta variable debe eliminarse.
     *
     * @var array
     */
    //protected $_dependentTables = array('DbTable_AclModulesActions', 'DbTable_AclModules');

    /**
     * Mapa relacional, esto emula DRI (declarative referential integrity) en base de datos y permite usar métodos Zend_Db que dependen de esto.
     *
     * NOTA: Si se usa DRI real, por ejemplo si se declaran foreign keys en SQL, esta variable debe eliminarse.
     *
     * @var array
     */
    protected $_referenceMap    = array(
        'acl_modules' => array(
            'columns'           => array('parent_id'),
            'refTableClass'     => 'DbTable_AclModules',
            'refColumns'        => array('id')
        )
    );
}

