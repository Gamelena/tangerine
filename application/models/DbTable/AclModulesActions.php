<?php

class DbTable_AclModulesActions extends Gamelena_Db_Table
{

    protected $_name = 'acl_modules_actions';
    
    protected $_dependentTables = array('DbTable_AclGroupsModulesActions', 'DbTable_AclRolesModulesActions');
    /**
     * Mapa relacional, esto emula DRI (declarative referential integrity) en base de datos y permite usar métodos Zend_Db que dependen de esto.
     *
     * NOTA: Si se usa DRI real, por ejemplo si se declaran foreign keys en SQL, esta variable debe eliminarse.
     *
     * @var array
     */
    protected $_referenceMap    = array(
        'acl_modules' => array(
            'columns'           => array('acl_modules_id'),
            'refTableClass'     => 'DbTable_AclModules',
            'refColumns'        => array('id'),
            'onDelete'          => self::CASCADE,
            'onUpdate'          => self::RESTRICT
        ),
        'acl_actions' => array(
            'columns'           => array('acl_actions_id'),
            'refTableClass'     => 'DbTable_AclActions',
            'refColumns'        => array('id'),
            'onDelete'          => self::CASCADE,
            'onUpdate'          => self::RESTRICT
        )
    );
    
    protected $_validateXmlAcl = array('EDIT' => true, 'ADD' => true, 'DELETE' => true, 'LIST' => false);
}

