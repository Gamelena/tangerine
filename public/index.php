<?php
/**
 * Puerta de entrada a la aplicación,
 * todo requerimiento es procesado por este script en primer lugar
 *
 *
 * @version $Id:$
 * @since 0.1
 *
 */
date_default_timezone_set('America/Lima');

$eop=(substr(dirname($_SERVER["SCRIPT_NAME"]),-1,1) == "/") ? '' : '/';

if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") {
	define('PROTO', 'https://');
}else{
	define('PROTO', 'http://');
}

define('ROOT_DIR', dirname(dirname(__FILE__)));
define('APPLICATION_PATH', ROOT_DIR . '/application');
define('COMPONENTS_ADMIN_PATH', APPLICATION_PATH.'/components');
define('BASE_URL', PROTO.$_SERVER['HTTP_HOST'].dirname($_SERVER["SCRIPT_NAME"]).$eop);
define('TEMPLATE', '');


// Define application environment
defined('APPLICATION_ENV')
|| define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
realpath(APPLICATION_PATH . '/../library'),
get_include_path(),
)));

 
require_once 'Zend/Loader/Autoloader.php';

$loader = Zend_Loader_Autoloader::getInstance();
set_include_path('.'
. PATH_SEPARATOR . ROOT_DIR.'/library'
. PATH_SEPARATOR . APPLICATION_PATH . '/models'
. PATH_SEPARATOR . APPLICATION_PATH . '/forms'
. PATH_SEPARATOR . get_include_path()
);

$loader->setFallbackAutoloader(true);

Zend_Session::start();

// Inicializar el MVC
Zend_Layout::startMvc(array('layoutPath' => ROOT_DIR.'/application/views/layouts'));

// Run!
$frontController = Zend_Controller_Front::getInstance();
$frontController->addControllerDirectory(ROOT_DIR.'/application/controllers');
$frontController->throwExceptions(true);

$config = new Zend_Config_Ini(ROOT_DIR.'/application/configs/application.ini', APPLICATION_ENV);

$db = Zend_Db::factory($config->db);
//$db->getProfiler()->setEnabled(true);//para debug de queries
Zend_Db_Table::setDefaultAdapter($db);


try {
	$frontController->dispatch();
} catch(Exception $e) {
	if ($config->resources->frontController->params->displayExceptions == "1") {
	   echo nl2br($e->__toString());	
	} else {
	   Zwei_Utils_Debug::write(nl2br($e->__toString()));
	}   
}

 