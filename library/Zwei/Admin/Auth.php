<?php 
/**
 * Valida sesión por admin web, evitando colisiones de sesión entre diferentes admin mediante flag
 * Zend_Auth::getInstance()->getStorage()->read()->sessionNamespace
 * 
 * @category   Zwei
 * @package    Zwei_Admin
 * @author rodrigo.riquelme@zweicom.com
 *
 */

class Zwei_Admin_Auth
{
     /**
     * Singleton instance
     *
     * @var Zwei_Admin_Auth
     */
    protected static $_instance = null;
    
    /**
     * Singleton pattern implementation makes "new" unavailable
     *
     * @return void
     */
    protected function __construct()
    {}

    /**
     * Singleton pattern implementation makes "clone" unavailable
     *
     * @return void
     */
    protected function __clone()
    {}

    /**
     * Returns an instance of Zwei_Admin_Auth
     *
     * Singleton pattern implementation
     *
     * @return Zwei_Admin_Auth Provides a fluent interface
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
    
    /**
     * Verifying if exists an instance with identity of Zend_Auth 
     * @return boolean
     */
    public function hasIdentity()
    {
        if (!Zend_Auth::getInstance()->hasIdentity()) return false;
        
        $userInfo = Zend_Auth::getInstance()->getStorage()->read();
        $options = Zend_Controller_Front::getInstance()->getParam("bootstrap")->getApplication()->getOptions();
        $config = new Zend_Config($options);
        if (isset($config->zwei->session->namespace)) {
            return (isset($userInfo->sessionNamespace) && $config->zwei->session->namespace == $userInfo->sessionNamespace) ? true : false;
        } else {
            return true;
        }    
    }
    
    /**
     * Authentification params against DB Table
     * @return Zend_Auth_Adapter_DbTable
     */
    public function getAuthAdapter() {
        $resource = Zend_Controller_Front::getInstance()->getParam("bootstrap")->getResource("multidb");
        $dbAdapter = isset($resource) && $resource->getDb("auth") ?
        $resource->getDb("auth") :
        Zend_Db_Table::getDefaultAdapter();
    
        $authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter);
        $authUsersTable = 'acl_users';
        $authUserName = 'user_name';
        $authPassword = 'password';
    
        $authAdapter->setTableName($authUsersTable)
        ->setIdentityColumn($authUserName)
        ->setCredentialColumn($authPassword)
        ->setCredentialTreatment('MD5(?) and approved="1"');
        
        return $authAdapter;
    }
    
    
    public static function initUserInfo($authAdapter) {
        $auth = Zend_Auth::getInstance();
        $userInfo = $authAdapter->getResultRowObject(null, 'password');
        
        $options = Zend_Controller_Front::getInstance()->getParam("bootstrap")->getApplication()->getOptions();
        $config = new Zend_Config($options);
        
        if (isset($config->zwei->session->namespace)) {
            $userInfo->sessionNamespace = $config->zwei->session->namespace;
        }
        
        $authStorage = $auth->getStorage();
        $aclUsersGroupsModel = new AclUsersGroupsModel();
        
        $buffGroups = $aclUsersGroupsModel->findByUserId($userInfo->id);
        $groups = array();
        
        foreach ($buffGroups as $g) {
            $groups[] = $g['id'];
        }
        
        
        $userInfo->groups = $groups;
        $authStorage->write($userInfo);
    }
}

