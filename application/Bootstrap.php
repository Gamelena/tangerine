<?php
/**
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
        
        /**
         * Setup of Autoloader workspace
         */
        $loader = Zend_Loader_Autoloader::getInstance();
        $loader->setFallbackAutoloader(true);
        set_include_path(
            '.'
            . PATH_SEPARATOR . APPLICATION_PATH . '/../library'
            . PATH_SEPARATOR . TANGERINE_APPLICATION_PATH . '/../library'
            . PATH_SEPARATOR . APPLICATION_PATH . '/models'
            . PATH_SEPARATOR . TANGERINE_APPLICATION_PATH . '/models'
            . PATH_SEPARATOR . APPLICATION_PATH . '/forms'
            . PATH_SEPARATOR . TANGERINE_APPLICATION_PATH . '/forms'
            . PATH_SEPARATOR . get_include_path()
        );
        
        /**
         * This allows Inheritance in Zend Controllers using Zend AutoLoader
         */
        $loader = new Zend_Loader_Autoloader_Resource(
            array(
                'namespace' => '',
                'basePath' => TANGERINE_APPLICATION_PATH
            )
        );
        
        $loader->addResourceTypes(
            array(
                array(
                    'type' => 'controllers',
                    'path' => '/controllers',
                    'namespace' => ''
                ),
                array(
                    'type' => 'controllers',
                    'path' => '/modules/elements/controllers',
                    'namespace' => 'Elements_'
                ),
                array(
                    'type' => 'controllers',
                    'path' => '/modules/components/controllers',
                    'namespace' => 'Components_'
                )
            )
        );
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
        
        $eop = (substr(dirname($_SERVER["SCRIPT_NAME"]), -1, 1) === "/") ? '' : '/';
        
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === "on") {
            defined('PROTO') || define('PROTO', 'https://');
        } else {
            defined('PROTO') || define('PROTO', 'http://');
        }
        
        defined('ROOT_DIR') ||
            define('ROOT_DIR', dirname(dirname(__FILE__)));
        
        defined('APPLICATION_PATH') ||
            define('APPLICATION_PATH', ROOT_DIR . '/application');
        
        defined('COMPONENTS_ADMIN_PATH') ||
            define('COMPONENTS_ADMIN_PATH', APPLICATION_PATH.'/components');
            
        defined('BASE_URL') ||
            define('BASE_URL', PROTO . $_SERVER['HTTP_HOST'] . dirname($_SERVER["SCRIPT_NAME"]) . $eop);
        
        // Define application environment
        defined('APPLICATION_ENV') ||
             define(
                 'APPLICATION_ENV',
                 (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production')
             );
        
        $this->_config = $this->getConfig();
        
        defined('TANGERINE_APPLICATION_PATH') ||
            define(
                'TANGERINE_APPLICATION_PATH',
                $this->_config->gamelena->tangerine->applicationPath
            );
        
        //Define ACL root role id
        defined('ROLES_ROOT_ID')
            || define(
                'ROLES_ROOT_ID',
                (isset($this->_config->gamelena->roles->rootId) ? $this->_config->gamelena->roles->rootId : '1' )
            );
    }
    
    /**
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
        Zend_Session::start();
        
        // Inicializar el MVC
        Zend_Layout::startMvc();
        Zend_Layout::getMvcInstance()->disableLayout();
        
        // Run!
        $frontController = Zend_Controller_Front::getInstance();
        //Plugin Timeout de Sesión
        $frontController->registerPlugin(new Gamelena_Controller_Plugin_TimeOutHandler($this->_config));
        
        //Plugin para usar multiples carpetas aplication sobreescribibles
        $frontController->registerPlugin(new Gamelena_Controller_Plugin_ApplicationPath());
        
        //Plugin para cache de páginas
        $frontController->registerPlugin(new Gamelena_Controller_Plugin_Cache($this->_config));
        
        Gamelena_Db_Table::setDefaultLogMode($this->_config->gamelena->db->table->logbook);
        
        try {
            parent::run();
        } catch (Zend_Controller_Dispatcher_Exception $e) {
            throw new Zend_Controller_Dispatcher_Exception(
                "\n\nRevise 'resources.frontController.moduleDirectory' y "
                . "resources.frontController.controllerDirectory' en 'application.ini'\n"
                . $e -> getMessage()
                . $e -> getTraceAsString()
            );
        }
    }
}
