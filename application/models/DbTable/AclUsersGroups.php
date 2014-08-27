<?php

class DbTable_AclUsersGroups extends Zwei_Db_Table
{

    protected $_name = 'acl_users_groups';

    protected $_referenceMap = array(
        'acl_users' => array(
            'columns'           => array('acl_users_id'),
            'refTableClass'     => 'DbTable_AclUsers',
            'refColumns'        => array('id')
        ),
        'acl_groups' => array(
            'columns'           => array('acl_groups_id'),
            'refTableClass'     => 'DbTable_AclGroups',
            'refColumns'        => array('id')
        )
    );
}

