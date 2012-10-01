<?php
/**
 * Plugin para escoger modulos y controladores primero en la aplicacion y luego en admportal
 * @author rodrigo
 *
 */

class Zwei_Controller_Plugin_ApplicationPath extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request) 
    {
        $controller = $request->getControllerName();
        $frontController = Zend_Controller_Front::getInstance()->getDispatcher();
        
        if (file_exists(APPLICATION_PATH . '/controllers/' . Zwei_Utils_String::toClassWord($controller).'Controller.php')) {
            $frontController->addControllerDirectory(APPLICATION_PATH . '/controllers');
        } else {
            $frontController->addControllerDirectory(ADMPORTAL_APPLICATION_PATH.'/controllers');
        }
    }   
}
