<?php
/**
 *
 * Gestor de arranque de la aplicación.
 *
 */
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    /**
     *
     * @var Zend_Config
     */
    protected $_config;
    
    /**
     * Inicia el autoloader de clases.
     * @return void
     */
    protected function _initAutoLoad()
    {
        require_once 'Zend/Loader/Autoloader.php';
        $this->loadConstants();
        
        $loader = Zend_Loader_Autoloader::getInstance();
        $loader->setFallbackAutoloader(true);
        set_include_path('.'
            . PATH_SEPARATOR . APPLICATION_PATH.'/../library'
            . PATH_SEPARATOR . ADMPORTAL_APPLICATION_PATH . '/../library'
            . PATH_SEPARATOR . APPLICATION_PATH . '/models'
            . PATH_SEPARATOR . ADMPORTAL_APPLICATION_PATH . '/models'
            . PATH_SEPARATOR . APPLICATION_PATH . '/forms'
            . PATH_SEPARATOR . ADMPORTAL_APPLICATION_PATH . '/forms'
            . PATH_SEPARATOR . get_include_path()
        );
        //$loader->pushAutoloader(new Zwei_Autoloader_PhpThumb());
    }
    
    /**
     * Carga constantes globales
     * @return void
     */
    public function loadConstants()
    {
        if (!defined('PHP_VERSION_ID')) {
            $version = explode('.', PHP_VERSION);
            define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
        }
        
        $eop = (substr(dirname($_SERVER["SCRIPT_NAME"]),-1,1) == "/") ? '' : '/';
        
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") {
            defined('PROTO') || define('PROTO', 'https://');
        } else {
            defined('PROTO') || define('PROTO', 'http://');
        }
        
        defined('ROOT_DIR') || define('ROOT_DIR', dirname(dirname(__FILE__)));
        defined('APPLICATION_PATH') || define('APPLICATION_PATH', ROOT_DIR . '/application');
        defined('COMPONENTS_ADMIN_PATH') || define('COMPONENTS_ADMIN_PATH', APPLICATION_PATH.'/components');
        defined('BASE_URL') || define('BASE_URL', PROTO.$_SERVER['HTTP_HOST'].dirname($_SERVER["SCRIPT_NAME"]).$eop);
        defined('TEMPLATE') || define('TEMPLATE', '');//template alternativo [TODO] esto es para desarrollo futuro

        
        // Define application environment
        defined('APPLICATION_ENV')
        || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));
        
        $this->_config = $this->getConfig();
        if (isset($this->_config->zwei->date->defaultTimezone)) date_default_timezone_set($this->_config->zwei->date->defaultTimezone);
        
        defined('ADMPORTAL_APPLICATION_PATH') || define('ADMPORTAL_APPLICATION_PATH', $this->_config->zwei->admportal->applicationPath);
        
        //Define ACL root role id
        defined('ROLES_ROOT_ID')
        || define('ROLES_ROOT_ID', (isset($this->_config->zwei->roles->rootId) ? $this->_config->zwei->roles->rootId : '1'));
    }
    
    /**
     * 
     * @return Zend_Config_Ini
     */
    public function getConfig()
    {
        return new Zend_Config($this->getOptions());
    }
    
    /**
     * Corre la aplicación.
     * @return void
     */
    public function run()
    {
        try {
            Zend_Session::start();
        } catch (Zend_Session_Exception $e) {
            session_start();
            Zwei_Utils_Debug::write($e->getCode()." ".$e->getMessage());
            //Zend_Session::start();
        }    
        
        // Inicializar el MVC
        Zend_Layout::startMvc();
        Zend_Layout::getMvcInstance()->disableLayout();
        
        // Run!
        $frontController = Zend_Controller_Front::getInstance();
        //Plugin Timeout de Sesión
        $frontController->registerPlugin(new Zwei_Controller_Plugin_TimeOutHandler($this->_config));
        
        //Plugin para usar multiples carpetas aplication sobreescribibles
        $frontController->registerPlugin(new Zwei_Controller_Plugin_ApplicationPath());       
        
        //Plugin para cache de páginas
        $frontController->registerPlugin(new Zwei_Controller_Plugin_Cache($this->_config));
        
        $frontController->throwExceptions(true);
        
        Zwei_Db_Table::setDefaultAdapter($db);
        Zwei_Db_Table::setDefaultLogMode($this->_config->zwei->db->table->logbook);
        
        
        parent::run();
    }
}

