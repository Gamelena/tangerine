<?php
/**
 * Valida sesión por admin web, evitando colisiones de sesión entre diferentes admin mediante flag
 * Zend_Auth::getInstance()->getStorage()->read()->sessionNamespace
 * 
 * @category Gamelena
 * @package  Gamelena_Admin
 * @author   rodrigo.riquelme@gamelena.com
 */

class Gamelena_Admin_Auth
{
    /**
     * Instancia singleton.
     *
     * @var Gamelena_Admin_Auth
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
     * Retorna instancia de Gamelena_Admin_Auth.
     * Implementación de patrón singleton.
     *
     * @return Gamelena_Admin_Auth Provides a fluent interface
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
        if (!Zend_Auth::getInstance()->hasIdentity()) {
            return false;
        }

        $userInfo = Zend_Auth::getInstance()->getStorage()->read();
        $options = Zend_Controller_Front::getInstance()->getParam("bootstrap")->getApplication()->getOptions();
        $config = new Zend_Config($options);
        if (isset($config->gamelena->session->namespace)) {
            return (isset($userInfo->sessionNamespace) && $config->gamelena->session->namespace == $userInfo->sessionNamespace) ? true : false;
        } else {
            return true;
        }
    }

    /**
     * Autentificación contra DB.
     * 
     * @return Zend_Auth_Adapter_Interface
     */
    public function getAuthAdapter()
    {
        // Return our custom adapter
        // We don't need identity/credential column setup here as the adapter handles it 
        // using the Model directly.
        // However, the caller of this method (AdminController::loginAction) sets identity and credential *after* getting the adapter.
        // But my new adapter takes them in constructor. 
        // Wait, AdminController sets them later: $authAdapter->setIdentity($username)->setCredential($password);
        // Standard Zend adapters allow late setting. My custom one should too or I need to change AdminController.
        // The interface Zend_Auth_Adapter_Interface only requires authenticate().
        // But standard practice in ZF1 is setIdentity/setCredential.
        // To minimize impact on AdminController, I'll make my adapter compatible with setIdentity/setCredential pattern
        // OR I'll update AdminController.
        // Looking at AdminController: 
        // $authAdapter = ...->getAuthAdapter();
        // $authAdapter->setIdentity($username)->setCredential($password);
        //
        // So my new adapter needs those methods if I want to keep AdminController as is.
        // Or I return a dummy wrapper? No, easier to implement setIdentity/setCredential in Gamelena_Auth_Adapter_Bcrypt.

        return new Gamelena_Auth_Adapter_Bcrypt();
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

        if (isset($config->gamelena->session->namespace)) {
            $userInfo->sessionNamespace = $config->gamelena->session->namespace;
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
                    $row->created = time();

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

