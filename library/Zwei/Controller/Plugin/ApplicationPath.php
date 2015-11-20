<?php
/**
 * Plugin que hace convivir modulos y controladores de la aplicacion final con los en AdmPortal.
 * Primero se busca que estos existan en aplicación final, si no existen se buscan en AdmPortal.
 * 
 * Las rutas modulos deben estar declarados en application.ini de esta forma:
 * 
 * resources.frontController.moduleDirectory[] = {Ruta modulos ZF admportal}
 * resources.frontController.moduleDirectory[] = {Ruta modulos ZF aplicacion}
 * 
 * Hacerlo para cada ambiente si es que las rutas cambian.
 * 
 * @author rodrigo.riquelme@zweicom.com
 * @since 0.9
 * @version 1
 *
 */

class Zwei_Controller_Plugin_ApplicationPath extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request) 
    {
        $controller = $request->getControllerName();
        $module = $request->getModuleName();
        $frontController = Zend_Controller_Front::getInstance();
        
        if (file_exists(APPLICATION_PATH . '/controllers/' . Zwei_Utils_String::toClassWord($controller, "-").'Controller.php')) {
<<<<<<< HEAD
//             Console::log("Existe ". APPLICATION_PATH . '/controllers/' . Zwei_Utils_String::toClassWord($controller, "-").'Controller.php');
            $frontController->addControllerDirectory(APPLICATION_PATH . '/controllers');
        } else {
//             Console::log("Buscando ".ADMPORTAL_APPLICATION_PATH . '/controllers/' . Zwei_Utils_String::toClassWord($controller).'Controller.php');
=======
            //Debug::write("Existe ". APPLICATION_PATH . '/controllers/' . Zwei_Utils_String::toClassWord($controller, "-").'Controller.php');
            $frontController->addControllerDirectory(APPLICATION_PATH . '/controllers');
        } else {
            //Debug::write("Buscando ".ADMPORTAL_APPLICATION_PATH . '/controllers/' . Zwei_Utils_String::toClassWord($controller).'Controller.php');
>>>>>>> f306af8cbc860e73b2c8de2e6c526d3db946b5d4
            $frontController->addControllerDirectory(ADMPORTAL_APPLICATION_PATH.'/controllers');
        }
        
        if ($module != "default") {
            if (file_exists(APPLICATION_PATH . '/modules/' . $module . '/controllers/' . Zwei_Utils_String::toClassWord($controller, "-").'Controller.php')) {
<<<<<<< HEAD
//                 Console::log("Existe ". APPLICATION_PATH . '/modules/' . $module . '/controllers/' . Zwei_Utils_String::toClassWord($controller, "-").'Controller.php');
                $frontController->addModuleDirectory(APPLICATION_PATH . '/modules');
            } else {
//                 Console::log("Buscando ". ADMPORTAL_APPLICATION_PATH . '/controllers/' . Zwei_Utils_String::toClassWord($controller, "-").'Controller.php');   
=======
                //Debug::write("Existe ". APPLICATION_PATH . '/modules/' . $module . '/controllers/' . Zwei_Utils_String::toClassWord($controller, "-").'Controller.php');
                $frontController->addModuleDirectory(APPLICATION_PATH . '/modules');
            } else {
                //Debug::write("Buscando ". ADMPORTAL_APPLICATION_PATH . '/controllers/' . Zwei_Utils_String::toClassWord($controller, "-").'Controller.php');   
>>>>>>> f306af8cbc860e73b2c8de2e6c526d3db946b5d4
                $frontController->addModuleDirectory(ADMPORTAL_APPLICATION_PATH . '/modules');            
            }
        }
    }
}
