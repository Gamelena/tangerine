<?php
/**
 * Plugin que hace convivir modulos y controladores de la aplicacion final con los en AdmPortal.
 * Primero se busca que estos existan en aplicación final, si no existen se buscan en AdmPortal.
 * 
 * @author rodrigo
 *
 */

class Zwei_Controller_Plugin_ApplicationPath extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request) 
    {
        $controller = $request->getControllerName();
        $module = $request->getModuleName();
        $frontController = Zend_Controller_Front::getInstance();
        //Debug::write("controller:$controller-module:$module");
        
        if (file_exists(APPLICATION_PATH . '/controllers/' . Zwei_Utils_String::toClassWord($controller).'Controller.php')) {
            //Debug::write("Existe ". APPLICATION_PATH . '/controllers/' . Zwei_Utils_String::toClassWord($controller).'Controller.php');
            $frontController->addControllerDirectory(APPLICATION_PATH . '/controllers');
        } else {
            //Debug::write("Buscando ".ADMPORTAL_APPLICATION_PATH . '/controllers/' . Zwei_Utils_String::toClassWord($controller).'Controller.php');
            $frontController->addControllerDirectory(ADMPORTAL_APPLICATION_PATH.'/controllers');
        }
        
        if ($module != "default") {
            if (file_exists(APPLICATION_PATH . '/modules/' . $module . '/controllers/' . Zwei_Utils_String::toClassWord($controller).'Controller.php')) {
                //Debug::write("Existe ". APPLICATION_PATH . '/controllers/' . Zwei_Utils_String::toClassWord($controller).'Controller.php');
                $frontController->addModuleDirectory(APPLICATION_PATH . '/modules');
            } else {
                //Debug::write("Buscando ". ADMPORTAL_APPLICATION_PATH . '/controllers/' . Zwei_Utils_String::toClassWord($controller).'Controller.php');   
                $frontController->addModuleDirectory(ADMPORTAL_APPLICATION_PATH . '/modules');            
            }
        }
    }
}