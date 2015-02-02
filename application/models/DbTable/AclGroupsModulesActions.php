<?php

class DbTable_AclGroupsModulesActions extends Zwei_Db_Table
{

    protected $_name = 'acl_groups_modules_actions';

    /**
     * Mapa relacional, esto emula DRI (declarative referential integrity) en base de datos.
     *
     * NOTA: Si se usa DRI real, por ejemplo si se declaran foreign keys en SQL, esta variable debiera eliminarse.  
     * 
     * @var array 
     */
    protected $_referenceMap  =  array(
        'acl_modules_actions' => array(
            'columns'           => array('acl_modules_actions_id'),
            'refTableClass'     => 'DbTable_AclModulesActions',
            'refColumns'        => array('id'),
            'onDelete'          => self::CASCADE,
            'onUpdate'          => self::CASCADE
        ),
        'acl_groups' => array(
            'columns'           => array('acl_groups_id'),
            'refTableClass'     => 'DbTable_AclGroups',
            'refColumns'        => array('id'),
            'onDelete'          => self::CASCADE,
            'onUpdate'          => self::CASCADE
        )
    );
    
//     protected $_validateXmlAcl = array('EDIT' => true, 'ADD' => true, 'DELETE' => true, 'LIST' => false);
}

