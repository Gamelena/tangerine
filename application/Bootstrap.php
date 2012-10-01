<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    
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
        defined('TEMPLATE') || define('TEMPLATE', '');//si es 'urban' se encontrará un huevito de pascua (en desarrollo) 
        
        // Define application environment
        defined('APPLICATION_ENV')
        || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));
    }
    
    public function run()
    {
        $this->loadConstants();
        
        require_once 'Zend/Loader/Autoloader.php';

        $loader = Zend_Loader_Autoloader::getInstance();
        $loader->setFallbackAutoloader(true);
        
        set_include_path('.'
            . PATH_SEPARATOR . ROOT_DIR.'/library'
            . PATH_SEPARATOR . APPLICATION_PATH . '/models'
            . PATH_SEPARATOR . APPLICATION_PATH . '/forms'
            . PATH_SEPARATOR . get_include_path()
        );
        
        
        try {
            Zend_Session::start();
        } catch (Zend_Session_Exception $e) {
            session_start();
            Zwei_Utils_Debug::write($e->getCode()." ".$e->getMessage());
            //Zend_Session::start();
        }    
        
        // Inicializar el MVC
        Zend_Layout::startMvc(array('layoutPath' => ROOT_DIR.'/application/views/layouts'));
        
        $config = new Zend_Config_Ini(ROOT_DIR.'/application/configs/application.ini', APPLICATION_ENV);
        if (isset($config->zwei->date->defaultTimezone)) date_default_timezone_set($config->zwei->date->defaultTimezone);
        defined('ADMPORTAL_APPLICATION_PATH') || define('ADMPORTAL_APPLICATION_PATH', $config->zwei->admportal->applicationPath);
        // Run!
        
        $frontController = Zend_Controller_Front::getInstance();
        //Plugin Timeout de Sesión
        $frontController->registerPlugin(new Zwei_Controller_Plugin_TimeOutHandler());
        
        //Plugin para usar multiples carpetas aplication sobreescribibles
        $frontController->registerPlugin(new Zwei_Controller_Plugin_ApplicationPath());       
        
        $frontController->addModuleDirectory(ROOT_DIR.'/application/modules');
        
        //$frontController->throwExceptions(true);
        
        $db = Zend_Db::factory($config->resources->db);
        
        Zwei_Db_Table::setDefaultAdapter($db);
        Zwei_Db_Table::setDefaultLogMode($config->zwei->db->table->logbook);
        
        
        $backendOpt = array('cache_dir' => ROOT_DIR .'/cache');
        $frontendOpt = array('lifetime' => 600);
        //Debug::write($config->zwei->admportal->applicationPath.'/controllers');
        $cache = new Zwei_Utils_Cache($backendOpt, $frontendOpt);
        $cache->start();
        
        if (!$cache->isStarted()) {
            try {
                $frontController->dispatch();
            } catch(Exception $e) {
                if ($config->resources->frontController->params->displayExceptions == "1") {
                   echo nl2br($e->__toString());    
                } else {
                   Zwei_Utils_Debug::write(nl2br($e->__toString()));
                }   
            }
        
            if ($cache->check()) {
                $cache->end();
            }
        } else {
            Zwei_Utils_Debug::write( "Está en cache:".@$_SERVER['PATH_INFO'] . @$_REQUEST['p'] );
        }
        
        //parent::run();
    }
}

