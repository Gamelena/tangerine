<?php 
/**
 * Valida sesión por admin web, evitando colisiones de sesión entre diferentes admin mediante flag
 * Zend_Auth::getInstance()->getStorage()->read()->sessionNamespace
 * 
 * @category Zwei
 * @package  Zwei_Admin
 * @author   rodrigo.riquelme@gamelena.com
 */

class Zwei_Admin_Auth
{
     /**
     * Instancia singleton.
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
    {
    }

    /**
     * Singleton pattern implementation makes "clone" unavailable
     *
     * @return void
     */
    protected function __clone()
    {
    }

    /**
     * Retorna instancia de Zwei_Admin_Auth.
     * Implementación de patrón singleton.
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
     * Verifica instancia con identitad de Zend_Auth.
     * 
     * @return boolean
     */
    public function hasIdentity()
    {
        if (!Zend_Auth::getInstance()->hasIdentity()) { return false; 
        }
        
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
     * Autentificación contra DB.
     * 
     * @return Zend_Auth_Adapter_DbTable
     */
    public function getAuthAdapter($hash = 'MD5')
    {
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
            ->setCredentialColumn($authPassword);
        
        if (!empty($hash)) {
            $authAdapter->setCredentialTreatment($hash.'(?) and approved="1"');
        } else {
            $authAdapter->setCredentialTreatment('? and approved="1"');
        }
        
        
        return $authAdapter;
    }
    
    /**
     * Inicializa la información de sesión con datos del usuario en DB.
     * 
     * @param Zend_Auth_Adapter_DbTable $authAdapter
     */
    public static function initUserInfo($authAdapter)
    {
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
            $groups[] = $g['acl_groups_id'];
        }
        
        
        $userInfo->groups = $groups;
        $authStorage->write($userInfo);
        
        /*Guardar sesion en base de datos, si tabla existe*/
        try {
            $db = Zend_Db_Table::getDefaultAdapter();
            if ($db->describeTable('acl_session')) {
                $aclSession = new AclSessionModel();
                $row = $aclSession->find(Zend_Session::getId())->current();
                if ($row) {
                    $row->acl_users_id = $userInfo->id;
                    $row->acl_roles_id = $userInfo->acl_roles_id;
                    $row->ip = $_SERVER['REMOTE_ADDR'];
                    $row->user_agent = $_SERVER['HTTP_USER_AGENT'];
                    
                    //@todo la siguiente condición "if" existe solo para compatibilidad hacia atras, deprecar cuando sea posible
                    if (in_array('created', $aclSession->info(Zend_Db_Table::COLS))) {
                        $row->created = time();
                    }
                    
                    $row->save();
                } else {
                    if (PHP_SAPI !== 'cli') { //Un Redirector fuera de un controlador mata silenciosamente a phpunit ya que usa exit(), lo evitamos.
                        $r = new Zend_Controller_Action_Helper_Redirector();
                        $r->gotoUrl('/admin/login');
                    }
                }
            }
        } catch (Exception $e) {
            Console::error($e->getMessage(), true);
        } //PDOException is not caught :facepalm:

    }
    
    /**
     * Limpia identidad Zend_Auth
     * @return void
     */
    public function clearIdentity()
    {
        return Zend_Auth::getInstance()->clearIdentity();
    }
}

