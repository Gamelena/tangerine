<?php

class DbTable_AclRolesModulesActions extends Zwei_Db_Table
{

    protected $_name = 'acl_roles_modules_actions';

    /**
     * Mapa relacional, esto emula DRI (declarative referential integrity) en base de datos.
     *
     * NOTA: Si se usa DRI real, por ejemplo si se declaran foreign keys en SQL, esta variable debiera eliminarse.
     *
     * @var array
     */
    protected $_referenceMap  =  array(
        'acl_modules_actions' => array(
            'columns'           => array('acl_modules_id'),
            'refTableClass'     => 'AclModulesModel',
            'refColumns'        => array('id'),
            'onDelete'          => self::CASCADE,
            'onUpdate'          => self::RESTRICT
        ),
        'acl_roles' => array(
            'columns'           => array('acl_roles_id'),
            'refTableClass'     => 'AclRolesModel',
            'refColumns'        => array('id'),
            'onDelete'          => self::CASCADE,
            'onUpdate'          => self::RESTRICT
        )
    );
    
    protected $_validateXmlAcl = array('EDIT' => true, 'ADD' => true, 'DELETE' => true, 'LIST' => false);
}

