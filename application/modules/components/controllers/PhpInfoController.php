<?php
/**
 * Controlador de despliegue de PHP Info
 * 
 * @author rodrigo.riquelme@zweicom.com
 *
 */
class Components_PhpInfoController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }
    
    /**
     * Acción index
     */
    public function indexAction()
    {
        $out = '';
        ob_start();
        phpinfo(INFO_GENERAL);
        $phpinfo = preg_replace('#<!DOCTYPE.+?<body>#is','',ob_get_clean());
        $out .= str_replace('</body></html>','',$phpinfo);
        
        ob_start();
        phpinfo(INFO_CONFIGURATION);
        $phpinfo = preg_replace('#<!DOCTYPE.+?<body>#is','',ob_get_clean());
        $out .= str_replace('</body></html>','',$phpinfo);
        
        $this->view->info = $out;
    }
}

