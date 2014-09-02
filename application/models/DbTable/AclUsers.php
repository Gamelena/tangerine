<?php

class DbTable_AclUsers extends Zwei_Db_TableLoggeable
{

    protected $_name = 'acl_users';
    /**
     * Mapa relacional, esto emula DRI (declarative referential integrity) en base de datos.
     *
     * NOTA: Si se usa DRI real, por ejemplo si se declaran foreign keys en SQL, esta variable debiera eliminarse.
     *
     * @var array
     */
    //protected $_dependentTables = array('DbTable_AclUsersGroups');
    
    
    protected $_referenceMap = array(
        'acl_roles' => array(
            'columns'           => array('acl_roles_id'),
            'refTableClass'     => 'DbTable_AclRoles',
            'refColumns'        => array('id')
        )
    );
    
    protected $_validateXmlAcl = array('EDIT' => true, 'ADD' => true, 'DELETE' => true, 'LIST' => false);
}

